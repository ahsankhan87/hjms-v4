<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixSuperAdminOverAssignment extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('roles') || ! $this->tableIsReadable('user_roles') || ! $this->tableIsReadable('users')) {
            return;
        }

        $superAdmin = $this->db->table('roles')
            ->select('id')
            ->where('name', 'super_admin')
            ->get()
            ->getRowArray();

        if ($superAdmin === null) {
            return;
        }

        $roleId = (int) $superAdmin['id'];

        $keeperUser = $this->db->table('users')
            ->select('id')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        $keeperUserId = (int) ($keeperUser['id'] ?? 0);
        if ($keeperUserId < 1) {
            return;
        }

        $this->db->table('user_roles')
            ->where('role_id', $roleId)
            ->where('user_id !=', $keeperUserId)
            ->delete();

        $keeperMapping = $this->db->table('user_roles')
            ->select('id')
            ->where('role_id', $roleId)
            ->where('user_id', $keeperUserId)
            ->get()
            ->getRowArray();

        if ($keeperMapping === null) {
            $this->db->table('user_roles')->insert([
                'user_id'    => $keeperUserId,
                'role_id'    => $roleId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        // Intentionally no-op: cleanup migration should not re-add removed elevated mappings.
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
