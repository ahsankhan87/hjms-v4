<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDistanceFieldsToHotels extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('hotels')) {
            return;
        }

        $columns = [
            'distance_makkah_m' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'city',
            ],
            'distance_madina_m' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'distance_makkah_m',
            ],
        ];

        foreach ($columns as $name => $definition) {
            if (! $this->columnExists('hotels', $name)) {
                $this->forge->addColumn('hotels', [$name => $definition]);
            }
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('hotels')) {
            return;
        }

        foreach (['distance_makkah_m', 'distance_madina_m'] as $column) {
            if ($this->columnExists('hotels', $column)) {
                $this->forge->dropColumn('hotels', $column);
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

            return $query->getRowArray() !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
