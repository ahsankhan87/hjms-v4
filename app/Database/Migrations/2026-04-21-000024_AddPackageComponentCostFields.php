<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPackageComponentCostFields extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('package_hotels', [
            'cost_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'null' => false,
                'default' => 0.00,
                'after' => 'room_type',
            ],
        ]);

        $this->forge->addColumn('package_flights', [
            'cost_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'null' => false,
                'default' => 0.00,
                'after' => 'arrival_at',
            ],
        ]);

        $this->forge->addColumn('package_transports', [
            'cost_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'null' => false,
                'default' => 0.00,
                'after' => 'seat_capacity',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('package_hotels', 'cost_amount');
        $this->forge->dropColumn('package_flights', 'cost_amount');
        $this->forge->dropColumn('package_transports', 'cost_amount');
    }
}
