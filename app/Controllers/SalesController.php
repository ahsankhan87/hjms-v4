<?php

namespace App\Controllers;

use App\Models\SalesCategoryModel;
use App\Models\SalesModel;
use App\Services\SalesLedgerService;

class SalesController extends BaseController
{
    public function index()
    {
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
        $query = $db->table('sales s')->select('s.*, c.name AS category_name, a.name AS agent_name')->join('sales_categories c', 'c.id = s.sales_category_id', 'left')->join('agents a', 'a.id = s.agent_id', 'left')->where('s.season_id', $seasonId);
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

        $totalAmount = 0.0;
        foreach ($rows as $row) {
            $amount = (float) ($row['amount'] ?? 0);
            $totalAmount += $amount;
        }

        return view('portal/sales/index', ['title' => 'HJMS ERP | Sales', 'headerTitle' => 'Sales', 'activePage' => 'sales', 'userEmail' => (string) session('user_email'), 'rows' => $rows, 'categories' => $categoryRows, 'filters' => $filters, 'filterErrors' => $filterErrors, 'totalAmount' => $totalAmount, 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    public function add()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $agents = $this->fetchAgentRows();

        return view('portal/sales/add', ['title' => 'HJMS ERP | Add Sale', 'headerTitle' => 'Sales', 'activePage' => 'sales', 'userEmail' => (string) session('user_email'), 'categories' => (new SalesCategoryModel())->where('season_id', $seasonId)->where('is_active', 1)->orderBy('name', 'ASC')->findAll(), 'agents' => $agents, 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
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

        $agents = $this->fetchAgentRows();

        return view('portal/sales/edit', ['title' => 'HJMS ERP | Edit Sale', 'headerTitle' => 'Sales', 'activePage' => 'sales', 'userEmail' => (string) session('user_email'), 'row' => $row, 'categories' => (new SalesCategoryModel())->where('season_id', $seasonId)->orderBy('name', 'ASC')->findAll(), 'agents' => $agents, 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    public function createSale()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = $this->extractSalePayload();
        if ($error = $this->validateSalePayload($payload, $seasonId)) {
            return redirect()->to('/sales/add')->withInput()->with($error['key'], $error['message']);
        }

        $db = db_connect();
        $model = new SalesModel();

        try {
            $db->transStart();

            $model->insert($this->buildSaleData($payload, $seasonId, true));
            $saleId = (int) $model->getInsertID();

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

        $payload = $this->extractSalePayload();
        if ($error = $this->validateSalePayload($payload, $seasonId)) {
            return redirect()->to('/sales/' . $saleId . '/edit')->withInput()->with($error['key'], $error['message']);
        }

        $db = db_connect();
        try {
            $db->transStart();

            $model->update($saleId, $this->buildSaleData($payload, $seasonId, false));
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

        $db = db_connect();
        try {
            $db->transStart();

            $model->delete($saleId);
            (new SalesLedgerService())->syncSaleLedger($saleId, $seasonId);

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Unable to delete sale at the moment.');
            }

            return redirect()->to('/sales')->with('success', 'Sale deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/sales')->with('error', $e->getMessage());
        }
    }

    private function extractSalePayload(): array
    {
        return ['sale_date' => (string) $this->request->getPost('sale_date'), 'sales_category_id' => (string) $this->request->getPost('sales_category_id'), 'customer_type' => (string) ($this->request->getPost('customer_type') ?: 'walk_in'), 'agent_id' => (string) $this->request->getPost('agent_id'), 'customer_name' => trim((string) $this->request->getPost('customer_name')), 'amount' => (string) $this->request->getPost('amount'), 'payment_method' => (string) ($this->request->getPost('payment_method') ?: 'cash'), 'reference_no' => trim((string) $this->request->getPost('reference_no')), 'note' => trim((string) $this->request->getPost('note')), 'status' => (string) ($this->request->getPost('status') ?: 'posted')];
    }

    private function saleRules(): array
    {
        return ['sale_date' => 'required|valid_date[Y-m-d]', 'sales_category_id' => 'required|integer', 'customer_type' => 'required|in_list[agent,walk_in]', 'agent_id' => 'permit_empty|integer', 'customer_name' => 'permit_empty|max_length[160]', 'amount' => 'required|decimal|greater_than[0]', 'payment_method' => 'required|in_list[cash,bank_transfer,cheque,card,online,other]', 'reference_no' => 'permit_empty|max_length[120]', 'note' => 'permit_empty|max_length[5000]', 'status' => 'required|in_list[posted,voided,pending]'];
    }

    private function validateSalePayload(array &$payload, int $seasonId)
    {
        if (! $this->validateData($payload, $this->saleRules())) {
            return ['key' => 'errors', 'message' => $this->validator->getErrors()];
        }

        if ((new SalesCategoryModel())->where('season_id', $seasonId)->find((int) $payload['sales_category_id']) === null) {
            return ['key' => 'error', 'message' => 'Sales category not found for the active season.'];
        }

        $amount = (float) $payload['amount'];

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

    private function buildSaleData(array $payload, int $seasonId, bool $isCreate): array
    {
        $now = date('Y-m-d H:i:s');
        $userId = (int) (session('user_id') ?? 0);

        $amount = (float) $payload['amount'];

        $agentId = (int) $payload['agent_id'];
        $customerType = (string) $payload['customer_type'];

        $data = [
            'season_id' => $seasonId,
            'sales_category_id' => (int) $payload['sales_category_id'],
            'sale_date' => $payload['sale_date'],
            'customer_type' => $customerType,
            'agent_id' => $agentId > 0 ? $agentId : null,
            'customer_name' => $customerType === 'walk_in' ? ($payload['customer_name'] !== '' ? $payload['customer_name'] : null) : null,
            'amount' => $amount,
            'payment_method' => $payload['payment_method'],
            'reference_no' => $payload['reference_no'] !== '' ? $payload['reference_no'] : null,
            'note' => $payload['note'] !== '' ? $payload['note'] : null,
            'status' => $payload['status'],
            'updated_at' => $now,
        ];

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
}
