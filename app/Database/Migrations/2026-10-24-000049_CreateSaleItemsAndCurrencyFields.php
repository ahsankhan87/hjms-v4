<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSaleItemsAndCurrencyFields extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('sales')) {
            return;
        }

        $salesColumns = [
            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => false,
                'default' => 'PKR',
                'after' => 'amount',
                'comment' => 'Sale currency: PKR or SAR',
            ],
            'exchange_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '14,4',
                'null' => false,
                'default' => 1,
                'after' => 'currency',
                'comment' => 'Currency to PKR exchange rate (1 for PKR sales)',
            ],
            'amount_pkr' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'null' => false,
                'default' => 0,
                'after' => 'exchange_rate',
                'comment' => 'Total amount converted to PKR',
            ],
        ];

        foreach ($salesColumns as $columnName => $definition) {
            if (! $this->db->fieldExists($columnName, 'sales')) {
                $this->forge->addColumn('sales', [$columnName => $definition]);
            }
        }

        if ($this->db->tableExists('sale_items')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'sale_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'item_type' => ['type' => 'VARCHAR', 'constraint' => 20],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'description' => ['type' => 'TEXT', 'null' => true],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => '0.00'],
            'attachment' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],

            'visa_type' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'visa_no' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],

            'pnr' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'sector' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'ticket_no' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],

            'hotel_name' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'check_in_date' => ['type' => 'DATE', 'null' => true],
            'check_out_date' => ['type' => 'DATE', 'null' => true],
            'no_of_nights' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'room_qty' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'room_type' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],

            'transport_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'transport_type' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'route' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'is_ziarat' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],

            'package_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'package_name' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'departure_date' => ['type' => 'DATE', 'null' => true],
            'arrival_date' => ['type' => 'DATE', 'null' => true],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('sale_id');
        $this->forge->addKey('item_type');
        $this->forge->addForeignKey('sale_id', 'sales', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sale_items', true);
    }

    public function down()
    {
        if ($this->db->tableExists('sale_items')) {
            $this->forge->dropTable('sale_items', true);
        }

        if (! $this->db->tableExists('sales')) {
            return;
        }

        foreach (['amount_pkr', 'exchange_rate', 'currency'] as $columnName) {
            if ($this->db->fieldExists($columnName, 'sales')) {
                $this->forge->dropColumn('sales', $columnName);
            }
        }
    }
}

ALTER TABLE `sales` ADD `currency` VARCHAR(10) NULL AFTER `amount`, ADD `exchange_rate` DECIMAL(14,4) NULL AFTER `currency`, ADD `amount_pkr` DECIMAL(14,2)  NULL AFTER `exchange_rate`;
ALTER TABLE `sales` ADD `package_name` VARCHAR(180) NULL AFTER `ticket_no`, ADD `package_details` TEXT NULL AFTER `package_name`, ADD `attachment` VARCHAR(255) NULL AFTER `package_details`;
