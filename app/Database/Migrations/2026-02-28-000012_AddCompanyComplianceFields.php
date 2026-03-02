<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyComplianceFields extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('companies')) {
            return;
        }

        $columns = [];
        if (! $this->columnExists('companies', 'logo_url')) {
            $columns['logo_url'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'website',
            ];
        }

        if (! $this->columnExists('companies', 'ntn')) {
            $columns['ntn'] = [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
                'after'      => 'logo_url',
            ];
        }

        if (! $this->columnExists('companies', 'strn')) {
            $columns['strn'] = [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
                'after'      => 'ntn',
            ];
        }

        if ($columns !== []) {
            $this->forge->addColumn('companies', $columns);
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('companies')) {
            return;
        }

        foreach (['strn', 'ntn', 'logo_url'] as $column) {
            if ($this->columnExists('companies', $column)) {
                $this->forge->dropColumn('companies', $column);
            }
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
