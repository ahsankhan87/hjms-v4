<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgentLedgerEntries extends Migration
{
    public function up()
    {
        if ($this->tableExists('agent_ledger_entries') || $this->tableExists('agent_ledger')) {
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
            'entry_date' => [
                'type' => 'DATE',
            ],
            'entry_type' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'debit_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => 0,
            ],
            'credit_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => 0,
            ],
            'reference_type' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
            ],
            'reference_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['agent_id', 'entry_date']);
        $this->forge->addKey(['reference_type', 'reference_id']);
        $this->forge->createTable('agent_ledger_entries', true);
    }

    public function down()
    {
        if ($this->tableExists('agent_ledger_entries')) {
            $this->forge->dropTable('agent_ledger_entries', true);
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            $this->db->query('SELECT 1 FROM ' . $table . ' LIMIT 1');

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
