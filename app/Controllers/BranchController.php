<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BranchModel;

class BranchController extends BaseController
{
    public function index(): string
    {
$model = new BranchModel();

        return view('portal/branches/index', [
            'title'       => 'HJMS ERP | Branches',
            'headerTitle' => 'Branch Management',
            'activePage'  => 'branches',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $model->orderBy('id', 'DESC')->findAll(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createBranch()
    {
        $payload = [
            'code'    => (string) $this->request->getPost('code'),
            'name'    => (string) $this->request->getPost('name'),
            'phone'   => (string) $this->request->getPost('phone'),
            'address' => (string) $this->request->getPost('address'),
        ];

        if (! $this->validateData($payload, [
            'code'  => 'required|alpha_numeric_punct|min_length[2]|max_length[30]',
            'name'  => 'required|min_length[3]|max_length[120]',
            'phone' => 'permit_empty|max_length[30]',
        ])) {
            return redirect()->to('/app/branches')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new BranchModel();
            $model->insert([
                                'code'       => $payload['code'],
                'name'       => $payload['name'],
                'phone'      => $payload['phone'] !== '' ? $payload['phone'] : null,
                'address'    => $payload['address'] !== '' ? $payload['address'] : null,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/app/branches')->with('success', 'Branch created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/branches')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateBranch()
    {
        $branchId = (int) $this->request->getPost('branch_id');
$payload = [
            'code'      => (string) $this->request->getPost('code'),
            'name'      => (string) $this->request->getPost('name'),
            'phone'     => (string) $this->request->getPost('phone'),
            'address'   => (string) $this->request->getPost('address'),
            'is_active' => (string) $this->request->getPost('is_active'),
        ];

        if ($branchId < 1) {
            return redirect()->to('/app/branches')->withInput()->with('error', 'Valid branch ID is required.');
        }

        if (! $this->validateData($payload, [
            'code'      => 'permit_empty|alpha_numeric_punct|min_length[2]|max_length[30]',
            'name'      => 'permit_empty|min_length[3]|max_length[120]',
            'phone'     => 'permit_empty|max_length[30]',
            'address'   => 'permit_empty',
            'is_active' => 'permit_empty|in_list[0,1]',
        ])) {
            return redirect()->to('/app/branches')->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = array_filter($payload, static function ($value) {
            return $value !== '';
        });

        if ($data === []) {
            return redirect()->to('/app/branches')->withInput()->with('error', 'Provide at least one field to update for branch.');
        }

        if (isset($data['is_active'])) {
            $data['is_active'] = (int) $data['is_active'];
        }

        try {
            $model = new BranchModel();
            $model->update($branchId, $data + ['updated_at' => date('Y-m-d H:i:s')]);

            return redirect()->to('/app/branches')->with('success', 'Branch updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/branches')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteBranch()
    {
        $branchId = (int) $this->request->getPost('branch_id');
if ($branchId < 1) {
            return redirect()->to('/app/branches')->with('error', 'Valid branch ID is required for delete.');
        }

        try {
            $model = new BranchModel();
            $deleted = $model->delete($branchId);

            if (! $deleted) {
                return redirect()->to('/app/branches')->with('error', 'Branch not found or already removed.');
            }

            return redirect()->to('/app/branches')->with('success', 'Branch deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/branches')->with('error', $e->getMessage());
        }
    }
}
