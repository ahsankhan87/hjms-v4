<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExpenseCrudPermissions extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('permissions')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $hasModuleColumn = $this->permissionsTableHasModuleColumn();

        foreach (
            [
                'expenses.view' => 'View expenses',
                'expenses.manage' => 'Manage expenses',
            ] as $name => $description
        ) {
            $exists = $this->db->table('permissions')->select('id')->where('name', $name)->get()->getRowArray();
            if ($exists !== null) {
                continue;
            }

            $row = [
                'name' => $name,
                'description' => $description,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($hasModuleColumn) {
                $row['module'] = 'expenses';
            }

            $this->db->table('permissions')->insert($row);
        }

        if (! $this->tableIsReadable('roles') || ! $this->tableIsReadable('role_permissions')) {
            return;
        }

        $superAdminRole = $this->db->table('roles')->select('id')->where('name', 'super_admin')->get()->getRowArray();
        if ($superAdminRole === null) {
            return;
        }

        $roleId = (int) $superAdminRole['id'];
        $permissionRows = $this->db->table('permissions')->select('id')->whereIn('name', ['expenses.view', 'expenses.manage'])->get()->getResultArray();

        foreach ($permissionRows as $permissionRow) {
            $permissionId = (int) ($permissionRow['id'] ?? 0);
            if ($permissionId < 1) {
                continue;
            }

            $existing = $this->db->table('role_permissions')->select('id')->where('role_id', $roleId)->where('permission_id', $permissionId)->get()->getRowArray();
            if ($existing === null) {
                $this->db->table('role_permissions')->insert(['role_id' => $roleId, 'permission_id' => $permissionId, 'created_at' => $now]);
            }
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('permissions')) {
            return;
        }

        $permissionRows = $this->db->table('permissions')->select('id')->whereIn('name', ['expenses.view', 'expenses.manage'])->get()->getResultArray();
        foreach ($permissionRows as $permissionRow) {
            $permissionId = (int) ($permissionRow['id'] ?? 0);
            if ($permissionId < 1) {
                continue;
            }

            if ($this->tableIsReadable('role_permissions')) {
                $this->db->table('role_permissions')->where('permission_id', $permissionId)->delete();
            }

            $this->db->table('permissions')->where('id', $permissionId)->delete();
        }
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
