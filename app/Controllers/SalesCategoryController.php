<?php

namespace App\Controllers;

use App\Models\SalesCategoryModel;

class SalesCategoryController extends BaseController
{
    public function index()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $db = db_connect();
        $rows = (new SalesCategoryModel())->where('season_id', $seasonId)->orderBy('name', 'ASC')->findAll();

        $summaryMap = [];
        if ($db->tableExists('sales') && $rows !== []) {
            $aggregateRows = $db->table('sales')->select('sales_category_id, COUNT(*) AS sale_count, COALESCE(SUM(amount), 0) AS total_amount', false)->where('season_id', $seasonId)->groupBy('sales_category_id')->get()->getResultArray();
            foreach ($aggregateRows as $aggregateRow) {
                $categoryId = (int) ($aggregateRow['sales_category_id'] ?? 0);
                if ($categoryId > 0) {
                    $summaryMap[$categoryId] = ['sale_count' => (int) ($aggregateRow['sale_count'] ?? 0), 'total_amount' => (float) ($aggregateRow['total_amount'] ?? 0)];
                }
            }
        }

        foreach ($rows as &$row) {
            $categoryId = (int) ($row['id'] ?? 0);
            $row['sale_count'] = (int) ($summaryMap[$categoryId]['sale_count'] ?? 0);
            $row['total_amount'] = (float) ($summaryMap[$categoryId]['total_amount'] ?? 0);
        }
        unset($row);

        return view('portal/sales_categories/index', ['title' => 'HJMS ERP | Sales Categories', 'headerTitle' => 'Sales Categories', 'activePage' => 'sales', 'userEmail' => (string) session('user_email'), 'rows' => $rows, 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    public function add()
    {
        if ($this->activeSeasonId() === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        return view('portal/sales_categories/add', ['title' => 'HJMS ERP | Add Sales Category', 'headerTitle' => 'Sales Categories', 'activePage' => 'sales', 'userEmail' => (string) session('user_email'), 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    public function edit(int $id)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $row = (new SalesCategoryModel())->where('season_id', $seasonId)->find($id);
        if (empty($row)) {
            return redirect()->to('/sales-categories')->with('error', 'Sales category not found.');
        }

        return view('portal/sales_categories/edit', ['title' => 'HJMS ERP | Edit Sales Category', 'headerTitle' => 'Sales Categories', 'activePage' => 'sales', 'userEmail' => (string) session('user_email'), 'row' => $row, 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    public function createSalesCategory()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = ['name' => trim((string) $this->request->getPost('name')), 'description' => trim((string) $this->request->getPost('description')), 'is_active' => (string) ($this->request->getPost('is_active') ?: '1')];
        if (! $this->validateData($payload, ['name' => 'required|min_length[2]|max_length[160]', 'description' => 'permit_empty|max_length[5000]', 'is_active' => 'required|in_list[0,1]'])) {
            return redirect()->to('/sales-categories/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new SalesCategoryModel();
        if ($model->where('season_id', $seasonId)->where('name', $payload['name'])->first() !== null) {
            return redirect()->to('/sales-categories/add')->withInput()->with('error', 'Sales category already exists in this season.');
        }

        try {
            $model->insert(['season_id' => $seasonId, 'name' => $payload['name'], 'description' => $payload['description'] !== '' ? $payload['description'] : null, 'is_active' => (int) $payload['is_active'], 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
            return redirect()->to('/sales-categories')->with('success', 'Sales category added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/sales-categories/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateSalesCategory()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $categoryId = (int) $this->request->getPost('category_id');
        if ($categoryId < 1) {
            return redirect()->to('/sales-categories')->with('error', 'Valid sales category ID is required.');
        }

        $payload = ['name' => trim((string) $this->request->getPost('name')), 'description' => trim((string) $this->request->getPost('description')), 'is_active' => (string) ($this->request->getPost('is_active') ?: '1')];
        if (! $this->validateData($payload, ['name' => 'required|min_length[2]|max_length[160]', 'description' => 'permit_empty|max_length[5000]', 'is_active' => 'required|in_list[0,1]'])) {
            return redirect()->to('/sales-categories/' . $categoryId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new SalesCategoryModel();
        $row = $model->where('season_id', $seasonId)->find($categoryId);
        if (empty($row)) {
            return redirect()->to('/sales-categories')->with('error', 'Sales category not found.');
        }

        if ($model->where('season_id', $seasonId)->where('name', $payload['name'])->where('id !=', $categoryId)->first() !== null) {
            return redirect()->to('/sales-categories/' . $categoryId . '/edit')->withInput()->with('error', 'Sales category already exists in this season.');
        }

        try {
            $model->update($categoryId, ['name' => $payload['name'], 'description' => $payload['description'] !== '' ? $payload['description'] : null, 'is_active' => (int) $payload['is_active'], 'updated_at' => date('Y-m-d H:i:s')]);
            return redirect()->to('/sales-categories')->with('success', 'Sales category updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/sales-categories/' . $categoryId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteSalesCategory()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $categoryId = (int) $this->request->getPost('category_id');
        if ($categoryId < 1) {
            return redirect()->to('/sales-categories')->with('error', 'Valid sales category ID is required.');
        }

        $model = new SalesCategoryModel();
        $row = $model->where('season_id', $seasonId)->find($categoryId);
        if (empty($row)) {
            return redirect()->to('/sales-categories')->with('error', 'Sales category not found.');
        }

        $db = db_connect();
        $linkedCount = $db->tableExists('sales') ? (int) $db->table('sales')->where('season_id', $seasonId)->where('sales_category_id', $categoryId)->countAllResults() : 0;
        if ($linkedCount > 0) {
            return redirect()->to('/sales-categories')->with('error', 'This category cannot be deleted because sales already exist for it.');
        }

        try {
            $model->delete($categoryId);
            return redirect()->to('/sales-categories')->with('success', 'Sales category deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/sales-categories')->with('error', $e->getMessage());
        }
    }
}
