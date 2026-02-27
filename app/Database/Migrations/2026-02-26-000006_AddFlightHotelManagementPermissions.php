<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFlightHotelManagementPermissions extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('permissions')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $hasModuleColumn = $this->permissionsTableHasModuleColumn();

        $permissions = [
            $this->buildPermissionRow('flights.view', 'View flights', 'flights', $hasModuleColumn),
            $this->buildPermissionRow('flights.manage', 'Manage flights', 'flights', $hasModuleColumn),
            $this->buildPermissionRow('hotels.view', 'View hotels', 'hotels', $hasModuleColumn),
            $this->buildPermissionRow('hotels.manage', 'Manage hotels', 'hotels', $hasModuleColumn),
        ];

        foreach ($permissions as $permission) {
            $exists = $this->db->table('permissions')
                ->select('id')
                ->where('name', $permission['name'])
                ->get()
                ->getRowArray();

            if ($exists === null) {
                $this->db->table('permissions')->insert($permission + [
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
        $permissionRows = $this->db->table('permissions')
            ->select('id')
            ->whereIn('name', ['flights.view', 'flights.manage', 'hotels.view', 'hotels.manage'])
            ->get()
            ->getResultArray();

        foreach ($permissionRows as $permissionRow) {
            $permissionId = (int) $permissionRow['id'];
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
            ->whereIn('name', ['flights.view', 'flights.manage', 'hotels.view', 'hotels.manage'])
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

    private function buildPermissionRow(string $name, string $description, string $module, bool $hasModuleColumn): array
    {
        $row = [
            'name' => $name,
            'description' => $description,
        ];

        if ($hasModuleColumn) {
            $row['module'] = $module;
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
