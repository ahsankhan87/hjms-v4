<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSeasonsAndSeasonScopes extends Migration
{
    public function up()
    {
        if (! $this->tableIsReadable('seasons')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                ],
                'year_start' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                ],
                'year_end' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 4,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('is_active');
            $this->forge->createTable('seasons', true);
        }

        $currentYear = (int) date('Y');
        $defaultName = $currentYear . ' - ' . ($currentYear + 1) . ' Season';

        $activeSeason = $this->db->table('seasons')->where('is_active', 1)->orderBy('id', 'DESC')->get()->getRowArray();
        if (empty($activeSeason)) {
            $this->db->table('seasons')->insert([
                'name' => $defaultName,
                'year_start' => $currentYear,
                'year_end' => $currentYear + 1,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $activeSeasonRow = $this->db->table('seasons')->where('is_active', 1)->orderBy('id', 'DESC')->get()->getRowArray();
        $activeSeasonId = (int) ($activeSeasonRow['id'] ?? 0);
        if ($activeSeasonId < 1) {
            return;
        }

        $tables = ['pilgrims', 'bookings', 'visas', 'payments', 'packages'];

        foreach ($tables as $table) {
            if (! $this->tableIsReadable($table)) {
                continue;
            }

            if (! $this->columnExists($table, 'season_id')) {
                $this->forge->addColumn($table, [
                    'season_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                    ],
                ]);
            }

            $this->db->table($table)
                ->where('season_id', null)
                ->set(['season_id' => $activeSeasonId])
                ->update();

            try {
                $this->db->query('ALTER TABLE ' . $table . ' MODIFY season_id INT(11) UNSIGNED NOT NULL');
            } catch (\Throwable $e) {
            }

            try {
                $this->db->query('ALTER TABLE ' . $table . ' ADD INDEX idx_' . $table . '_season_id (season_id)');
            } catch (\Throwable $e) {
            }
        }
    }

    public function down()
    {
        $tables = ['pilgrims', 'bookings', 'visas', 'payments', 'packages'];

        foreach ($tables as $table) {
            if (! $this->tableIsReadable($table)) {
                continue;
            }

            if ($this->columnExists($table, 'season_id')) {
                try {
                    $this->db->query('ALTER TABLE ' . $table . ' DROP INDEX idx_' . $table . '_season_id');
                } catch (\Throwable $e) {
                }

                $this->forge->dropColumn($table, 'season_id');
            }
        }

        if ($this->tableIsReadable('seasons')) {
            $this->forge->dropTable('seasons', true);
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
