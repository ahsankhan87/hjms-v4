<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTicketFieldsToSales extends Migration
{
    public function up()
    {
        if (! $this->tableExists('sales')) {
            return;
        }

        $columns = [
            'pnr' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'room_qty',
            ],
            'sector' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
                'after' => 'pnr',
            ],
            'ticket_no' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
                'after' => 'sector',
            ],
        ];

        foreach ($columns as $columnName => $definition) {
            if (! $this->fieldExists($columnName, 'sales')) {
                $this->forge->addColumn('sales', [$columnName => $definition]);
            }
        }
    }

    public function down()
    {
        if (! $this->tableExists('sales')) {
            return;
        }

        foreach (['ticket_no', 'sector', 'pnr'] as $columnName) {
            if ($this->fieldExists($columnName, 'sales')) {
                $this->forge->dropColumn('sales', $columnName);
            }
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

    private function fieldExists(string $field, string $table): bool
    {
        try {
            $query = $this->db->query("SHOW COLUMNS FROM " . $table . " LIKE '" . str_replace("'", "''", $field) . "'");
            return is_object($query) && $query->getRowArray() !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
