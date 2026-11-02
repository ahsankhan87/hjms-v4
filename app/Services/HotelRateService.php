<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Single source of truth for hotel room-type rates.
 *
 * Hotel room rates live in `hotel_rooms.room_cost` and are keyed by a canonical
 * tier so packages can load them without any manual mapping.
 */
class HotelRateService
{
    /** Canonical room tiers, cheapest occupancy first. */
    const TIERS = ['sharing', 'quint', 'quad', 'triple', 'double'];

    const TIER_LABELS = [
        'sharing' => 'Sharing',
        'quint'   => 'Quint',
        'quad'    => 'Quad',
        'triple'  => 'Triple',
        'double'  => 'Double',
    ];

    private $db;

    public function __construct(BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Maps a free-text room type ("Double", "DBL", "TRP", "sharing 5 bed", …) to a canonical tier.
     */
    public static function normalizeTier(?string $roomType): ?string
    {
        $value = strtolower(trim((string) $roomType));

        if ($value === '') {
            return null;
        }

        foreach (self::TIERS as $tier) {
            if ($value === $tier || strpos($value, $tier) !== false) {
                return $tier;
            }
        }

        $aliases = [
            'dbl'    => 'double',
            'trp'    => 'triple',
            'trpl'   => 'triple',
            'quint'  => 'quint',
            'quintu' => 'quint',
            '5 bed'  => 'quint',
            '5-bed'  => 'quint',
            'five'   => 'quint',
            'qd'     => 'quad',
            '4 bed'  => 'quad',
            'sgl'    => 'single',
            'singl'  => 'single',
        ];

        foreach ($aliases as $needle => $tier) {
            if (strpos($value, $needle) !== false) {
                return $tier;
            }
        }

        return null;
    }

    public static function label(string $tier): string
    {
        return self::TIER_LABELS[$tier] ?? ucfirst($tier);
    }

    /**
     * Returns [tier => rate] for one hotel. Missing tiers are simply absent.
     */
    public function ratesForHotel(int $hotelId): array
    {
        if ($hotelId < 1) {
            return [];
        }

        return $this->ratesForHotels([$hotelId])[$hotelId] ?? [];
    }

    /**
     * Returns [hotelId => [tier => rate]] for many hotels in one query.
     */
    public function ratesForHotels(array $hotelIds): array
    {
        $hotelIds = array_values(array_unique(array_filter(array_map('intval', $hotelIds))));
        if ($hotelIds === []) {
            return [];
        }

        $rows = $this->db->table('hotel_rooms')
            ->select('hotel_id, room_type, room_cost')
            ->whereIn('hotel_id', $hotelIds)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $rates = [];
        foreach ($rows as $row) {
            $hotelId = (int) ($row['hotel_id'] ?? 0);
            $tier = self::normalizeTier($row['room_type'] ?? '');
            $amount = (float) ($row['room_cost'] ?? 0);

            if ($hotelId < 1 || $tier === null || $amount <= 0 || isset($rates[$hotelId][$tier])) {
                continue;
            }

            $rates[$hotelId][$tier] = $amount;
        }

        return $rates;
    }

    /**
     * Creates or updates rate rows for the canonical tiers of a hotel.
     *
     * @param array<string, mixed> $rates [tier => amount]
     */
    public function syncTierRates(int $hotelId, array $rates): void
    {
        if ($hotelId < 1 || $rates === []) {
            return;
        }

        foreach (self::TIERS as $tier) {
            if (! array_key_exists($tier, $rates)) {
                continue;
            }

            $raw = trim((string) ($rates[$tier] ?? ''));
            if ($raw === '') {
                continue;
            }

            $amount = max(0.0, (float) $raw);
            $existing = $this->db->table('hotel_rooms')
                ->select('id')
                ->where('hotel_id', $hotelId)
                ->where('room_type', $tier)
                ->get()
                ->getRowArray();

            if (! empty($existing)) {
                $this->db->table('hotel_rooms')->where('id', (int) $existing['id'])->update([
                    'room_cost'  => $amount,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                continue;
            }

            $this->db->table('hotel_rooms')->insert([
                'hotel_id'        => $hotelId,
                'room_type'       => $tier,
                'room_cost'       => $amount,
                'total_rooms'     => 0,
                'allocated_rooms' => 0,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
