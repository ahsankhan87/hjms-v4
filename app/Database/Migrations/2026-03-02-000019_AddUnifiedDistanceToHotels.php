<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnifiedDistanceToHotels extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('hotels')) {
            return;
        }

        if (! $this->columnExists('hotels', 'distance_m')) {
            $this->forge->addColumn('hotels', [
                'distance_m' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'after' => 'city',
                ],
            ]);
        }

        if ($this->columnExists('hotels', 'distance_makkah_m') || $this->columnExists('hotels', 'distance_madina_m')) {
            $this->db->query(
                "UPDATE hotels
                 SET distance_m = CASE
                    WHEN distance_m IS NOT NULL THEN distance_m
                    WHEN LOWER(COALESCE(city, '')) LIKE '%makk%' THEN distance_makkah_m
                    WHEN LOWER(COALESCE(city, '')) LIKE '%madin%' THEN distance_madina_m
                    ELSE COALESCE(distance_makkah_m, distance_madina_m)
                 END"
            );
        }
    }

    public function down()
    {
        if (! $this->tableIsReadable('hotels')) {
            return;
        }

        if ($this->columnExists('hotels', 'distance_m')) {
            $this->forge->dropColumn('hotels', 'distance_m');
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
