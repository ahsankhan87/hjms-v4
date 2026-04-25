<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReturnFieldsToFlights extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('flights', [
            'return_airline' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
                'after' => 'ticket_file_path',
            ],
            'return_flight_no' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
                'after' => 'return_airline',
            ],
            'return_pnr' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
                'after' => 'return_flight_no',
            ],
            'return_departure_airport' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
                'after' => 'return_pnr',
            ],
            'return_arrival_airport' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
                'after' => 'return_departure_airport',
            ],
            'return_departure_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'return_arrival_airport',
            ],
            'return_arrival_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'return_departure_at',
            ],
            'return_ticket_file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'return_arrival_at',
            ],
            'return_ticket_file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'return_ticket_file_name',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('flights', [
            'return_airline',
            'return_flight_no',
            'return_pnr',
            'return_departure_airport',
            'return_arrival_airport',
            'return_departure_at',
            'return_arrival_at',
            'return_ticket_file_name',
            'return_ticket_file_path',
        ]);
    }
}
