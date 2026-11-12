<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\PackageModel;
use App\Services\PackagePricingService;

class CatalogController extends BaseController
{
    public function packages(string $packageType = 'all')
    {
        $packageType = strtolower(trim($packageType));
        if (! in_array($packageType, ['all', 'hajj', 'umrah'], true)) {
            $packageType = 'all';
        }

        $filters = [
            'min_price' => (float) ($this->request->getGet('min_price') ?? 0),
            'max_price' => (float) ($this->request->getGet('max_price') ?? 0),
            'duration_min' => (int) ($this->request->getGet('duration_min') ?? 0),
            'duration_max' => (int) ($this->request->getGet('duration_max') ?? 0),
            'hotel_rating' => (int) ($this->request->getGet('hotel_rating') ?? 0),
            'transport_included' => strtolower(trim((string) ($this->request->getGet('transport_included') ?? 'any'))),
        ];

        if (! in_array($filters['transport_included'], ['any', 'yes', 'no'], true)) {
            $filters['transport_included'] = 'any';
        }

        $rows = $this->loadBasePackages($packageType);
        $rows = $this->enrichPackages($rows);
        $rows = $this->applyPackageFilters($rows, $filters);

        return view('frontend/packages/index', [
            'title' => ucfirst($packageType) . ' Packages | HJMS Travel',
            'activePage' => 'packages',
            'packageType' => $packageType,
            'packages' => $rows,
            'filters' => $filters,
        ]);
    }

    public function show(string $slug)
    {
        $packageId = $this->resolvePackageIdFromSlug($slug);
        if ($packageId < 1) {
            return redirect()->to('/packages/hajj')->with('error', 'Package not found.');
        }

        $package = (new PackageModel())->where('id', $packageId)->where('is_active', 1)->first();
        if ($package === null) {
            return redirect()->to('/packages/hajj')->with('error', 'Package not found.');
        }

        $enriched = $this->enrichPackages([$package]);
        $item = $enriched[0] ?? $package;

        $db = db_connect();
        $item['hotels'] = $db->table('package_hotels ph')
            ->select('ph.hotel_name, ph.room_type, ph.check_in_date, ph.check_out_date, h.star_rating, h.city, h.image_url')
            ->join('hotels h', 'h.id = ph.hotel_id', 'left')
            ->where('ph.package_id', $packageId)
            ->orderBy('ph.id', 'ASC')
            ->get()
            ->getResultArray();

        $item['transports'] = $db->table('package_transports pt')
            ->select('pt.provider_name, pt.vehicle_type, pt.seat_capacity, t.transport_name')
            ->join('transports t', 't.id = pt.transport_id', 'left')
            ->where('pt.package_id', $packageId)
            ->orderBy('pt.id', 'ASC')
            ->get()
            ->getResultArray();

        $type = strtolower(trim((string) ($package['package_type'] ?? '')));
        $related = $this->loadBasePackages($type === '' ? 'all' : $type, (int) $packageId, 3);
        $related = $this->enrichPackages($related);

        return view('frontend/packages/show', [
            'title' => 'Package Details | HJMS Travel',
            'activePage' => 'packages',
            'package' => $item,
            'relatedPackages' => $related,
        ]);
    }

    private function loadBasePackages(string $packageType = 'all', int $excludeId = 0, int $limit = 100): array
    {
        $model = new PackageModel();
        $builder = $model->where('is_active', 1);

        if ($excludeId > 0) {
            $builder->where('id !=', $excludeId);
        }

        if ($packageType !== 'all') {
            $escapedType = db_connect()->escapeLikeString(strtolower($packageType));
            $builder->where("LOWER(TRIM(package_type)) LIKE '%{$escapedType}%'", null, false);
        }

        return $builder->orderBy('id', 'DESC')->findAll($limit);
    }

