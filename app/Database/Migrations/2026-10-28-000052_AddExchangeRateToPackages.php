<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExchangeRateToPackages extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('exchange_rate', 'packages')) {
            $this->forge->addColumn('packages', [
                'exchange_rate' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,4',
                    'null'       => false,
                    'default'    => 1.0000,
                    'after'      => 'purchase_price_visa',
                    'comment'    => 'SAR to PKR rate used to price SAR costs in this package',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('packages', 'exchange_rate');
    }
}
