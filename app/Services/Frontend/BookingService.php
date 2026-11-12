<?php

namespace App\Services\Frontend;

use DomainException;

class BookingService
{
    private CatalogService $catalogService;

    public function __construct(?CatalogService $catalogService = null)
    {
        $this->catalogService = $catalogService ?? new CatalogService();
    }

    public function createBooking(array $payload, array $viewer): array
    {
        $userId = (int) ($viewer['id'] ?? 0);
        if ($userId < 1) {
            throw new DomainException('Please login to create a booking.');
        }

        $seasonId = active_season_id();
        if ($seasonId === null) {
            throw new DomainException('No active season is configured yet.');
        }

        $packageId = (int) ($payload['package_id'] ?? 0);
        $tier = strtolower(trim((string) ($payload['pricing_tier'] ?? 'sharing')));
        if (! in_array($tier, ['sharing', 'quint', 'quad', 'triple', 'double'], true)) {
            $tier = 'sharing';
        }

        $pilgrimCount = max(1, (int) ($payload['pilgrims_count'] ?? 1));
        $remarks = trim((string) ($payload['remarks'] ?? ''));

        if ($packageId < 1) {
            throw new DomainException('Please select a valid package.');
        }

        $db = db_connect();
        $package = $db->table('packages')
            ->select('id, season_id, default_shirka_company_id, name')
            ->where('id', $packageId)
            ->where('is_active', 1)
            ->where('season_id', $seasonId)
            ->get()
            ->getRowArray();

        if ($package === null) {
            throw new DomainException('Selected package is not available in the active season.');
        }

        $price = $this->catalogService->resolvePackagePrice($packageId, $tier, $viewer);
        $unitPrice = (float) ($price['effective_price'] ?? 0);
        if ($unitPrice <= 0) {
            throw new DomainException('Selected package pricing is not configured.');
        }

        $agent = is_array($viewer['agent'] ?? null) ? $viewer['agent'] : [];
        $agentId = (int) ($agent['id'] ?? 0);
        if ($agentId < 1) {
            throw new DomainException('Customer profile is incomplete. Please contact support.');
        }

        $companyId = (int) ($package['default_shirka_company_id'] ?? 0);
        if ($companyId < 1 && function_exists('active_company')) {
            $activeCompany = active_company();
            $companyId = (int) ($activeCompany['id'] ?? 0);
        }

        $bookingNo = $this->generateBookingNo();
        $totalAmount = round($unitPrice * $pilgrimCount, 2);

        $insert = [
            'season_id' => $seasonId,
            'booking_no' => $bookingNo,
            'package_id' => $packageId,
            'agent_id' => $agentId,
            'branch_id' => isset($agent['branch_id']) && $agent['branch_id'] !== null ? (int) $agent['branch_id'] : null,
            'company_id' => $companyId > 0 ? $companyId : null,
            'status' => 'draft',
            'pricing_tier' => (string) ($price['tier'] ?? $tier),
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'pricing_source' => (string) ($viewer['type'] ?? 'b2c') . '_frontend',
            'price_locked_at' => date('Y-m-d H:i:s'),
            'total_pilgrims' => $pilgrimCount,
            'remarks' => $remarks !== '' ? $remarks : 'Booked from frontend portal.',
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $bookingFields = $db->getFieldNames('bookings');
        $insert = array_intersect_key($insert, array_fill_keys($bookingFields, true));

        $bookingId = (int) $db->table('bookings')->insert($insert, true);
        if ($bookingId < 1) {
            throw new DomainException('Unable to create booking. Please try again.');
        }

        return [
            'id' => $bookingId,
            'booking_no' => $bookingNo,
            'package_id' => $packageId,
            'package_name' => (string) ($package['name'] ?? ''),
            'pricing_tier' => (string) ($insert['pricing_tier'] ?? $tier),
            'unit_price' => round($unitPrice, 2),
            'total_amount' => $totalAmount,
            'pilgrims_count' => $pilgrimCount,
            'currency' => 'PKR',
        ];
    }

    public function listUserBookings(array $viewer): array
    {
        $userId = (int) ($viewer['id'] ?? 0);
        if ($userId < 1) {
            return [];
        }

        $seasonId = active_season_id();
        $builder = db_connect()->table('bookings b')
            ->select('b.id, b.booking_no, b.status, b.pricing_tier, b.unit_price, b.total_amount, b.total_pilgrims, b.created_at, p.name AS package_name, p.package_type')
            ->join('packages p', 'p.id = b.package_id', 'left')
            ->orderBy('b.id', 'DESC');

        if ($seasonId !== null) {
            $builder->where('b.season_id', $seasonId);
        }

        $userType = strtolower((string) ($viewer['type'] ?? ''));
        $agentId = (int) ($viewer['agent']['id'] ?? 0);
        if ($userType === 'b2b' && $agentId > 0) {
            $builder->where('b.agent_id', $agentId);
        } else {
            $builder->where('b.created_by', $userId);
        }

        return $builder->get()->getResultArray();
    }

    private function generateBookingNo(): string
    {
        return 'FW-' . date('YmdHis') . '-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
    }
}

