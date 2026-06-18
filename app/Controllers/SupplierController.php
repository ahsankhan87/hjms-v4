<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SupplierLedgerEntryModel;
use App\Models\SupplierModel;

class SupplierController extends BaseController
{
    public function index(): string
    {
        $rows = (new SupplierModel())->orderBy('id', 'DESC')->findAll();

        return view('portal/suppliers/index', [
            'title' => 'HJMS ERP | Suppliers',
            'headerTitle' => 'Supplier Management',
            'activePage' => 'suppliers',
            'userEmail' => (string) session('user_email'),
            'rows' => $rows,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add(): string
    {
        return view('portal/suppliers/add', [
            'title' => 'HJMS ERP | Add Supplier',
            'headerTitle' => 'Supplier Management',
            'activePage' => 'suppliers',
            'userEmail' => (string) session('user_email'),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $row = (new SupplierModel())->find($id);
        if (empty($row)) {
            return redirect()->to('/suppliers')->with('error', 'Supplier not found.');
        }

        return view('portal/suppliers/edit', [
            'title' => 'HJMS ERP | Edit Supplier',
            'headerTitle' => 'Supplier Management',
            'activePage' => 'suppliers',
            'userEmail' => (string) session('user_email'),
            'row' => $row,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function ledger(int $id)
    {
        $supplier = (new SupplierModel())->find($id);
        if (empty($supplier)) {
            return redirect()->to('/suppliers')->with('error', 'Supplier not found.');
        }

        $rows = (new SupplierLedgerEntryModel())
            ->where('supplier_id', $id)
            ->orderBy('entry_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $runningBalance = (float) ($supplier['opening_balance'] ?? 0);
        foreach ($rows as &$item) {
            // Supplier payable balance: credit increases liability, debit decreases it.
            $runningBalance += (float) ($item['credit_amount'] ?? 0);
            $runningBalance -= (float) ($item['debit_amount'] ?? 0);
            $item['running_balance'] = $runningBalance;
        }
        unset($item);

        return view('portal/suppliers/ledger', [
            'title' => 'HJMS ERP | Supplier Ledger',
            'headerTitle' => 'Supplier Ledger',
            'activePage' => 'suppliers',
            'userEmail' => (string) session('user_email'),
            'supplier' => $supplier,
            'rows' => $rows,
            'closingBalance' => $runningBalance,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createSupplier()
    {
        $payload = $this->extractSupplierPayload();

        if (! $this->validateData($payload, $this->supplierRules())) {
            return redirect()->to('/suppliers/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            (new SupplierModel())->insert($this->buildSupplierData($payload, true));
            return redirect()->to('/suppliers')->with('success', 'Supplier added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/suppliers/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateSupplier()
    {
        $supplierId = (int) $this->request->getPost('supplier_id');
        $payload = $this->extractSupplierPayload();

        if ($supplierId < 1) {
            return redirect()->to('/suppliers')->with('error', 'Valid supplier ID is required.');
        }

        if (! $this->validateData($payload, $this->supplierRules())) {
            return redirect()->to('/suppliers/' . $supplierId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            (new SupplierModel())->update($supplierId, $this->buildSupplierData($payload, false));
            return redirect()->to('/suppliers')->with('success', 'Supplier updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/suppliers/' . $supplierId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteSupplier()
    {
        $supplierId = (int) $this->request->getPost('supplier_id');
        if ($supplierId < 1) {
            return redirect()->to('/suppliers')->with('error', 'Valid supplier ID is required for delete.');
        }

        try {
            (new SupplierModel())->delete($supplierId);
            return redirect()->to('/suppliers')->with('success', 'Supplier deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/suppliers')->with('error', $e->getMessage());
        }
    }

    public function createLedgerEntry()
    {
        $payload = [
            'supplier_id' => (int) $this->request->getPost('supplier_id'),
            'entry_date' => (string) $this->request->getPost('entry_date'),
            'entry_type' => (string) $this->request->getPost('entry_type'),
            'amount' => (string) $this->request->getPost('amount'),
            'description' => trim((string) $this->request->getPost('description')),
        ];

        if (! $this->validateData($payload, [
            'supplier_id' => 'required|integer',
            'entry_date' => 'required|valid_date[Y-m-d]',
            'entry_type' => 'required|in_list[bill,payment,adjustment]',
            'amount' => 'required|decimal',
            'description' => 'permit_empty|max_length[255]',
        ])) {
            return redirect()->to('/suppliers/' . $payload['supplier_id'] . '/ledger')->withInput()->with('errors', $this->validator->getErrors());
        }

        $supplier = (new SupplierModel())->find($payload['supplier_id']);
        if (empty($supplier)) {
            return redirect()->to('/suppliers')->with('error', 'Supplier not found.');
        }

        try {
            $amount = (float) $payload['amount'];
            if (abs($amount) < 0.0000001) {
                return redirect()->to('/suppliers/' . $payload['supplier_id'] . '/ledger')->withInput()->with('error', 'Amount must be greater than zero.');
            }

            $debit = 0.0;
            $credit = 0.0;

            if ($payload['entry_type'] === 'bill') {
                $credit = abs($amount);
            } elseif ($payload['entry_type'] === 'payment') {
                $debit = abs($amount);
            } else {
                if ($amount >= 0) {
                    $credit = $amount;
                } else {
                    $debit = abs($amount);
                }
            }

            (new SupplierLedgerEntryModel())->insert([
                'supplier_id' => $payload['supplier_id'],
                'entry_date' => $payload['entry_date'],
                'entry_type' => $payload['entry_type'],
                'debit_amount' => $debit,
                'credit_amount' => $credit,
                'description' => $payload['description'] !== '' ? $payload['description'] : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/suppliers/' . $payload['supplier_id'] . '/ledger')->with('success', 'Ledger entry posted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/suppliers/' . $payload['supplier_id'] . '/ledger')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteLedgerEntry()
    {
        $supplierId = (int) $this->request->getPost('supplier_id');
        $entryId = (int) $this->request->getPost('entry_id');

        if ($supplierId < 1 || $entryId < 1) {
            return redirect()->to('/suppliers')->with('error', 'Valid supplier and entry IDs are required.');
        }

        try {
            $supplier = (new SupplierModel())->find($supplierId);
            if (empty($supplier)) {
                return redirect()->to('/suppliers')->with('error', 'Supplier not found.');
            }

            $entryModel = new SupplierLedgerEntryModel();
            $entry = $entryModel->find($entryId);
            if (empty($entry) || (int) ($entry['supplier_id'] ?? 0) !== $supplierId) {
                return redirect()->to('/suppliers/' . $supplierId . '/ledger')->with('error', 'Ledger entry not found for this supplier.');
            }

            $entryModel->delete($entryId);
            return redirect()->to('/suppliers/' . $supplierId . '/ledger')->with('success', 'Ledger entry deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/suppliers/' . $supplierId . '/ledger')->with('error', $e->getMessage());
        }
    }

    private function extractSupplierPayload(): array
    {
        return [
            'supplier_name' => trim((string) $this->request->getPost('supplier_name')),
            'supplier_type' => strtolower(trim((string) $this->request->getPost('supplier_type'))),
            'contact_person' => trim((string) $this->request->getPost('contact_person')),
            'phone' => trim((string) $this->request->getPost('phone')),
            'email' => trim((string) $this->request->getPost('email')),
            'address' => trim((string) $this->request->getPost('address')),
            'opening_balance' => (string) $this->request->getPost('opening_balance'),
            'is_active' => (string) ($this->request->getPost('is_active') ?? '1'),
        ];
    }

    private function supplierRules(): array
    {
        return [
            'supplier_name' => 'required|max_length[180]',
            'supplier_type' => 'required|in_list[visa,transport,hotel,ticket,other]',
            'contact_person' => 'permit_empty|max_length[120]',
            'phone' => 'permit_empty|max_length[40]',
            'email' => 'permit_empty|valid_email|max_length[120]',
            'address' => 'permit_empty|max_length[255]',
            'opening_balance' => 'permit_empty|decimal',
            'is_active' => 'required|in_list[0,1]',
        ];
    }

    private function buildSupplierData(array $payload, bool $isCreate): array
    {
        $data = [
            'supplier_name' => $payload['supplier_name'],
            'supplier_type' => $payload['supplier_type'],
            'contact_person' => $payload['contact_person'] !== '' ? $payload['contact_person'] : null,
            'phone' => $payload['phone'] !== '' ? $payload['phone'] : null,
            'email' => $payload['email'] !== '' ? $payload['email'] : null,
            'address' => $payload['address'] !== '' ? $payload['address'] : null,
            'opening_balance' => $payload['opening_balance'] !== '' ? (float) $payload['opening_balance'] : 0,
            'is_active' => (int) $payload['is_active'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->suppliersHasColumn('tenant_id')) {
            $data['tenant_id'] = $this->resolveSupplierTenantId();
        }

        if ($isCreate && $this->suppliersHasColumn('supplier_code')) {
            $tenantId = (int) ($data['tenant_id'] ?? $this->resolveSupplierTenantId());
            $data['supplier_code'] = $this->generateUniqueSupplierCode($tenantId);
        }

        if ($isCreate) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $data;
    }

    private function resolveSupplierTenantId(): int
    {
        $sessionTenantId = (int) (session('tenant_id') ?? 0);
        return $sessionTenantId > 0 ? $sessionTenantId : 1;
    }

    private function suppliersHasColumn(string $column): bool
    {
        static $columnExists = [];

        if (array_key_exists($column, $columnExists)) {
            return $columnExists[$column];
        }

        try {
            $columnExists[$column] = db_connect()->fieldExists($column, 'suppliers');
        } catch (\Throwable $e) {
            $columnExists[$column] = false;
        }

        return $columnExists[$column];
    }

    private function generateUniqueSupplierCode(int $tenantId): string
    {
        $db = db_connect();
        $builder = $db->table('suppliers');

        if ($this->suppliersHasColumn('tenant_id')) {
            $builder->where('tenant_id', $tenantId);
        }

        if ($this->suppliersHasColumn('supplier_code')) {
            $builder->select('supplier_code');
        }

        $rows = $builder->get()->getResultArray();
        $maxNumber = 0;

        foreach ($rows as $row) {
            $rawCode = isset($row['supplier_code']) ? (string) $row['supplier_code'] : '';
            if (preg_match('/(\d+)$/', $rawCode, $matches) === 1) {
                $number = (int) $matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }

        for ($attempt = 1; $attempt <= 2000; $attempt++) {
            $candidate = 'SUP-' . str_pad((string) ($maxNumber + $attempt), 4, '0', STR_PAD_LEFT);
            $existsQuery = $db->table('suppliers')->where('supplier_code', $candidate);

            if ($this->suppliersHasColumn('tenant_id')) {
                $existsQuery->where('tenant_id', $tenantId);
            }

            if ($existsQuery->countAllResults() === 0) {
                return $candidate;
            }
        }

        return 'SUP-' . strtoupper(substr(md5((string) microtime(true)), 0, 8));
    }
}
