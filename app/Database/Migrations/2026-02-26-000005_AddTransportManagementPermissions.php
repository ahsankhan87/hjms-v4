<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransportManagementPermissions extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('permissions')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $hasModuleColumn = $this->permissionsTableHasModuleColumn();

        $permissionRows = [
            $this->buildPermissionRow('transports.view', 'View transports', $hasModuleColumn),
            $this->buildPermissionRow('transports.manage', 'Manage transports', $hasModuleColumn),
        ];

        foreach ($permissionRows as $permissionRow) {
            $exists = $this->db->table('permissions')
                ->select('id')
                ->where('name', $permissionRow['name'])
                ->get()
                ->getRowArray();

            if ($exists === null) {
                $this->db->table('permissions')->insert($permissionRow + [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (! $this->tableIsReadable('roles') || ! $this->tableIsReadable('role_permissions')) {
            return;
        }

        $superAdminRole = $this->db->table('roles')
            ->select('id')
            ->where('name', 'super_admin')
            ->get()
            ->getRowArray();

        if ($superAdminRole === null) {
            return;
        }

        $roleId = (int) $superAdminRole['id'];
        $permissions = $this->db->table('permissions')
            ->select('id')
            ->whereIn('name', ['transports.view', 'transports.manage'])
            ->get()
            ->getResultArray();

        foreach ($permissions as $permission) {
            $permissionId = (int) $permission['id'];
            $existing = $this->db->table('role_permissions')
                ->select('id')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->get()
                ->getRowArray();

            if ($existing === null) {
                $this->db->table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('permissions')) {
            return;
        }

        $permissionRows = $this->db->table('permissions')
            ->select('id')
            ->whereIn('name', ['transports.view', 'transports.manage'])
            ->get()
            ->getResultArray();

        foreach ($permissionRows as $permissionRow) {
            $permissionId = (int) $permissionRow['id'];

            if ($this->tableIsReadable('role_permissions')) {
                $this->db->table('role_permissions')->where('permission_id', $permissionId)->delete();
            }

            $this->db->table('permissions')->where('id', $permissionId)->delete();
        }
    }

    private function buildPermissionRow(string $name, string $description, bool $hasModuleColumn): array
    {
        $row = [
            'name' => $name,
            'description' => $description,
        ];

        if ($hasModuleColumn) {
            $row['module'] = 'transports';
        }

        return $row;
    }

    private function tableIsReadable(string $table): bool
    {
        try {
            $this->db->query('SELECT 1 FROM ' . $table . ' LIMIT 1');

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function permissionsTableHasModuleColumn(): bool
    {
        try {
            $this->db->query('SELECT module FROM permissions LIMIT 1');

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
