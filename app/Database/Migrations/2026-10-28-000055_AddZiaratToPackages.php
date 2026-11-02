<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZiaratToPackages extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('include_ziarat', 'packages')) {
            $this->forge->addColumn('packages', [
                'include_ziarat' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 0,
                    'after'      => 'include_transport',
                ],
                'ziarat_cost' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '14,2',
                    'null'       => false,
                    'default'    => 0.00,
                    'after'      => 'include_ziarat',
                    'comment'    => 'Ziarat (holy sites visit) cost in SAR',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('packages', ['include_ziarat', 'ziarat_cost']);
    }
}
