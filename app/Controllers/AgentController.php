<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgentModel;

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

    public function createAgent()
    {
        $payload = [
            'code'              => (string) $this->request->getPost('code'),
            'name'              => (string) $this->request->getPost('name'),
            'email'             => (string) $this->request->getPost('email'),
            'phone'             => (string) $this->request->getPost('phone'),
            'commission_type'   => (string) $this->request->getPost('commission_type'),
            'commission_value'  => (string) $this->request->getPost('commission_value'),
            'credit_limit'      => (string) $this->request->getPost('credit_limit'),
            'branch_id'         => $this->request->getPost('branch_id') !== '' ? (int) $this->request->getPost('branch_id') : null,
        ];

        if (! $this->validateData($payload, [
            'code'             => 'required|alpha_numeric_punct|min_length[2]|max_length[40]',
            'name'             => 'required|min_length[3]|max_length[150]',
            'email'            => 'permit_empty|valid_email',
            'phone'            => 'permit_empty|max_length[30]',
            'commission_type'  => 'permit_empty|in_list[percentage,fixed]',
            'commission_value' => 'permit_empty|decimal',
            'credit_limit'     => 'permit_empty|decimal',
            'branch_id'        => 'permit_empty|integer',
        ])) {
            return redirect()->to('/app/agents')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new AgentModel();
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

            return redirect()->to('/app/agents')->with('success', 'Agent created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/agents')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateAgent()
    {
        $agentId = (int) $this->request->getPost('agent_id');
$payload = [
            'code'             => (string) $this->request->getPost('code'),
            'name'             => (string) $this->request->getPost('name'),
            'email'            => (string) $this->request->getPost('email'),
            'phone'            => (string) $this->request->getPost('phone'),
            'commission_type'  => (string) $this->request->getPost('commission_type'),
            'commission_value' => (string) $this->request->getPost('commission_value'),
            'credit_limit'     => (string) $this->request->getPost('credit_limit'),
            'branch_id'        => (string) $this->request->getPost('branch_id'),
            'is_active'        => (string) $this->request->getPost('is_active'),
        ];

        if ($agentId < 1) {
            return redirect()->to('/app/agents')->withInput()->with('error', 'Valid agent ID is required.');
        }

        if (! $this->validateData($payload, [
            'code'             => 'permit_empty|alpha_numeric_punct|min_length[2]|max_length[40]',
            'name'             => 'permit_empty|min_length[3]|max_length[150]',
            'email'            => 'permit_empty|valid_email',
            'phone'            => 'permit_empty|max_length[30]',
            'commission_type'  => 'permit_empty|in_list[percentage,fixed]',
            'commission_value' => 'permit_empty|decimal',
            'credit_limit'     => 'permit_empty|decimal',
            'branch_id'        => 'permit_empty|integer',
            'is_active'        => 'permit_empty|in_list[0,1]',
        ])) {
            return redirect()->to('/app/agents')->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = array_filter($payload, static function ($value) {
            return $value !== '';
        });

        if ($data === []) {
            return redirect()->to('/app/agents')->withInput()->with('error', 'Provide at least one field to update for agent.');
        }

        if (isset($data['commission_value'])) {
            $data['commission_value'] = (float) $data['commission_value'];
        }
        if (isset($data['credit_limit'])) {
            $data['credit_limit'] = (float) $data['credit_limit'];
        }
        if (isset($data['branch_id'])) {
            $data['branch_id'] = (int) $data['branch_id'];
        }
        if (isset($data['is_active'])) {
            $data['is_active'] = (int) $data['is_active'];
        }

        try {
            $model = new AgentModel();
            $model->update($agentId, $data + ['updated_at' => date('Y-m-d H:i:s')]);

            return redirect()->to('/app/agents')->with('success', 'Agent updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/agents')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteAgent()
    {
        $agentId = (int) $this->request->getPost('agent_id');
if ($agentId < 1) {
            return redirect()->to('/app/agents')->with('error', 'Valid agent ID is required for delete.');
        }

        try {
            $model = new AgentModel();
            $deleted = $model->delete($agentId);

            if (! $deleted) {
                return redirect()->to('/app/agents')->with('error', 'Agent not found or already removed.');
            }

            return redirect()->to('/app/agents')->with('success', 'Agent deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/agents')->with('error', $e->getMessage());
        }
    }
}
