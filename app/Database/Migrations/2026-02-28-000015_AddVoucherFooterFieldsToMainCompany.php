<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVoucherFooterFieldsToMainCompany extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('main_company')) {
            return;
        }

        $columns = [
            'voucher_instructions' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'makkah_contact' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'madina_contact' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'transport_contact' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ];

        foreach ($columns as $name => $definition) {
            if (! $this->columnExists('main_company', $name)) {
                $this->forge->addColumn('main_company', [$name => $definition]);
            }
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('main_company')) {
            return;
        }

        foreach (['voucher_instructions', 'makkah_contact', 'madina_contact', 'transport_contact'] as $column) {
            if ($this->columnExists('main_company', $column)) {
                $this->forge->dropColumn('main_company', $column);
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
