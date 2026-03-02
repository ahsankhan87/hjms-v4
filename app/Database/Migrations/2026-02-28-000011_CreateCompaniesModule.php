<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompaniesModule extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('companies')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 160,
                ],
                'tagline' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'address' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'phone' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 60,
                    'null'       => true,
                ],
                'email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 160,
                    'null'       => true,
                ],
                'website' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 160,
                    'null'       => true,
                ],
                'saudi_partner' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('is_active');
            $this->forge->createTable('companies', true);
        }

        $now = date('Y-m-d H:i:s');
        $defaultCompany = $this->db->table('companies')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();
        if ($defaultCompany === null) {
            $this->db->table('companies')->insert([
                'name' => 'KARWAN-E-TAIF PVT LTD',
                'tagline' => 'Hajj & Umrah Management',
                'address' => 'Shop # B-7, B-8, Hanifullah Plaza Charsadda Road, Opposite Charsadda Bus Stand Peshawar',
                'saudi_partner' => 'AlAhela Establishment for Umrah Services',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->addPermissions();
    }

    public function down()
    {
        if ($this->tableIsReadable('permissions')) {
            $permissionRows = $this->db->table('permissions')
                ->select('id')
                ->whereIn('name', ['companies.view', 'companies.manage'])
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

        if ($this->tableIsReadable('companies')) {
            $this->forge->dropTable('companies', true);
        }
    }

    private function addPermissions(): void
    {
        if (! $this->tableIsReadable('permissions')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $hasModuleColumn = $this->permissionsTableHasModuleColumn();

        $permissions = [
            $this->buildPermissionRow('companies.view', 'View companies', 'companies', $hasModuleColumn),
            $this->buildPermissionRow('companies.manage', 'Manage companies', 'companies', $hasModuleColumn),
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
            ->whereIn('name', ['companies.view', 'companies.manage'])
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
