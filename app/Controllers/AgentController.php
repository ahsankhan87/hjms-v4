<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgentModel;
use App\Services\AgentLedgerService;

class AgentController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $model = new AgentModel();

        return view('portal/agents/index', [
            'title'       => 'HJMS ERP | Agents',
            'headerTitle' => 'Agent Management',
            'activePage'  => 'agents',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $model->orderBy('id', 'DESC')->findAll(),
            'branches'    => $db->table('branches')->orderBy('name', 'ASC')->get()->getResultArray(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add(): string
    {
        $db = db_connect();

        return view('portal/agents/add', [
            'title'       => 'HJMS ERP | Add Agent',
            'headerTitle' => 'Agent Management',
            'activePage'  => 'agents',
            'userEmail'   => (string) session('user_email'),
            'branches'    => $db->table('branches')->orderBy('name', 'ASC')->get()->getResultArray(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $model = new AgentModel();
        $row = $model->find($id);
        if (empty($row)) {
            return redirect()->to('/agents')->with('error', 'Agent not found.');
        }

        $db = db_connect();

        return view('portal/agents/edit', [
            'title'       => 'HJMS ERP | Edit Agent',
            'headerTitle' => 'Agent Management',
            'activePage'  => 'agents',
            'userEmail'   => (string) session('user_email'),
            'row'         => $row,
            'branches'    => $db->table('branches')->orderBy('name', 'ASC')->get()->getResultArray(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function ledger(int $id)
    {
        $agent = (new AgentModel())->find($id);
        if (empty($agent)) {
            return redirect()->to('/agents')->with('error', 'Agent not found.');
        }

        $ledgerService = new AgentLedgerService();
        $rows = $ledgerService->getAgentLedgerRows($id);

        $runningBalance = 0.0;
        foreach ($rows as &$item) {
            $runningBalance += (float) ($item['debit_amount'] ?? 0);
            $runningBalance -= (float) ($item['credit_amount'] ?? 0);
            $item['running_balance'] = $runningBalance;
        }
        unset($item);

        return view('portal/agents/ledger', [
            'title'       => 'HJMS ERP | Agent Ledger',
            'headerTitle' => 'Agent Management',
            'activePage'  => 'agents',
            'userEmail'   => (string) session('user_email'),
            'agent'       => $agent,
            'rows'        => $rows,
            'closingBalance' => $runningBalance,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createLedgerEntry()
    {
        $payload = [
            'agent_id' => (int) $this->request->getPost('agent_id'),
            'entry_date' => (string) $this->request->getPost('entry_date'),
            'entry_type' => (string) $this->request->getPost('entry_type'),
            'amount' => (string) $this->request->getPost('amount'),
            'description' => trim((string) $this->request->getPost('description')),
        ];

        if (! $this->validateData($payload, [
            'agent_id' => 'required|integer',
            'entry_date' => 'required|valid_date[Y-m-d]',
            'entry_type' => 'required|in_list[debit,credit,adjustment]',
            'amount' => 'required|decimal',
            'description' => 'permit_empty|max_length[255]',
        ])) {
            return redirect()->to('/agents/' . $payload['agent_id'] . '/ledger')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $ledgerService = new AgentLedgerService();
            $ledgerService->createManualEntry($payload);
            $ledgerService->recalculateAgentBalance((int) $payload['agent_id']);

            return redirect()->to('/agents/' . $payload['agent_id'] . '/ledger')->with('success', 'Ledger entry posted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/agents/' . $payload['agent_id'] . '/ledger')->withInput()->with('error', $e->getMessage());
        }
    }

    public function createAgent()
    {
        $payload = [
            'code'              => trim((string) $this->request->getPost('code')),
            'name'              => trim((string) $this->request->getPost('name')),
            'email'             => strtolower(trim((string) $this->request->getPost('email'))),
            'phone'             => trim((string) $this->request->getPost('phone')),
            'commission_type'   => trim((string) $this->request->getPost('commission_type')),
            'commission_value'  => trim((string) $this->request->getPost('commission_value')),
            'credit_limit'      => trim((string) $this->request->getPost('credit_limit')),
            'branch_id'         => $this->request->getPost('branch_id') !== '' ? (int) $this->request->getPost('branch_id') : null,
        ];

        if (! $this->validateData($payload, [
            'code'             => 'required|alpha_numeric_punct|min_length[2]|max_length[40]',
            'name'             => 'required|min_length[3]|max_length[150]',
            'email'            => 'permit_empty|valid_email',
            'phone'            => 'permit_empty|max_length[30]',
            'commission_type'  => 'permit_empty|in_list[percentage,fixed]',
            'commission_value' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'credit_limit'     => 'permit_empty|decimal|greater_than_equal_to[0]',
            'branch_id'        => 'permit_empty|integer',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new AgentModel();

            if ($this->isCodeTaken($payload['code'])) {
                return redirect()->back()->withInput()->with('error', 'Agent code already exists.');
            }

            if ($payload['email'] !== '' && $this->isEmailTaken($payload['email'])) {
                return redirect()->back()->withInput()->with('error', 'Agent email already exists.');
            }

            if ($payload['branch_id'] !== null && ! $this->branchExists($payload['branch_id'])) {
                return redirect()->back()->withInput()->with('error', 'Selected branch does not exist.');
            }

            $model->insert([
                'branch_id'         => $payload['branch_id'],
                'code'              => $payload['code'],
                'name'              => $payload['name'],
                'email'             => $payload['email'] !== '' ? $payload['email'] : null,
                'phone'             => $payload['phone'] !== '' ? $payload['phone'] : null,
                'commission_type'   => $payload['commission_type'] !== '' ? $payload['commission_type'] : 'percentage',
                'commission_value'  => $payload['commission_value'] !== '' ? (float) $payload['commission_value'] : 0,
                'credit_limit'      => $payload['credit_limit'] !== '' ? (float) $payload['credit_limit'] : 0,
                'current_balance'   => 0,
                'is_active'         => 1,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/agents')->with('success', 'Agent created successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateAgent()
    {
        $agentId = (int) $this->request->getPost('agent_id');
        $payload = [
            'code'             => trim((string) $this->request->getPost('code')),
            'name'             => trim((string) $this->request->getPost('name')),
            'email'            => strtolower(trim((string) $this->request->getPost('email'))),
            'phone'            => trim((string) $this->request->getPost('phone')),
            'commission_type'  => trim((string) $this->request->getPost('commission_type')),
            'commission_value' => trim((string) $this->request->getPost('commission_value')),
            'credit_limit'     => trim((string) $this->request->getPost('credit_limit')),
            'branch_id'        => trim((string) $this->request->getPost('branch_id')),
            'is_active'        => trim((string) $this->request->getPost('is_active')),
        ];

        if ($agentId < 1) {
            return redirect()->back()->withInput()->with('error', 'Valid agent ID is required.');
        }

        if (! $this->validateData($payload, [
            'code'             => 'permit_empty|alpha_numeric_punct|min_length[2]|max_length[40]',
            'name'             => 'permit_empty|min_length[3]|max_length[150]',
            'email'            => 'permit_empty|valid_email',
            'phone'            => 'permit_empty|max_length[30]',
            'commission_type'  => 'permit_empty|in_list[percentage,fixed]',
            'commission_value' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'credit_limit'     => 'permit_empty|decimal|greater_than_equal_to[0]',
            'branch_id'        => 'permit_empty|integer',
            'is_active'        => 'permit_empty|in_list[0,1]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new AgentModel();
        $existing = $model->find($agentId);
        if (empty($existing)) {
            return redirect()->to('/agents')->withInput()->with('error', 'Agent not found.');
        }

        $data = array_filter($payload, static function ($value) {
            return $value !== '';
        });

        if ($data === []) {
            return redirect()->back()->withInput()->with('error', 'Provide at least one field to update for agent.');
        }

        if (isset($data['commission_value'])) {
            $data['commission_value'] = (float) $data['commission_value'];
        }
        if (isset($data['credit_limit'])) {
            $data['credit_limit'] = (float) $data['credit_limit'];
        }
        if (isset($data['branch_id'])) {
            $data['branch_id'] = (int) $data['branch_id'];
            if (! $this->branchExists($data['branch_id'])) {
                return redirect()->back()->withInput()->with('error', 'Selected branch does not exist.');
            }
        }
        if (isset($data['is_active'])) {
            $data['is_active'] = (int) $data['is_active'];
        }

        if (isset($data['code']) && $this->isCodeTaken($data['code'], $agentId)) {
            return redirect()->back()->withInput()->with('error', 'Agent code already exists.');
        }

        if (isset($data['email']) && $data['email'] !== '' && $this->isEmailTaken($data['email'], $agentId)) {
            return redirect()->back()->withInput()->with('error', 'Agent email already exists.');
        }

        try {
            $model->update($agentId, $data + ['updated_at' => date('Y-m-d H:i:s')]);

            return redirect()->to('/agents')->with('success', 'Agent updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteAgent()
    {
        $agentId = (int) $this->request->getPost('agent_id');
        if ($agentId < 1) {
            return redirect()->to('/agents')->with('error', 'Valid agent ID is required for delete.');
        }

        try {
            $model = new AgentModel();
            $existing = $model->find($agentId);
            if (empty($existing)) {
                return redirect()->to('/agents')->with('error', 'Agent not found or already removed.');
            }

            $bookingCount = db_connect()->table('bookings')->where('agent_id', $agentId)->countAllResults();
            if ($bookingCount > 0) {
                return redirect()->to('/agents')->with('error', 'Cannot delete this agent because it is linked to bookings.');
            }

            $deleted = $model->delete($agentId);

            if (! $deleted) {
                return redirect()->to('/agents')->with('error', 'Agent not found or already removed.');
            }

            return redirect()->to('/agents')->with('success', 'Agent deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/agents')->with('error', $e->getMessage());
        }
    }

    private function branchExists(int $branchId): bool
    {
        return db_connect()->table('branches')->where('id', $branchId)->countAllResults() > 0;
    }

    private function isCodeTaken(string $code, $excludeAgentId = null): bool
    {
        $builder = db_connect()->table('agents')->where('code', $code);
        if ($excludeAgentId !== null) {
            $builder->where('id !=', $excludeAgentId);
        }

        return $builder->countAllResults() > 0;
    }

    private function isEmailTaken(string $email, $excludeAgentId = null): bool
    {
        $builder = db_connect()->table('agents')->where('email', $email);
        if ($excludeAgentId !== null) {
            $builder->where('id !=', $excludeAgentId);
        }

        return $builder->countAllResults() > 0;
    }
}