    private function enrichPackages(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $pricingService = new PackagePricingService();
        $db = db_connect();
        $ids = array_map(static fn(array $r): int => (int) ($r['id'] ?? 0), $rows);
        $ids = array_values(array_filter($ids, static fn(int $id): bool => $id > 0));
        if ($ids === []) {
            return $rows;
        }

        $hotelRatings = $db->table('package_hotels ph')
            ->select('ph.package_id, MAX(COALESCE(h.star_rating, 0)) AS hotel_rating')
            ->join('hotels h', 'h.id = ph.hotel_id', 'left')
            ->whereIn('ph.package_id', $ids)
            ->groupBy('ph.package_id')
            ->get()
            ->getResultArray();
        $ratingsByPackage = [];
        foreach ($hotelRatings as $row) {
            $ratingsByPackage[(int) ($row['package_id'] ?? 0)] = (int) ($row['hotel_rating'] ?? 0);
        }

        foreach ($rows as &$row) {
            $id = (int) ($row['id'] ?? 0);
            $summary = $pricingService->summarizePackage($id);
            $price = 0.0;
            if (($summary['mode'] ?? 'tiered') === 'flat') {
                $price = (float) ($summary['flat_price'] ?? 0);
            } else {
                $map = (array) ($summary['price_map'] ?? []);
                if ($map !== []) {
                    $price = min(array_map('floatval', $map));
                }
            }
            if ($price <= 0) {
                $price = (float) ($row['selling_price'] ?? 0);
            }

            $row['price_amount'] = round($price, 2);
            $row['hotel_rating'] = (int) ($ratingsByPackage[$id] ?? 0);
            $row['transport_included'] = (int) ($row['include_transport'] ?? 0) === 1 ? 'yes' : 'no';
            $row['slug'] = $this->buildPackageSlug($row);
            $row['hero_image'] = trim((string) ($row['airline_logo'] ?? '')) !== ''
                ? (string) $row['airline_logo']
                : base_url('assets/uploads/makkah-logo.png');
        }

        return $rows;
    }

    private function applyPackageFilters(array $rows, array $filters): array
    {
        return array_values(array_filter($rows, static function (array $row) use ($filters): bool {
            $price = (float) ($row['price_amount'] ?? 0);
            $duration = (int) ($row['duration_days'] ?? 0);
            $rating = (int) ($row['hotel_rating'] ?? 0);
            $transportIncluded = (string) ($row['transport_included'] ?? 'no');

            if (($filters['min_price'] ?? 0) > 0 && $price < (float) $filters['min_price']) {
                return false;
            }
            if (($filters['max_price'] ?? 0) > 0 && $price > (float) $filters['max_price']) {
                return false;
            }
            if (($filters['duration_min'] ?? 0) > 0 && $duration < (int) $filters['duration_min']) {
                return false;
            }
            if (($filters['duration_max'] ?? 0) > 0 && $duration > (int) $filters['duration_max']) {
                return false;
            }
            if (($filters['hotel_rating'] ?? 0) > 0 && $rating < (int) $filters['hotel_rating']) {
                return false;
            }
            if (($filters['transport_included'] ?? 'any') === 'yes' && $transportIncluded !== 'yes') {
                return false;
            }
            if (($filters['transport_included'] ?? 'any') === 'no' && $transportIncluded !== 'no') {
                return false;
            }

            return true;
        }));
    }

    private function buildPackageSlug(array $row): string
    {
        $name = strtolower(trim((string) ($row['name'] ?? 'package')));
        $name = preg_replace('/[^a-z0-9]+/i', '-', $name);
        $name = trim((string) $name, '-');
        if ($name === '') {
            $name = 'package';
        }

        return $name . '-' . (int) ($row['id'] ?? 0);
    }

    private function resolvePackageIdFromSlug(string $slug): int
    {
        $slug = trim($slug);
        if ($slug === '') {
            return 0;
        }

        if (ctype_digit($slug)) {
            return (int) $slug;
        }

        if (preg_match('/-(\d+)$/', $slug, $m) === 1) {
            return (int) ($m[1] ?? 0);
        }

        return 0;
    }
}
