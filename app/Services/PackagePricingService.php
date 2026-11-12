<?php

namespace App\Services;

use App\Models\PackageCostModel;
use CodeIgniter\Database\BaseConnection;

class PackagePricingService
{
    const TIERS = ['sharing', 'quint', 'quad', 'triple', 'double'];

    private $db;

    public function __construct(BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function summarizePackage(int $packageId): array
    {
        if ($packageId < 1) {
            return $this->emptySummary();
        }

        $package = $this->db->table('packages')
            ->select('id, total_seats, include_hotel, include_ticket, include_transport, include_ziarat, ziarat_cost, purchase_price_visa, exchange_rate, profit_mode, profit_value')
            ->where('id', $packageId)
            ->get()
            ->getRowArray();

        if (empty($package)) {
            return $this->emptySummary();
        }

        $hotelRows = $this->db->table('package_hotels')
            ->select('id, hotel_id, room_type, cost_amount, sharing_cost, quint_cost, quad_cost, triple_cost, double_cost')
            ->where('package_id', $packageId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $flightRows = $this->db->table('package_flights')
            ->select('cost_amount')
            ->where('package_id', $packageId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $transportRows = $this->db->table('package_transports')
            ->select('cost_amount')
            ->where('package_id', $packageId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->buildSummary($package, $hotelRows, $flightRows, $transportRows);
    }

    public function recalculatePackage(int $packageId): array
    {
        $summary = $this->summarizePackage($packageId);
        if (($summary['package_id'] ?? 0) < 1) {
            return $summary;
        }

        $costModel = new PackageCostModel();
        $existingRows = $costModel->where('package_id', $packageId)->findAll();

        $targetRows = [];
        if ($summary['mode'] === 'flat') {
            $flatPrice = (float) ($summary['flat_price'] ?? 0);
            $seatLimit = $summary['flat_seats_limit'];
            foreach (self::TIERS as $tier) {
                $targetRows[$tier] = [
                    'package_id' => $packageId,
                    'cost_type' => $tier,
                    'cost_amount' => $flatPrice,
                    'seats_limit' => $seatLimit,
                ];
            }
        } else {
            // Seats are a single package-level pool, so every tier carries the same limit.
            $seatLimit = $summary['flat_seats_limit'];
            foreach ($summary['price_map'] as $tier => $amount) {
                $normalizedTier = $this->normalizeTier((string) $tier);
                if ($normalizedTier === null) {
                    continue;
                }

                $targetRows[$normalizedTier] = [
                    'package_id' => $packageId,
                    'cost_type' => $normalizedTier,
                    'cost_amount' => (float) $amount,
                    'seats_limit' => $seatLimit,
                ];
            }
        }

        foreach ($existingRows as $row) {
            $tier = $this->normalizeTier((string) ($row['cost_type'] ?? ''));
            if ($tier === null || ! isset($targetRows[$tier])) {
                $costModel->delete((int) ($row['id'] ?? 0));
                continue;
            }

            $costModel->update((int) $row['id'], $targetRows[$tier]);
            unset($targetRows[$tier]);
        }

        foreach ($targetRows as $insertRow) {
            $costModel->insert($insertRow + ['created_at' => date('Y-m-d H:i:s')]);
        }

        return $this->summarizePackage($packageId);
    }

    public function normalizeRequestedTier(int $packageId, string $requestedTier): string
    {
        $summary = $this->summarizePackage($packageId);
        if (($summary['mode'] ?? 'tiered') === 'flat') {
            return 'sharing';
        }

        $tier = $this->normalizeTier($requestedTier);
        return $tier ?? 'sharing';
    }

    private function buildSummary(array $package, array $hotelRows, array $flightRows, array $transportRows): array
    {
        $packageId = (int) ($package['id'] ?? 0);
        $includeHotel = (int) ($package['include_hotel'] ?? 1) === 1;
        $includeTicket = (int) ($package['include_ticket'] ?? 1) === 1;
        $includeTransport = (int) ($package['include_transport'] ?? 1) === 1;
        $includeZiarat = (int) ($package['include_ziarat'] ?? 0) === 1;

        $hotelTotals = [];
        $uniqueHotelRows = [];
        foreach ($hotelRows as $row) {
            $uniqueKey = implode('|', [
                (int) ($row['hotel_id'] ?? 0),
                round((float) ($row['sharing_cost'] ?? 0), 2),
                round((float) ($row['quint_cost'] ?? 0), 2),
                round((float) ($row['quad_cost'] ?? 0), 2),
                round((float) ($row['triple_cost'] ?? 0), 2),
                round((float) ($row['double_cost'] ?? 0), 2),
                round((float) ($row['cost_amount'] ?? 0), 2),
                strtolower(trim((string) ($row['room_type'] ?? ''))),
            ]);

            if (! isset($uniqueHotelRows[$uniqueKey])) {
                $uniqueHotelRows[$uniqueKey] = $row;
            }
        }

        foreach ($uniqueHotelRows as $row) {
            foreach (self::TIERS as $tier) {
                $field = $tier . '_cost';
                $amount = array_key_exists($field, $row) ? (float) ($row[$field] ?? 0) : 0.0;

                if ($amount <= 0) {
                    $legacyTier = $this->normalizeTier((string) ($row['room_type'] ?? ''));
                    if ($legacyTier === $tier) {
                        $amount = (float) ($row['cost_amount'] ?? 0);
                    }
                }

                if ($amount <= 0) {
                    continue;
                }

                if (! isset($hotelTotals[$tier])) {
                    $hotelTotals[$tier] = 0.0;
                }

                $hotelTotals[$tier] += $amount;
            }
        }

        // Flight/ticket costs are entered in PKR; hotel, transport and visa costs in SAR.
        $flightTotal = 0.0;
        foreach ($flightRows as $row) {
            $flightTotal += (float) ($row['cost_amount'] ?? 0);
        }

        $transportTotal = 0.0;
        foreach ($transportRows as $row) {
            $transportTotal += (float) ($row['cost_amount'] ?? 0);
        }
        $visaTotal = (float) ($package['purchase_price_visa'] ?? 0);

        if (! $includeTicket) {
            $flightTotal = 0.0;
        }
        if (! $includeTransport) {
            $transportTotal = 0.0;
        }
        if (! $includeHotel) {
            $hotelTotals = [];
        }

        $exchangeRate = (float) ($package['exchange_rate'] ?? 0);
        if ($exchangeRate <= 0) {
            $exchangeRate = 1.0;
        }

        // Ziarat (holy sites visit) is a SAR cost that only applies when opted in.
        $ziaratTotal = $includeZiarat ? (float) ($package['ziarat_cost'] ?? 0) : 0.0;

        $mode = $includeHotel ? 'tiered' : 'flat';
        $sarExtrasPkr = ($transportTotal + $visaTotal + $ziaratTotal) * $exchangeRate;
        $flatExtras = $flightTotal + $sarExtrasPkr;
        $priceMap = [];

        // Company profit is either a PKR amount per pilgrim or a percentage of the cost.
        $profitMode = strtolower(trim((string) ($package['profit_mode'] ?? 'amount'))) === 'percent' ? 'percent' : 'amount';
        $profitValue = max(0.0, (float) ($package['profit_value'] ?? 0));
        $profitFor = static function (float $costPkr) use ($profitMode, $profitValue): float {
            if ($costPkr <= 0 || $profitValue <= 0) {
                return 0.0;
            }

            return round($profitMode === 'percent' ? ($costPkr * ($profitValue / 100)) : $profitValue, 2);
        };
        $profitMap = [];

        if ($mode === 'tiered') {
            foreach (self::TIERS as $tier) {
                if (! array_key_exists($tier, $hotelTotals)) {
                    continue;
                }

                $tierCost = (float) $hotelTotals[$tier] * $exchangeRate + $flatExtras;
                $tierProfit = $profitFor($tierCost);
                $profitMap[$tier] = $tierProfit;
                $priceMap[$tier] = round($tierCost + $tierProfit, 2);
            }
        }

        $flatPrice = null;
        if ($mode === 'flat') {
            $flatProfit = $profitFor($flatExtras);
            $profitMap['flat'] = $flatProfit;
            $flatPrice = round($flatExtras + $flatProfit, 2);
        }

        // Seats are one pool per package regardless of mode/tier.
        $packageTotalSeats = (int) ($package['total_seats'] ?? 0);
        $flatSeatLimit = $packageTotalSeats > 0 ? $packageTotalSeats : null;
        $seatLimitMap = $packageTotalSeats > 0 ? array_fill_keys(self::TIERS, $packageTotalSeats) : [];

        return [
            'package_id' => $packageId,
            'mode' => $mode,
            'exchange_rate' => $exchangeRate,
            'hotel_totals' => $hotelTotals,
            'flight_total' => round($flightTotal, 2),
            'transport_total_sar' => round($transportTotal, 2),
            'visa_total_sar' => round($visaTotal, 2),
            'ziarat_total_sar' => round($ziaratTotal, 2),
            'transport_total' => round($transportTotal * $exchangeRate, 2),
            'visa_total' => round($visaTotal * $exchangeRate, 2),
            'ziarat_total' => round($ziaratTotal * $exchangeRate, 2),
            'price_map' => $priceMap,
            'flat_price' => $flatPrice,
            'profit_mode' => $profitMode,
            'profit_value' => round($profitValue, 2),
            'profit_map' => $profitMap,
            'has_pricing' => $mode === 'flat' ? ($flatPrice !== null && $flatPrice > 0) : $priceMap !== [],
            'flat_seats_limit' => $flatSeatLimit,
            'seat_limit_map' => $seatLimitMap,
            'include_hotel' => $includeHotel,
            'include_ticket' => $includeTicket,
            'include_transport' => $includeTransport,
            'include_ziarat' => $includeZiarat,
        ];
    }

    private function normalizeTier(string $value)
    {
        $tier = strtolower(trim($value));
        return in_array($tier, self::TIERS, true) ? $tier : null;
    }

    private function emptySummary(): array
    {
        return [
            'package_id' => 0,
            'mode' => 'tiered',
            'exchange_rate' => 1.0,
            'hotel_totals' => [],
            'flight_total' => 0.0,
            'transport_total_sar' => 0.0,
            'visa_total_sar' => 0.0,
            'ziarat_total_sar' => 0.0,
            'transport_total' => 0.0,
            'visa_total' => 0.0,
            'ziarat_total' => 0.0,
            'price_map' => [],
            'flat_price' => null,
            'profit_mode' => 'amount',
            'profit_value' => 0.0,
            'profit_map' => [],
            'has_pricing' => false,
            'flat_seats_limit' => null,
            'seat_limit_map' => [],
            'include_hotel' => true,
            'include_ticket' => true,
            'include_transport' => true,
            'include_ziarat' => false,
        ];
    }
}
