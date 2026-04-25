<?php

namespace App\Services;

use App\Models\PackageCostModel;
use CodeIgniter\Database\BaseConnection;

class PackagePricingService
{
    const TIERS = ['sharing', 'quad', 'triple', 'double'];

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
            ->select('id, total_seats, include_hotel, include_ticket, include_transport')
            ->where('id', $packageId)
            ->get()
            ->getRowArray();

        if (empty($package)) {
            return $this->emptySummary();
        }

        $existingCosts = $this->db->table('package_costs')
            ->select('cost_type, seats_limit, id')
            ->where('package_id', $packageId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $seatLimitMap = [];
        foreach ($existingCosts as $costRow) {
            $tier = $this->normalizeTier((string) ($costRow['cost_type'] ?? ''));
            if ($tier === null || isset($seatLimitMap[$tier])) {
                continue;
            }

            $seatLimitMap[$tier] = $costRow['seats_limit'] !== null ? (int) $costRow['seats_limit'] : null;
        }

        $hotelRows = $this->db->table('package_hotels')
            ->select('id, hotel_id, room_type, cost_amount, sharing_cost, quad_cost, triple_cost, double_cost')
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

        return $this->buildSummary($package, $hotelRows, $flightRows, $transportRows, $seatLimitMap);
    }

    public function recalculatePackage(int $packageId): array
    {
        $summary = $this->summarizePackage($packageId);
        if (($summary['package_id'] ?? 0) < 1) {
            return $summary;
        }

        $costModel = new PackageCostModel();
        $existingRows = $costModel->where('package_id', $packageId)->findAll();

        $preservedSeatLimits = [];
        foreach ($existingRows as $row) {
            $tier = $this->normalizeTier((string) ($row['cost_type'] ?? ''));
            if ($tier === null || isset($preservedSeatLimits[$tier])) {
                continue;
            }

            $preservedSeatLimits[$tier] = $row['seats_limit'] !== null ? (int) $row['seats_limit'] : null;
        }

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
            foreach ($summary['price_map'] as $tier => $amount) {
                $normalizedTier = $this->normalizeTier((string) $tier);
                if ($normalizedTier === null) {
                    continue;
                }

                $targetRows[$normalizedTier] = [
                    'package_id' => $packageId,
                    'cost_type' => $normalizedTier,
                    'cost_amount' => (float) $amount,
                    'seats_limit' => $preservedSeatLimits[$normalizedTier] ?? null,
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

    private function buildSummary(array $package, array $hotelRows, array $flightRows, array $transportRows, array $seatLimitMap): array
    {
        $packageId = (int) ($package['id'] ?? 0);
        $includeHotel = (int) ($package['include_hotel'] ?? 1) === 1;
        $includeTicket = (int) ($package['include_ticket'] ?? 1) === 1;
        $includeTransport = (int) ($package['include_transport'] ?? 1) === 1;

        $hotelTotals = [];
        $uniqueHotelRows = [];
        foreach ($hotelRows as $row) {
            $uniqueKey = implode('|', [
                (int) ($row['hotel_id'] ?? 0),
                round((float) ($row['sharing_cost'] ?? 0), 2),
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

        $flightTotal = 0.0;
        foreach ($flightRows as $row) {
            $flightTotal += (float) ($row['cost_amount'] ?? 0);
        }

        $transportTotal = 0.0;
        foreach ($transportRows as $row) {
            $transportTotal += (float) ($row['cost_amount'] ?? 0);
        }

        if (! $includeTicket) {
            $flightTotal = 0.0;
        }
        if (! $includeTransport) {
            $transportTotal = 0.0;
        }
        if (! $includeHotel) {
            $hotelTotals = [];
        }

        $mode = $includeHotel ? 'tiered' : 'flat';
        $flatExtras = $flightTotal + $transportTotal;
        $priceMap = [];

        if ($mode === 'tiered') {
            foreach (self::TIERS as $tier) {
                if (! array_key_exists($tier, $hotelTotals)) {
                    continue;
                }

                $priceMap[$tier] = round((float) $hotelTotals[$tier] + $flatExtras, 2);
            }
        }

        $flatPrice = null;
        if ($mode === 'flat') {
            $flatPrice = round($flatExtras, 2);
        }

        $flatSeatLimit = null;
        if ($mode === 'flat') {
            $packageTotalSeats = (int) ($package['total_seats'] ?? 0);
            if ($packageTotalSeats > 0) {
                $flatSeatLimit = $packageTotalSeats;
            } elseif ($seatLimitMap !== []) {
                $flatSeatLimit = max(array_map(static function ($value): int {
                    return (int) ($value ?? 0);
                }, $seatLimitMap));
            }
        }

        return [
            'package_id' => $packageId,
            'mode' => $mode,
            'hotel_totals' => $hotelTotals,
            'flight_total' => round($flightTotal, 2),
            'transport_total' => round($transportTotal, 2),
            'price_map' => $priceMap,
            'flat_price' => $flatPrice,
            'has_pricing' => $mode === 'flat' ? ($flatPrice !== null && $flatPrice > 0) : $priceMap !== [],
            'flat_seats_limit' => $flatSeatLimit,
            'seat_limit_map' => $seatLimitMap,
            'include_hotel' => $includeHotel,
            'include_ticket' => $includeTicket,
            'include_transport' => $includeTransport,
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
            'hotel_totals' => [],
            'flight_total' => 0.0,
            'transport_total' => 0.0,
            'price_map' => [],
            'flat_price' => null,
            'has_pricing' => false,
            'flat_seats_limit' => null,
            'seat_limit_map' => [],
            'include_hotel' => true,
            'include_ticket' => true,
            'include_transport' => true,
        ];
    }
}
