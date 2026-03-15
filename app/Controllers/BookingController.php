<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BookingModel;
use App\Models\BookingPilgrimModel;
use App\Services\AgentLedgerService;

class BookingController extends BaseController
{
    const PRICING_TIERS = ['sharing', 'quad', 'triple', 'double'];

    public function index()
    {
        $db = db_connect();
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $bookingFields = $db->getFieldNames('bookings');
        $hasPricing = in_array('pricing_tier', $bookingFields, true)
            && in_array('total_amount', $bookingFields, true)
            && in_array('unit_price', $bookingFields, true);

        $pricingSelect = $hasPricing
            ? ", b.pricing_tier, b.unit_price, b.total_amount,
                    COALESCE((
                        SELECT SUM(CASE WHEN pm.payment_type = 'refund' THEN -pm.amount ELSE pm.amount END)
                        FROM payments pm
                        WHERE pm.booking_id = b.id
                          AND pm.season_id = b.season_id
                          AND IFNULL(pm.status, 'posted') = 'posted'
                    ), 0) AS paid_amount,
                    (COALESCE(b.total_amount, 0) - COALESCE((
                        SELECT SUM(CASE WHEN pm2.payment_type = 'refund' THEN -pm2.amount ELSE pm2.amount END)
                        FROM payments pm2
                        WHERE pm2.booking_id = b.id
                          AND pm2.season_id = b.season_id
                          AND IFNULL(pm2.status, 'posted') = 'posted'
                    ), 0)) AS outstanding_amount"
            : ", '' AS pricing_tier, 0 AS unit_price, 0 AS total_amount, 0 AS paid_amount, 0 AS outstanding_amount";

        return view('portal/bookings/index', [
            'title'      => 'HJMS ERP | Bookings',
            'headerTitle' => 'Booking Operations',
            'activePage' => 'bookings',
            'userEmail' => (string) session('user_email'),
            'rows'      => $db->table('bookings b')
                ->select("b.*, c.name AS company_name, p.name AS package_name,
                    DATE_FORMAT(COALESCE(
                        (SELECT MIN(ph.check_in_date) FROM package_hotels ph WHERE ph.package_id = b.package_id),
                        (SELECT DATE(MIN(pf.arrival_at)) FROM package_flights pf WHERE pf.package_id = b.package_id)
                    ), '%Y-%m-%d') AS ksa_arrival_date,
                    DATE_FORMAT(COALESCE(
                        (SELECT MAX(ph.check_out_date) FROM package_hotels ph WHERE ph.package_id = b.package_id),
                        (SELECT DATE(MAX(pf.departure_at)) FROM package_flights pf WHERE pf.package_id = b.package_id)
                    ), '%Y-%m-%d') AS ksa_return_date,
                    DATE_FORMAT(b.created_at, '%Y-%m-%d') AS voucher_date,
                    (SELECT GROUP_CONCAT(DISTINCT TRIM(ph.room_type) ORDER BY ph.room_type SEPARATOR ', ')
                     FROM package_hotels ph
                     WHERE ph.package_id = b.package_id AND TRIM(IFNULL(ph.room_type, '')) <> '') AS room_types" . $pricingSelect)
                ->join('packages p', 'p.id = b.package_id', 'left')
                ->join('companies c', 'c.id = b.company_id', 'left')
                ->where('b.season_id', $seasonId)
                ->orderBy('b.id', 'DESC')
                ->get()
                ->getResultArray(),
            'success'   => session()->getFlashdata('success'),
            'error'     => session()->getFlashdata('error'),
            'errors'    => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        return view('portal/bookings/add', [
            'title'           => 'HJMS ERP | Add Booking',
            'headerTitle'     => 'Booking Operations',
            'activePage'      => 'bookings',
            'userEmail'       => (string) session('user_email'),
            'selectedPackageId' => (int) ($this->request->getGet('package_id') ?? 0),
            'pricingTiers'    => self::PRICING_TIERS,
            'success'         => session()->getFlashdata('success'),
            'error'           => session()->getFlashdata('error'),
            'errors'          => session()->getFlashdata('errors') ?: [],
        ] + $this->bookingFormData($seasonId, null));
    }

    public function edit(int $id)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $db = db_connect();
        $row = $db->table('bookings')
            ->where('id', $id)
            ->where('season_id', $seasonId)
            ->get()
            ->getRowArray();

        if (empty($row)) {
            return redirect()->to('/bookings')->with('error', 'Booking not found in active season.');
        }

        $selectedPilgrimIds = array_map('intval', array_column(
            $db->table('booking_pilgrims')->select('pilgrim_id')->where('booking_id', $id)->get()->getResultArray(),
            'pilgrim_id'
        ));

        return view('portal/bookings/edit', [
            'title'             => 'HJMS ERP | Edit Booking',
            'headerTitle'       => 'Booking Operations',
            'activePage'        => 'bookings',
            'userEmail'         => (string) session('user_email'),
            'row'               => $row,
            'pricingTiers'      => self::PRICING_TIERS,
            'selectedPilgrimIds' => $selectedPilgrimIds,
            'success'           => session()->getFlashdata('success'),
            'error'             => session()->getFlashdata('error'),
            'errors'            => session()->getFlashdata('errors') ?: [],
        ] + $this->bookingFormData($seasonId, $id));
    }

    public function createBooking()
    {
        $returnUrl = $this->resolveReturnUrl('/bookings/add');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $pilgrimIds = (array) $this->request->getPost('pilgrim_ids');
        $pilgrimIds = array_values(array_filter(array_map('intval', $pilgrimIds)));

        $payload = [
            'package_id'  => (int) $this->request->getPost('package_id'),
            'pricing_tier' => trim((string) $this->request->getPost('pricing_tier')),
            'agent_id'    => $this->request->getPost('agent_id') !== '' ? (int) $this->request->getPost('agent_id') : null,
            'branch_id'   => $this->request->getPost('branch_id') !== '' ? (int) $this->request->getPost('branch_id') : null,
            'company_id'  => $this->request->getPost('company_id') !== '' ? (int) $this->request->getPost('company_id') : null,
            'status'      => (string) ($this->request->getPost('status') ?: 'draft'),
            'remarks'     => (string) $this->request->getPost('remarks'),
            'pilgrim_ids' => $pilgrimIds,
        ];

        if (! $this->validateData($payload, [
            'package_id'  => 'required|integer',
            'pricing_tier' => 'required|in_list[sharing,quad,triple,double]',
            'agent_id'    => 'permit_empty|integer',
            'branch_id'   => 'permit_empty|integer',
            'company_id'  => 'required|integer',
            'status'      => 'required|in_list[draft,confirmed,cancelled]',
            'remarks'     => 'permit_empty|max_length[5000]',
            'pilgrim_ids' => 'required',
        ])) {
            return redirect()->to($returnUrl)->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $bookingModel = new BookingModel();
            $bookingPilgrimModel = new BookingPilgrimModel();
            $db = db_connect();

            $package = $db->table('packages')->select('id')->where('id', $payload['package_id'])->where('season_id', $seasonId)->get()->getRowArray();
            if (empty($package)) {
                return redirect()->to($returnUrl)->withInput()->with('error', 'Selected package is not in active season.');
            }

            if ($payload['company_id'] === null || ! company_table_ready()) {
                return redirect()->to($returnUrl)->withInput()->with('error', 'Please select a valid shirka company.');
            }

            $company = $db->table('companies')->select('id')->where('id', $payload['company_id'])->get()->getRowArray();
            if (empty($company)) {
                return redirect()->to($returnUrl)->withInput()->with('error', 'Selected shirka company was not found.');
            }

            if ($pilgrimIds === []) {
                return redirect()->to($returnUrl)->withInput()->with('error', 'Please select at least one pilgrim.');
            }

            $priceContext = $this->resolvePackageTierPrice((int) $payload['package_id'], (string) $payload['pricing_tier']);
            if ($priceContext === null) {
                return redirect()->to($returnUrl)->withInput()->with('error', 'Selected package does not have pricing configured for chosen tier.');
            }

            $seasonPilgrimCount = $db->table('pilgrims')->where('season_id', $seasonId)->whereIn('id', $pilgrimIds)->countAllResults();
            if ($seasonPilgrimCount !== count($pilgrimIds)) {
                return redirect()->to($returnUrl)->withInput()->with('error', 'Some selected pilgrims do not belong to active season.');
            }

            $confirmedLinkCount = $db->table('booking_pilgrims bp')
                ->join('bookings b', 'b.id = bp.booking_id', 'inner')
                ->where('b.season_id', $seasonId)
                ->where('b.status', 'confirmed')
                ->whereIn('bp.pilgrim_id', $pilgrimIds)
                ->countAllResults();
            if ($confirmedLinkCount > 0) {
                return redirect()->to($returnUrl)->withInput()->with('error', 'Some selected pilgrims are already in confirmed bookings.');
            }

            $unitPrice = (float) ($priceContext['unit_price'] ?? 0);
            $totalAmount = round($unitPrice * count($pilgrimIds), 2);

            $db->transStart();

            $bookingNo = 'BKG-' . date('YmdHis') . '-' . mt_rand(100, 999);
            $bookingData = [
                'season_id'      => $seasonId,
                'booking_no'     => $bookingNo,
                'package_id'     => $payload['package_id'],
                'package_variant_id' => $priceContext['package_variant_id'],
                'agent_id'       => $payload['agent_id'],
                'branch_id'      => $payload['branch_id'],
                'company_id'     => $payload['company_id'],
                'status'         => $payload['status'],
                'pricing_tier'   => $payload['pricing_tier'],
                'unit_price'     => $unitPrice,
                'total_amount'   => $totalAmount,
                'pricing_source' => (string) ($priceContext['source'] ?? 'package_costs'),
                'price_locked_at' => date('Y-m-d H:i:s'),
                'total_pilgrims' => count($pilgrimIds),
                'remarks'        => $payload['remarks'] !== '' ? $payload['remarks'] : null,
                'created_by'     => session('user_id') ? (int) session('user_id') : null,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ];

            $bookingModel->insert($this->filterBookingColumns($bookingData));

            $bookingId = (int) $bookingModel->getInsertID();
            foreach ($pilgrimIds as $pilgrimId) {
                $bookingPilgrimModel->insert([
                    'booking_id' => $bookingId,
                    'pilgrim_id' => $pilgrimId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transComplete();

            if (! $db->transStatus()) {
                throw new \RuntimeException('Failed to create booking.');
            }

            (new AgentLedgerService())->syncBookingLedger($bookingId, $seasonId);

            return redirect()->to('/bookings')->with('success', 'Booking created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to($returnUrl)->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateBooking()
    {
        $bookingId = (int) $this->request->getPost('booking_id');
        $returnUrl = $this->resolveReturnUrl($bookingId > 0 ? '/bookings/' . $bookingId . '/edit' : '/bookings');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }
        $postedPilgrims = $this->request->getPost('pilgrim_ids');

        $payload = [
            'package_id'  => (string) $this->request->getPost('package_id'),
            'pricing_tier' => trim((string) $this->request->getPost('pricing_tier')),
            'agent_id'    => (string) $this->request->getPost('agent_id'),
            'branch_id'   => (string) $this->request->getPost('branch_id'),
            'company_id'  => (string) $this->request->getPost('company_id'),
            'status'      => (string) $this->request->getPost('status'),
            'remarks'     => (string) $this->request->getPost('remarks'),
        ];

        if ($bookingId < 1) {
            return redirect()->to($returnUrl)->withInput()->with('error', 'Valid booking ID is required.');
        }

        if (! $this->validateData($payload, [
            'package_id' => 'permit_empty|integer',
            'pricing_tier' => 'permit_empty|in_list[sharing,quad,triple,double]',
            'agent_id'   => 'permit_empty|integer',
            'branch_id'  => 'permit_empty|integer',
            'company_id' => 'permit_empty|integer',
            'status'     => 'permit_empty|in_list[draft,confirmed,cancelled]',
            'remarks'    => 'permit_empty|max_length[5000]',
        ])) {
            return redirect()->to($returnUrl)->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [];
        if ($payload['package_id'] !== '') {
            $data['package_id'] = (int) $payload['package_id'];
        }
        if ($payload['pricing_tier'] !== '') {
            $data['pricing_tier'] = $payload['pricing_tier'];
        }
        if ($payload['agent_id'] !== '') {
            $data['agent_id'] = (int) $payload['agent_id'];
        }
        if ($payload['branch_id'] !== '') {
            $data['branch_id'] = (int) $payload['branch_id'];
        }
        if ($payload['company_id'] !== '') {
            $data['company_id'] = (int) $payload['company_id'];
        }
        if ($payload['status'] !== '') {
            $data['status'] = $payload['status'];
        }
        if ($payload['remarks'] !== '') {
            $data['remarks'] = $payload['remarks'];
        }

        $pilgrimIds = [];
        if ($postedPilgrims !== null) {
            $pilgrimIds = array_values(array_filter(array_map('intval', (array) $postedPilgrims)));
            if ($pilgrimIds === []) {
                return redirect()->to($returnUrl)->withInput()->with('error', 'Select at least one pilgrim when updating pilgrims list.');
            }
            $data['total_pilgrims'] = count($pilgrimIds);
        }

        if ($data === []) {
            return redirect()->to($returnUrl)->withInput()->with('error', 'Provide at least one field to update for booking.');
        }

        try {
            $bookingModel = new BookingModel();
            $bookingPilgrimModel = new BookingPilgrimModel();
            $db = db_connect();

            $existing = $bookingModel->where('id', $bookingId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/bookings')->with('error', 'Booking not found in active season.');
            }

            if (isset($data['package_id'])) {
                $package = $db->table('packages')->select('id')->where('id', (int) $data['package_id'])->where('season_id', $seasonId)->get()->getRowArray();
                if (empty($package)) {
                    return redirect()->to($returnUrl)->withInput()->with('error', 'Selected package is not in active season.');
                }
            }

            if (isset($data['company_id'])) {
                if (! company_table_ready()) {
                    return redirect()->to($returnUrl)->withInput()->with('error', 'Shirka company table is not available.');
                }

                $company = $db->table('companies')->select('id')->where('id', (int) $data['company_id'])->get()->getRowArray();
                if (empty($company)) {
                    return redirect()->to($returnUrl)->withInput()->with('error', 'Selected shirka company was not found.');
                }
            }

            $effectivePackageId = isset($data['package_id']) ? (int) $data['package_id'] : (int) ($existing['package_id'] ?? 0);
            $effectivePricingTier = isset($data['pricing_tier'])
                ? (string) $data['pricing_tier']
                : trim((string) ($existing['pricing_tier'] ?? ''));

            $shouldReprice = isset($data['package_id']) || isset($data['pricing_tier']) || $postedPilgrims !== null;
            if ($shouldReprice) {
                if ($effectivePricingTier === '') {
                    return redirect()->to($returnUrl)->withInput()->with('error', 'Please select a pricing tier.');
                }

                $priceContext = $this->resolvePackageTierPrice($effectivePackageId, $effectivePricingTier);
                if ($priceContext === null) {
                    return redirect()->to($returnUrl)->withInput()->with('error', 'Selected package does not have pricing configured for chosen tier.');
                }

                $effectivePilgrimCount = $postedPilgrims !== null
                    ? count($pilgrimIds)
                    : (int) ($existing['total_pilgrims'] ?? 0);

                if ($effectivePilgrimCount < 1) {
                    return redirect()->to($returnUrl)->withInput()->with('error', 'At least one pilgrim is required to price booking.');
                }

                $unitPrice = (float) ($priceContext['unit_price'] ?? 0);
                $data['package_variant_id'] = $priceContext['package_variant_id'];
                $data['pricing_tier'] = $effectivePricingTier;
                $data['unit_price'] = $unitPrice;
                $data['total_amount'] = round($unitPrice * $effectivePilgrimCount, 2);
                $data['pricing_source'] = (string) ($priceContext['source'] ?? 'package_costs');
                $data['price_locked_at'] = date('Y-m-d H:i:s');
            }

            $db->transStart();

            $bookingModel->update($bookingId, $this->filterBookingColumns($data + ['updated_at' => date('Y-m-d H:i:s')]));

            if ($postedPilgrims !== null) {
                $seasonPilgrimCount = $db->table('pilgrims')->where('season_id', $seasonId)->whereIn('id', $pilgrimIds)->countAllResults();
                if ($seasonPilgrimCount !== count($pilgrimIds)) {
                    return redirect()->to($returnUrl)->withInput()->with('error', 'Some selected pilgrims do not belong to active season.');
                }

                $confirmedLinkCount = $db->table('booking_pilgrims bp')
                    ->join('bookings b', 'b.id = bp.booking_id', 'inner')
                    ->where('b.season_id', $seasonId)
                    ->where('b.status', 'confirmed')
                    ->where('b.id !=', $bookingId)
                    ->whereIn('bp.pilgrim_id', $pilgrimIds)
                    ->countAllResults();
                if ($confirmedLinkCount > 0) {
                    return redirect()->to($returnUrl)->withInput()->with('error', 'Some selected pilgrims are already in confirmed bookings.');
                }

                $bookingPilgrimModel->where('booking_id', $bookingId)->delete();
                foreach ($pilgrimIds as $pilgrimId) {
                    $bookingPilgrimModel->insert([
                        'booking_id' => $bookingId,
                        'pilgrim_id' => $pilgrimId,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Failed to update booking.');
            }

            (new AgentLedgerService())->syncBookingLedger($bookingId, $seasonId);

            return redirect()->to('/bookings')->with('success', 'Booking updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to($returnUrl)->withInput()->with('error', $e->getMessage());
        }
    }

    private function bookingFormData(int $seasonId, $editingBookingId = null): array
    {
        $db = db_connect();

        $packages = $db->table('packages')->where('season_id', $seasonId)->orderBy('departure_date', 'DESC')->get()->getResultArray();
        $packageIds = array_values(array_unique(array_map(static function (array $row): int {
            return (int) ($row['id'] ?? 0);
        }, $packages)));

        $packagePricingOptions = [];
        if ($packageIds !== []) {
            $costRows = $db->table('package_costs')
                ->select('package_id, cost_type, cost_amount, id')
                ->whereIn('package_id', $packageIds)
                ->whereIn('cost_type', self::PRICING_TIERS)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();

            foreach ($costRows as $costRow) {
                $packageId = (int) ($costRow['package_id'] ?? 0);
                $tier = strtolower(trim((string) ($costRow['cost_type'] ?? '')));
                if ($packageId < 1 || $tier === '' || ! in_array($tier, self::PRICING_TIERS, true)) {
                    continue;
                }

                if (! isset($packagePricingOptions[$packageId])) {
                    $packagePricingOptions[$packageId] = [];
                }

                if (! isset($packagePricingOptions[$packageId][$tier])) {
                    $packagePricingOptions[$packageId][$tier] = (float) ($costRow['cost_amount'] ?? 0);
                }
            }
        }

        $excludeConfirmedCondition = 'NOT EXISTS (
            SELECT 1
            FROM booking_pilgrims bp
            INNER JOIN bookings b ON b.id = bp.booking_id
            WHERE bp.pilgrim_id = pilgrims.id
              AND b.season_id = ' . (int) $seasonId . '
              AND b.status = "confirmed"';
        if ($editingBookingId !== null && $editingBookingId > 0) {
            $excludeConfirmedCondition .= ' AND b.id != ' . (int) $editingBookingId;
        }
        $excludeConfirmedCondition .= '
        )';

        return [
            'packages'  => $packages,
            'packagePricingOptions' => $packagePricingOptions,
            'agents'    => $db->table('agents')->orderBy('name', 'ASC')->get()->getResultArray(),
            'branches'  => $db->table('branches')->orderBy('name', 'ASC')->get()->getResultArray(),
            'companies' => company_table_ready()
                ? $db->table('companies')->orderBy('name', 'ASC')->get()->getResultArray()
                : [],
            'pilgrims'  => $db->table('pilgrims')
                ->select('id, first_name, last_name, passport_no')
                ->where('season_id', $seasonId)
                ->where($excludeConfirmedCondition, null, false)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray(),
        ];
    }

    private function resolvePackageTierPrice(int $packageId, string $pricingTier)
    {
        if ($packageId < 1) {
            return null;
        }

        $tier = strtolower(trim($pricingTier));
        if (! in_array($tier, self::PRICING_TIERS, true)) {
            return null;
        }

        $db = db_connect();

        $costRow = $db->table('package_costs')
            ->select('cost_amount')
            ->where('package_id', $packageId)
            ->where('cost_type', $tier)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if (! empty($costRow)) {
            return [
                'unit_price' => (float) ($costRow['cost_amount'] ?? 0),
                'source' => 'package_costs',
                'package_variant_id' => null,
            ];
        }

        $priceLine = $db->table('package_price_lines ppl')
            ->select('ppl.sell_price_pkr')
            ->join('package_cost_sheets pcs', 'pcs.id = ppl.cost_sheet_id', 'inner')
            ->where('pcs.package_id', $packageId)
            ->where('pcs.is_published', 1)
            ->where('ppl.sharing_type', $tier)
            ->orderBy('pcs.version_no', 'DESC')
            ->orderBy('ppl.id', 'DESC')
            ->get()
            ->getRowArray();

        if (! empty($priceLine)) {
            return [
                'unit_price' => (float) ($priceLine['sell_price_pkr'] ?? 0),
                'source' => 'package_price_lines',
                'package_variant_id' => null,
            ];
        }

        $variantRow = $db->table('package_variants')
            ->select('id, selling_price')
            ->where('package_id', $packageId)
            ->where('LOWER(room_type)', $tier)
            ->where('is_active', 1)
            ->orderBy('sequence_no', 'ASC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if (! empty($variantRow)) {
            return [
                'unit_price' => (float) ($variantRow['selling_price'] ?? 0),
                'source' => 'package_variants',
                'package_variant_id' => (int) ($variantRow['id'] ?? 0),
            ];
        }

        return null;
    }

    private function filterBookingColumns(array $data): array
    {
        $fields = db_connect()->getFieldNames('bookings');

        return array_filter(
            $data,
            static function ($value, $key) use ($fields): bool {
                return in_array($key, $fields, true);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function resolveReturnUrl(string $default): string
    {
        $returnUrl = trim((string) $this->request->getPost('return_url'));
        if ($returnUrl === '' || strpos($returnUrl, '/') !== 0) {
            return $default;
        }

        return $returnUrl;
    }

    public function deleteBooking()
    {
        $bookingId = (int) $this->request->getPost('booking_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }
        if ($bookingId < 1) {
            return redirect()->to('/bookings')->with('error', 'Valid booking ID is required for delete.');
        }

        try {
            $bookingModel = new BookingModel();
            $bookingPilgrimModel = new BookingPilgrimModel();
            $db = db_connect();

            $existing = $bookingModel->where('id', $bookingId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/bookings')->with('error', 'Booking not found in active season.');
            }

            $db->transStart();
            $bookingPilgrimModel->where('booking_id', $bookingId)->delete();
            $bookingModel->delete($bookingId);
            $db->transComplete();

            if (! $db->transStatus()) {
                throw new \RuntimeException('Failed to delete booking.');
            }

            (new AgentLedgerService())->removeBookingLedger($bookingId);

            return redirect()->to('/bookings')->with('success', 'Booking deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/bookings')->with('error', $e->getMessage());
        }
    }

    public function voucher(int $bookingId)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        if ($bookingId < 1) {
            return redirect()->to('/bookings')->with('error', 'Valid booking ID is required for voucher.');
        }

        $db = db_connect();

        $booking = $db->table('bookings b')
            ->select('b.*, p.code AS package_code, p.name AS package_name, p.package_type, c.id AS shirka_id, c.name AS shirka_name, c.logo_url AS shirka_logo_url, c.address AS shirka_address')
            ->join('packages p', 'p.id = b.package_id', 'left')
            ->join('companies c', 'c.id = b.company_id', 'left')
            ->where('b.season_id', $seasonId)
            ->where('b.id', $bookingId)
            ->get()
            ->getRowArray();

        if (empty($booking)) {
            return redirect()->to('/bookings')->with('error', 'Booking not found.');
        }

        $paymentRows = $db->table('payments')
            ->where('booking_id', $bookingId)
            ->where('season_id', $seasonId)
            ->where('status', 'posted')
            ->orderBy('payment_date', 'ASC')
            ->get()
            ->getResultArray();

        if ($paymentRows === []) {
            return redirect()->to('/bookings')->with('error', 'Final voucher is available after payment is posted for this booking.');
        }

        $flightRows = $db->table('package_flights pf')
            ->select('pf.*, f.departure_airport, f.arrival_airport, f.pnr')
            ->join('flights f', 'f.id = pf.flight_id', 'left')
            ->where('pf.package_id', (int) $booking['package_id'])
            ->orderBy('pf.departure_at', 'ASC')
            ->orderBy('pf.id', 'ASC')
            ->get()
            ->getResultArray();

        $hotelRows = $db->table('package_hotels ph')
            ->select('ph.*, h.city AS hotel_city, h.name AS hotel_master_name')
            ->join('hotels h', 'h.id = ph.hotel_id', 'left')
            ->where('ph.package_id', (int) $booking['package_id'])
            ->orderBy('ph.check_in_date', 'ASC')
            ->orderBy('ph.id', 'ASC')
            ->get()
            ->getResultArray();

        $transportRows = $db->table('package_transports pt')
            ->select('pt.*, t.transport_name AS master_transport_name')
            ->join('transports t', 't.id = pt.transport_id', 'left')
            ->where('pt.package_id', (int) $booking['package_id'])
            ->orderBy('pt.id', 'ASC')
            ->get()
            ->getResultArray();

        $transportRouteByTransport = [];
        $transportZiaratByTransport = [];

        if ($db->tableExists('transport_legs') && $transportRows !== []) {
            foreach ($transportRows as $transportRow) {
                $transportId = (int) ($transportRow['transport_id'] ?? 0);
                if ($transportId < 1) {
                    continue;
                }

                $legs = $db->table('transport_legs')
                    ->where('transport_id', $transportId)
                    ->orderBy('seq_no', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();

                if ($legs === []) {
                    continue;
                }

                $points = [];
                $ziaratSites = [];
                foreach ($legs as $index => $leg) {
                    $from = strtoupper(str_replace(' ', '', (string) ($leg['from_code'] ?? '')));
                    $to = strtoupper(str_replace(' ', '', (string) ($leg['to_code'] ?? '')));

                    if ($index === 0 && $from !== '') {
                        $points[] = $from;
                    }
                    if ($to !== '') {
                        $points[] = $to;
                    }

                    if ((int) ($leg['is_ziarat'] ?? 0) === 1) {
                        $site = trim((string) ($leg['ziarat_site'] ?? ''));
                        if ($site !== '') {
                            $ziaratSites[] = $site;
                        }
                    }
                }

                $route = implode('-', array_values(array_filter($points, static function (string $point): bool {
                    return $point !== '';
                })));

                if ($route !== '') {
                    $transportRouteByTransport[$transportId] = $route;
                }

                $transportZiaratByTransport[$transportId] = $ziaratSites !== []
                    ? implode(', ', array_values(array_unique($ziaratSites)))
                    : 'No';
            }
        }

        $pilgrimRows = $db->table('booking_pilgrims bp')
            ->select('p.first_name, p.last_name, p.passport_no, p.date_of_birth, p.gender')
            ->join('pilgrims p', 'p.id = bp.pilgrim_id', 'left')
            ->where('bp.booking_id', $bookingId)
            ->orderBy('bp.id', 'ASC')
            ->get()
            ->getResultArray();

        $totalPaid = 0.0;
        foreach ($paymentRows as $payment) {
            $amount = (float) ($payment['amount'] ?? 0);
            $totalPaid += (string) ($payment['payment_type'] ?? 'payment') === 'refund' ? -$amount : $amount;
        }

        $outboundFlight = $flightRows[0] ?? null;
        $returnFlight = count($flightRows) > 1 ? $flightRows[count($flightRows) - 1] : null;

        $arrivalAt = (string) ($outboundFlight['arrival_at'] ?? $outboundFlight['departure_at'] ?? '');
        $departureAt = (string) ($returnFlight['departure_at'] ?? $outboundFlight['departure_at'] ?? '');

        if ($arrivalAt !== '') {
            $booking['arrival_date'] = date('Y-m-d', strtotime($arrivalAt));
        }
        if ($departureAt !== '') {
            $booking['departure_date'] = date('Y-m-d', strtotime($departureAt));
        }

        if ($hotelRows !== []) {
            $hotelStart = null;
            $hotelEnd = null;
            $hotelNights = 0;

            foreach ($hotelRows as $hotel) {
                $checkIn = (string) ($hotel['check_in_date'] ?? '');
                $checkOut = (string) ($hotel['check_out_date'] ?? '');

                if ($checkIn === '' || $checkOut === '') {
                    continue;
                }

                $startTs = strtotime($checkIn);
                $endTs = strtotime($checkOut);
                if (! $startTs || ! $endTs || $endTs <= $startTs) {
                    continue;
                }

                $hotelNights += (int) floor(($endTs - $startTs) / 86400);
                $hotelStart = $hotelStart === null ? $startTs : min($hotelStart, $startTs);
                $hotelEnd = $hotelEnd === null ? $endTs : max($hotelEnd, $endTs);
            }

            if ($hotelNights > 0) {
                $booking['duration_days'] = $hotelNights;
            }
            if ($hotelStart !== null) {
                $booking['arrival_date'] = date('Y-m-d', $hotelStart);
            }
            if ($hotelEnd !== null) {
                $booking['departure_date'] = date('Y-m-d', $hotelEnd);
            }
        }

        if (empty($booking['duration_days']) && !empty($booking['arrival_date']) && !empty($booking['departure_date'])) {
            $start = strtotime((string) $booking['arrival_date']);
            $end = strtotime((string) $booking['departure_date']);
            if ($start && $end && $end >= $start) {
                $booking['duration_days'] = (int) floor(($end - $start) / 86400);
            }
        }

        return view('portal/bookings/voucher', [
            'title' => 'HJMS ERP | Final Voucher',
            'mainCompany' => main_company(),
            'shirkaCompany' => [
                'id' => $booking['shirka_id'] ?? null,
                'name' => $booking['shirka_name'] ?? '',
                'logo_url' => $booking['shirka_logo_url'] ?? '',
                'address' => $booking['shirka_address'] ?? '',
            ],
            'booking' => $booking,
            'payments' => $paymentRows,
            'totalPaid' => $totalPaid,
            'outboundFlight' => $outboundFlight,
            'returnFlight' => $returnFlight,
            'hotelRows' => $hotelRows,
            'transportRows' => $transportRows,
            'transportRouteByTransport' => $transportRouteByTransport,
            'transportZiaratByTransport' => $transportZiaratByTransport,
            'pilgrimRows' => $pilgrimRows,
            'voucherNo' => 'VCH-' . str_pad((string) $bookingId, 5, '0', STR_PAD_LEFT),
            'voucherDate' => date('d M Y'),
        ]);
    }
}
