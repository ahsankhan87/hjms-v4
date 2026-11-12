<?php

namespace App\Services\Frontend;

use App\Services\PackagePricingService;
use CodeIgniter\Database\BaseConnection;

class CatalogService
{
    private BaseConnection $db;
    private PackagePricingService $pricingService;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->pricingService = new PackagePricingService($this->db);
    }

    public function homeData(?array $viewer = null): array
    {
        return [
            'featured_hajj' => $this->listPackages([
                'package_type' => 'hajj',
                'limit' => 4,
            ], $viewer),
            'featured_umrah' => $this->listPackages([
                'package_type' => 'umrah',
                'limit' => 4,
            ], $viewer),
            'featured_flights' => $this->listFlights(['limit' => 6]),
            'featured_hotels' => $this->listHotels(['limit' => 6]),
            'featured_transports' => $this->listTransports(['limit' => 6]),
        ];
    }

    public function listPackages(array $filters = [], ?array $viewer = null): array
    {
        $seasonId = active_season_id();
        $builder = $this->db->table('packages p')
            ->select('p.id, p.code, p.name, p.package_type, p.airline, p.airline_logo, p.departure_date, p.arrival_date, p.duration_days, p.total_seats, p.include_hotel, p.include_ticket, p.include_transport, p.include_ziarat, p.ziarat_cost, p.profit_mode, p.profit_value, p.default_shirka_company_id, MIN(pc.cost_amount) AS base_cost')
            ->join('package_costs pc', 'pc.package_id = p.id', 'left')
            ->where('p.is_active', 1)
            ->groupBy('p.id')
            ->orderBy('p.id', 'DESC');

        if ($seasonId !== null) {
            $builder->where('p.season_id', $seasonId);
        }

        $packageType = strtolower(trim((string) ($filters['package_type'] ?? '')));
        if ($packageType !== '' && $packageType !== 'all') {
            $escapedType = $this->db->escapeLikeString($packageType);
            $builder->where("LOWER(TRIM(p.package_type)) LIKE '%{$escapedType}%'", null, false);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder
                ->groupStart()
                ->like('p.name', $search)
                ->orLike('p.code', $search)
                ->orLike('p.airline', $search)
                ->groupEnd();
        }

        $limit = (int) ($filters['limit'] ?? 20);
        if ($limit > 0) {
            $builder->limit(min($limit, 50));
        }

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            $tier = strtolower(trim((string) ($filters['tier'] ?? 'sharing')));
            $price = $this->resolvePackagePrice((int) ($row['id'] ?? 0), $tier, $viewer);
            $row['price'] = $price;
        }

        return $rows;
    }

    public function packageDetails(int $packageId, ?array $viewer = null): ?array
    {
        if ($packageId < 1) {
            return null;
        }

        $seasonId = active_season_id();
        $builder = $this->db->table('packages p')
            ->select('p.*')
            ->where('p.id', $packageId)
            ->where('p.is_active', 1);

        if ($seasonId !== null) {
            $builder->where('p.season_id', $seasonId);
        }

        $package = $builder->get()->getRowArray();
        if ($package === null) {
            return null;
        }

        $package['flights'] = $this->db->table('package_flights pf')
            ->select('pf.*, f.departure_airport, f.arrival_airport, f.departure_at, f.arrival_at, f.fare, f.return_airline, f.return_flight_no, f.return_departure_airport, f.return_arrival_airport, f.return_departure_at, f.return_arrival_at')
            ->join('flights f', 'f.id = pf.flight_id', 'left')
            ->where('pf.package_id', $packageId)
            ->orderBy('pf.id', 'ASC')
            ->get()
            ->getResultArray();

        $package['hotels'] = $this->db->table('package_hotels ph')
            ->select('ph.*, h.city, h.star_rating, h.image_url, h.map_url')
            ->join('hotels h', 'h.id = ph.hotel_id', 'left')
            ->where('ph.package_id', $packageId)
            ->orderBy('ph.id', 'ASC')
            ->get()
            ->getResultArray();

        $package['transports'] = $this->db->table('package_transports pt')
            ->select('pt.*, t.transport_name, t.provider_name, t.vehicle_type')
            ->join('transports t', 't.id = pt.transport_id', 'left')
            ->where('pt.package_id', $packageId)
            ->orderBy('pt.id', 'ASC')
            ->get()
            ->getResultArray();

        $package['pricing'] = $this->resolvePackagePrice($packageId, 'sharing', $viewer);

        return $package;
    }

    public function listFlights(array $filters = []): array
    {
        $builder = $this->db->table('flights')
            ->select('id, airline, flight_no, departure_airport, arrival_airport, departure_at, arrival_at, fare, return_airline, return_flight_no, return_departure_airport, return_arrival_airport, return_departure_at, return_arrival_at')
            ->orderBy('id', 'DESC');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder
                ->groupStart()
                ->like('airline', $search)
                ->orLike('flight_no', $search)
                ->orLike('departure_airport', $search)
                ->orLike('arrival_airport', $search)
                ->groupEnd();
        }

        $limit = (int) ($filters['limit'] ?? 20);
        if ($limit > 0) {
            $builder->limit(min($limit, 50));
        }

        return $builder->get()->getResultArray();
    }

    public function flightDetails(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        return $this->db->table('flights')
            ->where('id', $id)
            ->get()
            ->getRowArray();
    }

    public function listHotels(array $filters = []): array
    {
        $builder = $this->db->table('hotels')
            ->select('id, name, city, distance_m, star_rating, address, image_url, image_gallery, youtube_url, map_url')
            ->orderBy('id', 'DESC');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder
                ->groupStart()
                ->like('name', $search)
                ->orLike('city', $search)
                ->groupEnd();
        }

        $city = trim((string) ($filters['city'] ?? ''));
        if ($city !== '') {
            $builder->where('city', $city);
        }

        $limit = (int) ($filters['limit'] ?? 20);
        if ($limit > 0) {
            $builder->limit(min($limit, 50));
        }

        return $builder->get()->getResultArray();
    }

    public function hotelDetails(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $hotel = $this->db->table('hotels')->where('id', $id)->get()->getRowArray();
        if ($hotel === null) {
            return null;
        }

        $hotel['rooms'] = $this->db->table('hotel_rooms')
            ->where('hotel_id', $id)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return $hotel;
    }

    public function listTransports(array $filters = []): array
    {
        $builder = $this->db->table('transports')
            ->select('id, transport_name, provider_name, vehicle_type, seat_capacity')
            ->orderBy('id', 'DESC');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder
                ->groupStart()
                ->like('transport_name', $search)
                ->orLike('provider_name', $search)
                ->orLike('vehicle_type', $search)
                ->groupEnd();
        }

        $limit = (int) ($filters['limit'] ?? 20);
        if ($limit > 0) {
            $builder->limit(min($limit, 50));
        }

        return $builder->get()->getResultArray();
    }

    public function transportDetails(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $transport = $this->db->table('transports')->where('id', $id)->get()->getRowArray();
        if ($transport === null) {
            return null;
        }

        $transport['legs'] = $this->db->table('transport_legs')
            ->where('transport_id', $id)
            ->orderBy('sequence_no', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return $transport;
    }

    public function resolvePackagePrice(int $packageId, string $requestedTier = 'sharing', ?array $viewer = null): array
    {
        $summary = $this->pricingService->summarizePackage($packageId);
        $mode = (string) ($summary['mode'] ?? 'tiered');
        $tier = strtolower(trim($requestedTier));
        if (! in_array($tier, PackagePricingService::TIERS, true)) {
            $tier = 'sharing';
        }

        $basePrice = 0.0;
        if ($mode === 'flat') {
            $basePrice = (float) ($summary['flat_price'] ?? 0.0);
            $tier = 'sharing';
        } else {
            $priceMap = (array) ($summary['price_map'] ?? []);
            $basePrice = (float) ($priceMap[$tier] ?? 0.0);
            if ($basePrice <= 0 && $priceMap !== []) {
                $basePrice = (float) min(array_map('floatval', $priceMap));
            }
        }

        $effective = $basePrice;
        $discount = 0.0;

        $userType = strtolower((string) ($viewer['type'] ?? ''));
        $agent = is_array($viewer['agent'] ?? null) ? $viewer['agent'] : null;
        if ($userType === 'b2b' && $agent !== null) {
            $commissionType = strtolower(trim((string) ($agent['commission_type'] ?? '')));
            $commissionValue = (float) ($agent['commission_value'] ?? 0);

            if ($commissionValue > 0) {
                if ($commissionType === 'percent') {
                    $discount = ($basePrice * $commissionValue) / 100;
                } else {
                    $discount = $commissionValue;
                }
                $effective = max(0.0, $basePrice - $discount);
            }
        }

        return [
            'mode' => $mode,
            'tier' => $tier,
            'base_price' => round($basePrice, 2),
            'effective_price' => round($effective, 2),
            'discount_amount' => round($discount, 2),
            'currency' => 'PKR',
        ];
    }
}
