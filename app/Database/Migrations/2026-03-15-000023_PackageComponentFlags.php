<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PackageComponentFlags extends Migration
{
    public function up(): void
    {
        $fields = [
            'include_hotel' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 1,
                'after'      => 'is_active',
            ],
            'include_ticket' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 1,
                'after'      => 'include_hotel',
            ],
            'include_transport' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 1,
                'after'      => 'include_ticket',
            ],
        ];

        $this->forge->addColumn('packages', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('packages', ['include_hotel', 'include_ticket', 'include_transport']);
    }
}
