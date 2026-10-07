<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPilgrimAvailabilityFields extends Migration
{
    public function up()
    {
        $fields = [];

        if (! $this->db->fieldExists('sale_released_at', 'pilgrims')) {
            $fields['sale_released_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When the pilgrim was released so they can be sold/booked again for a new trip',
            ];
        }

        if (! $this->db->fieldExists('sale_released_by', 'pilgrims')) {
            $fields['sale_released_by'] = [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User id that released the pilgrim for a new trip',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('pilgrims', $fields);
        }
    }

    public function down()
    {
        foreach (['sale_released_by', 'sale_released_at'] as $column) {
            if ($this->db->fieldExists($column, 'pilgrims')) {
                $this->forge->dropColumn('pilgrims', $column);
            }
        }
    }
}
