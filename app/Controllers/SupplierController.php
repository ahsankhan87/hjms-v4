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
            return redirect()->to('/app/suppliers')->with('error', 'Supplier not found.');
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
            return redirect()->to('/app/suppliers')->with('error', 'Supplier not found.');
        }

        $rows = (new SupplierLedgerEntryModel())
            ->where('supplier_id', $id)
            ->orderBy('entry_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $runningBalance = (float) ($supplier['opening_balance'] ?? 0);
        foreach ($rows as &$item) {
            $runningBalance += (float) ($item['debit_amount'] ?? 0);
            $runningBalance -= (float) ($item['credit_amount'] ?? 0);
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
            return redirect()->to('/app/suppliers/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            (new SupplierModel())->insert($this->buildSupplierData($payload, true));
            return redirect()->to('/app/suppliers')->with('success', 'Supplier added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/suppliers/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateSupplier()
    {
        $supplierId = (int) $this->request->getPost('supplier_id');
        $payload = $this->extractSupplierPayload();

        if ($supplierId < 1) {
            return redirect()->to('/app/suppliers')->with('error', 'Valid supplier ID is required.');
        }

        if (! $this->validateData($payload, $this->supplierRules())) {
            return redirect()->to('/app/suppliers/' . $supplierId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            (new SupplierModel())->update($supplierId, $this->buildSupplierData($payload, false));
            return redirect()->to('/app/suppliers')->with('success', 'Supplier updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/suppliers/' . $supplierId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteSupplier()
    {
        $supplierId = (int) $this->request->getPost('supplier_id');
        if ($supplierId < 1) {
            return redirect()->to('/app/suppliers')->with('error', 'Valid supplier ID is required for delete.');
        }

        try {
            (new SupplierModel())->delete($supplierId);
            return redirect()->to('/app/suppliers')->with('success', 'Supplier deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/suppliers')->with('error', $e->getMessage());
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
            return redirect()->to('/app/suppliers/' . $payload['supplier_id'] . '/ledger')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $amount = (float) $payload['amount'];
            $debit = 0.0;
            $credit = 0.0;

            if ($payload['entry_type'] === 'bill') {
                $debit = $amount;
            } elseif ($payload['entry_type'] === 'payment') {
                $credit = $amount;
            } else {
                if ($amount >= 0) {
                    $debit = $amount;
                } else {
                    $credit = abs($amount);
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

            return redirect()->to('/app/suppliers/' . $payload['supplier_id'] . '/ledger')->with('success', 'Ledger entry posted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/suppliers/' . $payload['supplier_id'] . '/ledger')->withInput()->with('error', $e->getMessage());
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

        if ($isCreate) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $data;
    }
}
