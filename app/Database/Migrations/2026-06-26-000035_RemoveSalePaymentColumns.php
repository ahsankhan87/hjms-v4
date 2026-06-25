<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveSalePaymentColumns extends Migration
{
    public function up()
    {
        if (! $this->tableExists('sales')) {
            return;
        }

        if ($this->fieldExists('paid_amount', 'sales')) {
            $this->forge->dropColumn('sales', 'paid_amount');
        }

        if ($this->fieldExists('payment_status', 'sales')) {
            $this->forge->dropColumn('sales', 'payment_status');
        }
    }

    public function down()
    {
        if (! $this->tableExists('sales')) {
            return;
        }

        if (! $this->fieldExists('paid_amount', 'sales')) {
            $this->forge->addColumn('sales', [
                'paid_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,2',
                    'null' => false,
                    'default' => 0,
                    'after' => 'amount',
                ],
            ]);
        }

        if (! $this->fieldExists('payment_status', 'sales')) {
            $this->forge->addColumn('sales', [
                'payment_status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => false,
                    'default' => 'unpaid',
                    'after' => 'paid_amount',
                ],
            ]);
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            $this->db->query('SELECT 1 FROM ' . $table . ' LIMIT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function fieldExists(string $field, string $table): bool
    {
        try {
            $query = $this->db->query("SHOW COLUMNS FROM " . $table . " LIKE '" . str_replace("'", "''", $field) . "'");
            return is_object($query) && $query->getRowArray() !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
