<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSaleHotelStays extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('sale_hotel_stays')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'sale_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'hotel_name' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
                'null' => true,
            ],
            'check_in_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'check_out_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'room_name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'room_qty' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('sale_id');
        $this->forge->createTable('sale_hotel_stays', true);

        // Migrate existing hotel sale data
        if ($this->db->tableExists('sales')) {
            $rows = $this->db->table('sales')
                ->select('id, hotel_name, checkin_date, checkout_date, room_name, room_qty, created_at')
                ->where('sale_type', 'hotel')
                ->where('checkin_date IS NOT NULL', null, false)
                ->where('checkout_date IS NOT NULL', null, false)
                ->get()
                ->getResultArray();

            if (!empty($rows)) {
                $stayBuilder = $this->db->table('sale_hotel_stays');
                foreach ($rows as $row) {
                    $checkIn = (string) ($row['checkin_date'] ?? '');
                    $checkOut = (string) ($row['checkout_date'] ?? '');
                    if ($checkIn !== '' && $checkOut !== '') {
                        $stayBuilder->insert([
                            'sale_id' => (int) ($row['id'] ?? 0),
                            'hotel_name' => (string) ($row['hotel_name'] ?? ''),
                            'check_in_date' => $checkIn,
                            'check_out_date' => $checkOut,
                            'room_name' => (string) ($row['room_name'] ?? ''),
                            'room_qty' => (int) ($row['room_qty'] ?? 1),
                            'created_at' => (string) ($row['created_at'] ?? date('Y-m-d H:i:s')),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('sale_hotel_stays')) {
            $this->forge->dropTable('sale_hotel_stays', true);
        }
    }
}
