<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PackageDatetimeAndCostSeats extends Migration
{
    public function up(): void
    {
        // Change departure_date and arrival_date from DATE to DATETIME
        $this->db->query('ALTER TABLE packages MODIFY COLUMN departure_date DATETIME NULL');
        $this->db->query('ALTER TABLE packages MODIFY COLUMN arrival_date DATETIME NULL');

        // Add seats_limit to package_costs
        $fields = [
            'seats_limit' => [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'cost_amount',
            ],
        ];

        $this->forge->addColumn('package_costs', $fields);
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE packages MODIFY COLUMN departure_date DATE NULL');
        $this->db->query('ALTER TABLE packages MODIFY COLUMN arrival_date DATE NULL');
        $this->forge->dropColumn('package_costs', 'seats_limit');
    }
}
