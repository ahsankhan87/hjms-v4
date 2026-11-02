<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Turns confirmed bookings into hotel room allocations and rooming lists.
 *
 * A pilgrim occupies one bed in a room of their booking's pricing tier, so the
 * number of rooms a hotel has committed is ceil(pilgrims / beds per room).
 * Allocations only count while the package trip is still open; once the pilgrims
 * fly back (package return date has passed) the rooms are released again.
 */
class RoomAllocationService
{
    /** Default beds per room, cheapest tier first. */
    const BEDS_BY_TIER = [
        'sharing' => 6,
        'quint'   => 5,
        'quad'    => 4,
        'triple'  => 3,
        'double'  => 2,
        'single'  => 1,
    ];

    private $db;

    public function __construct(BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function tierBedLabel(): array
    {
        return self::BEDS_BY_TIER;
    }

    public function defaultBedsFor(?string $roomType): int
    {
        $tier = HotelRateService::normalizeTier($roomType);
        if ($tier !== null && isset(self::BEDS_BY_TIER[$tier])) {
            return self::BEDS_BY_TIER[$tier];
        }

        $value = strtolower(trim((string) $roomType));
        if ($value !== '' && preg_match('/(\d+)\s*(?:bed|pax|person|sharing)/', $value, $matches) === 1) {
            return max(1, (int) $matches[1]);
        }

        return 0;
    }

    public function bedsFor(?string $roomType, int $explicitBeds = 0): int
    {
        if ($explicitBeds > 0) {
            return $explicitBeds;
        }

        $default = $this->defaultBedsFor($roomType);

        return $default > 0 ? $default : 1;
    }

    /**
     * Recomputes hotel_rooms.allocated_rooms for every room (or one hotel).
     * Rooms whose package trip has already finished drop back to zero.
     */
    public function sync(?int $hotelId = null): void
    {
        if (! $this->db->tableExists('hotel_rooms')) {
            return;
        }

        $needed = $this->requiredRoomsByHotel();

        $query = $this->db->table('hotel_rooms')->select('id, hotel_id, room_type, beds');
        if ($hotelId !== null && $hotelId > 0) {
            $query->where('hotel_id', $hotelId);
        }

        foreach ($query->get()->getResultArray() as $room) {
            $roomId = (int) ($room['id'] ?? 0);
            $hotelKey = (int) ($room['hotel_id'] ?? 0);
            $tier = HotelRateService::normalizeTier((string) ($room['room_type'] ?? ''));

            $allocated = ($tier !== null && $hotelKey > 0)
                ? (int) ($needed[$hotelKey]['tiers'][$tier] ?? 0)
                : 0;

            if ($roomId > 0) {
                $this->db->table('hotel_rooms')->where('id', $roomId)->update([
                    'allocated_rooms' => $allocated,
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /**
     * @return array<int, array{tiers: array<string, int>, pilgrims: array<string, int>}>
     *         hotelId => ['tiers' => [tier => rooms], 'pilgrims' => [tier => pilgrims]]
     */
    public function requiredRoomsByHotel(): array
    {
        $result = [];

        foreach ($this->activePackagePilgrimsByTier() as $packageId => $tiers) {
            $hotelIds = $this->packageHotelIds((int) $packageId);
            if ($hotelIds === []) {
                continue;
            }

            $bedsByHotelTier = $this->bedsByHotelTier($hotelIds);

            foreach ($hotelIds as $hotelId) {
                if (! isset($result[$hotelId])) {
                    $result[$hotelId] = ['tiers' => [], 'pilgrims' => []];
                }

                foreach ($tiers as $tier => $pilgrimCount) {
                    $beds = (int) ($bedsByHotelTier[$hotelId][$tier] ?? 0);
                    if ($beds < 1) {
                        $beds = $this->bedsFor($tier);
                    }

                    $rooms = (int) ceil($pilgrimCount / max(1, $beds));

                    $result[$hotelId]['tiers'][$tier] = (int) ($result[$hotelId]['tiers'][$tier] ?? 0) + $rooms;
                    $result[$hotelId]['pilgrims'][$tier] = (int) ($result[$hotelId]['pilgrims'][$tier] ?? 0) + $pilgrimCount;
                }
            }
        }

        return $result;
    }

    /**
     * Confirmed pilgrims per tier for packages whose trip is still running.
     *
     * @return array<int, array<string, int>>
     */
    public function activePackagePilgrimsByTier(): array
    {
        $rows = $this->db->query(
            "SELECT b.package_id, LOWER(TRIM(b.pricing_tier)) AS tier, COUNT(bp.id) AS pilgrims
             FROM bookings b
             INNER JOIN booking_pilgrims bp ON bp.booking_id = b.id
             INNER JOIN packages p ON p.id = b.package_id
             WHERE b.status = 'confirmed'
               AND (p.arrival_date IS NULL OR DATE(p.arrival_date) >= CURDATE())
             GROUP BY b.package_id, LOWER(TRIM(b.pricing_tier))"
        )->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $packageId = (int) ($row['package_id'] ?? 0);
            $tier = (string) ($row['tier'] ?? '');
            if ($packageId < 1 || $tier === '') {
                continue;
            }

            $map[$packageId][$tier] = (int) ($row['pilgrims'] ?? 0);
        }

        return $map;
    }

    /**
     * @return int[]
     */
    private function packageHotelIds(int $packageId): array
    {
        $rows = $this->db->table('package_hotels')
            ->select('hotel_id')
            ->where('package_id', $packageId)
            ->where('hotel_id IS NOT NULL', null, false)
            ->groupBy('hotel_id')
            ->get()
            ->getResultArray();

        return array_values(array_filter(array_map(static function (array $row): int {
            return (int) ($row['hotel_id'] ?? 0);
        }, $rows)));
    }

    /**
     * @param int[] $hotelIds
     *
     * @return array<int, array<string, int>> hotelId => [tier => beds]
     */
    private function bedsByHotelTier(array $hotelIds): array
    {
        if ($hotelIds === []) {
            return [];
        }

        $rows = $this->db->table('hotel_rooms')
            ->select('hotel_id, room_type, beds')
            ->whereIn('hotel_id', $hotelIds)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $hotelId = (int) ($row['hotel_id'] ?? 0);
            $tier = HotelRateService::normalizeTier((string) ($row['room_type'] ?? ''));
            if ($hotelId < 1 || $tier === null || isset($map[$hotelId][$tier])) {
                continue;
            }

            $map[$hotelId][$tier] = $this->bedsFor((string) ($row['room_type'] ?? ''), (int) ($row['beds'] ?? 0));
        }

        return $map;
    }

    /**
     * Builds the per-pilgrim rooming list for a package.
     *
     * @return array{
     *     package: array|null,
     *     stays: array<int, array<string, mixed>>,
     *     totals: array{pilgrims:int, rooms:int}
     * }
     */
    public function roomingListForPackage(int $packageId, string $city = ''): array
    {
        $package = $this->db->table('packages')
            ->select('id, code, name, departure_date, arrival_date, duration_days, total_seats')
            ->where('id', $packageId)
            ->get()
            ->getRowArray();

        if (empty($package)) {
            return ['package' => null, 'stays' => [], 'totals' => ['pilgrims' => 0, 'rooms' => 0]];
        }

        $bookings = $this->db->table('bookings b')
            ->select('b.id, b.booking_no, b.pricing_tier, b.agent_id, a.name AS agent_name')
            ->join('agents a', 'a.id = b.agent_id', 'left')
            ->where('b.package_id', $packageId)
            ->where('b.status', 'confirmed')
            ->orderBy('b.id', 'ASC')
            ->get()
            ->getResultArray();

        if ($bookings === []) {
            return ['package' => $package, 'stays' => [], 'totals' => ['pilgrims' => 0, 'rooms' => 0]];
        }

        $bookingById = [];
        foreach ($bookings as $booking) {
            $bookingById[(int) $booking['id']] = $booking;
        }

        $pilgrimRows = $this->db->table('booking_pilgrims bp')
            ->select('bp.booking_id, p.id, p.first_name, p.last_name, p.passport_no, p.gender, p.date_of_birth, p.mobile_no, p.phone, p.city')
            ->join('pilgrims p', 'p.id = bp.pilgrim_id', 'inner')
            ->whereIn('bp.booking_id', array_keys($bookingById))
            ->orderBy('p.last_name', 'ASC')
            ->orderBy('p.first_name', 'ASC')
            ->get()
            ->getResultArray();

        $pilgrimsByTier = [];
        foreach ($pilgrimRows as $row) {
            $booking = $bookingById[(int) ($row['booking_id'] ?? 0)] ?? null;
            if ($booking === null) {
                continue;
            }

            $tier = strtolower(trim((string) ($booking['pricing_tier'] ?? '')));
            if ($tier === '') {
                $tier = 'sharing';
            }

            $pilgrimsByTier[$tier][] = [
                'id'          => (int) ($row['id'] ?? 0),
                'name'        => trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? '')),
                'passport_no' => (string) ($row['passport_no'] ?? ''),
                'gender'      => (string) ($row['gender'] ?? ''),
                'dob'         => (string) ($row['date_of_birth'] ?? ''),
                'contact'     => (string) (($row['mobile_no'] ?? '') !== '' ? $row['mobile_no'] : ($row['phone'] ?? '')),
                'city'        => (string) ($row['city'] ?? ''),
                'booking_no'  => (string) ($booking['booking_no'] ?? ''),
                'agent_name'  => (string) ($booking['agent_name'] ?? ''),
            ];
        }

        $stayRows = $this->db->table('package_hotel_stays phs')
            ->select('phs.id, phs.check_in_date, phs.check_out_date, phs.package_hotel_id, ph.hotel_id, ph.hotel_name, h.name AS master_hotel_name, h.city AS hotel_city, h.star_rating')
            ->join('package_hotels ph', 'ph.id = phs.package_hotel_id', 'left')
            ->join('hotels h', 'h.id = ph.hotel_id', 'left')
            ->where('phs.package_id', $packageId)
            ->orderBy('phs.check_in_date', 'ASC')
            ->orderBy('phs.id', 'ASC')
            ->get()
            ->getResultArray();

        $cityFilter = strtolower(trim($city));
        $stays = [];
        $totalRooms = 0;
        $totalPilgrims = 0;

        foreach ($stayRows as $stayRow) {
            $hotelCity = (string) (($stayRow['hotel_city'] ?? '') !== '' ? $stayRow['hotel_city'] : '');
            if ($cityFilter !== '' && strpos(strtolower($hotelCity), $cityFilter) === false) {
                continue;
            }

            $hotelId = (int) ($stayRow['hotel_id'] ?? 0);
            $hotelName = (string) (($stayRow['hotel_name'] ?? '') !== '' ? $stayRow['hotel_name'] : ($stayRow['master_hotel_name'] ?? ''));

            $checkIn = (string) ($stayRow['check_in_date'] ?? '');
            $checkOut = (string) ($stayRow['check_out_date'] ?? '');
            $nights = 0;
            if ($checkIn !== '' && $checkOut !== '' && strtotime($checkOut) > strtotime($checkIn)) {
                $nights = (int) round((strtotime($checkOut) - strtotime($checkIn)) / 86400);
            }

            $bedsByTier = $this->bedsByHotelTier($hotelId > 0 ? [$hotelId] : []);
            $groups = [];
            $stayRooms = 0;
            $stayPilgrims = 0;
            $roomNumber = 0;

            foreach ($pilgrimsByTier as $tier => $pilgrims) {
                $beds = (int) ($bedsByTier[$hotelId][$tier] ?? 0);
                if ($beds < 1) {
                    $beds = $this->bedsFor($tier);
                }

                foreach (array_chunk($pilgrims, $beds) as $chunk) {
                    $roomNumber++;
                    $stayRooms++;
                    $stayPilgrims += count($chunk);

                    $groups[] = [
                        'room_no' => $roomNumber,
                        'tier'    => $tier,
                        'tier_label' => HotelRateService::label($tier),
                        'beds'    => $beds,
                        'occupied' => count($chunk),
                        'pilgrims' => $chunk,
                    ];
                }
            }

            $totalRooms += $stayRooms;
            $totalPilgrims = max($totalPilgrims, $stayPilgrims);

            $stays[] = [
                'hotel_id'   => $hotelId,
                'hotel_name' => $hotelName,
                'city'       => $hotelCity,
                'star_rating' => (int) ($stayRow['star_rating'] ?? 0),
                'check_in'   => $checkIn,
                'check_out'  => $checkOut,
                'nights'     => $nights,
                'rooms'      => $stayRooms,
                'pilgrims'   => $stayPilgrims,
                'groups'     => $groups,
            ];
        }

        return [
            'package' => $package,
            'stays'   => $stays,
            'totals'  => ['pilgrims' => $totalPilgrims, 'rooms' => $totalRooms],
        ];
    }
}
