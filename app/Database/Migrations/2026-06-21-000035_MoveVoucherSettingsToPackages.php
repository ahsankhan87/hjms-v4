<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MoveVoucherSettingsToPackages extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('packages')) {
            return;
        }

        $packageColumns = [
            'default_shirka_company_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'voucher_instructions_ur' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'voucher_instructions_en' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'makkah_contact' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'makkah_contact_en' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'madina_contact' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'madina_contact_en' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'transport_contact' => [
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

        foreach ($packageColumns as $name => $definition) {
            if (! $this->columnExists('packages', $name)) {
                $this->forge->addColumn('packages', [$name => $definition]);
            }
        }

        $this->copyMainCompanyValuesToPackages(array_keys($packageColumns));

        if (! $this->tableIsReadable('main_company')) {
            return;
        }

        foreach (array_keys($packageColumns) as $column) {
            if ($this->columnExists('main_company', $column)) {
                $this->forge->dropColumn('main_company', $column);
            }
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('main_company')) {
            return;
        }

        $mainCompanyColumns = [
            'default_shirka_company_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'voucher_instructions_ur' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'voucher_instructions_en' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'makkah_contact' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'makkah_contact_en' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'madina_contact' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'madina_contact_en' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'transport_contact' => [
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

        foreach ($mainCompanyColumns as $name => $definition) {
            if (! $this->columnExists('main_company', $name)) {
                $this->forge->addColumn('main_company', [$name => $definition]);
            }
        }

        $this->copyPackageValuesToMainCompany(array_keys($mainCompanyColumns));

        if (! $this->tableIsReadable('packages')) {
            return;
        }

        foreach (array_keys($mainCompanyColumns) as $column) {
            if ($this->columnExists('packages', $column)) {
                $this->forge->dropColumn('packages', $column);
            }
        }
    }

    private function copyMainCompanyValuesToPackages(array $columns): void
    {
        if (! $this->tableIsReadable('main_company')) {
            return;
        }

        $selectColumns = [];
        foreach ($columns as $column) {
            if ($this->columnExists('main_company', $column)) {
                $selectColumns[] = $column;
            }
        }

        if ($selectColumns === []) {
            return;
        }

        $mainCompany = $this->db->table('main_company')
            ->select(implode(', ', $selectColumns))
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (empty($mainCompany)) {
            return;
        }

        $this->db->table('packages')->update($mainCompany);
    }

    private function copyPackageValuesToMainCompany(array $columns): void
    {
        if (! $this->tableIsReadable('packages')) {
            return;
        }

        $selectColumns = [];
        foreach ($columns as $column) {
            if ($this->columnExists('packages', $column)) {
                $selectColumns[] = $column;
            }
        }

        if ($selectColumns === []) {
            return;
        }

        $package = $this->db->table('packages')
            ->select(implode(', ', $selectColumns))
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (empty($package)) {
            return;
        }

        $mainCompanyRow = $this->db->table('main_company')
            ->select('id')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        if (! empty($mainCompanyRow)) {
            $this->db->table('main_company')->where('id', (int) $mainCompanyRow['id'])->update($package);
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
