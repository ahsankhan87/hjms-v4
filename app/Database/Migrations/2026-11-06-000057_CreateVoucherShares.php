<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVoucherShares extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('voucher_shares')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'booking_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'revoked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('booking_id');
        $this->forge->addUniqueKey('token');
        $this->forge->createTable('voucher_shares', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('voucher_shares', true);
    }
}
