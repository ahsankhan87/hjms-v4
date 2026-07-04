<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCreditNotesTable extends Migration
{
    public function up()
    {
        if ($this->tableExists('credit_notes')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'season_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'sale_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'visa',
            ],
            'sale_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'agent_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'invoice_no' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
            ],
            'sale_date' => [
                'type' => 'DATE',
            ],
            'credit_note_date' => [
                'type' => 'DATE',
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => 0,
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'posted',
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
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
        $this->forge->addKey(['season_id', 'sale_type', 'credit_note_date']);
        $this->forge->addKey(['sale_id', 'sale_type']);
        $this->forge->addUniqueKey(['season_id', 'invoice_no']);
        $this->forge->createTable('credit_notes', true);
    }

    public function down()
    {
        if ($this->tableExists('credit_notes')) {
            $this->forge->dropTable('credit_notes', true);
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
