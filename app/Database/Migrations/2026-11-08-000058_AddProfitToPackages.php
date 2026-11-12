<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfitToPackages extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('profit_mode', 'packages')) {
            $this->forge->addColumn('packages', [
                'profit_mode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => false,
                    'default'    => 'amount',
                    'after'      => 'exchange_rate',
                    'comment'    => 'Company profit type: amount (PKR per pilgrim) or percent (% of cost)',
                ],
            ]);
        }

        if (! $this->db->fieldExists('profit_value', 'packages')) {
            $this->forge->addColumn('packages', [
                'profit_value' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => false,
                    'default'    => 0.00,
                    'after'      => 'profit_mode',
                    'comment'    => 'Company profit per pilgrim, read according to profit_mode',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('packages', 'profit_value');
        $this->forge->dropColumn('packages', 'profit_mode');
    }
}
