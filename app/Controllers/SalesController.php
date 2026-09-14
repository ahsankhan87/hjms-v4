<?php

namespace App\Controllers;

use App\Models\SalesCategoryModel;
use App\Models\HotelModel;
use App\Models\SalesModel;
use App\Models\SaleHotelStayModel;
use App\Services\SalesLedgerService;

class SalesController extends BaseController
{
    private $salesColumnExists = [];
    private $pilgrimDisplayCacheBySeason = [];

    public function index()
    {
        return $this->listByType('visa');
    }

    public function hotelIndex()
    {
        return $this->listByType('hotel');
    }

    public function ticketIndex()
    {
        return $this->listByType('ticket');
    }

    public function packageIndex()
    {
        return $this->listByType('package');
    }

    public function add()
    {
        return $this->addByType('visa');
    }

    public function hotelAdd()
    {
        return $this->addByType('hotel');
    }

    public function ticketAdd()
    {
        return $this->addByType('ticket');
    }

    public function packageAdd()
    {
        return $this->addByType('package');
    }

    public function edit(int $id)
    {
        return $this->editByType('visa', $id);
    }

    public function hotelEdit(int $id)
    {
        return $this->editByType('hotel', $id);
    }

    public function ticketEdit(int $id)
    {
        return $this->editByType('ticket', $id);
    }

    public function packageEdit(int $id)
    {
        return $this->editByType('package', $id);
    }

    public function createSale()
    {
        return $this->createByType('visa');
    }

    public function createHotelSale()
    {
        return $this->createByType('hotel');
    }

    public function createPackageSale()
    {
        return $this->createByType('package');
    }

    public function createTicketSale()
    {
        return $this->createByType('ticket');
    }

    public function updateSale()
    {
        return $this->updateByType('visa');
    }

    public function updatePackageSale()
    {
        return $this->updateByType('package');
    }

    public function updateHotelSale()
    {
        return $this->updateByType('hotel');
    }

    public function updateTicketSale()
    {
        return $this->updateByType('ticket');
    }

    public function deletePackageSale()
    {
        return $this->deleteByType('package');
    }

    public function deleteSale()
    {
        return $this->deleteByType('visa');
    }

