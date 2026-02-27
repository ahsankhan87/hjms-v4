<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPilgrimLegacyAndImageFields extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('pilgrims')) {
            return;
        }

        $fields = [
            'father_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'cnic' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'country' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'passport_issue_date' => ['type' => 'DATE', 'null' => true],
            'city' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'mobile_no' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'mehram' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'visa_no' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'visa_status' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'visa_type' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'pilgrim_image_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'pilgrim_image_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'passport_image_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'passport_image_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ];

        foreach ($fields as $fieldName => $definition) {
            if (! $this->columnExists('pilgrims', $fieldName)) {
                $this->forge->addColumn('pilgrims', [$fieldName => $definition]);
            }
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('pilgrims')) {
            return;
        }

        $columns = [
            'father_name',
            'cnic',
            'country',
            'passport_issue_date',
            'city',
            'mobile_no',
            'mehram',
            'visa_no',
            'visa_status',
            'visa_type',
            'description',
            'pilgrim_image_name',
            'pilgrim_image_path',
            'passport_image_name',
            'passport_image_path',
        ];

        foreach ($columns as $column) {
            if ($this->columnExists('pilgrims', $column)) {
                $this->forge->dropColumn('pilgrims', $column);
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
