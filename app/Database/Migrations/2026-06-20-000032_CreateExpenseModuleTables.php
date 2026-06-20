<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpenseModuleTables extends Migration
{
    public function up()
    {
        if (! $this->tableExists('expense_categories')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'season_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 160],
                'description' => ['type' => 'TEXT', 'null' => true],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('season_id');
            $this->forge->addUniqueKey(['season_id', 'name']);
            $this->forge->createTable('expense_categories', true);
        }

        if (! $this->tableExists('expenses')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'season_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'expense_category_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'expense_date' => ['type' => 'DATE'],
                'amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => '0.00'],
                'paid_to' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
                'payment_method' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
                'reference_no' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'note' => ['type' => 'TEXT', 'null' => true],
                'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'posted'],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('season_id');
            $this->forge->addKey('expense_category_id');
            $this->forge->addKey('expense_date');
            $this->forge->addKey('status');
            $this->forge->addForeignKey('expense_category_id', 'expense_categories', 'id', 'RESTRICT', 'CASCADE');
            $this->forge->createTable('expenses', true);
        } else {
            $this->ensureExpenseColumns();
        }
    }

    public function down()
    {
        if ($this->tableExists('expenses')) {
            $this->forge->dropTable('expenses', true);
        }

        if ($this->tableExists('expense_categories')) {
            $this->forge->dropTable('expense_categories', true);
        }
    }

    private function ensureExpenseColumns(): void
    {
        $columns = [
            'season_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'expense_category_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'expense_date' => ['type' => 'DATE', 'null' => true],
            'paid_to' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'payment_method' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'reference_no' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'note' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'posted'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ];

        foreach ($columns as $columnName => $definition) {
            if (! $this->fieldExists($columnName, 'expenses')) {
                $this->forge->addColumn('expenses', [$columnName => $definition]);
            }
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
