<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogsModule extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('audit_logs')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'constraint'     => 20,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'user_email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                    'null'       => true,
                ],
                'http_method' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                ],
                'request_path' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'action_label' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                ],
                'status_code' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned'   => true,
                    'default'    => 200,
                ],
                'ip_address' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 45,
                    'null'       => true,
                ],
                'user_agent' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'payload_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id');
            $this->forge->addKey('http_method');
            $this->forge->addKey('request_path');
            $this->forge->addKey('created_at');
            $this->forge->createTable('audit_logs', true);
        }

        if (! $this->tableIsReadable('permissions')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $permissionName = 'audit.view';
        $permission = $this->db->table('permissions')
            ->select('id')
            ->where('name', $permissionName)
            ->get()
            ->getRowArray();

        if ($permission === null) {
            $insertPayload = [
                'name'        => $permissionName,
                'description' => 'View audit logs',
                'module'      => 'audit',
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            try {
                $this->db->table('permissions')->insert($insertPayload);
            } catch (\Throwable $e) {
                unset($insertPayload['module']);
                $this->db->table('permissions')->insert($insertPayload);
            }

            $permission = $this->db->table('permissions')
                ->select('id')
                ->where('name', $permissionName)
                ->get()
                ->getRowArray();
        }

        $permissionId = (int) ($permission['id'] ?? 0);
        if ($permissionId < 1 || ! $this->tableIsReadable('roles') || ! $this->tableIsReadable('role_permissions')) {
            return;
        }

        $superAdmin = $this->db->table('roles')
            ->select('id')
            ->where('name', 'super_admin')
            ->get()
            ->getRowArray();

        $roleId = (int) ($superAdmin['id'] ?? 0);
        if ($roleId < 1) {
            return;
        }

        $mapping = $this->db->table('role_permissions')
            ->select('id')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->get()
            ->getRowArray();

        if ($mapping === null) {
            $this->db->table('role_permissions')->insert([
                'role_id'       => $roleId,
                'permission_id' => $permissionId,
                'created_at'    => $now,
            ]);
        }
    }

    public function down()
    {
        if ($this->tableIsReadable('permissions')) {
            $permission = $this->db->table('permissions')
                ->select('id')
                ->where('name', 'audit.view')
                ->get()
                ->getRowArray();

            if ($permission !== null && $this->tableIsReadable('role_permissions')) {
                $this->db->table('role_permissions')
                    ->where('permission_id', (int) $permission['id'])
                    ->delete();
            }

            if ($permission !== null) {
                $this->db->table('permissions')
                    ->where('id', (int) $permission['id'])
                    ->delete();
            }
        }

        if ($this->tableIsReadable('audit_logs')) {
            $this->forge->dropTable('audit_logs', true);
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
}
