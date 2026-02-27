<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveVisaFieldsFromPilgrims extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('pilgrims')) {
            return;
        }

        foreach (['visa_no', 'visa_status', 'visa_type'] as $column) {
            if ($this->columnExists('pilgrims', $column)) {
                $this->forge->dropColumn('pilgrims', $column);
            }
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('pilgrims')) {
            return;
        }

        if (! $this->columnExists('pilgrims', 'visa_no')) {
            $this->forge->addColumn('pilgrims', [
                'visa_no' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            ]);
        }

        if (! $this->columnExists('pilgrims', 'visa_status')) {
            $this->forge->addColumn('pilgrims', [
                'visa_status' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            ]);
        }

        if (! $this->columnExists('pilgrims', 'visa_type')) {
            $this->forge->addColumn('pilgrims', [
                'visa_type' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            ]);
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

    private function columnExists(string $table, string $column): bool
    {
        try {
            $query = $this->db->query('SHOW COLUMNS FROM ' . $table . ' LIKE ?', [$column]);
            $row = $query->getRowArray();

            return $row !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
