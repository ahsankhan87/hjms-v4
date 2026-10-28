<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCurrencyToSaleItems extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('sale_items')) {
            $columns = [
                'currency' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'null' => false,
                    'default' => 'PKR',
                    'after' => 'amount',
                    'comment' => 'Component currency: PKR or SAR',
                ],
                'exchange_rate' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,4',
                    'null' => false,
                    'default' => 1,
                    'after' => 'currency',
                    'comment' => 'Component currency to PKR exchange rate',
                ],
                'amount_pkr' => [
                    'type' => 'DECIMAL',
                    'constraint' => '14,2',
                    'null' => false,
                    'default' => 0,
                    'after' => 'exchange_rate',
                    'comment' => 'Component amount converted to PKR',
                ],
            ];

            foreach ($columns as $columnName => $definition) {
                if (! $this->db->fieldExists($columnName, 'sale_items')) {
                    $this->forge->addColumn('sale_items', [$columnName => $definition]);
                }
            }

            $this->db->query('UPDATE sale_items SET amount_pkr = amount WHERE amount_pkr = 0');
        }

        // The category is derived from the sale components now, so it can stay empty.
        if ($this->db->tableExists('sales') && $this->db->fieldExists('sales_category_id', 'sales')) {
            $this->dropForeignKey('sales', 'sales_sales_category_id_foreign');
            $this->db->query('ALTER TABLE `sales` MODIFY `sales_category_id` INT(11) UNSIGNED NULL');
            $this->db->query('ALTER TABLE `sales` ADD CONSTRAINT `sales_sales_category_id_foreign` FOREIGN KEY (`sales_category_id`) REFERENCES `sales_categories`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE');
            $this->db->query("UPDATE sales SET amount_pkr = amount WHERE amount_pkr = 0 AND currency = 'PKR'");
        }
    }

    public function down()
    {
        if ($this->db->tableExists('sale_items')) {
            foreach (['amount_pkr', 'exchange_rate', 'currency'] as $columnName) {
                if ($this->db->fieldExists($columnName, 'sale_items')) {
                    $this->forge->dropColumn('sale_items', $columnName);
                }
            }
        }

        if (! $this->db->tableExists('sales') || ! $this->db->fieldExists('sales_category_id', 'sales')) {
            return;
        }

        try {
            $this->db->query('UPDATE sales SET sales_category_id = (SELECT id FROM sales_categories ORDER BY id LIMIT 1) WHERE sales_category_id IS NULL');
        } catch (\Throwable $e) {
            // Leave null categories in place when there is no category to fall back to.
        }

        $this->dropForeignKey('sales', 'sales_sales_category_id_foreign');
        $this->db->query('ALTER TABLE `sales` MODIFY `sales_category_id` INT(11) UNSIGNED NOT NULL');
        $this->db->query('ALTER TABLE `sales` ADD CONSTRAINT `sales_sales_category_id_foreign` FOREIGN KEY (`sales_category_id`) REFERENCES `sales_categories`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE');
    }

    private function dropForeignKey(string $table, string $constraint): void
    {
        try {
            $exists = $this->db->query(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?",
                [$table, $constraint]
            )->getRowArray();

            if ($exists !== null) {
                $this->db->query('ALTER TABLE `' . $table . '` DROP FOREIGN KEY `' . $constraint . '`');
            }
        } catch (\Throwable $e) {
            // Nothing to drop when the constraint is missing.
        }
    }
}
