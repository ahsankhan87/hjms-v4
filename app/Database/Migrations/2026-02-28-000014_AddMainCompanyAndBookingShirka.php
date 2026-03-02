<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMainCompanyAndBookingShirka extends Migration
{
    public function up()
    {
        $this->createMainCompanyTable();
        $this->ensureMainCompanyRow();
        $this->addBookingCompanyColumn();
    }

    public function down()
    {
        if ($this->tableIsReadable('bookings') && $this->columnExists('bookings', 'company_id')) {
            try {
                $this->db->query('ALTER TABLE bookings DROP INDEX idx_bookings_company_id');
            } catch (\Throwable $e) {
            }

            $this->forge->dropColumn('bookings', 'company_id');
        }

        if ($this->tableIsReadable('main_company')) {
            $this->forge->dropTable('main_company', true);
        }
    }

    private function createMainCompanyTable(): void
    {
        if ($this->tableIsReadable('main_company')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
            ],
            'tagline' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
                'null' => true,
            ],
            'website' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
                'null' => true,
            ],
            'logo_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'ntn' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'strn' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
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
        $this->forge->createTable('main_company', true);
    }

    private function ensureMainCompanyRow(): void
    {
        if (! $this->tableIsReadable('main_company')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $row = $this->db->table('main_company')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();

        if ($row === null) {
            $this->db->table('main_company')->insert([
                'name' => 'KARWAN-E-TAIF PVT LTD',
                'tagline' => 'Hajj & Umrah Management',
                'address' => 'Shop # B-7, B-8, Hanifullah Plaza Charsadda Road, Opposite Charsadda Bus Stand Peshawar',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return;
        }

        $this->db->table('main_company')
            ->where('id', (int) $row['id'])
            ->where('name', null)
            ->update(['name' => 'KARWAN-E-TAIF PVT LTD', 'updated_at' => $now]);
    }

    private function addBookingCompanyColumn(): void
    {
        if (! $this->tableIsReadable('bookings')) {
            return;
        }

        if (! $this->columnExists('bookings', 'company_id')) {
            $this->forge->addColumn('bookings', [
                'company_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'branch_id',
                ],
            ]);
        }

        try {
            $this->db->query('ALTER TABLE bookings ADD INDEX idx_bookings_company_id (company_id)');
        } catch (\Throwable $e) {
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
