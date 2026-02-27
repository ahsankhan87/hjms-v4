<?php

namespace App\Controllers;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;

class RbacController extends BaseController
{
    public function index()
    {
        return view('portal/rbac/index', $this->baseViewData([
            'title'           => 'HJMS ERP | RBAC',
            'headerTitle'     => 'Role & Permission Management',
            'activeSubPage'   => 'home',
        ]));
    }

    public function roles()
    {
        return view('portal/rbac/roles', $this->baseViewData([
            'title'       => 'HJMS ERP | RBAC Roles',
            'headerTitle' => 'Role Management',
            'activeSubPage' => 'roles',
        ]));
    }

    public function permissions()
    {
        return view('portal/rbac/permissions', $this->baseViewData([
            'title'       => 'HJMS ERP | RBAC Permissions',
            'headerTitle' => 'Permission Management',
            'activeSubPage' => 'permissions',
        ]));
    }

    public function assign()
    {
        return view('portal/rbac/assign', $this->baseViewData([
            'title'       => 'HJMS ERP | RBAC Assignments',
            'headerTitle' => 'Role Assignments',
            'activeSubPage' => 'assign',
        ]));
    }

    public function createRole()
    {
        $payload = [
            'name'        => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')),
        ];

        if (! $this->validateData($payload, [
            'name'        => 'required|alpha_dash|min_length[3]|max_length[50]',
            'description' => 'permit_empty|max_length[255]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new RoleModel();
        $exists = $model->where('name', $payload['name'])->first();
        if ($exists !== null) {
            return redirect()->to('/app/rbac/roles')->with('error', 'Role already exists.');
        }

        $model->insert([
            'name'        => $payload['name'],
            'description' => $payload['description'] !== '' ? $payload['description'] : null,
            'is_system'   => 0,
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/app/rbac/roles')->with('success', 'Role created.');
    }

    public function createPermission()
    {
        $payload = [
            'name'        => trim((string) $this->request->getPost('name')),
            'module'      => trim((string) $this->request->getPost('module')),
            'description' => trim((string) $this->request->getPost('description')),
        ];

        if (! $this->validateData($payload, [
            'name'        => 'required|alpha_dash|min_length[3]|max_length[100]',
            'module'      => 'permit_empty|max_length[80]',
            'description' => 'permit_empty|max_length[255]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new PermissionModel();
        $exists = $model->where('name', $payload['name'])->first();
        if ($exists !== null) {
            return redirect()->to('/app/rbac/permissions')->with('error', 'Permission already exists.');
        }

        $model->insert([
            'name'        => $payload['name'],
            'module'      => $payload['module'] !== '' ? $payload['module'] : null,
            'description' => $payload['description'] !== '' ? $payload['description'] : null,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/app/rbac/permissions')->with('success', 'Permission created.');
    }

    public function syncRolePermissions()
    {
        $roleId = (int) $this->request->getPost('role_id');
        $permissionIds = $this->request->getPost('permission_ids');
        $permissionIds = is_array($permissionIds) ? array_map('intval', $permissionIds) : [];

        if ($roleId < 1) {
            return redirect()->to('/app/rbac/permissions')->with('error', 'Valid role is required.');
        }

        $role = (new RoleModel())->where('id', $roleId)->first();
        if ($role === null) {
            return redirect()->to('/app/rbac/permissions')->with('error', 'Role not found.');
        }

        $rpModel = new RolePermissionModel();
        $rpModel->where('role_id', $roleId)->delete();

        $now = date('Y-m-d H:i:s');
        foreach ($permissionIds as $permissionId) {
            if ($permissionId > 0) {
                $rpModel->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                    'created_at'    => $now,
                ]);
            }
        }

        return redirect()->to('/app/rbac/permissions')->with('success', 'Role permissions updated.');
    }

    public function assignUserRole()
    {
        $userId = (int) $this->request->getPost('user_id');
        $roleId = (int) $this->request->getPost('role_id');

        if ($userId < 1 || $roleId < 1) {
            return redirect()->to('/app/rbac/assign')->with('error', 'User and role are required.');
        }

        $role = (new RoleModel())->where('id', $roleId)->first();
        $user = (new UserModel())->where('id', $userId)->first();
        if ($role === null || $user === null) {
            return redirect()->to('/app/rbac/assign')->with('error', 'Invalid user or role.');
        }

        $urModel = new UserRoleModel();
        $exists = $urModel
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->first();

        if ($exists === null) {
            $urModel->insert([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ((int) session('user_id') === $userId) {
            service('authService')->refreshAuthorization($userId);
        }

        return redirect()->to('/app/rbac/assign')->with('success', 'Role assigned to user.');
    }

    private function baseViewData(array $extra = []): array
    {
        $roles = (new RoleModel())
            ->orderBy('name', 'ASC')
            ->findAll();

        $permissions = (new PermissionModel())
            ->orderBy('module', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $users = (new UserModel())
            ->select('id, name, email, is_active')
            ->orderBy('name', 'ASC')
            ->findAll();

        $rolePermissionRows = db_connect()->table('role_permissions rp')
            ->select('rp.role_id, p.name AS permission_name')
            ->join('permissions p', 'p.id = rp.permission_id', 'inner')
            ->get()
            ->getResultArray();

        $rolePermissions = [];
        foreach ($rolePermissionRows as $row) {
            $roleId = (int) $row['role_id'];
            if (! isset($rolePermissions[$roleId])) {
                $rolePermissions[$roleId] = [];
            }
            $rolePermissions[$roleId][] = (string) $row['permission_name'];
        }

        $userRoleRows = db_connect()->table('user_roles ur')
            ->select('ur.user_id, r.name AS role_name')
            ->join('roles r', 'r.id = ur.role_id', 'inner')
            ->get()
            ->getResultArray();

        $userRoles = [];
        foreach ($userRoleRows as $row) {
            $userId = (int) $row['user_id'];
            if (! isset($userRoles[$userId])) {
                $userRoles[$userId] = [];
            }
            $userRoles[$userId][] = (string) $row['role_name'];
        }

        return array_merge([
            'activePage'      => 'rbac',
            'userEmail'       => (string) session('user_email'),
            'roles'           => $roles,
            'permissions'     => $permissions,
            'users'           => $users,
            'rolePermissions' => $rolePermissions,
            'userRoles'       => $userRoles,
            'success'         => session()->getFlashdata('success'),
            'error'           => session()->getFlashdata('error'),
            'errors'          => session()->getFlashdata('errors') ?: [],
        ], $extra);
    }
}
