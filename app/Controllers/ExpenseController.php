<?php

namespace App\Controllers;

use App\Models\ExpenseCategoryModel;
use App\Models\ExpenseModel;

class ExpenseController extends BaseController
{
    public function index()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $filters = ['from_date' => (string) $this->request->getGet('from_date'), 'to_date' => (string) $this->request->getGet('to_date'), 'expense_category_id' => (string) $this->request->getGet('expense_category_id'), 'payment_method' => (string) $this->request->getGet('payment_method'), 'status' => (string) $this->request->getGet('status')];
        $filterErrors = [];
        if (! $this->validateData($filters, ['from_date' => 'permit_empty|valid_date[Y-m-d]', 'to_date' => 'permit_empty|valid_date[Y-m-d]', 'expense_category_id' => 'permit_empty|integer', 'payment_method' => 'permit_empty|in_list[cash,bank_transfer,cheque,card,online,other]', 'status' => 'permit_empty|in_list[posted,voided,pending]'])) {
            $filterErrors = $this->validator->getErrors();
            $filters = ['from_date' => '', 'to_date' => '', 'expense_category_id' => '', 'payment_method' => '', 'status' => ''];
        }

        $db = db_connect();
        $categoryRows = (new ExpenseCategoryModel())->where('season_id', $seasonId)->orderBy('name', 'ASC')->findAll();
        $query = $db->table('expenses e')->select('e.*, c.name AS category_name')->join('expense_categories c', 'c.id = e.expense_category_id', 'left')->where('e.season_id', $seasonId);
        if ($filters['from_date'] !== '') {
            $query->where('DATE(e.expense_date) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $query->where('DATE(e.expense_date) <=', $filters['to_date']);
        }
        if ($filters['expense_category_id'] !== '') {
            $query->where('e.expense_category_id', (int) $filters['expense_category_id']);
        }
        if ($filters['payment_method'] !== '') {
            $query->where('e.payment_method', $filters['payment_method']);
        }
        if ($filters['status'] !== '') {
            $query->where('e.status', $filters['status']);
        }
        $rows = $query->orderBy('e.expense_date', 'DESC')->orderBy('e.id', 'DESC')->get()->getResultArray();
        $totalAmount = 0.0;
        foreach ($rows as $row) {
            $totalAmount += (float) ($row['amount'] ?? 0);
        }

        return view('portal/expenses/index', ['title' => 'HJMS ERP | Expenses', 'headerTitle' => 'Expenses', 'activePage' => 'expenses', 'userEmail' => (string) session('user_email'), 'rows' => $rows, 'categories' => $categoryRows, 'filters' => $filters, 'filterErrors' => $filterErrors, 'totalAmount' => $totalAmount, 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    public function add()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        return view('portal/expenses/add', ['title' => 'HJMS ERP | Add Expense', 'headerTitle' => 'Expenses', 'activePage' => 'expenses', 'userEmail' => (string) session('user_email'), 'categories' => (new ExpenseCategoryModel())->where('season_id', $seasonId)->orderBy('name', 'ASC')->findAll(), 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    public function edit(int $id)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $row = (new ExpenseModel())->where('season_id', $seasonId)->find($id);
        if (empty($row)) {
            return redirect()->to('/expenses')->with('error', 'Expense not found.');
        }

        return view('portal/expenses/edit', ['title' => 'HJMS ERP | Edit Expense', 'headerTitle' => 'Expenses', 'activePage' => 'expenses', 'userEmail' => (string) session('user_email'), 'row' => $row, 'categories' => (new ExpenseCategoryModel())->where('season_id', $seasonId)->orderBy('name', 'ASC')->findAll(), 'success' => session()->getFlashdata('success'), 'error' => session()->getFlashdata('error'), 'errors' => session()->getFlashdata('errors') ?: []]);
    }

    public function createExpense()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = $this->extractExpensePayload();
        if (! $this->validateData($payload, $this->expenseRules())) {
            return redirect()->to('/expenses/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        if ((new ExpenseCategoryModel())->where('season_id', $seasonId)->find((int) $payload['expense_category_id']) === null) {
            return redirect()->to('/expenses/add')->withInput()->with('error', 'Expense category not found for the active season.');
        }

        try {
            (new ExpenseModel())->insert($this->buildExpenseData($payload, $seasonId, true));
            return redirect()->to('/expenses')->with('success', 'Expense added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/expenses/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateExpense()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $expenseId = (int) $this->request->getPost('expense_id');
        if ($expenseId < 1) {
            return redirect()->to('/expenses')->with('error', 'Valid expense ID is required.');
        }

        $payload = $this->extractExpensePayload();
        if (! $this->validateData($payload, $this->expenseRules())) {
            return redirect()->to('/expenses/' . $expenseId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new ExpenseModel();
        $row = $model->where('season_id', $seasonId)->find($expenseId);
        if (empty($row)) {
            return redirect()->to('/expenses')->with('error', 'Expense not found.');
        }

        if ((new ExpenseCategoryModel())->where('season_id', $seasonId)->find((int) $payload['expense_category_id']) === null) {
            return redirect()->to('/expenses/' . $expenseId . '/edit')->withInput()->with('error', 'Expense category not found for the active season.');
        }

        try {
            $model->update($expenseId, $this->buildExpenseData($payload, $seasonId, false));
            return redirect()->to('/expenses')->with('success', 'Expense updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/expenses/' . $expenseId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteExpense()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $expenseId = (int) $this->request->getPost('expense_id');
        if ($expenseId < 1) {
            return redirect()->to('/expenses')->with('error', 'Valid expense ID is required.');
        }

        $model = new ExpenseModel();
        $row = $model->where('season_id', $seasonId)->find($expenseId);
        if (empty($row)) {
            return redirect()->to('/expenses')->with('error', 'Expense not found.');
        }

        try {
            $model->delete($expenseId);
            return redirect()->to('/expenses')->with('success', 'Expense deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/expenses')->with('error', $e->getMessage());
        }
    }

    private function extractExpensePayload(): array
    {
        return ['expense_date' => (string) $this->request->getPost('expense_date'), 'expense_category_id' => (string) $this->request->getPost('expense_category_id'), 'amount' => (string) $this->request->getPost('amount'), 'paid_to' => trim((string) $this->request->getPost('paid_to')), 'payment_method' => (string) ($this->request->getPost('payment_method') ?: 'cash'), 'reference_no' => trim((string) $this->request->getPost('reference_no')), 'note' => trim((string) $this->request->getPost('note')), 'status' => (string) ($this->request->getPost('status') ?: 'posted')];
    }

    private function expenseRules(): array
    {
        return ['expense_date' => 'required|valid_date[Y-m-d]', 'expense_category_id' => 'required|integer', 'amount' => 'required|decimal|greater_than[0]', 'paid_to' => 'permit_empty|max_length[160]', 'payment_method' => 'required|in_list[cash,bank_transfer,cheque,card,online,other]', 'reference_no' => 'permit_empty|max_length[120]', 'note' => 'permit_empty|max_length[5000]', 'status' => 'required|in_list[posted,voided,pending]'];
    }

    private function buildExpenseData(array $payload, int $seasonId, bool $isCreate): array
    {
        $now = date('Y-m-d H:i:s');
        $userId = (int) (session('user_id') ?? 0);
        $data = ['season_id' => $seasonId, 'expense_category_id' => (int) $payload['expense_category_id'], 'expense_date' => $payload['expense_date'], 'amount' => (float) $payload['amount'], 'paid_to' => $payload['paid_to'] !== '' ? $payload['paid_to'] : null, 'payment_method' => $payload['payment_method'], 'reference_no' => $payload['reference_no'] !== '' ? $payload['reference_no'] : null, 'note' => $payload['note'] !== '' ? $payload['note'] : null, 'status' => $payload['status'], 'updated_at' => $now];
        if ($isCreate) {
            $data['created_at'] = $now;
            $data['created_by'] = $userId > 0 ? $userId : null;
        }
        return $data;
    }
}
