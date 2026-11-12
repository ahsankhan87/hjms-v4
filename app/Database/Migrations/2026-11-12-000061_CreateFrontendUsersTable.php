<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFrontendUsersTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('frontend_users')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'company_name' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
            ],
            'license_number' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email', 'uq_frontend_users_email');
        $this->forge->addKey(['role', 'status'], false, false, 'idx_frontend_users_role_status');
        $this->forge->createTable('frontend_users', true);
    }

    public function down()
    {
        $this->forge->dropTable('frontend_users', true);
    }
}

