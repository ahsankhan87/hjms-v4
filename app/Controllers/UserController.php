<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;

class UserController extends BaseController
{
    public function index(): string
    {
        $users = (new UserModel())
            ->select('id, name, email, is_active, last_login_at, created_at')
            ->orderBy('id', 'DESC')
            ->findAll();

        $roles = [];
        $userRoles = [];

        $db = db_connect();
        if ($db->tableExists('roles')) {
            $roles = (new RoleModel())
                ->select('id, name')
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll();
        }

        if ($db->tableExists('user_roles') && $db->tableExists('roles')) {
            $rows = $db->table('user_roles ur')
                ->select('ur.user_id, r.name AS role_name')
                ->join('roles r', 'r.id = ur.role_id', 'inner')
                ->orderBy('r.name', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $userId = (int) $row['user_id'];
                if (! isset($userRoles[$userId])) {
                    $userRoles[$userId] = [];
                }
                $userRoles[$userId][] = (string) $row['role_name'];
            }
        }

        return view('portal/users/index', [
            'title'       => 'HJMS ERP | Users',
            'headerTitle' => 'User Management',
            'activePage'  => 'users',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $users,
            'roles'       => $roles,
            'userRoles'   => $userRoles,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add(): string
    {
        return view('portal/users/add', [
            'title'       => 'HJMS ERP | Add User',
            'headerTitle' => 'User Management',
            'activePage'  => 'users',
            'userEmail'   => (string) session('user_email'),
            'roles'       => $this->getActiveRoles(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $user = (new UserModel())
            ->select('id, name, email, is_active, created_at, updated_at')
            ->find($id);

        if (empty($user)) {
            return redirect()->to('/app/users')->with('error', 'User not found.');
        }

        return view('portal/users/edit', [
            'title'       => 'HJMS ERP | Edit User',
            'headerTitle' => 'User Management',
            'activePage'  => 'users',
            'userEmail'   => (string) session('user_email'),
            'row'         => $user,
            'roles'       => $this->getActiveRoles(),
            'userRoles'   => $this->getUserRoleNames((int) $user['id']),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createUser()
    {
        $payload = [
            'name'      => trim((string) $this->request->getPost('name')),
            'email'     => strtolower(trim((string) $this->request->getPost('email'))),
            'password'  => (string) $this->request->getPost('password'),
            'is_active' => (string) $this->request->getPost('is_active'),
            'role_id'   => (string) $this->request->getPost('role_id'),
        ];

        if ($payload['is_active'] === '') {
            $payload['is_active'] = '1';
        }

        if (! $this->validateData($payload, [
            'name'      => 'required|min_length[3]|max_length[120]',
            'email'     => 'required|valid_email|max_length[191]',
            'password'  => 'required|min_length[8]|max_length[255]',
            'is_active' => 'required|in_list[0,1]',
            'role_id'   => 'permit_empty|integer|greater_than[0]',
        ])) {
            return redirect()->to('/app/users/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $existing = $userModel->where('email', $payload['email'])->first();
        if ($existing !== null) {
            return redirect()->to('/app/users/add')->withInput()->with('error', 'Email is already in use.');
        }

        try {
            $userId = (int) $userModel->insert([
                'name'         => $payload['name'],
                'email'        => $payload['email'],
                'password_hash' => password_hash($payload['password'], PASSWORD_DEFAULT),
                'is_active'    => (int) $payload['is_active'],
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ], true);

            if ($payload['role_id'] !== '') {
                $this->assignRoleIfValid($userId, (int) $payload['role_id']);
            }

            return redirect()->to('/app/users')->with('success', 'User created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/users/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateUser()
    {
        $userId = (int) $this->request->getPost('user_id');
        $payload = [
            'name'      => trim((string) $this->request->getPost('name')),
            'email'     => strtolower(trim((string) $this->request->getPost('email'))),
            'password'  => (string) $this->request->getPost('password'),
            'is_active' => (string) $this->request->getPost('is_active'),
            'role_id'   => (string) $this->request->getPost('role_id'),
        ];

        if ($userId < 1) {
            return redirect()->to('/app/users')->withInput()->with('error', 'Valid user ID is required.');
        }

        $editUrl = '/app/users/' . $userId . '/edit';

        if (! $this->validateData($payload, [
            'name'      => 'permit_empty|min_length[3]|max_length[120]',
            'email'     => 'permit_empty|valid_email|max_length[191]',
            'password'  => 'permit_empty|min_length[8]|max_length[255]',
            'is_active' => 'permit_empty|in_list[0,1]',
            'role_id'   => 'permit_empty|integer|greater_than[0]',
        ])) {
            return redirect()->to($editUrl)->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $existingUser = $userModel->find($userId);
        if ($existingUser === null) {
            return redirect()->to('/app/users')->with('error', 'User not found.');
        }

        if ($payload['is_active'] === '0' && $userId === (int) session('user_id')) {
            return redirect()->to($editUrl)->withInput()->with('error', 'You cannot deactivate your own account.');
        }

        if ($payload['email'] !== '') {
            $sameEmailUser = $userModel->where('email', $payload['email'])->first();
            if ($sameEmailUser !== null && (int) $sameEmailUser['id'] !== $userId) {
                return redirect()->to($editUrl)->withInput()->with('error', 'Email is already in use.');
            }
        }

        $data = [];
        if ($payload['name'] !== '') {
            $data['name'] = $payload['name'];
        }
        if ($payload['email'] !== '') {
            $data['email'] = $payload['email'];
        }
        if ($payload['password'] !== '') {
            $data['password_hash'] = password_hash($payload['password'], PASSWORD_DEFAULT);
        }
        if ($payload['is_active'] !== '') {
            $data['is_active'] = (int) $payload['is_active'];
        }

        if ($data === [] && $payload['role_id'] === '') {
            return redirect()->to($editUrl)->withInput()->with('error', 'Provide at least one field to update.');
        }

        try {
            if ($data !== []) {
                $userModel->update($userId, $data + ['updated_at' => date('Y-m-d H:i:s')]);
            }

            if ($payload['role_id'] !== '') {
                $this->assignRoleIfValid($userId, (int) $payload['role_id']);
                if ($userId === (int) session('user_id')) {
                    service('authService')->refreshAuthorization($userId);
                }
            }

            return redirect()->to('/app/users')->with('success', 'User updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to($editUrl)->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteUser()
    {
        $userId = (int) $this->request->getPost('user_id');

        if ($userId < 1) {
            return redirect()->to('/app/users')->with('error', 'Valid user ID is required for delete.');
        }

        if ($userId === (int) session('user_id')) {
            return redirect()->to('/app/users')->with('error', 'You cannot delete your own account.');
        }

        $userModel = new UserModel();
        $existingUser = $userModel->find($userId);
        if ($existingUser === null) {
            return redirect()->to('/app/users')->with('error', 'User not found or already removed.');
        }

        try {
            $db = db_connect();
            if ($db->tableExists('user_roles')) {
                $db->table('user_roles')->where('user_id', $userId)->delete();
            }
            if ($db->tableExists('password_reset_tokens')) {
                $db->table('password_reset_tokens')->where('user_id', $userId)->delete();
            }

            $userModel->delete($userId);

            return redirect()->to('/app/users')->with('success', 'User deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/app/users')->with('error', $e->getMessage());
        }
    }

    private function assignRoleIfValid(int $userId, int $roleId): void
    {
        if ($roleId < 1) {
            return;
        }

        $db = db_connect();
        if (! $db->tableExists('roles') || ! $db->tableExists('user_roles')) {
            return;
        }

        $role = (new RoleModel())
            ->select('id')
            ->where('id', $roleId)
            ->where('is_active', 1)
            ->first();

        if ($role === null) {
            return;
        }

        $userRoleModel = new UserRoleModel();
        $alreadyMapped = $userRoleModel
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->first();

        if ($alreadyMapped === null) {
            $userRoleModel->insert([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function getActiveRoles(): array
    {
        $db = db_connect();
        if (! $db->tableExists('roles')) {
            return [];
        }

        return (new RoleModel())
            ->select('id, name')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    private function getUserRoleNames(int $userId): array
    {
        $db = db_connect();
        if (! $db->tableExists('user_roles') || ! $db->tableExists('roles')) {
            return [];
        }

        $rows = $db->table('user_roles ur')
            ->select('r.name')
            ->join('roles r', 'r.id = ur.role_id', 'inner')
            ->where('ur.user_id', $userId)
            ->orderBy('r.name', 'ASC')
            ->get()
            ->getResultArray();

        $roleNames = [];
        foreach ($rows as $row) {
            $roleNames[] = (string) $row['name'];
        }

        return $roleNames;
    }
}
