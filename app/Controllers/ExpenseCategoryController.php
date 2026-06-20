<?php

namespace App\Controllers;

use App\Models\ExpenseCategoryModel;

class ExpenseCategoryController extends BaseController
{
    public function index()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $db = db_connect();
        $rows = (new ExpenseCategoryModel())->where('season_id', $seasonId)->orderBy('name', 'ASC')->findAll();

        $summaryMap = [];
        if ($db->tableExists('expenses') && $rows !== []) {
            $aggregateRows = $db->table('expenses')->select('expense_category_id, COUNT(*) AS expense_count, COALESCE(SUM(amount), 0) AS total_amount', false)->where('season_id', $seasonId)->groupBy('expense_category_id')->get()->getResultArray();
            foreach ($aggregateRows as $aggregateRow) {
                $categoryId = (int) ($aggregateRow['expense_category_id'] ?? 0);
                if ($categoryId > 0) {
                    $summaryMap[$categoryId] = ['expense_count' => (int) ($aggregateRow['expense_count'] ?? 0), 'total_amount' => (float) ($aggregateRow['total_amount'] ?? 0)];
                }
            }
        }

        foreach ($rows as &$row) {
            $categoryId = (int) ($row['id'] ?? 0);
            $row['expense_count'] = (int) ($summaryMap[$categoryId]['expense_count'] ?? 0);
            $row['total_amount'] = (float) ($summaryMap[$categoryId]['total_amount'] ?? 0);
        }
        unset($row);

        return view('portal/expense_categories/index', ['title' => 'HJMS ERP | Expense Categories', 'headerTitle' => 'Expense Categories', 'activePage' => 'expense_categories', 'userEmail' => (string) session('user_email'), 'rows' => $rows, 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    public function add()
    {
        if ($this->activeSeasonId() === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        return view('portal/expense_categories/add', ['title' => 'HJMS ERP | Add Expense Category', 'headerTitle' => 'Expense Categories', 'activePage' => 'expense_categories', 'userEmail' => (string) session('user_email'), 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    public function edit(int $id)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $row = (new ExpenseCategoryModel())->where('season_id', $seasonId)->find($id);
        if (empty($row)) {
            return redirect()->to('/expense-categories')->with('error', 'Expense category not found.');
        }

        return view('portal/expense_categories/edit', ['title' => 'HJMS ERP | Edit Expense Category', 'headerTitle' => 'Expense Categories', 'activePage' => 'expense_categories', 'userEmail' => (string) session('user_email'), 'row' => $row, 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    public function createExpenseCategory()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = ['name' => trim((string) $this->request->getPost('name')), 'description' => trim((string) $this->request->getPost('description')), 'is_active' => (string) ($this->request->getPost('is_active') ?: '1')];
        if (! $this->validateData($payload, ['name' => 'required|min_length[2]|max_length[160]', 'description' => 'permit_empty|max_length[5000]', 'is_active' => 'required|in_list[0,1]'])) {
            return redirect()->to('/expense-categories/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new ExpenseCategoryModel();
        if ($model->where('season_id', $seasonId)->where('name', $payload['name'])->first() !== null) {
            return redirect()->to('/expense-categories/add')->withInput()->with('error', 'Expense category already exists in this season.');
        }

        try {
            $model->insert(['season_id' => $seasonId, 'name' => $payload['name'], 'description' => $payload['description'] !== '' ? $payload['description'] : null, 'is_active' => (int) $payload['is_active'], 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
            return redirect()->to('/expense-categories')->with('success', 'Expense category added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/expense-categories/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateExpenseCategory()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $categoryId = (int) $this->request->getPost('category_id');
        if ($categoryId < 1) {
            return redirect()->to('/expense-categories')->with('error', 'Valid expense category ID is required.');
        }

        $payload = ['name' => trim((string) $this->request->getPost('name')), 'description' => trim((string) $this->request->getPost('description')), 'is_active' => (string) ($this->request->getPost('is_active') ?: '1')];
        if (! $this->validateData($payload, ['name' => 'required|min_length[2]|max_length[160]', 'description' => 'permit_empty|max_length[5000]', 'is_active' => 'required|in_list[0,1]'])) {
            return redirect()->to('/expense-categories/' . $categoryId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new ExpenseCategoryModel();
        $row = $model->where('season_id', $seasonId)->find($categoryId);
        if (empty($row)) {
            return redirect()->to('/expense-categories')->with('error', 'Expense category not found.');
        }

        if ($model->where('season_id', $seasonId)->where('name', $payload['name'])->where('id !=', $categoryId)->first() !== null) {
            return redirect()->to('/expense-categories/' . $categoryId . '/edit')->withInput()->with('error', 'Expense category already exists in this season.');
        }

        try {
            $model->update($categoryId, ['name' => $payload['name'], 'description' => $payload['description'] !== '' ? $payload['description'] : null, 'is_active' => (int) $payload['is_active'], 'updated_at' => date('Y-m-d H:i:s')]);
            return redirect()->to('/expense-categories')->with('success', 'Expense category updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/expense-categories/' . $categoryId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteExpenseCategory()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $categoryId = (int) $this->request->getPost('category_id');
        if ($categoryId < 1) {
            return redirect()->to('/expense-categories')->with('error', 'Valid expense category ID is required.');
        }

        $model = new ExpenseCategoryModel();
        $row = $model->where('season_id', $seasonId)->find($categoryId);
        if (empty($row)) {
            return redirect()->to('/expense-categories')->with('error', 'Expense category not found.');
        }

        $db = db_connect();
        $linkedCount = $db->tableExists('expenses') ? (int) $db->table('expenses')->where('season_id', $seasonId)->where('expense_category_id', $categoryId)->countAllResults() : 0;
        if ($linkedCount > 0) {
            return redirect()->to('/expense-categories')->with('error', 'This category cannot be deleted because expenses already exist for it.');
        }

        try {
            $model->delete($categoryId);
            return redirect()->to('/expense-categories')->with('success', 'Expense category deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/expense-categories')->with('error', $e->getMessage());
        }
    }
}
