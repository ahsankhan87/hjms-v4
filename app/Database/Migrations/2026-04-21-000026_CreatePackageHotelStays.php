<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePackageHotelStays extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'package_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'package_hotel_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'check_in_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'check_out_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('package_id');
        $this->forge->addKey('package_hotel_id');
        $this->forge->createTable('package_hotel_stays', true);

        $rows = $this->db->table('package_hotels')
            ->select('id, package_id, check_in_date, check_out_date, created_at')
            ->where('check_in_date IS NOT NULL', null, false)
            ->where('check_out_date IS NOT NULL', null, false)
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return;
        }

        $stayBuilder = $this->db->table('package_hotel_stays');
        foreach ($rows as $row) {
            $checkIn = (string) ($row['check_in_date'] ?? '');
            $checkOut = (string) ($row['check_out_date'] ?? '');
            if ($checkIn === '' || $checkOut === '') {
                continue;
            }

            $stayBuilder->insert([
                'package_id' => (int) ($row['package_id'] ?? 0),
                'package_hotel_id' => (int) ($row['id'] ?? 0),
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'created_at' => (string) ($row['created_at'] ?? date('Y-m-d H:i:s')),
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('package_hotel_stays', true);
    }
}
