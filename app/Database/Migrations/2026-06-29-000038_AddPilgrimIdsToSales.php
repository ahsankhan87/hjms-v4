<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPilgrimIdsToSales extends Migration
{
    public function up()
    {
        if (! $this->tableExists('sales')) {
            return;
        }

        if (! $this->fieldExists('pilgrim_ids', 'sales')) {
            $this->forge->addColumn('sales', [
                'pilgrim_ids' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'pilgrim_id',
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->tableExists('sales')) {
            return;
        }

        if ($this->fieldExists('pilgrim_ids', 'sales')) {
            $this->forge->dropColumn('sales', 'pilgrim_ids');
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
