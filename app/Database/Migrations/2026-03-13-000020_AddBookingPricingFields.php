<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBookingPricingFields extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('bookings')) {
            return;
        }

        $columns = [
            'package_variant_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'package_id',
            ],
            'pricing_tier' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
                'after' => 'status',
            ],
            'unit_price' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => 0,
                'after' => 'pricing_tier',
            ],
            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'default' => 0,
                'after' => 'unit_price',
            ],
            'pricing_source' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'total_amount',
            ],
            'price_locked_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'pricing_source',
            ],
        ];

        foreach ($columns as $column => $definition) {
            if (! $this->columnExists('bookings', $column)) {
                $this->forge->addColumn('bookings', [$column => $definition]);
            }
        }

        try {
            $this->db->query('ALTER TABLE bookings ADD INDEX idx_bookings_pricing_tier (pricing_tier)');
        } catch (\Throwable $e) {
        }

        try {
            $this->db->query('ALTER TABLE bookings ADD INDEX idx_bookings_package_variant_id (package_variant_id)');
        } catch (\Throwable $e) {
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('bookings')) {
            return;
        }

        try {
            $this->db->query('ALTER TABLE bookings DROP INDEX idx_bookings_pricing_tier');
        } catch (\Throwable $e) {
        }

        try {
            $this->db->query('ALTER TABLE bookings DROP INDEX idx_bookings_package_variant_id');
        } catch (\Throwable $e) {
        }

        $columns = ['price_locked_at', 'pricing_source', 'total_amount', 'unit_price', 'pricing_tier', 'package_variant_id'];
        foreach ($columns as $column) {
            if ($this->columnExists('bookings', $column)) {
                $this->forge->dropColumn('bookings', $column);
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
            $row = $query->getRowArray();

            return $row !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
