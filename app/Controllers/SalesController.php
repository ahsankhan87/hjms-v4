<?php

namespace App\Controllers;

use App\Models\HotelModel;
use App\Models\PackageModel;
use App\Models\SaleItemModel;
use App\Models\SalesCategoryModel;
use App\Models\SalesModel;
use App\Services\SalesLedgerService;

class SalesController extends BaseController
{
    private const SALE_TYPES = ['visa', 'ticket', 'hotel', 'transport', 'package'];

    private $salesColumnExists = [];
    private $pilgrimDisplayCacheBySeason = [];
    private $newUploads = [];

    public function index()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $filters = [
            'from_date' => (string) $this->request->getGet('from_date'),
            'to_date' => (string) $this->request->getGet('to_date'),
            'component' => (string) $this->request->getGet('component'),
            'sales_category_id' => (string) $this->request->getGet('sales_category_id'),
            'customer_type' => (string) $this->request->getGet('customer_type'),
            'status' => (string) $this->request->getGet('status'),
        ];

        $filterErrors = [];
        if (! $this->validateData($filters, [
            'from_date' => 'permit_empty|valid_date[Y-m-d]',
            'to_date' => 'permit_empty|valid_date[Y-m-d]',
            'component' => 'permit_empty|in_list[visa,ticket,hotel,transport,package]',
            'sales_category_id' => 'permit_empty|integer',
            'customer_type' => 'permit_empty|in_list[agent,walk_in]',
            'status' => 'permit_empty|in_list[posted,voided,pending]',
        ])) {
            $filterErrors = $this->validator->getErrors();
            $filters = ['from_date' => '', 'to_date' => '', 'component' => '', 'sales_category_id' => '', 'customer_type' => '', 'status' => ''];
        }

        $db = db_connect();
        $query = $db->table('sales s')
            ->select('s.*, c.name AS category_name, a.name AS agent_name')
            ->join('sales_categories c', 'c.id = s.sales_category_id', 'left')
            ->join('agents a', 'a.id = s.agent_id', 'left')
            ->where('s.season_id', $seasonId);

        if ($this->salesHasColumn('pilgrim_id') && $db->tableExists('pilgrims')) {
            $query->select("CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, '')) AS pilgrim_name", false)
                ->select('p.passport_no AS pilgrim_passport_no')
                ->join('pilgrims p', 'p.id = s.pilgrim_id', 'left');
        } else {
            $query->select("'' AS pilgrim_name", false);
            $query->select("'' AS pilgrim_passport_no", false);
        }

