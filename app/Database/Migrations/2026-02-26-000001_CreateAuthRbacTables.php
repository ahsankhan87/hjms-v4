<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthRbacTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_system' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('roles', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'module' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('permissions', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'role_id']);
        $this->forge->addKey('user_id');
        $this->forge->addKey('role_id');
        $this->forge->createTable('user_roles', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'permission_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['role_id', 'permission_id']);
        $this->forge->addKey('permission_id');
        $this->forge->createTable('role_permissions', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
            ],
            'token_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('token_hash');
        $this->forge->addKey('email');
        $this->forge->createTable('password_reset_tokens', true);

        $now = date('Y-m-d H:i:s');

        $permissions = [
            ['name' => 'dashboard.view', 'module' => 'dashboard', 'description' => 'View dashboard'],
            ['name' => 'pilgrims.view', 'module' => 'pilgrims', 'description' => 'View pilgrims'],
            ['name' => 'pilgrims.manage', 'module' => 'pilgrims', 'description' => 'Manage pilgrims'],
            ['name' => 'packages.view', 'module' => 'packages', 'description' => 'View packages'],
            ['name' => 'packages.manage', 'module' => 'packages', 'description' => 'Manage packages'],
            ['name' => 'bookings.view', 'module' => 'bookings', 'description' => 'View bookings'],
            ['name' => 'bookings.manage', 'module' => 'bookings', 'description' => 'Manage bookings'],
            ['name' => 'payments.view', 'module' => 'payments', 'description' => 'View payments'],
            ['name' => 'payments.manage', 'module' => 'payments', 'description' => 'Manage payments'],
            ['name' => 'branches.view', 'module' => 'branches', 'description' => 'View branches'],
            ['name' => 'branches.manage', 'module' => 'branches', 'description' => 'Manage branches'],
            ['name' => 'agents.view', 'module' => 'agents', 'description' => 'View agents'],
            ['name' => 'agents.manage', 'module' => 'agents', 'description' => 'Manage agents'],
            ['name' => 'visas.view', 'module' => 'visas', 'description' => 'View visas'],
            ['name' => 'visas.manage', 'module' => 'visas', 'description' => 'Manage visas'],
            ['name' => 'reports.view', 'module' => 'reports', 'description' => 'View reports'],
            ['name' => 'reports.manage', 'module' => 'reports', 'description' => 'Manage reports'],
            ['name' => 'rbac.manage', 'module' => 'rbac', 'description' => 'Manage roles and permissions'],
        ];

        foreach ($permissions as $permission) {
            $exists = $this->db->table('permissions')
                ->select('id')
                ->where('name', $permission['name'])
                ->get()
                ->getRowArray();

            if ($exists === null) {
                try {
                    $this->db->table('permissions')->insert($permission + ['created_at' => $now, 'updated_at' => $now]);
                } catch (\Throwable $e) {
                    $fallback = $permission;
                    unset($fallback['module']);
                    $this->db->table('permissions')->insert($fallback + ['created_at' => $now, 'updated_at' => $now]);
                }
            }
        }

        $users = $this->db->table('users')->select('id')->get()->getResultArray();
        $permissionsRows = $this->db->table('permissions')->select('id')->get()->getResultArray();

        $roleRow = $this->db->table('roles')
            ->select('id')
            ->where('name', 'super_admin')
            ->get()
            ->getRowArray();

        if ($roleRow === null) {
            $insertRolePayload = [
                'name'        => 'super_admin',
                'description' => 'Full system access',
                'is_system'   => 1,
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            $this->db->table('roles')->insert($insertRolePayload);
            $roleRow = $this->db->table('roles')
                ->select('id')
                ->where('name', 'super_admin')
                ->get()
                ->getRowArray();
        }

        $roleId = (int) ($roleRow['id'] ?? 0);

        foreach ($users as $user) {
            $mappingExists = $this->db->table('user_roles')
                ->select('id')
                ->where('user_id', (int) $user['id'])
                ->where('role_id', $roleId)
                ->get()
                ->getRowArray();

            if ($mappingExists === null) {
                $this->db->table('user_roles')->insert([
                    'user_id'    => (int) $user['id'],
                    'role_id'    => $roleId,
                    'created_at' => $now,
                ]);
            }
        }

        foreach ($permissionsRows as $permissionRow) {
            $rpExists = $this->db->table('role_permissions')
                ->select('id')
                ->where('role_id', $roleId)
                ->where('permission_id', (int) $permissionRow['id'])
                ->get()
                ->getRowArray();

            if ($rpExists === null) {
                $this->db->table('role_permissions')->insert([
                    'role_id'       => $roleId,
                    'permission_id' => (int) $permissionRow['id'],
                    'created_at'    => $now,
                ]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('password_reset_tokens', true);
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
    }
}
