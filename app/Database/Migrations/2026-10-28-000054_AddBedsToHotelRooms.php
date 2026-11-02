<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBedsToHotelRooms extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('beds', 'hotel_rooms')) {
            $this->forge->addColumn('hotel_rooms', [
                'beds' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 0,
                    'after'      => 'room_type',
                    'comment'    => 'Beds per room; 0 = derive from room type',
                ],
            ]);
        }

        // Seed existing room types with their conventional bed count.
        $rows = $this->db->table('hotel_rooms')->select('id, room_type')->get()->getResultArray();
        foreach ($rows as $row) {
            $beds = self::bedsForRoomType((string) ($row['room_type'] ?? ''));
            if ($beds > 0) {
                $this->db->table('hotel_rooms')->where('id', (int) $row['id'])->update(['beds' => $beds]);
            }
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('hotel_rooms', 'beds');
    }

    private static function bedsForRoomType(string $roomType): int
    {
        $value = strtolower(trim($roomType));
        if ($value === '') {
            return 0;
        }

        // "sharing 5 bed" style names carry their own capacity.
        if (preg_match('/(\d+)\s*(?:bed|pax|person|sharing)/', $value, $matches) === 1) {
            return max(1, (int) $matches[1]);
        }

        $map = [
            'sharing' => 6,
            'quint'   => 5,
            'quad'    => 4,
            'triple'  => 3,
            'trp'     => 3,
            'double'  => 2,
            'dbl'     => 2,
            'single'  => 1,
        ];

        foreach ($map as $needle => $beds) {
            if (strpos($value, $needle) !== false) {
                return $beds;
            }
        }

        return 0;
    }
}
