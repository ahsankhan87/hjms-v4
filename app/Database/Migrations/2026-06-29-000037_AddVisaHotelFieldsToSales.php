<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVisaHotelFieldsToSales extends Migration
{
    public function up()
    {
        if (! $this->tableExists('sales')) {
            return;
        }

        $columns = [
            'sale_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'default' => 'visa',
                'after' => 'season_id',
            ],
            'invoice_no' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
                'after' => 'sale_type',
            ],
            'pilgrim_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'agent_id',
            ],
            'pax_name' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
                'null' => true,
                'after' => 'pilgrim_id',
            ],
            'visa_type' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
                'after' => 'reference_no',
            ],
            'visa_agency' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
                'null' => true,
                'after' => 'visa_type',
            ],
            'hotel_name' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
                'null' => true,
                'after' => 'visa_agency',
            ],
            'checkin_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'hotel_name',
            ],
            'checkout_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'checkin_date',
            ],
            'no_of_nights' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'checkout_date',
            ],
            'room_name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
                'after' => 'no_of_nights',
            ],
            'room_qty' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'room_name',
            ],
        ];

        foreach ($columns as $columnName => $definition) {
            if (! $this->fieldExists($columnName, 'sales')) {
                $this->forge->addColumn('sales', [$columnName => $definition]);
            }
        }
    }

    public function down()
    {
        if (! $this->tableExists('sales')) {
            return;
        }

        $columnNames = [
            'room_qty',
            'room_name',
            'no_of_nights',
            'checkout_date',
            'checkin_date',
            'hotel_name',
            'visa_agency',
            'visa_type',
            'pax_name',
            'pilgrim_id',
            'invoice_no',
            'sale_type',
        ];

        foreach ($columnNames as $columnName) {
            if ($this->fieldExists($columnName, 'sales')) {
                $this->forge->dropColumn('sales', $columnName);
            }
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
