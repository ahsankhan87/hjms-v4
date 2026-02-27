<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVisaNoToVisas extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('visas')) {
            return;
        }

        if (! $this->columnExists('visas', 'visa_no')) {
            $this->forge->addColumn('visas', [
                'visa_no' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 60,
                    'null'       => true,
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('visas')) {
            return;
        }

        if ($this->columnExists('visas', 'visa_no')) {
            $this->forge->dropColumn('visas', 'visa_no');
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
