<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDefaultShirkaToMainCompany extends Migration
{
    public function up(): void
    {
        if (! $this->tableIsReadable('main_company')) {
            return;
        }

        if (! $this->columnExists('main_company', 'default_shirka_company_id')) {
            $this->forge->addColumn('main_company', [
                'default_shirka_company_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'strn',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if (! $this->tableIsReadable('main_company')) {
            return;
        }

        if ($this->columnExists('main_company', 'default_shirka_company_id')) {
            $this->forge->dropColumn('main_company', 'default_shirka_company_id');
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
            return $query->getRowArray() !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
