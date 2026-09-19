<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SupplierCategoryModel;

class SupplierCategoryController extends BaseController
{
    public function index(): string
    {
        $model = new SupplierCategoryModel();
        $rows = $model->orderBy('name', 'ASC')->findAll();

        $usageMap = [];
        try {
            $db = db_connect();
            if ($db->tableExists('supplier_ledger_entries') && $db->fieldExists('category_id', 'supplier_ledger_entries')) {
                $aggregateRows = $db->table('supplier_ledger_entries')
                    ->select('category_id, COUNT(*) AS entry_count', false)
                    ->where('category_id IS NOT NULL', null, false)
                    ->groupBy('category_id')
                    ->get()
                    ->getResultArray();

                foreach ($aggregateRows as $aggregateRow) {
                    $categoryId = (int) ($aggregateRow['category_id'] ?? 0);
                    if ($categoryId > 0) {
                        $usageMap[$categoryId] = (int) ($aggregateRow['entry_count'] ?? 0);
                    }
                }
            }
        } catch (\Throwable $e) {
            $usageMap = [];
        }

        foreach ($rows as &$row) {
            $row['entry_count'] = (int) ($usageMap[(int) ($row['id'] ?? 0)] ?? 0);
        }
        unset($row);

        return view('portal/supplier_categories/index', [
            'title' => 'HJMS ERP | Supplier Categories',
            'headerTitle' => 'Supplier Categories',
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
        return view('portal/supplier_categories/add', [
            'title' => 'HJMS ERP | Add Supplier Category',
            'headerTitle' => 'Supplier Categories',
            'activePage' => 'suppliers',
            'userEmail' => (string) session('user_email'),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $row = (new SupplierCategoryModel())->find($id);
        if (empty($row)) {
            return redirect()->to('/supplier-categories')->with('error', 'Supplier category not found.');
        }

        return view('portal/supplier_categories/edit', [
            'title' => 'HJMS ERP | Edit Supplier Category',
            'headerTitle' => 'Supplier Categories',
            'activePage' => 'suppliers',
            'userEmail' => (string) session('user_email'),
            'row' => $row,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createSupplierCategory()
    {
        $payload = $this->extractCategoryPayload();

        if (! $this->validateData($payload, $this->categoryRules())) {
            return redirect()->to('/supplier-categories/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new SupplierCategoryModel();
        if ($model->where('name', $payload['name'])->first() !== null) {
            return redirect()->to('/supplier-categories/add')->withInput()->with('error', 'Supplier category already exists.');
        }

        try {
            $model->insert([
                'name' => $payload['name'],
                'description' => $payload['description'] !== '' ? $payload['description'] : null,
                'is_active' => (int) $payload['is_active'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/supplier-categories')->with('success', 'Supplier category added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/supplier-categories/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateSupplierCategory()
    {
        $categoryId = (int) $this->request->getPost('category_id');
        if ($categoryId < 1) {
            return redirect()->to('/supplier-categories')->with('error', 'Valid supplier category ID is required.');
        }

        $payload = $this->extractCategoryPayload();

        if (! $this->validateData($payload, $this->categoryRules())) {
            return redirect()->to('/supplier-categories/' . $categoryId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new SupplierCategoryModel();
        if (empty($model->find($categoryId))) {
            return redirect()->to('/supplier-categories')->with('error', 'Supplier category not found.');
        }

        if ($model->where('name', $payload['name'])->where('id !=', $categoryId)->first() !== null) {
            return redirect()->to('/supplier-categories/' . $categoryId . '/edit')->withInput()->with('error', 'Supplier category already exists.');
        }

        try {
            $model->update($categoryId, [
                'name' => $payload['name'],
                'description' => $payload['description'] !== '' ? $payload['description'] : null,
                'is_active' => (int) $payload['is_active'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/supplier-categories')->with('success', 'Supplier category updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/supplier-categories/' . $categoryId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteSupplierCategory()
    {
        $categoryId = (int) $this->request->getPost('category_id');
        if ($categoryId < 1) {
            return redirect()->to('/supplier-categories')->with('error', 'Valid supplier category ID is required for delete.');
        }

        try {
            $db = db_connect();
            if ($db->tableExists('supplier_ledger_entries') && $db->fieldExists('category_id', 'supplier_ledger_entries')) {
                $inUse = $db->table('supplier_ledger_entries')->where('category_id', $categoryId)->countAllResults();
                if ($inUse > 0) {
                    return redirect()->to('/supplier-categories')->with('error', 'This category is used by ' . $inUse . ' ledger entr' . ($inUse === 1 ? 'y' : 'ies') . ' and cannot be deleted.');
                }
            }

            (new SupplierCategoryModel())->delete($categoryId);

            return redirect()->to('/supplier-categories')->with('success', 'Supplier category deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/supplier-categories')->with('error', $e->getMessage());
        }
    }

    private function extractCategoryPayload(): array
    {
        return [
            'name' => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')),
            'is_active' => (string) ($this->request->getPost('is_active') ?: '1'),
        ];
    }

    private function categoryRules(): array
    {
        return [
            'name' => 'required|min_length[2]|max_length[160]',
            'description' => 'permit_empty|max_length[500]',
            'is_active' => 'required|in_list[0,1]',
        ];
    }
}
