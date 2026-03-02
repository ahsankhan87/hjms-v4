<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMultilingualVoucherFieldsToMainCompany extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('main_company')) {
            return;
        }

        $columns = [
            'voucher_instructions_ur' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'voucher_instructions_en' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'makkah_contact_ur' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'makkah_contact_en' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'madina_contact_ur' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'madina_contact_en' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'transport_contact_ur' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'transport_contact_en' => [
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

        foreach (
            [
                'voucher_instructions_ur',
                'voucher_instructions_en',
                'makkah_contact_ur',
                'makkah_contact_en',
                'madina_contact_ur',
                'madina_contact_en',
                'transport_contact_ur',
                'transport_contact_en',
            ] as $column
        ) {
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
