<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFareToFlights extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('fare', 'flights')) {
            $this->forge->addColumn('flights', [
                'fare' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '14,2',
                    'null'       => false,
                    'default'    => 0.00,
                    'after'      => 'arrival_at',
                    'comment'    => 'Ticket fare per pilgrim in PKR',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('flights', 'fare');
    }
}
