<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSupplierCategoriesAndLedgerCategory extends Migration
{
    public function up()
    {
        if (! $this->tableExists('supplier_categories')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 160],
                'description' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('name');
            $this->forge->createTable('supplier_categories', true);
        }

        $this->seedDefaultCategories();

        if ($this->tableExists('supplier_ledger_entries') && ! $this->fieldExists('category_id', 'supplier_ledger_entries')) {
            $this->forge->addColumn('supplier_ledger_entries', [
                'category_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'entry_type',
                    'comment' => 'Supplier category (supplier_categories.id)',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->tableExists('supplier_ledger_entries') && $this->fieldExists('category_id', 'supplier_ledger_entries')) {
            $this->forge->dropColumn('supplier_ledger_entries', 'category_id');
        }

        if ($this->tableExists('supplier_categories')) {
            $this->forge->dropTable('supplier_categories', true);
        }
    }

    private function seedDefaultCategories(): void
    {
        if (! $this->tableExists('supplier_categories')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach (['Visa', 'Transport', 'Hotel', 'Ticket', 'Other'] as $name) {
            try {
                $exists = $this->db->table('supplier_categories')->where('name', $name)->countAllResults();
            } catch (\Throwable $e) {
                return;
            }

            if ($exists > 0) {
                continue;
            }

            $this->db->table('supplier_categories')->insert([
                'name' => $name,
                'description' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            return $this->db->tableExists($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function fieldExists(string $field, string $table): bool
    {
        try {
            return $this->db->fieldExists($field, $table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
