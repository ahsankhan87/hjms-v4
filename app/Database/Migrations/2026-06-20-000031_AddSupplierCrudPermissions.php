<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSupplierCrudPermissions extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('permissions')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $hasModuleColumn = $this->permissionsTableHasModuleColumn();

        $permissionNames = [
            'suppliers.view' => 'View suppliers',
            'suppliers.create' => 'Create suppliers',
            'suppliers.edit' => 'Edit suppliers',
            'suppliers.delete' => 'Delete suppliers',
        ];

        foreach ($permissionNames as $name => $description) {
            $exists = $this->db->table('permissions')
                ->select('id')
                ->where('name', $name)
                ->get()
                ->getRowArray();

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
                $row['module'] = 'suppliers';
            }

            $this->db->table('permissions')->insert($row);
        }

        if (! $this->tableIsReadable('role_permissions')) {
            return;
        }

        // Backfill role mappings from existing payment permissions used by supplier routes.
        $sourceMap = [
            'payments.view' => ['suppliers.view'],
            'payments.create' => ['suppliers.create'],
            'payments.edit' => ['suppliers.edit'],
            'payments.delete' => ['suppliers.delete'],
            'payments.manage' => ['suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete'],
        ];

        foreach ($sourceMap as $sourcePermissionName => $targetPermissionNames) {
            $sourcePermission = $this->db->table('permissions')
                ->select('id')
                ->where('name', $sourcePermissionName)
                ->get()
                ->getRowArray();

            if ($sourcePermission === null) {
                continue;
            }

            $sourcePermissionId = (int) ($sourcePermission['id'] ?? 0);
            if ($sourcePermissionId < 1) {
                continue;
            }

            $roleRows = $this->db->table('role_permissions')
                ->select('role_id')
                ->where('permission_id', $sourcePermissionId)
                ->get()
                ->getResultArray();

            if ($roleRows === []) {
                continue;
            }

            $targetPermissionRows = $this->db->table('permissions')
                ->select('id')
                ->whereIn('name', $targetPermissionNames)
                ->get()
                ->getResultArray();

            $targetPermissionIds = array_values(array_filter(array_map(static function (array $row): int {
                return (int) ($row['id'] ?? 0);
            }, $targetPermissionRows)));

            foreach ($roleRows as $roleRow) {
                $roleId = (int) ($roleRow['role_id'] ?? 0);
                if ($roleId < 1) {
                    continue;
                }

                foreach ($targetPermissionIds as $targetPermissionId) {
                    $exists = $this->db->table('role_permissions')
                        ->select('id')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $targetPermissionId)
                        ->get()
                        ->getRowArray();

                    if ($exists === null) {
                        $this->db->table('role_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $targetPermissionId,
                            'created_at' => $now,
                        ]);
                    }
                }
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
            ->whereIn('name', [
                'suppliers.view',
                'suppliers.create',
                'suppliers.edit',
                'suppliers.delete',
            ])
            ->get()
            ->getResultArray();

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
