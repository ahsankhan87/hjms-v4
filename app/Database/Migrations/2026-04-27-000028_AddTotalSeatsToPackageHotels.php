<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTotalSeatsToPackageHotels extends Migration
{
    public function up(): void
    {
        $exists = $this->db->query("SHOW COLUMNS FROM package_hotels LIKE 'total_seats'")->getRowArray();
        if (empty($exists)) {
            $this->forge->addColumn('package_hotels', [
                'total_seats' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                    'default' => 0,
                    'after' => 'double_cost',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $exists = $this->db->query("SHOW COLUMNS FROM package_hotels LIKE 'total_seats'")->getRowArray();
        if (! empty($exists)) {
            $this->forge->dropColumn('package_hotels', 'total_seats');
        }
    }
}
