<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQuintCostToPackageHotels extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('quint_cost', 'package_hotels')) {
            $this->forge->addColumn('package_hotels', [
                'quint_cost' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '14,2',
                    'null'       => false,
                    'default'    => 0.00,
                    'after'      => 'sharing_cost',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('package_hotels', 'quint_cost');
    }
}