        if ($filters['component'] !== '') {
            $query->groupStart()
                ->where('s.sale_type', $filters['component']);

            if ($db->tableExists('sale_items')) {
                $query->orWhere('s.id IN (SELECT sale_id FROM sale_items WHERE item_type = ' . $db->escape($filters['component']) . ')', null, false);
            }

            $query->groupEnd();
        }
        if ($filters['from_date'] !== '') {
            $query->where('DATE(s.sale_date) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $query->where('DATE(s.sale_date) <=', $filters['to_date']);
        }
        if ($filters['sales_category_id'] !== '') {
            $query->where('s.sales_category_id', (int) $filters['sales_category_id']);
        }
        if ($filters['customer_type'] !== '') {
            $query->where('s.customer_type', $filters['customer_type']);
        }
        if ($filters['status'] !== '') {
            $query->where('s.status', $filters['status']);
        }

        $rows = $query->orderBy('s.sale_date', 'DESC')->orderBy('s.id', 'DESC')->get()->getResultArray();
        $itemsBySale = $this->saleItemsForSales(array_map(static function (array $row): int {
            return (int) ($row['id'] ?? 0);
        }, $rows));

        $totalAmountPkr = 0.0;
        foreach ($rows as &$row) {
            $saleId = (int) ($row['id'] ?? 0);
            $items = $itemsBySale[$saleId] ?? [];

            $row['pax_display'] = $this->buildPaxDisplayForSaleRow($row, $seasonId);
            $row['component_counts'] = $this->componentCounts($row, $items);
            $row['item_attachments'] = $this->collectItemAttachments($items);
            $row['amount_pkr_total'] = $this->saleAmountPkr($row);
            $totalAmountPkr += (float) $row['amount_pkr_total'];
        }
        unset($row);

        return view('portal/sales/index', [
            'componentSummary' => $this->componentSummary($itemsBySale, $rows),
            'title' => 'HJMS ERP | Sale',
            'headerTitle' => 'Sale',
            'activePage' => 'sales',
            'userEmail' => (string) session('user_email'),
            'rows' => $rows,
            'categories' => (new SalesCategoryModel())->where('season_id', $seasonId)->orderBy('name', 'ASC')->findAll(),
            'saleTypes' => $this->saleTypeOptions(),
            'filters' => $filters,
            'filterErrors' => $filterErrors,
            'totalAmountPkr' => $totalAmountPkr,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        return view('portal/sales/add', $this->saleFormData($seasonId, [
            'title' => 'HJMS ERP | New Sale',
            'headerTitle' => 'Sale',
            'activePage' => 'sales',
        ]));
    }

    public function edit(int $id)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $row = (new SalesModel())->where('season_id', $seasonId)->find($id);
        if (empty($row)) {
            return redirect()->to('/sales')->with('error', 'Sale not found.');
        }

        $items = $this->saleItems($id);
        if ($items === []) {
            $items = $this->legacyItemsForSale($row);
        }

        $selectedPilgrimIds = $this->extractPilgrimIdsFromSaleRow($row);
        $pilgrims = $this->fetchPilgrimRows($seasonId, $selectedPilgrimIds);

        return view('portal/sales/edit', $this->saleFormData($seasonId, [
            'title' => 'HJMS ERP | Edit Sale',
            'headerTitle' => 'Sale',
            'activePage' => 'sales',
            'row' => $row,
            'saleItems' => $this->groupItems($items),
            'pilgrims' => $pilgrims,
        ]));
    }

    public function show(int $id)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $db = db_connect();
        $row = $db->table('sales s')
            ->select('s.*, c.name AS category_name, a.name AS agent_name, a.phone AS agent_phone')
            ->join('sales_categories c', 'c.id = s.sales_category_id', 'left')
            ->join('agents a', 'a.id = s.agent_id', 'left')
            ->where('s.season_id', $seasonId)
            ->where('s.id', $id)
            ->get()
            ->getRowArray();

        if (empty($row)) {
            return redirect()->to('/sales')->with('error', 'Sale not found.');
        }

        $items = $this->saleItems($id);
        $isLegacy = $items === [];
        if ($isLegacy) {
            $items = $this->legacyItemsForSale($row);
        }

        foreach ($items as &$item) {
            $amountPkr = (float) ($item['amount_pkr'] ?? 0);
            if ($amountPkr <= 0) {
                $rate = (float) ($item['exchange_rate'] ?? 1);
                $amountPkr = round((float) ($item['amount'] ?? 0) * ($rate > 0 ? $rate : 1), 2);
            }
            $item['amount_pkr'] = $amountPkr;
            $item['currency'] = (string) ($item['currency'] ?? '') !== '' ? (string) $item['currency'] : 'PKR';
        }
        unset($item);

        $pilgrimIds = $this->extractPilgrimIdsFromSaleRow($row);
        $pilgrims = [];
        if ($pilgrimIds !== [] && $db->tableExists('pilgrims')) {
            $pilgrims = $db->table('pilgrims')
                ->select('id, first_name, last_name, passport_no, gender, mobile_no, phone')
                ->whereIn('id', $pilgrimIds)
                ->orderBy('first_name', 'ASC')
                ->get()
                ->getResultArray();
        }

        $saleAttachments = array_values(array_filter([
            (string) ($row['attachment'] ?? ''),
            (string) ($row['attachment2'] ?? ''),
            (string) ($row['attachment3'] ?? ''),
        ], static function (string $file): bool {
            return $file !== '';
        }));

        return view('portal/sales/view', [
            'title' => 'HJMS ERP | Sale ' . (string) ($row['invoice_no'] ?? ''),
            'headerTitle' => 'Sale',
            'activePage' => 'sales',
            'userEmail' => (string) session('user_email'),
            'sale' => $row,
            'items' => $items,
            'isLegacy' => $isLegacy,
            'pilgrims' => $pilgrims,
            'saleAttachments' => $saleAttachments,
            'totalPkr' => $this->saleAmountPkr($row),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function createSale()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = $this->extractPayload();
        $error = $this->validatePayload($payload, $seasonId);
        if ($error !== null) {
            $this->discardNewUploads($payload);

            return redirect()->to('/sales/add')->withInput()->with($error['key'], $error['message']);
        }

        $db = db_connect();
        $model = new SalesModel();

        try {
            $db->transStart();

            $model->insert($this->buildSaleData($payload, $seasonId, true));
            $saleId = (int) $model->getInsertID();

            $this->replaceSaleItems($saleId, $payload['items']);

            (new SalesLedgerService())->syncSaleLedger($saleId, $seasonId);

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Unable to create sale at the moment.');
            }

            return redirect()->to('/sales')->with('success', 'Sale added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/sales/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateSale()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $saleId = (int) $this->request->getPost('sale_id');
        if ($saleId < 1) {
            return redirect()->to('/sales')->with('error', 'Valid sale ID is required.');
        }

        $model = new SalesModel();
        $row = $model->where('season_id', $seasonId)->find($saleId);
        if (empty($row)) {
            return redirect()->to('/sales')->with('error', 'Sale not found.');
        }

        $existingItems = $this->saleItems($saleId);
        $payload = $this->extractPayload($existingItems);
        $error = $this->validatePayload($payload, $seasonId);
        if ($error !== null) {
            $this->discardNewUploads($payload);

            return redirect()->to('/sales/' . $saleId . '/edit')->withInput()->with($error['key'], $error['message']);
        }

        $db = db_connect();

        try {
            $db->transStart();

            $data = $this->buildSaleData($payload, $seasonId, false);

            if ((string) $this->request->getPost('remove_attachment') === '1' && (string) ($row['attachment'] ?? '') !== '') {
                asset_upload_remove((string) $row['attachment'], 'sales');
                $data['attachment'] = null;
            } elseif ($payload['attachment'] !== '' && (string) ($row['attachment'] ?? '') !== '') {
                asset_upload_remove((string) $row['attachment'], 'sales');
            }

            $model->update($saleId, $data);

            $this->replaceSaleItems($saleId, $payload['items'], $existingItems);

            (new SalesLedgerService())->syncSaleLedger($saleId, $seasonId);

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Unable to update sale at the moment.');
            }

            return redirect()->to('/sales')->with('success', 'Sale updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/sales/' . $saleId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteSale()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $saleId = (int) $this->request->getPost('sale_id');
        if ($saleId < 1) {
            return redirect()->to('/sales')->with('error', 'Valid sale ID is required.');
        }

        $model = new SalesModel();
        $row = $model->where('season_id', $seasonId)->find($saleId);
        if (empty($row)) {
            return redirect()->to('/sales')->with('error', 'Sale not found.');
        }

        $items = $this->saleItems($saleId);
        $db = db_connect();

        try {
            $db->transStart();

            $model->delete($saleId);
            if ($db->tableExists('sale_items')) {
                $db->table('sale_items')->where('sale_id', $saleId)->delete();
            }
            (new SalesLedgerService())->syncSaleLedger($saleId, $seasonId);

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Unable to delete sale at the moment.');
            }

            foreach (['attachment', 'attachment2', 'attachment3'] as $attachmentField) {
                if ((string) ($row[$attachmentField] ?? '') !== '') {
                    asset_upload_remove((string) $row[$attachmentField], 'sales');
                }
            }
            foreach ($items as $item) {
                if ((string) ($item['attachment'] ?? '') !== '') {
                    asset_upload_remove((string) $item['attachment'], 'sales');
                }
            }

            return redirect()->to('/sales')->with('success', 'Sale deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/sales')->with('error', $e->getMessage());
        }
    }

    public function hotelIndex()
    {
        return redirect()->to('/sales?component=hotel');
    }

    public function ticketIndex()
    {
        return redirect()->to('/sales?component=ticket');
    }

    public function packageIndex()
    {
        return redirect()->to('/sales?component=package');
    }

    public function hotelAdd()
    {
        return redirect()->to('/sales/add?type=hotel');
    }

    public function ticketAdd()
    {
        return redirect()->to('/sales/add?type=ticket');
    }

    public function packageAdd()
    {
        return redirect()->to('/sales/add?type=package');
    }

    public function hotelEdit(int $id)
    {
        return redirect()->to('/sales/' . $id . '/edit');
    }

    public function ticketEdit(int $id)
    {
        return redirect()->to('/sales/' . $id . '/edit');
    }

    public function packageEdit(int $id)
    {
        return redirect()->to('/sales/' . $id . '/edit');
    }

    private function saleFormData(int $seasonId, array $extra = []): array
    {
        return array_merge([
            'title' => 'HJMS ERP | Sale',
            'headerTitle' => 'Sale',
            'activePage' => 'sales',
            'userEmail' => (string) session('user_email'),
            'agents' => $this->fetchAgentRows(),
            'pilgrims' => $this->fetchPilgrimRows($seasonId),
            'hotels' => $this->fetchHotelRows(),
            'transports' => $this->fetchTransportRows(),
            'packages' => $this->fetchPackageRows($seasonId),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?: [],
        ], $extra);
    }

    /**
     * Read the sale header and every sale component from the request.
     * Existing item attachments are carried forward when no replacement file is uploaded.
     */
    private function extractPayload(array $existingItems = []): array
    {
        $this->newUploads = [];

        $pilgrimIds = $this->request->getPost('pilgrim_ids');
        if (! is_array($pilgrimIds)) {
            $pilgrimIds = [];
        }
        $pilgrimIds = array_values(array_unique(array_filter(array_map(static function ($id) {
            return (int) $id;
        }, $pilgrimIds), static function ($id) {
            return $id > 0;
        })));

        $payload = [
            'sale_type' => '',
            'invoice_no' => trim((string) $this->request->getPost('invoice_no')),
            'sale_date' => (string) $this->request->getPost('sale_date'),
            'sales_category_id' => null,
            'customer_type' => (string) ($this->request->getPost('customer_type') ?: 'walk_in'),
            'agent_id' => (string) $this->request->getPost('agent_id'),
            'customer_name' => trim((string) $this->request->getPost('customer_name')),
            'currency' => 'PKR',
            'exchange_rate' => 1.0,
            'note' => trim((string) $this->request->getPost('note')),
            'status' => (string) ($this->request->getPost('status') ?: 'posted'),
            'attachment' => $this->storeUpload('attachment'),
            'pilgrim_ids' => $pilgrimIds,
            'items' => $this->extractItemPayload($existingItems),
        ];

        $payload['new_uploads'] = $this->newUploads;

        return $payload;
    }

    private function extractItemPayload(array $existingItems = []): array
    {
        $existingIndex = $this->indexItemsByTypePosition($existingItems);
        $items = [];

        $visaType = trim((string) $this->request->getPost('visa_type'));
        $visaNo = trim((string) $this->request->getPost('visa_no'));
        $visaAmount = (string) $this->request->getPost('visa_amount');
        $visaUpload = $this->storeUpload('visa_attachment');
        if ($visaType !== '' || $visaNo !== '' || (float) $visaAmount > 0 || $visaUpload !== '') {
            $items[] = [
                'item_type' => 'visa',
                'amount' => (float) $visaAmount,
                'currency' => (string) $this->request->getPost('visa_currency'),
                'exchange_rate' => (string) $this->request->getPost('visa_exchange_rate'),
                'visa_type' => $visaType !== '' ? $visaType : null,
                'visa_no' => $visaNo !== '' ? $visaNo : null,
                'attachment' => $visaUpload !== '' ? $visaUpload : (string) ($existingIndex['visa:0']['attachment'] ?? ''),
            ];
        }

        $ticketPnr = trim((string) $this->request->getPost('ticket_pnr'));
        $ticketSector = trim((string) $this->request->getPost('ticket_sector'));
        $ticketNo = trim((string) $this->request->getPost('ticket_no'));
        $ticketAmount = (string) $this->request->getPost('ticket_amount');
        $ticketUpload = $this->storeUpload('ticket_attachment');
        if ($ticketPnr !== '' || $ticketSector !== '' || $ticketNo !== '' || (float) $ticketAmount > 0 || $ticketUpload !== '') {
            $items[] = [
                'item_type' => 'ticket',
                'amount' => (float) $ticketAmount,
                'currency' => (string) $this->request->getPost('ticket_currency'),
                'exchange_rate' => (string) $this->request->getPost('ticket_exchange_rate'),
                'pnr' => $ticketPnr !== '' ? $ticketPnr : null,
                'sector' => $ticketSector !== '' ? $ticketSector : null,
                'ticket_no' => $ticketNo !== '' ? $ticketNo : null,
                'attachment' => $ticketUpload !== '' ? $ticketUpload : (string) ($existingIndex['ticket:0']['attachment'] ?? ''),
            ];
        }

        $packageName = trim((string) $this->request->getPost('package_name'));
        $packageDetails = trim((string) $this->request->getPost('package_details'));
        $packageId = (int) $this->request->getPost('package_id');
        $departureDate = (string) $this->request->getPost('departure_date');
        $arrivalDate = (string) $this->request->getPost('arrival_date');
        $packageAmount = (string) $this->request->getPost('package_amount');
        $packageUpload = $this->storeUpload('package_attachment');
        if ($packageName !== '' || $packageDetails !== '' || $packageId > 0 || $departureDate !== '' || $arrivalDate !== '' || (float) $packageAmount > 0 || $packageUpload !== '') {
            $items[] = [
                'item_type' => 'package',
                'amount' => (float) $packageAmount,
                'currency' => (string) $this->request->getPost('package_currency'),
                'exchange_rate' => (string) $this->request->getPost('package_exchange_rate'),
                'package_id' => $packageId > 0 ? $packageId : null,
                'package_name' => $packageName !== '' ? $packageName : null,
                'description' => $packageDetails !== '' ? $packageDetails : null,
                'departure_date' => $departureDate !== '' ? $departureDate : null,
                'arrival_date' => $arrivalDate !== '' ? $arrivalDate : null,
                'attachment' => $packageUpload !== '' ? $packageUpload : (string) ($existingIndex['package:0']['attachment'] ?? ''),
            ];
        }

        $hotelNames = $this->request->getPost('hotel_name');
        $hotelNames = is_array($hotelNames) ? $hotelNames : [];
        $hotelCheckIns = (array) $this->request->getPost('hotel_check_in');
        $hotelCheckOuts = (array) $this->request->getPost('hotel_check_out');
        $hotelRoomQtys = (array) $this->request->getPost('hotel_room_qty');
        $hotelRoomTypes = (array) $this->request->getPost('hotel_room_type');
        $hotelAmounts = (array) $this->request->getPost('hotel_amount');
        $hotelCurrencies = (array) $this->request->getPost('hotel_currency');
        $hotelRates = (array) $this->request->getPost('hotel_exchange_rate');

        $hotelPosition = 0;
        foreach ($hotelNames as $index => $hotelName) {
            $hotelName = trim((string) $hotelName);
            $checkIn = (string) ($hotelCheckIns[$index] ?? '');
            $checkOut = (string) ($hotelCheckOuts[$index] ?? '');
            $roomType = trim((string) ($hotelRoomTypes[$index] ?? ''));
            $amount = (float) ($hotelAmounts[$index] ?? 0);

            if ($hotelName === '' && $checkIn === '' && $checkOut === '' && $roomType === '' && $amount <= 0) {
                continue;
            }

            $items[] = [
                'item_type' => 'hotel',
                'amount' => $amount,
                'currency' => (string) ($hotelCurrencies[$index] ?? ''),
                'exchange_rate' => (string) ($hotelRates[$index] ?? ''),
                'hotel_name' => $hotelName !== '' ? $hotelName : null,
                'check_in_date' => $checkIn !== '' ? $checkIn : null,
                'check_out_date' => $checkOut !== '' ? $checkOut : null,
                'no_of_nights' => $this->nightsBetween($checkIn, $checkOut),
                'room_qty' => max(1, (int) ($hotelRoomQtys[$index] ?? 1)),
                'room_type' => $roomType !== '' ? $roomType : null,
                'attachment' => (string) ($existingIndex['hotel:' . $hotelPosition]['attachment'] ?? ''),
            ];
            $hotelPosition++;
        }

        $transportTypes = (array) $this->request->getPost('transport_type');
        $transportIds = (array) $this->request->getPost('transport_id');
        $transportRoutes = (array) $this->request->getPost('transport_route');
        $transportZiarat = (array) $this->request->getPost('transport_ziarat');
        $transportAmounts = (array) $this->request->getPost('transport_amount');
        $transportCurrencies = (array) $this->request->getPost('transport_currency');
        $transportRates = (array) $this->request->getPost('transport_exchange_rate');
        $transportUploads = array_values($this->storeUploadMultiple('transport_attachment'));

        $transportPosition = 0;
        foreach ($transportTypes as $index => $transportType) {
            $transportType = trim((string) $transportType);
            $transportId = (int) ($transportIds[$index] ?? 0);
            $route = trim((string) ($transportRoutes[$index] ?? ''));
            $amount = (float) ($transportAmounts[$index] ?? 0);
            $upload = (string) ($transportUploads[$transportPosition] ?? '');

            if ($transportType === '' && $transportId < 1 && $route === '' && $amount <= 0 && $upload === '') {
                continue;
            }

            $items[] = [
                'item_type' => 'transport',
                'amount' => $amount,
                'currency' => (string) ($transportCurrencies[$index] ?? ''),
                'exchange_rate' => (string) ($transportRates[$index] ?? ''),
                'transport_id' => $transportId > 0 ? $transportId : null,
                'transport_type' => $transportType !== '' ? $transportType : null,
                'route' => $route !== '' ? $route : null,
                'is_ziarat' => (int) ($transportZiarat[$index] ?? 0) === 1 ? 1 : 0,
                'attachment' => $upload !== '' ? $upload : (string) ($existingIndex['transport:' . $transportPosition]['attachment'] ?? ''),
            ];
            $transportPosition++;
        }

        return $items;
    }

    private function validatePayload(array &$payload, int $seasonId)
    {
        $itemErrors = $this->validateItems($payload['items']);
        if ($itemErrors !== []) {
            return ['key' => 'errors', 'message' => $itemErrors];
        }

        // Every component carries its own currency, so the sale total is the PKR sum.
        $total = 0.0;
        foreach ($payload['items'] as $item) {
            $total += (float) ($item['amount_pkr'] ?? 0);
        }
        $payload['amount'] = round($total, 2);
        $payload['amount_pkr'] = round($total, 2);
        $payload['currency'] = 'PKR';
        $payload['exchange_rate'] = 1.0;
        $payload['sale_type'] = $this->deriveSaleType($payload['items']);
        $payload['sales_category_id'] = $this->deriveCategoryId($seasonId, (string) $payload['sale_type']);

        if (! $this->validateData($payload, [
            'invoice_no' => 'permit_empty|max_length[40]',
            'sale_date' => 'required|valid_date[Y-m-d]',
            'sale_type' => 'required|in_list[visa,ticket,hotel,transport,package]',
            'customer_type' => 'required|in_list[agent,walk_in]',
            'agent_id' => 'permit_empty|integer',
            'customer_name' => 'permit_empty|max_length[160]',
            'note' => 'permit_empty|max_length[5000]',
            'status' => 'required|in_list[posted,voided,pending]',
        ])) {
            return ['key' => 'errors', 'message' => array_values($this->validator->getErrors())];
        }

        if ((float) $payload['amount'] <= 0) {
            return ['key' => 'errors', 'message' => ['Enter an amount for at least one sale component.']];
        }

        $pilgrimIds = (array) ($payload['pilgrim_ids'] ?? []);
        if ($pilgrimIds === []) {
            return ['key' => 'error', 'message' => 'At least one pilgrim is required.'];
        }

        $pilgrimRows = db_connect()->table('pilgrims')
            ->select('id, first_name, last_name')
            ->where('season_id', $seasonId)
            ->whereIn('id', $pilgrimIds)
            ->get()
            ->getResultArray();

        if (count($pilgrimRows) !== count($pilgrimIds)) {
            return ['key' => 'error', 'message' => 'One or more selected pilgrims do not exist for active season.'];
        }

        $pilgrimNameMap = [];
        foreach ($pilgrimRows as $pilgrimRow) {
            $pilgrimNameMap[(int) $pilgrimRow['id']] = trim((string) ($pilgrimRow['first_name'] ?? '') . ' ' . (string) ($pilgrimRow['last_name'] ?? ''));
        }

        $payload['pilgrim_id'] = (int) $pilgrimIds[0];
        $payload['pax_name'] = implode(', ', array_values(array_filter(array_map(static function ($id) use ($pilgrimNameMap) {
            return (string) ($pilgrimNameMap[(int) $id] ?? '');
        }, $pilgrimIds))));

        if ($payload['customer_type'] === 'walk_in') {
            if ($payload['customer_name'] === '') {
                return ['key' => 'error', 'message' => 'Customer name is required for walk-in sales.'];
            }

            $payload['agent_id'] = '';

            return null;
        }

        if ((int) $payload['agent_id'] < 1) {
            return ['key' => 'error', 'message' => 'Agent is required for agent sales.'];
        }

        $agent = db_connect()->table('agents')->select('id')->where('id', (int) $payload['agent_id'])->get()->getRowArray();
        if (empty($agent)) {
            return ['key' => 'error', 'message' => 'Selected agent does not exist.'];
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function validateItems(array &$items): array
    {
        if ($items === []) {
            return ['Add at least one sale component: visa, ticket, hotel, transport or package.'];
        }

        $errors = [];
        foreach ($items as &$item) {
            $type = (string) ($item['item_type'] ?? '');

            $currency = strtoupper(trim((string) ($item['currency'] ?? '')));
            $rate = (float) ($item['exchange_rate'] ?? 0);
            if ($currency !== 'SAR') {
                $currency = 'PKR';
                $rate = 1.0;
            } elseif ($rate <= 0) {
                $label = $type !== '' ? ucfirst($type) : 'sale';
                $errors[] = 'Exchange rate is required for the ' . $label . ' amount in SAR.';
                $rate = 0.0;
            }

            $item['currency'] = $currency;
            $item['exchange_rate'] = $rate;
            $item['amount'] = round((float) ($item['amount'] ?? 0), 2);
            $item['amount_pkr'] = round($item['amount'] * $rate, 2);

            if ($type === 'visa') {
                if ((string) ($item['visa_type'] ?? '') === '') {
                    $errors[] = 'Visa type is required for the visa component.';
                }
            } elseif ($type === 'ticket') {
                foreach (['pnr' => 'PNR', 'sector' => 'Sector', 'ticket_no' => 'Ticket No'] as $field => $label) {
                    if ((string) ($item[$field] ?? '') === '') {
                        $errors[] = $label . ' is required for the ticket component.';
                    }
                }
            } elseif ($type === 'hotel') {
                if ((string) ($item['hotel_name'] ?? '') === '') {
                    $errors[] = 'Hotel name is required for every hotel stay.';
                }
                if ((string) ($item['check_in_date'] ?? '') === '' || (string) ($item['check_out_date'] ?? '') === '') {
                    $errors[] = 'Check-in and check-out dates are required for every hotel stay.';
                } elseif ((string) $item['check_out_date'] < (string) $item['check_in_date']) {
                    $errors[] = 'Hotel check-out date must be on or after the check-in date.';
                }
            } elseif ($type === 'transport') {
                if ((string) ($item['transport_type'] ?? '') === '' && (string) ($item['route'] ?? '') === '') {
                    $errors[] = 'Transport type or route is required for every transport entry.';
                }
            } elseif ($type === 'package') {
                if ((string) ($item['package_name'] ?? '') === '') {
                    $errors[] = 'Package name is required for the package component.';
                }
            }
        }
        unset($item);

        return $errors;
    }

    /**
     * A single component keeps its own type; a combined sale is treated as a package.
     */
    private function deriveSaleType(array $items): string
    {
        $types = [];
        foreach ($items as $item) {
            $type = (string) ($item['item_type'] ?? '');
            if (in_array($type, self::SALE_TYPES, true)) {
                $types[$type] = true;
            }
        }

        if (count($types) === 1) {
            return (string) array_key_first($types);
        }

        return $types === [] ? 'visa' : 'package';
    }

    /**
     * Category is no longer selected on the form; reuse the matching category when one exists.
     */
    private function deriveCategoryId(int $seasonId, string $saleType): ?int
    {
        $rows = (new SalesCategoryModel())
            ->where('season_id', $seasonId)
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ((array) $rows as $row) {
            if (strtolower(trim((string) ($row['name'] ?? ''))) === strtolower($saleType)) {
                return (int) $row['id'];
            }
        }

        return null;
    }

    private function buildSaleData(array $payload, int $seasonId, bool $isCreate): array
    {
        $now = date('Y-m-d H:i:s');
        $userId = (int) (session('user_id') ?? 0);

        if ((string) ($payload['invoice_no'] ?? '') === '') {
            $payload['invoice_no'] = $this->generateInvoiceNo($seasonId, (string) $payload['sale_type']);
        }

        $agentId = (int) $payload['agent_id'];
        $customerType = (string) $payload['customer_type'];

        $data = [
            'season_id' => $seasonId,
            'sale_type' => $payload['sale_type'],
            'invoice_no' => $payload['invoice_no'] !== '' ? $payload['invoice_no'] : null,
            'sales_category_id' => $payload['sales_category_id'] !== null ? (int) $payload['sales_category_id'] : null,
            'sale_date' => $payload['sale_date'],
            'customer_type' => $customerType,
            'agent_id' => $agentId > 0 ? $agentId : null,
            'pilgrim_id' => (int) $payload['pilgrim_id'] > 0 ? (int) $payload['pilgrim_id'] : null,
            'pilgrim_ids' => ! empty($payload['pilgrim_ids']) ? implode(',', array_map(static function ($id) {
                return (string) (int) $id;
            }, (array) $payload['pilgrim_ids'])) : null,
            'pax_name' => $payload['pax_name'] !== '' ? $payload['pax_name'] : null,
            'customer_name' => $customerType === 'walk_in' ? ($payload['customer_name'] !== '' ? $payload['customer_name'] : null) : null,
            'amount' => (float) $payload['amount'],
            'currency' => 'PKR',
            'exchange_rate' => 1.0,
            'amount_pkr' => (float) $payload['amount_pkr'],
            'note' => $payload['note'] !== '' ? $payload['note'] : null,
            'status' => $payload['status'],
            'updated_at' => $now,
        ];

        if ($payload['attachment'] !== '') {
            $data['attachment'] = $payload['attachment'];
        } elseif ($isCreate) {
            $data['attachment'] = null;
        }

        foreach ($this->legacyColumnsFromItems($payload['items']) as $column => $value) {
            if ($this->salesHasColumn($column)) {
                $data[$column] = $value;
            }
        }

        if ($isCreate) {
            $data['created_at'] = $now;
            $data['created_by'] = $userId > 0 ? $userId : null;
            $data['payment_method'] = null;
            $data['reference_no'] = null;
        }

        foreach (['sale_type', 'invoice_no', 'pilgrim_id', 'pax_name', 'pilgrim_ids', 'currency', 'exchange_rate', 'amount_pkr'] as $column) {
            if (! $this->salesHasColumn($column)) {
                unset($data[$column]);
            }
        }

        return $data;
    }

    /**
     * Mirror the first component values into the legacy flat sale columns so
     * existing reports and credit notes keep working for single-component sales.
     *
     * @return array<string, mixed>
     */
    private function legacyColumnsFromItems(array $items): array
    {
        $first = static function (array $items, string $type): array {
            foreach ($items as $item) {
                if ((string) ($item['item_type'] ?? '') === $type) {
                    return $item;
                }
            }

            return [];
        };

        $visa = $first($items, 'visa');
        $ticket = $first($items, 'ticket');
        $hotel = $first($items, 'hotel');
        $package = $first($items, 'package');

        return [
            'visa_type' => $visa['visa_type'] ?? null,
            'hotel_name' => $hotel['hotel_name'] ?? null,
            'checkin_date' => $hotel['check_in_date'] ?? null,
            'checkout_date' => $hotel['check_out_date'] ?? null,
            'no_of_nights' => isset($hotel['no_of_nights']) ? (int) $hotel['no_of_nights'] : null,
            'room_name' => $hotel['room_type'] ?? null,
            'room_qty' => isset($hotel['room_qty']) ? (int) $hotel['room_qty'] : null,
            'pnr' => $ticket['pnr'] ?? null,
            'sector' => $ticket['sector'] ?? null,
            'ticket_no' => $ticket['ticket_no'] ?? null,
            'package_name' => $package['package_name'] ?? null,
            'package_details' => $package['description'] ?? null,
        ];
    }

    private function replaceSaleItems(int $saleId, array $items, array $previousItems = []): void
    {
        $db = db_connect();
        if (! $db->tableExists('sale_items')) {
            return;
        }

        $db->table('sale_items')->where('sale_id', $saleId)->delete();

        $model = new SaleItemModel();
        $keptAttachments = [];

        foreach (array_values($items) as $position => $item) {
            $attachment = (string) ($item['attachment'] ?? '');
            if ($attachment !== '') {
                $keptAttachments[$attachment] = true;
            }

            $item['sale_id'] = $saleId;
            $item['sort_order'] = $position;
            $item['attachment'] = $attachment !== '' ? $attachment : null;
            $model->insert($item);
        }

        foreach ($previousItems as $previousItem) {
            $previousAttachment = (string) ($previousItem['attachment'] ?? '');
            if ($previousAttachment !== '' && ! isset($keptAttachments[$previousAttachment])) {
                asset_upload_remove($previousAttachment, 'sales');
            }
        }
    }

    private function saleItems(int $saleId): array
    {
        $db = db_connect();
        if (! $db->tableExists('sale_items')) {
            return [];
        }

        $rows = $db->table('sale_items')
            ->where('sale_id', $saleId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param  list<int>         $saleIds
     * @return array<int, array> Sale id to component rows
     */
    private function saleItemsForSales(array $saleIds): array
    {
        $saleIds = array_values(array_unique(array_filter(array_map('intval', $saleIds), static function (int $id): bool {
            return $id > 0;
        })));

        if ($saleIds === []) {
            return [];
        }

        $db = db_connect();
        if (! $db->tableExists('sale_items')) {
            return [];
        }

        $rows = $db->table('sale_items')
            ->whereIn('sale_id', $saleIds)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ((array) $rows as $row) {
            $map[(int) $row['sale_id']][] = $row;
        }

        return $map;
    }

    /**
     * Sales created before the unified page only have flat columns. Convert them
     * into component rows so the unified edit form can show and re-save them.
     */
    private function legacyItemsForSale(array $row): array
    {
        $saleId = (int) ($row['id'] ?? 0);
        $saleType = (string) ($row['sale_type'] ?? 'visa');
        $amount = (float) ($row['amount'] ?? 0);

        if ($saleType === 'hotel') {
            $stays = $this->legacyHotelStays($saleId);
            if ($stays === [] && trim((string) ($row['hotel_name'] ?? '')) !== '') {
                $stays = [[
                    'hotel_name' => (string) $row['hotel_name'],
                    'check_in_date' => (string) ($row['checkin_date'] ?? ''),
                    'check_out_date' => (string) ($row['checkout_date'] ?? ''),
                    'room_name' => (string) ($row['room_name'] ?? ''),
                    'room_qty' => (int) ($row['room_qty'] ?? 1),
                ]];
            }

            if ($stays === []) {
                return [[
                    'item_type' => 'hotel',
                    'amount' => $amount,
                    'currency' => 'PKR',
                    'exchange_rate' => 1.0,
                    'amount_pkr' => $amount,
                    'check_in_date' => (string) ($row['checkin_date'] ?? ''),
                    'check_out_date' => (string) ($row['checkout_date'] ?? ''),
                ]];
            }

            $items = [];
            foreach (array_values($stays) as $position => $stay) {
                $checkIn = (string) ($stay['check_in_date'] ?? '');
                $checkOut = (string) ($stay['check_out_date'] ?? '');
                $items[] = [
                    'item_type' => 'hotel',
                    'amount' => $position === 0 ? $amount : 0.0,
                    'currency' => 'PKR',
                    'exchange_rate' => 1.0,
                    'amount_pkr' => $position === 0 ? $amount : 0.0,
                    'hotel_name' => (string) ($stay['hotel_name'] ?? ''),
                    'check_in_date' => $checkIn,
                    'check_out_date' => $checkOut,
                    'no_of_nights' => $this->nightsBetween($checkIn, $checkOut),
                    'room_qty' => (int) ($stay['room_qty'] ?? 1),
                    'room_type' => (string) ($stay['room_name'] ?? ''),
                ];
            }

            return $items;
        }

        if ($saleType === 'ticket') {
            return [[
                'item_type' => 'ticket',
                'amount' => $amount,
                'currency' => 'PKR',
                'exchange_rate' => 1.0,
                'amount_pkr' => $amount,
                'pnr' => (string) ($row['pnr'] ?? ''),
                'sector' => (string) ($row['sector'] ?? ''),
                'ticket_no' => (string) ($row['ticket_no'] ?? ''),
            ]];
        }

        if ($saleType === 'package') {
            return [[
                'item_type' => 'package',
                'amount' => $amount,
                'currency' => 'PKR',
                'exchange_rate' => 1.0,
                'amount_pkr' => $amount,
                'package_name' => (string) ($row['package_name'] ?? ''),
                'description' => (string) ($row['package_details'] ?? ''),
            ]];
        }

        return [[
            'item_type' => 'visa',
            'amount' => $amount,
            'currency' => 'PKR',
            'exchange_rate' => 1.0,
            'amount_pkr' => $amount,
            'visa_type' => (string) ($row['visa_type'] ?? ''),
        ]];
    }

    private function legacyHotelStays(int $saleId): array
    {
        $db = db_connect();
        if ($saleId < 1 || ! $db->tableExists('sale_hotel_stays')) {
            return [];
        }

        $rows = $db->table('sale_hotel_stays')
            ->where('sale_id', $saleId)
            ->orderBy('check_in_date', 'ASC')
            ->get()
            ->getResultArray();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array{visa: array, ticket: array, hotel: array, transport: array, package: array}
     */
    private function groupItems(array $items): array
    {
        $grouped = ['visa' => [], 'ticket' => [], 'hotel' => [], 'transport' => [], 'package' => []];

        foreach ($items as $item) {
            $type = (string) ($item['item_type'] ?? '');
            if (isset($grouped[$type])) {
                $grouped[$type][] = $item;
            }
        }

        return [
            'visa' => $grouped['visa'][0] ?? [],
            'ticket' => $grouped['ticket'][0] ?? [],
            'hotel' => $grouped['hotel'],
            'transport' => $grouped['transport'],
            'package' => $grouped['package'][0] ?? [],
        ];
    }

    /**
     * Issued counts and PKR value per component type for the currently listed sales.
     *
     * @return array<string, array{count: int, total_pkr: float}>
     */
    private function componentSummary(array $itemsBySale, array $rows): array
    {
        $summary = [];
        foreach (self::SALE_TYPES as $type) {
            $summary[$type] = ['count' => 0, 'total_pkr' => 0.0];
        }

        foreach ($rows as $row) {
            $items = $itemsBySale[(int) ($row['id'] ?? 0)] ?? [];

            if ($items === []) {
                $type = (string) ($row['sale_type'] ?? '');
                if (isset($summary[$type])) {
                    $summary[$type]['count']++;
                    $summary[$type]['total_pkr'] += $this->saleAmountPkr($row);
                }
                continue;
            }

            foreach ($items as $item) {
                $type = (string) ($item['item_type'] ?? '');
                if (! isset($summary[$type])) {
                    continue;
                }

                $amountPkr = (float) ($item['amount_pkr'] ?? 0);
                $summary[$type]['count']++;
                $summary[$type]['total_pkr'] += $amountPkr > 0 ? $amountPkr : (float) ($item['amount'] ?? 0);
            }
        }

        return $summary;
    }

    private function componentCounts(array $saleRow, array $items): array
    {
        $counts = [];
        foreach ($items as $item) {
            $type = (string) ($item['item_type'] ?? '');
            if ($type === '') {
                continue;
            }
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }

        if ($counts === []) {
            $type = (string) ($saleRow['sale_type'] ?? 'visa');
            if ($type !== '') {
                $counts[$type] = 1;
            }
        }

        return $counts;
    }

    private function collectItemAttachments(array $items): array
    {
        $attachments = [];
        foreach ($items as $item) {
            $attachment = trim((string) ($item['attachment'] ?? ''));
            if ($attachment !== '') {
                $attachments[] = [
                    'file' => $attachment,
                    'type' => (string) ($item['item_type'] ?? ''),
                ];
            }
        }

        return $attachments;
    }

    private function saleAmountPkr(array $row): float
    {
        $amountPkr = (float) ($row['amount_pkr'] ?? 0);
        if ($amountPkr > 0) {
            return $amountPkr;
        }

        $amount = (float) ($row['amount'] ?? 0);
        $rate = (float) ($row['exchange_rate'] ?? 1);

        return round($amount * ($rate > 0 ? $rate : 1), 2);
    }

    private function indexItemsByTypePosition(array $items): array
    {
        $indexed = [];
        $counters = [];

        foreach ($items as $item) {
            $type = (string) ($item['item_type'] ?? '');
            if ($type === '') {
                continue;
            }

            $position = $counters[$type] ?? 0;
            $indexed[$type . ':' . $position] = $item;
            $counters[$type] = $position + 1;
        }

        return $indexed;
    }

    private function nightsBetween(string $checkIn, string $checkOut): ?int
    {
        $inTime = strtotime($checkIn);
        $outTime = strtotime($checkOut);
        if ($inTime === false || $outTime === false) {
            return null;
        }

        return max(1, (int) floor(($outTime - $inTime) / 86400));
    }

    private function storeUpload(string $field): string
    {
        $file = $this->request->getFile($field);
        if (! $file || is_array($file) || ! $file->isValid() || $file->hasMoved()) {
            return '';
        }

        return $this->moveUploadedFile($file);
    }

    /**
     * @return list<string> Positional list of stored file names for uploaded rows
     */
    private function storeUploadMultiple(string $field): array
    {
        $files = $this->request->getFileMultiple($field);
        if (! is_array($files)) {
            return [];
        }

        $stored = [];
        foreach ($files as $file) {
            $stored[] = ($file && $file->isValid() && ! $file->hasMoved())
                ? $this->moveUploadedFile($file)
                : '';
        }

        return $stored;
    }

    private function moveUploadedFile($file): string
    {
        $filename = $file->getRandomName();
        $file->move(asset_upload_dir('sales'), $filename);
        $this->newUploads[] = $filename;

        return $filename;
    }

    private function discardNewUploads(array $payload): void
    {
        foreach ((array) ($payload['new_uploads'] ?? []) as $filename) {
            if ((string) $filename !== '') {
                asset_upload_remove((string) $filename, 'sales');
            }
        }
    }

    private function fetchAgentRows(): array
    {
        $db = db_connect();
        if (! $db->tableExists('agents')) {
            return [];
        }

        $select = $db->fieldExists('is_active', 'agents') ? 'id, name, is_active' : 'id, name';
        $query = $db->table('agents')->select($select)->orderBy('name', 'ASC')->get();
        if (! is_object($query)) {
            return [];
        }

        return $query->getResultArray();
    }

    /**
     * Pilgrims selectable on a sale form.
     *
     * A pilgrim is hidden once they have "availed" — either they belong to a confirmed
     * booking, or they already appear on a non-voided sale for the active season. They
     * become selectable again only after being released on the pilgrim view page.
     * Pass $includePilgrimIds to keep the pilgrims already attached to the sale being edited.
     */
    private function fetchPilgrimRows(int $seasonId, array $includePilgrimIds = []): array
    {
        $db = db_connect();
        if (! $db->tableExists('pilgrims')) {
            return [];
        }

        $includePilgrimIds = array_values(array_filter(array_map('intval', $includePilgrimIds), static function (int $id): bool {
            return $id > 0;
        }));

        $query = $db->table('pilgrims')
            ->select('id, first_name, last_name, passport_no')
            ->where('season_id', $seasonId);

        $hasBookingTables = $db->tableExists('bookings') && $db->tableExists('booking_pilgrims');
        $hasSalesTable = $db->tableExists('sales') && $this->salesHasColumn('pilgrim_id');

        if ($hasBookingTables || $hasSalesTable) {
            $availedCondition = pilgrim_availed_sql(
                (string) (int) $seasonId,
                'pilgrims.id',
                'pilgrims.sale_released_at',
                '',
                $hasBookingTables,
                $hasSalesTable,
                $hasSalesTable && $this->salesHasColumn('pilgrim_ids')
            );

            if ($includePilgrimIds !== []) {
                $query->groupStart()
                    ->where($availedCondition, null, false)
                    ->orWhereIn('id', $includePilgrimIds)
                    ->groupEnd();
            } else {
                $query->where($availedCondition, null, false);
            }
        }

        $query->orderBy('first_name', 'ASC')->orderBy('last_name', 'ASC');
        $result = $query->get();

        if (! is_object($result)) {
            return [];
        }

        return $result->getResultArray();
    }

    private function fetchHotelRows(): array
    {
        $db = db_connect();
        if (! $db->tableExists('hotels')) {
            return [];
        }

        $query = (new HotelModel())
            ->select('id, name, city')
            ->orderBy('name', 'ASC')
            ->findAll();

        return is_array($query) ? $query : [];
    }

    /**
     * Transport master records with their legs, used to prefill type and route.
     */
    private function fetchTransportRows(): array
    {
        $db = db_connect();
        if (! $db->tableExists('transports')) {
            return [];
        }

        $rows = $db->table('transports')
            ->select('id, transport_name, provider_name, vehicle_type')
            ->orderBy('transport_name', 'ASC')
            ->get()
            ->getResultArray();

        if (! is_array($rows) || $rows === []) {
            return [];
        }

        $legsByTransport = [];
        if ($db->tableExists('transport_legs')) {
            $legs = $db->table('transport_legs')
                ->select('transport_id, from_code, to_code, is_ziarat, ziarat_site')
                ->orderBy('seq_no', 'ASC')
                ->get()
                ->getResultArray();

            foreach ((array) $legs as $leg) {
                $legsByTransport[(int) $leg['transport_id']][] = $leg;
            }
        }

        foreach ($rows as &$row) {
            $legs = $legsByTransport[(int) $row['id']] ?? [];
            $segments = [];
            $sites = [];
            foreach ($legs as $leg) {
                $segments[] = trim((string) ($leg['from_code'] ?? '')) . '-' . trim((string) ($leg['to_code'] ?? ''));
                if ((int) ($leg['is_ziarat'] ?? 0) === 1 && trim((string) ($leg['ziarat_site'] ?? '')) !== '') {
                    $sites[] = trim((string) $leg['ziarat_site']);
                }
            }

            $row['route_hint'] = implode(' / ', array_filter($segments));
            $row['ziarat_hint'] = implode(', ', $sites);
        }
        unset($row);

        return $rows;
    }

    private function fetchPackageRows(int $seasonId): array
    {
        $db = db_connect();
        if (! $db->tableExists('packages')) {
            return [];
        }

        $query = (new PackageModel())
            ->select('id, code, name, departure_date, arrival_date, selling_price, notes')
            ->where('season_id', $seasonId)
            ->orderBy('name', 'ASC')
            ->findAll();

        return is_array($query) ? $query : [];
    }

    private function salesHasColumn(string $column): bool
    {
        if (array_key_exists($column, $this->salesColumnExists)) {
            return (bool) $this->salesColumnExists[$column];
        }

        try {
            $this->salesColumnExists[$column] = db_connect()->fieldExists($column, 'sales');
        } catch (\Throwable $e) {
            $this->salesColumnExists[$column] = false;
        }

        return (bool) $this->salesColumnExists[$column];
    }

    private function generateInvoiceNo(int $seasonId, string $saleType): string
    {
        $prefix = $this->saleTypeMeta($saleType)['invoicePrefix'];
        $db = db_connect();
        $query = $db->table('sales')
            ->select('invoice_no')
            ->where('season_id', $seasonId)
            ->like('invoice_no', $prefix, 'after');

        if ($this->salesHasColumn('sale_type')) {
            $query->where('sale_type', $saleType);
        }

        $rows = $query->get()->getResultArray();
        $max = 0;
        foreach ($rows as $row) {
            $invoiceNo = (string) ($row['invoice_no'] ?? '');
            if (strpos($invoiceNo, $prefix) !== 0) {
                continue;
            }

            $suffix = substr($invoiceNo, strlen($prefix));
            if (! ctype_digit($suffix)) {
                continue;
            }

            $number = (int) $suffix;
            if ($number > $max) {
                $max = $number;
            }
        }

        return $prefix . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{title: string, invoicePrefix: string}
     */
    private function saleTypeMeta(string $saleType): array
    {
        if ($saleType === 'hotel') {
            return ['title' => 'Hotel', 'invoicePrefix' => 'HS-'];
        }

        if ($saleType === 'ticket') {
            return ['title' => 'Ticket', 'invoicePrefix' => 'TS-'];
        }

        if ($saleType === 'transport') {
            return ['title' => 'Transport', 'invoicePrefix' => 'TRS-'];
        }

        if ($saleType === 'package') {
            return ['title' => 'Package', 'invoicePrefix' => 'PS-'];
        }

        return ['title' => 'Visa', 'invoicePrefix' => 'VS-'];
    }

    private function saleTypeOptions(): array
    {
        return [
            'visa' => 'Visa',
            'ticket' => 'Ticket',
            'hotel' => 'Hotel',
            'transport' => 'Transport',
            'package' => 'Package',
        ];
    }

    private function buildPaxDisplayForSaleRow(array $row, int $seasonId): string
    {
        $map = $this->pilgrimDisplayMap($seasonId);
        $pilgrimIds = $this->extractPilgrimIdsFromSaleRow($row);

        $items = [];
        foreach ($pilgrimIds as $pilgrimId) {
            if (isset($map[$pilgrimId]) && trim((string) $map[$pilgrimId]) !== '') {
                $items[] = (string) $map[$pilgrimId];
            }
        }

        if ($items !== []) {
            return implode(', ', $items);
        }

        $paxName = trim((string) ($row['pax_name'] ?? ''));
        $passportNo = trim((string) ($row['pilgrim_passport_no'] ?? ''));
        if ($paxName !== '') {
            return $passportNo !== '' ? ($paxName . ' (' . $passportNo . ')') : $paxName;
        }

        $pilgrimName = trim((string) ($row['pilgrim_name'] ?? ''));
        if ($pilgrimName !== '') {
            return $passportNo !== '' ? ($pilgrimName . ' (' . $passportNo . ')') : $pilgrimName;
        }

        $customerName = trim((string) ($row['customer_name'] ?? ''));

        return $customerName !== '' ? $customerName : '-';
    }

    private function extractPilgrimIdsFromSaleRow(array $row): array
    {
        $ids = [];

        $pilgrimIdsCsv = trim((string) ($row['pilgrim_ids'] ?? ''));
        if ($pilgrimIdsCsv !== '') {
            foreach (explode(',', $pilgrimIdsCsv) as $part) {
                $id = (int) trim($part);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        $singlePilgrimId = (int) ($row['pilgrim_id'] ?? 0);
        if ($singlePilgrimId > 0) {
            $ids[] = $singlePilgrimId;
        }

        return array_values(array_unique(array_filter($ids, static function ($id) {
            return (int) $id > 0;
        })));
    }

    private function pilgrimDisplayMap(int $seasonId): array
    {
        if (isset($this->pilgrimDisplayCacheBySeason[$seasonId]) && is_array($this->pilgrimDisplayCacheBySeason[$seasonId])) {
            return $this->pilgrimDisplayCacheBySeason[$seasonId];
        }

        $db = db_connect();
        if (! $db->tableExists('pilgrims')) {
            $this->pilgrimDisplayCacheBySeason[$seasonId] = [];

            return $this->pilgrimDisplayCacheBySeason[$seasonId];
        }

        $rows = $db->table('pilgrims')
            ->select('id, first_name, last_name, passport_no')
            ->where('season_id', $seasonId)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $pilgrimId = (int) ($row['id'] ?? 0);
            if ($pilgrimId < 1) {
                continue;
            }

            $name = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));
            $passportNo = trim((string) ($row['passport_no'] ?? ''));

            if ($name === '' && $passportNo === '') {
                continue;
            }

            $map[$pilgrimId] = $passportNo !== '' ? (($name !== '' ? $name : 'Pax') . ' (' . $passportNo . ')') : $name;
        }

        $this->pilgrimDisplayCacheBySeason[$seasonId] = $map;

        return $this->pilgrimDisplayCacheBySeason[$seasonId];
    }
}
