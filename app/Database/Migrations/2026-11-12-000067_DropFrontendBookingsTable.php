<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropFrontendBookingsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('frontend_bookings')) {
            $this->forge->dropTable('frontend_bookings', true);
        }
    }

    public function down()
    {
        // frontend_bookings has been retired in favor of main bookings table.
    }
}

