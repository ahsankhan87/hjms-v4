<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesModuleTables extends Migration
{
    public function up()
    {
        if (! $this->tableExists('sales_categories')) {
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
            $this->forge->createTable('sales_categories', true);
        }

        if (! $this->tableExists('sales')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'season_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'sales_category_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'sale_date' => ['type' => 'DATE'],
                'customer_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'walk_in'],
                'agent_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'customer_name' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
                'amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => '0.00'],
                'paid_amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => '0.00'],
                'payment_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'unpaid'],
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
            $this->forge->addKey('sales_category_id');
            $this->forge->addKey('sale_date');
            $this->forge->addKey('status');
            $this->forge->addKey('payment_status');
            $this->forge->addKey('agent_id');
            $this->forge->addForeignKey('sales_category_id', 'sales_categories', 'id', 'RESTRICT', 'CASCADE');
            $this->forge->createTable('sales', true);
        } else {
            $this->ensureSalesColumns();
        }
    }

    public function down()
    {
        if ($this->tableExists('sales')) {
            $this->forge->dropTable('sales', true);
        }

        if ($this->tableExists('sales_categories')) {
            $this->forge->dropTable('sales_categories', true);
        }
    }

    private function ensureSalesColumns(): void
    {
        $columns = [
            'season_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'sales_category_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'sale_date' => ['type' => 'DATE', 'null' => true],
            'customer_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'walk_in'],
            'agent_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'customer_name' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'paid_amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => '0.00'],
            'payment_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'unpaid'],
            'payment_method' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'reference_no' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'note' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'posted'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ];

        foreach ($columns as $columnName => $definition) {
            if (! $this->fieldExists($columnName, 'sales')) {
                $this->forge->addColumn('sales', [$columnName => $definition]);
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
