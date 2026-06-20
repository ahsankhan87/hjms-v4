<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SplitManagePermissionsToCrud extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('permissions')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $hasModuleColumn = $this->permissionsTableHasModuleColumn();

        $modules = [
            'pilgrims',
            'packages',
            'bookings',
            'payments',
            'branches',
            'agents',
            'visas',
            'reports',
            'users',
            'transports',
            'flights',
            'hotels',
            'companies',
        ];

        foreach ($modules as $module) {
            // Keep/ensure view permission exists.
            $this->ensurePermission($module . '.view', $module, 'View ' . $module, $hasModuleColumn, $now);

            // New explicit CRUD permissions.
            $this->ensurePermission($module . '.create', $module, 'Create ' . $module, $hasModuleColumn, $now);
            $this->ensurePermission($module . '.edit', $module, 'Edit ' . $module, $hasModuleColumn, $now);
            $this->ensurePermission($module . '.delete', $module, 'Delete ' . $module, $hasModuleColumn, $now);

            // Backward-safe migration of existing role assignments from <module>.manage.
            $managePermission = $this->db->table('permissions')
                ->select('id')
                ->where('name', $module . '.manage')
                ->get()
                ->getRowArray();

            if ($managePermission === null || ! $this->tableIsReadable('role_permissions')) {
                continue;
            }

            $managePermissionId = (int) ($managePermission['id'] ?? 0);
            if ($managePermissionId < 1) {
                continue;
            }

            $roleRows = $this->db->table('role_permissions')
                ->select('role_id')
                ->where('permission_id', $managePermissionId)
                ->get()
                ->getResultArray();

            if ($roleRows === []) {
                continue;
            }

            $targetPermissionRows = $this->db->table('permissions')
                ->select('id')
                ->whereIn('name', [
                    $module . '.view',
                    $module . '.create',
                    $module . '.edit',
                    $module . '.delete',
                ])
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

                foreach ($targetPermissionIds as $permissionId) {
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
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('permissions')) {
            return;
        }

        $modules = [
            'pilgrims',
            'packages',
            'bookings',
            'payments',
            'branches',
            'agents',
            'visas',
            'reports',
            'users',
            'transports',
            'flights',
            'hotels',
            'companies',
        ];

        foreach ($modules as $module) {
            $permissionRows = $this->db->table('permissions')
                ->select('id')
                ->whereIn('name', [
                    $module . '.create',
                    $module . '.edit',
                    $module . '.delete',
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
    }

    private function ensurePermission(string $name, string $module, string $description, bool $hasModuleColumn, string $now): void
    {
        $exists = $this->db->table('permissions')
            ->select('id')
            ->where('name', $name)
            ->get()
            ->getRowArray();

        if ($exists !== null) {
            return;
        }

        $row = [
            'name' => $name,
            'description' => $description,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($hasModuleColumn) {
            $row['module'] = $module;
        }

        $this->db->table('permissions')->insert($row);
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
