<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFrontendUserIdToBookings extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('bookings')) {
            return;
        }

        if (! $this->db->fieldExists('frontend_user_id', 'bookings')) {
            $this->forge->addColumn('bookings', [
                'frontend_user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'remarks',
                ],
            ]);
            $this->db->query('CREATE INDEX idx_bookings_frontend_user_id ON bookings (frontend_user_id)');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('bookings') && $this->db->fieldExists('frontend_user_id', 'bookings')) {
            $this->forge->dropColumn('bookings', 'frontend_user_id');
        }
    }
}

