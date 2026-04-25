<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPackageHotelTierCosts extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('package_hotels', [
            'sharing_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'null' => false,
                'default' => 0.00,
                'after' => 'cost_amount',
            ],
            'quad_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'null' => false,
                'default' => 0.00,
                'after' => 'sharing_cost',
            ],
            'triple_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'null' => false,
                'default' => 0.00,
                'after' => 'quad_cost',
            ],
            'double_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'null' => false,
                'default' => 0.00,
                'after' => 'triple_cost',
            ],
        ]);

        $this->db->query("UPDATE package_hotels SET sharing_cost = cost_amount WHERE LOWER(TRIM(IFNULL(room_type, ''))) = 'sharing'");
        $this->db->query("UPDATE package_hotels SET quad_cost = cost_amount WHERE LOWER(TRIM(IFNULL(room_type, ''))) = 'quad'");
        $this->db->query("UPDATE package_hotels SET triple_cost = cost_amount WHERE LOWER(TRIM(IFNULL(room_type, ''))) = 'triple'");
        $this->db->query("UPDATE package_hotels SET double_cost = cost_amount WHERE LOWER(TRIM(IFNULL(room_type, ''))) = 'double'");
    }

    public function down(): void
    {
        $this->forge->dropColumn('package_hotels', ['sharing_cost', 'quad_cost', 'triple_cost', 'double_cost']);
    }
}
