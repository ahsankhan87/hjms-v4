<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgentClientsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('agent_clients')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'agent_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
            ],
            'passport_no' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('agent_id');
        $this->forge->addKey(['agent_id', 'name'], false, false, 'idx_agent_clients_agent_name');
        $this->forge->createTable('agent_clients', true);
    }

    public function down()
    {
        $this->forge->dropTable('agent_clients', true);
    }
}

