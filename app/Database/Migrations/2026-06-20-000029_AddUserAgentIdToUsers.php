<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserAgentIdToUsers extends Migration
{
    public function up(): void
    {
        if (! $this->tableIsReadable('users') || ! $this->tableIsReadable('agents')) {
            return;
        }

        if (! $this->columnExists('users', 'user_agent_id')) {
            $this->forge->addColumn('users', [
                'user_agent_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'email',
                ],
            ]);
        }

        if (! $this->indexExists('users', 'user_agent_id')) {
            $this->db->query('ALTER TABLE users ADD UNIQUE KEY user_agent_id (user_agent_id)');
        }
    }

    public function down(): void
    {
        if (! $this->tableIsReadable('users')) {
            return;
        }

        if ($this->indexExists('users', 'user_agent_id')) {
            $this->db->query('ALTER TABLE users DROP INDEX user_agent_id');
        }

        if ($this->columnExists('users', 'user_agent_id')) {
            $this->forge->dropColumn('users', 'user_agent_id');
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

    private function indexExists(string $table, string $column): bool
    {
        try {
            $query = $this->db->query('SHOW INDEX FROM ' . $table . ' WHERE Key_name = ?', [$column]);

            return $query->getRowArray() !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