    public function deleteHotelSale()
    {
        return $this->deleteByType('hotel');
    }
    public function createHotelStay()
    {
        $rules = [
            'sale_id' => 'required|integer|greater_than[0]',
            'hotel_name' => 'required|max_length[180]',
            'check_in_date' => 'required|valid_date[Y-m-d]',
            'check_out_date' => 'required|valid_date[Y-m-d]',
            'room_name' => 'permit_empty|max_length[120]',
            'room_qty' => 'permit_empty|integer|greater_than_equal_to[1]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $saleId = (int) $this->request->getPost('sale_id');
        $hotelName = trim((string) $this->request->getPost('hotel_name'));
        $checkInDate = (string) $this->request->getPost('check_in_date');
        $checkOutDate = (string) $this->request->getPost('check_out_date');
        $roomName = trim((string) $this->request->getPost('room_name'));
        $roomQty = (int) $this->request->getPost('room_qty', 1);

        $checkInTime = strtotime($checkInDate);
        $checkOutTime = strtotime($checkOutDate);
        if ($checkInTime === false || $checkOutTime === false || $checkOutTime <= $checkInTime) {
            return $this->response->setJSON(['success' => false, 'error' => 'Check-out date must be after check-in date']);
        }

        try {
            $db = db_connect();
            $db->transStart();

            (new SaleHotelStayModel())->insert([
                'sale_id' => $saleId,
                'hotel_name' => $hotelName,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'room_name' => $roomName !== '' ? $roomName : null,
                'room_qty' => $roomQty > 0 ? $roomQty : 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->syncSaleHotelStayWindow($saleId, $db);

            $db->transComplete();
            if (!$db->transStatus()) {
                throw new \RuntimeException('Unable to save hotel stay.');
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Hotel stay added successfully.']);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function updateHotelStay()
    {
        $rules = [
            'stay_id' => 'required|integer|greater_than[0]',
            'hotel_name' => 'required|max_length[180]',
            'check_in_date' => 'required|valid_date[Y-m-d]',
            'check_out_date' => 'required|valid_date[Y-m-d]',
            'room_name' => 'permit_empty|max_length[120]',
            'room_qty' => 'permit_empty|integer|greater_than_equal_to[1]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $stayId = (int) $this->request->getPost('stay_id');
        $hotelName = trim((string) $this->request->getPost('hotel_name'));
        $checkInDate = (string) $this->request->getPost('check_in_date');
        $checkOutDate = (string) $this->request->getPost('check_out_date');
        $roomName = trim((string) $this->request->getPost('room_name'));
        $roomQty = (int) $this->request->getPost('room_qty', 1);

        $checkInTime = strtotime($checkInDate);
        $checkOutTime = strtotime($checkOutDate);
        if ($checkInTime === false || $checkOutTime === false || $checkOutTime <= $checkInTime) {
            return $this->response->setJSON(['success' => false, 'error' => 'Check-out date must be after check-in date']);
        }

        try {
            $db = db_connect();
            $db->transStart();

            $stayRecord = $db->table('sale_hotel_stays')->select('sale_id')->where('id', $stayId)->get()->getRowArray();
            if (empty($stayRecord)) {
                throw new \RuntimeException('Hotel stay not found.');
            }

            (new SaleHotelStayModel())->update($stayId, [
                'hotel_name' => $hotelName,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'room_name' => $roomName !== '' ? $roomName : null,
                'room_qty' => $roomQty > 0 ? $roomQty : 1,
            ]);

            $this->syncSaleHotelStayWindow((int) $stayRecord['sale_id'], $db);

            $db->transComplete();
            if (!$db->transStatus()) {
                throw new \RuntimeException('Unable to update hotel stay.');
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Hotel stay updated successfully.']);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function deleteHotelStay()
    {
        $stayId = (int) $this->request->getPost('stay_id');

        if ($stayId < 1) {
            return $this->response->setJSON(['success' => false, 'error' => 'Valid stay ID is required.']);
        }

        try {
            $db = db_connect();
            $db->transStart();

            $stayRecord = $db->table('sale_hotel_stays')->select('sale_id')->where('id', $stayId)->get()->getRowArray();
            if (empty($stayRecord)) {
                throw new \RuntimeException('Hotel stay not found.');
            }

            (new SaleHotelStayModel())->delete($stayId);

            $this->syncSaleHotelStayWindow((int) $stayRecord['sale_id'], $db);

            $db->transComplete();
            if (!$db->transStatus()) {
                throw new \RuntimeException('Unable to delete hotel stay.');
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Hotel stay deleted successfully.']);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function deleteTicketSale()
    {
        return $this->deleteByType('ticket');
    }

    private function listByType(string $saleType)
    {
        $saleMeta = $this->saleTypeMeta($saleType);
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $filters = ['from_date' => (string) $this->request->getGet('from_date'), 'to_date' => (string) $this->request->getGet('to_date'), 'sales_category_id' => (string) $this->request->getGet('sales_category_id'), 'payment_method' => (string) $this->request->getGet('payment_method'), 'status' => (string) $this->request->getGet('status'), 'customer_type' => (string) $this->request->getGet('customer_type')];
        $filterErrors = [];
        if (! $this->validateData($filters, ['from_date' => 'permit_empty|valid_date[Y-m-d]', 'to_date' => 'permit_empty|valid_date[Y-m-d]', 'sales_category_id' => 'permit_empty|integer', 'payment_method' => 'permit_empty|in_list[cash,bank_transfer,cheque,card,online,other]', 'status' => 'permit_empty|in_list[posted,voided,pending]', 'customer_type' => 'permit_empty|in_list[agent,walk_in]'])) {
            $filterErrors = $this->validator->getErrors();
            $filters = ['from_date' => '', 'to_date' => '', 'sales_category_id' => '', 'payment_method' => '', 'status' => '', 'customer_type' => ''];
        }

        $db = db_connect();
        $categoryRows = (new SalesCategoryModel())->where('season_id', $seasonId)->orderBy('name', 'ASC')->findAll();
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

        if ($this->salesHasColumn('sale_type')) {
            $query->where('s.sale_type', $saleType);
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
        if ($filters['payment_method'] !== '') {
            $query->where('s.payment_method', $filters['payment_method']);
        }
        if ($filters['status'] !== '') {
            $query->where('s.status', $filters['status']);
        }
        if ($filters['customer_type'] !== '') {
            $query->where('s.customer_type', $filters['customer_type']);
        }
        $rows = $query->orderBy('s.sale_date', 'DESC')->orderBy('s.id', 'DESC')->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['pax_display'] = $this->buildPaxDisplayForSaleRow($row, $seasonId);
        }
        unset($row);

        $totalAmount = 0.0;
        foreach ($rows as $row) {
            $amount = (float) ($row['amount'] ?? 0);
            $totalAmount += $amount;
        }

        return view($saleMeta['viewFolder'] . '/index', ['title' => 'HJMS ERP | ' . $saleMeta['title'], 'headerTitle' => $saleMeta['title'], 'activePage' => $saleMeta['activePage'], 'userEmail' => (string) session('user_email'), 'rows' => $rows, 'categories' => $categoryRows, 'filters' => $filters, 'filterErrors' => $filterErrors, 'totalAmount' => $totalAmount, 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    private function addByType(string $saleType)
    {
        $saleMeta = $this->saleTypeMeta($saleType);
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $agents = $this->fetchAgentRows();
        $pilgrims = $this->fetchPilgrimRows($seasonId);
        $hotels = $saleType === 'hotel' ? $this->fetchHotelRows() : [];

        return view($saleMeta['viewFolder'] . '/add', ['title' => 'HJMS ERP | Add ' . $saleMeta['title'], 'headerTitle' => $saleMeta['title'], 'activePage' => $saleMeta['activePage'], 'userEmail' => (string) session('user_email'), 'categories' => (new SalesCategoryModel())->where('season_id', $seasonId)->where('is_active', 1)->orderBy('name', 'ASC')->findAll(), 'agents' => $agents, 'pilgrims' => $pilgrims, 'hotels' => $hotels, 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    private function editByType(string $saleType, int $id)
    {
        $saleMeta = $this->saleTypeMeta($saleType);
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $row = (new SalesModel())->where('season_id', $seasonId)->find($id);
        if (empty($row)) {
            return redirect()->to($saleMeta['indexPath'])->with('error', 'Sale not found.');
        }

        $rowType = (string) ($row['sale_type'] ?? 'visa');
        if ($rowType !== $saleType) {
            $rowTypeMeta = $this->saleTypeMeta($rowType);
            if ($rowType !== 'visa') {
                return redirect()->to($rowTypeMeta['indexPath'] . '/' . $id . '/edit');
            }

            return redirect()->to('/sales/' . $id . '/edit');
        }

        $agents = $this->fetchAgentRows();
        $pilgrims = $this->fetchPilgrimRows($seasonId);
        $hotels = $saleType === 'hotel' ? $this->fetchHotelRows() : [];
        $hotelStays = $saleType === 'hotel' ? $this->saleHotelStayRows($id) : [];

        return view($saleMeta['viewFolder'] . '/edit', ['title' => 'HJMS ERP | Edit ' . $saleMeta['title'], 'headerTitle' => $saleMeta['title'], 'activePage' => $saleMeta['activePage'], 'userEmail' => (string) session('user_email'), 'row' => $row, 'categories' => (new SalesCategoryModel())->where('season_id', $seasonId)->orderBy('name', 'ASC')->findAll(), 'agents' => $agents, 'pilgrims' => $pilgrims, 'hotels' => $hotels, 'hotelStays' => $hotelStays, 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    private function createByType(string $saleType)
    {
        $saleMeta = $this->saleTypeMeta($saleType);
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = $this->extractSalePayload($saleType);
        if ($error = $this->validateSalePayload($payload, $seasonId, $saleType)) {
            return redirect()->to($saleMeta['addPath'])->withInput()->with($error['key'], $error['message']);
        }

        $db = db_connect();
        $model = new SalesModel();

        try {
            $db->transStart();

            $model->insert($this->buildSaleData($payload, $seasonId, true, $saleType));
            $saleId = (int) $model->getInsertID();

            // Create initial hotel stay for hotel sales
            if ($saleType === 'hotel' && $this->saleHotelStayTableExists($db)) {
                (new SaleHotelStayModel())->insert([
                    'sale_id' => $saleId,
                    'hotel_name' => $payload['hotel_name'] !== '' ? $payload['hotel_name'] : null,
                    'check_in_date' => $payload['checkin_date'],
                    'check_out_date' => $payload['checkout_date'],
                    'room_name' => $payload['room_name'] !== '' ? $payload['room_name'] : null,
                    'room_qty' => (int) $payload['room_qty'] > 0 ? (int) $payload['room_qty'] : 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            (new SalesLedgerService())->syncSaleLedger($saleId, $seasonId);

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Unable to create sale at the moment.');
            }

            return redirect()->to($saleMeta['indexPath'])->with('success', $saleMeta['title'] . ' added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to($saleMeta['addPath'])->withInput()->with('error', $e->getMessage());
        }
    }

    private function updateByType(string $saleType)
    {
        $saleMeta = $this->saleTypeMeta($saleType);
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $saleId = (int) $this->request->getPost('sale_id');
        if ($saleId < 1) {
            return redirect()->to($saleMeta['indexPath'])->with('error', 'Valid sale ID is required.');
        }

        $model = new SalesModel();
        $row = $model->where('season_id', $seasonId)->find($saleId);
        if (empty($row)) {
            return redirect()->to($saleMeta['indexPath'])->with('error', 'Sale not found.');
        }

        $rowType = (string) ($row['sale_type'] ?? 'visa');
        if ($rowType !== $saleType) {
            return redirect()->to($saleMeta['indexPath'])->with('error', 'Invalid sale type selected for update.');
        }

        $payload = $this->extractSalePayload($saleType);
        if ($error = $this->validateSalePayload($payload, $seasonId, $saleType)) {
            return redirect()->to($saleMeta['indexPath'] . '/' . $saleId . '/edit')->withInput()->with($error['key'], $error['message']);
        }

        $db = db_connect();
        try {
            $db->transStart();

            $model->update($saleId, $this->buildSaleData($payload, $seasonId, false, $saleType));

            // Update or create primary hotel stay for hotel sales
            if ($saleType === 'hotel' && $this->saleHotelStayTableExists($db)) {
                $existingStays = $this->saleHotelStayRows($saleId, $db);
                if (!empty($existingStays)) {
                    // Update the first stay with form data
                    $firstStay = $existingStays[0];
                    (new SaleHotelStayModel())->update((int) $firstStay['id'], [
                        'hotel_name' => $payload['hotel_name'] !== '' ? $payload['hotel_name'] : null,
                        'check_in_date' => $payload['checkin_date'],
                        'check_out_date' => $payload['checkout_date'],
                        'room_name' => $payload['room_name'] !== '' ? $payload['room_name'] : null,
                        'room_qty' => (int) $payload['room_qty'] > 0 ? (int) $payload['room_qty'] : 1,
                    ]);
                } else {
                    // Create initial stay if none exist
                    (new SaleHotelStayModel())->insert([
                        'sale_id' => $saleId,
                        'hotel_name' => $payload['hotel_name'] !== '' ? $payload['hotel_name'] : null,
                        'check_in_date' => $payload['checkin_date'],
                        'check_out_date' => $payload['checkout_date'],
                        'room_name' => $payload['room_name'] !== '' ? $payload['room_name'] : null,
                        'room_qty' => (int) $payload['room_qty'] > 0 ? (int) $payload['room_qty'] : 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                $this->syncSaleHotelStayWindow($saleId, $db);
            }

            (new SalesLedgerService())->syncSaleLedger($saleId, $seasonId);

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Unable to update sale at the moment.');
            }

            return redirect()->to($saleMeta['indexPath'])->with('success', $saleMeta['title'] . ' updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to($saleMeta['indexPath'] . '/' . $saleId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    private function deleteByType(string $saleType)
    {
        $saleMeta = $this->saleTypeMeta($saleType);
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $saleId = (int) $this->request->getPost('sale_id');
        if ($saleId < 1) {
            return redirect()->to($saleMeta['indexPath'])->with('error', 'Valid sale ID is required.');
        }

        $model = new SalesModel();
        $row = $model->where('season_id', $seasonId)->find($saleId);
        if (empty($row)) {
            return redirect()->to($saleMeta['indexPath'])->with('error', 'Sale not found.');
        }

        $rowType = (string) ($row['sale_type'] ?? 'visa');
        if ($rowType !== $saleType) {
            return redirect()->to($saleMeta['indexPath'])->with('error', 'Invalid sale type selected for delete.');
        }

        $db = db_connect();
        try {
            $db->transStart();

            $model->delete($saleId);
            (new SalesLedgerService())->syncSaleLedger($saleId, $seasonId);

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Unable to delete sale at the moment.');
            }

            return redirect()->to($saleMeta['indexPath'])->with('success', $saleMeta['title'] . ' deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to($saleMeta['indexPath'])->with('error', $e->getMessage());
        }
    }

    private function extractSalePayload(string $saleType): array
    {
        $pilgrimIds = $this->request->getPost('pilgrim_ids');
        if (! is_array($pilgrimIds)) {
            $pilgrimIds = [];
        }
        $pilgrimIds = array_values(array_unique(array_filter(array_map(static function ($id) {
            return (int) $id;
        }, $pilgrimIds), static function ($id) {
            return $id > 0;
        })));

        $attachmentFile = '';
        $attachmentFile2 = '';
        $attachmentFile3 = '';

        if ($this->request->getFile('attachment')) {
            $file = $this->request->getFile('attachment');
            if ($file->isValid() && !$file->hasMoved()) {
                $filename = $file->getRandomName();
                $file->move('writable/uploads', $filename);
                $attachmentFile = $filename;
            }
        }

        if ($this->request->getFile('attachment2')) {
            $file = $this->request->getFile('attachment2');
            if ($file->isValid() && !$file->hasMoved()) {
                $filename = $file->getRandomName();
                $file->move('writable/uploads', $filename);
                $attachmentFile2 = $filename;
            }
        }

        if ($this->request->getFile('attachment3')) {
            $file = $this->request->getFile('attachment3');
            if ($file->isValid() && !$file->hasMoved()) {
                $filename = $file->getRandomName();
                $file->move('writable/uploads', $filename);
                $attachmentFile3 = $filename;
            }
        }

        return ['sale_type' => $saleType, 'invoice_no' => trim((string) $this->request->getPost('invoice_no')), 'sale_date' => (string) $this->request->getPost('sale_date'), 'sales_category_id' => (string) $this->request->getPost('sales_category_id'), 'customer_type' => (string) ($this->request->getPost('customer_type') ?: 'walk_in'), 'agent_id' => (string) $this->request->getPost('agent_id'), 'pilgrim_ids' => $pilgrimIds, 'customer_name' => trim((string) $this->request->getPost('customer_name')), 'amount' => (string) $this->request->getPost('amount'), 'payment_method' => (string) ($this->request->getPost('payment_method') ?: 'cash'), 'reference_no' => trim((string) $this->request->getPost('reference_no')), 'visa_type' => trim((string) $this->request->getPost('visa_type')), 'visa_agency' => trim((string) $this->request->getPost('visa_agency')), 'hotel_name' => trim((string) $this->request->getPost('hotel_name')), 'checkin_date' => (string) $this->request->getPost('checkin_date'), 'checkout_date' => (string) $this->request->getPost('checkout_date'), 'no_of_nights' => (string) $this->request->getPost('no_of_nights'), 'room_name' => trim((string) $this->request->getPost('room_name')), 'room_qty' => (string) $this->request->getPost('room_qty'), 'pnr' => trim((string) $this->request->getPost('pnr')), 'sector' => trim((string) $this->request->getPost('sector')), 'ticket_no' => trim((string) $this->request->getPost('ticket_no')), 'package_name' => trim((string) $this->request->getPost('package_name')), 'package_details' => trim((string) $this->request->getPost('package_details')), 'attachment' => $attachmentFile, 'attachment2' => $attachmentFile2, 'attachment3' => $attachmentFile3, 'note' => trim((string) $this->request->getPost('note')), 'status' => (string) ($this->request->getPost('status') ?: 'posted')];
    }

    private function saleRules(string $saleType): array
    {
        $rules = ['invoice_no' => 'permit_empty|max_length[40]', 'sale_date' => 'required|valid_date[Y-m-d]', 'sales_category_id' => 'required|integer', 'customer_type' => 'required|in_list[agent,walk_in]', 'agent_id' => 'permit_empty|integer', 'customer_name' => 'permit_empty|max_length[160]', 'amount' => 'required|decimal|greater_than[0]', 'payment_method' => 'required|in_list[cash,bank_transfer,cheque,card,online,other]', 'reference_no' => 'permit_empty|max_length[120]', 'note' => 'permit_empty|max_length[5000]', 'status' => 'required|in_list[posted,voided,pending]'];

        if ($saleType === 'hotel') {
            $rules['hotel_name'] = 'required|max_length[180]';
            $rules['checkin_date'] = 'required|valid_date[Y-m-d]';
            $rules['checkout_date'] = 'required|valid_date[Y-m-d]';
            $rules['no_of_nights'] = 'permit_empty|integer';
            $rules['room_name'] = 'permit_empty|max_length[120]';
            $rules['room_qty'] = 'permit_empty|integer';
        } elseif ($saleType === 'ticket') {
            $rules['pnr'] = 'required|max_length[50]';
            $rules['sector'] = 'required|max_length[80]';
            $rules['ticket_no'] = 'required|max_length[80]';
        } elseif ($saleType === 'package') {
            $rules['package_name'] = 'required|max_length[180]';
            $rules['package_details'] = 'permit_empty|max_length[1000]';
            $rules['attachment'] = 'permit_empty|uploaded[attachment]|max_size[attachment,5120]|mime_in[attachment,image/jpg,image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document]';
            $rules['attachment2'] = 'permit_empty|uploaded[attachment2]|max_size[attachment2,5120]|mime_in[attachment2,image/jpg,image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document]';
            $rules['attachment3'] = 'permit_empty|uploaded[attachment3]|max_size[attachment3,5120]|mime_in[attachment3,image/jpg,image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document]';
        } else {
            $rules['visa_type'] = 'permit_empty|max_length[80]';
            $rules['visa_agency'] = 'permit_empty|max_length[160]';
        }

        return $rules;
    }

    private function validateSalePayload(array &$payload, int $seasonId, string $saleType)
    {
        if (! $this->validateData($payload, $this->saleRules($saleType))) {
            return ['key' => 'errors', 'message' => $this->validator->getErrors()];
        }

        if ((new SalesCategoryModel())->where('season_id', $seasonId)->find((int) $payload['sales_category_id']) === null) {
            return ['key' => 'error', 'message' => 'Sales category not found for the active season.'];
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
            $pid = (int) ($pilgrimRow['id'] ?? 0);
            $pilgrimNameMap[$pid] = trim((string) ($pilgrimRow['first_name'] ?? '') . ' ' . (string) ($pilgrimRow['last_name'] ?? ''));
        }

        $payload['pilgrim_ids'] = array_values($pilgrimIds);
        $payload['pilgrim_id'] = (int) $payload['pilgrim_ids'][0];
        $payload['pax_name'] = implode(', ', array_values(array_filter(array_map(static function ($id) use ($pilgrimNameMap) {
            return (string) ($pilgrimNameMap[(int) $id] ?? '');
        }, $payload['pilgrim_ids']))));

        if ($saleType === 'hotel') {
            $checkinDate = strtotime((string) $payload['checkin_date']);
            $checkoutDate = strtotime((string) $payload['checkout_date']);
            if ($checkinDate === false || $checkoutDate === false || $checkoutDate < $checkinDate) {
                return ['key' => 'error', 'message' => 'Checkout date must be on or after check-in date.'];
            }

            $computedNights = (int) floor(($checkoutDate - $checkinDate) / 86400);
            $payload['no_of_nights'] = (string) max(1, $computedNights);

            if ((int) $payload['room_qty'] < 1) {
                $payload['room_qty'] = '1';
            }
        }

        if ($payload['customer_type'] === 'walk_in') {
            if ($payload['customer_name'] === '') {
                return ['key' => 'error', 'message' => 'Customer name is required for walk-in sales.'];
            }
            $payload['agent_id'] = '';
            return null;
        }

        $isCreditSale = $payload['status'] !== 'voided';
        if ($isCreditSale && (int) $payload['agent_id'] < 1) {
            return ['key' => 'error', 'message' => 'Agent is required for credit sales.'];
        }

        if ((int) $payload['agent_id'] > 0) {
            $agentQuery = db_connect()->table('agents')->select('id')->where('id', (int) $payload['agent_id'])->get();
            if (! is_object($agentQuery)) {
                return ['key' => 'error', 'message' => 'Unable to verify selected agent at the moment.'];
            }
            $agent = $agentQuery->getRowArray();
            if (empty($agent)) {
                return ['key' => 'error', 'message' => 'Selected agent does not exist.'];
            }
        }

        return null;
    }

    private function buildSaleData(array $payload, int $seasonId, bool $isCreate, string $saleType): array
    {
        $now = date('Y-m-d H:i:s');
        $userId = (int) (session('user_id') ?? 0);

        if ((string) ($payload['invoice_no'] ?? '') === '') {
            $payload['invoice_no'] = $this->generateInvoiceNo($seasonId, $saleType);
        }

        $amount = (float) $payload['amount'];

        $agentId = (int) $payload['agent_id'];
        $customerType = (string) $payload['customer_type'];

        $data = [
            'season_id' => $seasonId,
            'sale_type' => $saleType,
            'invoice_no' => $payload['invoice_no'] !== '' ? $payload['invoice_no'] : null,
            'sales_category_id' => (int) $payload['sales_category_id'],
            'sale_date' => $payload['sale_date'],
            'customer_type' => $customerType,
            'agent_id' => $agentId > 0 ? $agentId : null,
            'pilgrim_id' => (int) $payload['pilgrim_id'] > 0 ? (int) $payload['pilgrim_id'] : null,
            'pilgrim_ids' => ! empty($payload['pilgrim_ids']) ? implode(',', array_map(static function ($id) {
                return (string) (int) $id;
            }, (array) $payload['pilgrim_ids'])) : null,
            'pax_name' => $payload['pax_name'] !== '' ? $payload['pax_name'] : null,
            'customer_name' => $customerType === 'walk_in' ? ($payload['customer_name'] !== '' ? $payload['customer_name'] : null) : null,
            'amount' => $amount,
            'payment_method' => $payload['payment_method'],
            'reference_no' => $payload['reference_no'] !== '' ? $payload['reference_no'] : null,
            'visa_type' => $saleType === 'visa' && $payload['visa_type'] !== '' ? $payload['visa_type'] : null,
            'visa_agency' => $saleType === 'visa' && $payload['visa_agency'] !== '' ? $payload['visa_agency'] : null,
            'hotel_name' => $saleType === 'hotel' && $payload['hotel_name'] !== '' ? $payload['hotel_name'] : null,
            'checkin_date' => $saleType === 'hotel' ? $payload['checkin_date'] : null,
            'checkout_date' => $saleType === 'hotel' ? $payload['checkout_date'] : null,
            'no_of_nights' => $saleType === 'hotel' ? (int) $payload['no_of_nights'] : null,
            'room_name' => $saleType === 'hotel' && $payload['room_name'] !== '' ? $payload['room_name'] : null,
            'room_qty' => $saleType === 'hotel' ? (int) $payload['room_qty'] : null,
            'pnr' => $saleType === 'ticket' && $payload['pnr'] !== '' ? $payload['pnr'] : null,
            'sector' => $saleType === 'ticket' && $payload['sector'] !== '' ? $payload['sector'] : null,
            'ticket_no' => $saleType === 'ticket' && $payload['ticket_no'] !== '' ? $payload['ticket_no'] : null,
            'package_name' => $saleType === 'package' && $payload['package_name'] !== '' ? $payload['package_name'] : null,
            'package_details' => $saleType === 'package' && $payload['package_details'] !== '' ? $payload['package_details'] : null,
            'attachment' => $payload['attachment'] !== '' ? $payload['attachment'] : null,
            'attachment2' => $payload['attachment2'] !== '' ? $payload['attachment2'] : null,
            'attachment3' => $payload['attachment3'] !== '' ? $payload['attachment3'] : null,
            'note' => $payload['note'] !== '' ? $payload['note'] : null,
            'status' => $payload['status'],
            'updated_at' => $now,
        ];

        if (! $this->salesHasColumn('sale_type')) {
            unset($data['sale_type']);
        }
        if (! $this->salesHasColumn('invoice_no')) {
            unset($data['invoice_no']);
        }
        if (! $this->salesHasColumn('pilgrim_id')) {
            unset($data['pilgrim_id']);
        }
        if (! $this->salesHasColumn('pax_name')) {
            unset($data['pax_name']);
        }
        if (! $this->salesHasColumn('pilgrim_ids')) {
            unset($data['pilgrim_ids']);
        }
        if (! $this->salesHasColumn('visa_type')) {
            unset($data['visa_type']);
        }
        if (! $this->salesHasColumn('visa_agency')) {
            unset($data['visa_agency']);
        }
        if (! $this->salesHasColumn('hotel_name')) {
            unset($data['hotel_name']);
        }
        if (! $this->salesHasColumn('checkin_date')) {
            unset($data['checkin_date']);
        }
        if (! $this->salesHasColumn('checkout_date')) {
            unset($data['checkout_date']);
        }
        if (! $this->salesHasColumn('no_of_nights')) {
            unset($data['no_of_nights']);
        }
        if (! $this->salesHasColumn('room_name')) {
            unset($data['room_name']);
        }
        if (! $this->salesHasColumn('room_qty')) {
            unset($data['room_qty']);
        }
        if (! $this->salesHasColumn('pnr')) {
            unset($data['pnr']);
        }
        if (! $this->salesHasColumn('sector')) {
            unset($data['sector']);
        }
        if (! $this->salesHasColumn('ticket_no')) {
            unset($data['ticket_no']);
        }
        if (! $this->salesHasColumn('package_name')) {
            unset($data['package_name']);
        }
        if (! $this->salesHasColumn('package_details')) {
            unset($data['package_details']);
        }
        if (! $this->salesHasColumn('attachment')) {
            unset($data['attachment']);
        }
        if (! $this->salesHasColumn('attachment2')) {
            unset($data['attachment2']);
        }
        if (! $this->salesHasColumn('attachment3')) {
            unset($data['attachment3']);
        }

        if ($isCreate) {
            $data['created_at'] = $now;
            $data['created_by'] = $userId > 0 ? $userId : null;
        }

        return $data;
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

    private function fetchPilgrimRows(int $seasonId): array
    {
        $db = db_connect();
        if (! $db->tableExists('pilgrims')) {
            return [];
        }

        $query = $db->table('pilgrims')
            ->select('id, first_name, last_name, passport_no')
            ->where('season_id', $seasonId)
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->get();

        if (! is_object($query)) {
            return [];
        }

        return $query->getResultArray();
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

    private function saleTypeMeta(string $saleType): array
    {
        if ($saleType === 'hotel') {
            return [
                'title' => 'Hotel Sale',
                'activePage' => 'hotel_sales',
                'viewFolder' => 'portal/hotel_sales',
                'indexPath' => '/hotel-sales',
                'addPath' => '/hotel-sales/add',
                'invoicePrefix' => 'HS-',
            ];
        }

        if ($saleType === 'ticket') {
            return [
                'title' => 'Ticket Sale',
                'activePage' => 'ticket_sales',
                'viewFolder' => 'portal/ticket_sales',
                'indexPath' => '/ticket-sales',
                'addPath' => '/ticket-sales/add',
                'invoicePrefix' => 'TS-',
            ];
        }

        if ($saleType === 'package') {
            return [
                'title' => 'Package Sale',
                'activePage' => 'package_sales',
                'viewFolder' => 'portal/package_sales',
                'indexPath' => '/package-sales',
                'addPath' => '/package-sales/add',
                'invoicePrefix' => 'PS-',
            ];
        }

        return [
            'title' => 'Visa Sale',
            'activePage' => 'visa_sales',
            'viewFolder' => 'portal/sales',
            'indexPath' => '/sales',
            'addPath' => '/sales/add',
            'invoicePrefix' => 'VS-',
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

        $ids = array_values(array_unique(array_filter($ids, static function ($id) {
            return (int) $id > 0;
        })));

        return $ids;
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

    private function saleHotelStayTableExists($db = null): bool
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }

        $db = $db ?? db_connect();
        $exists = $db->tableExists('sale_hotel_stays');
        return $exists;
    }

    private function saleHotelStayRows(int $saleId, $db = null): array
    {
        $db = $db ?? db_connect();
        if (!$this->saleHotelStayTableExists($db)) {
            return [];
        }

        return $db->table('sale_hotel_stays shs')
            ->select('shs.*')
            ->where('shs.sale_id', $saleId)
            ->orderBy('shs.check_in_date', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function syncSaleHotelStayWindow(int $saleId, $db = null): void
    {
        $db = $db ?? db_connect();
        if (!$this->saleHotelStayTableExists($db)) {
            return;
        }

        $range = $db->table('sale_hotel_stays')
            ->select('MIN(check_in_date) AS min_check_in, MAX(check_out_date) AS max_check_out')
            ->where('sale_id', $saleId)
            ->get()
            ->getRowArray();

        if (empty($range)) {
            return;
        }

        $minCheckIn = (string) ($range['min_check_in'] ?? '');
        $maxCheckOut = (string) ($range['max_check_out'] ?? '');

        if ($minCheckIn !== '' && $maxCheckOut !== '') {
            $checkInTime = strtotime($minCheckIn);
            $checkOutTime = strtotime($maxCheckOut);
            if ($checkInTime !== false && $checkOutTime !== false) {
                $nights = (int) floor(($checkOutTime - $checkInTime) / 86400);
                (new SalesModel())->update($saleId, [
                    'checkin_date' => $minCheckIn,
                    'checkout_date' => $maxCheckOut,
                    'no_of_nights' => max(1, $nights),
                ]);
            }
        }
    }
}
