<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Folds the short-lived profit_amount / profit_percent columns into the single
 * profit_value column so only one profit figure is stored per package.
 */
class ConsolidateLegacyProfitColumns extends Migration
{
    public function up(): void
    {
        $hasAmount  = $this->db->fieldExists('profit_amount', 'packages');
        $hasPercent = $this->db->fieldExists('profit_percent', 'packages');

        if (! $this->db->fieldExists('profit_mode', 'packages')) {
            $this->forge->addColumn('packages', [
                'profit_mode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => false,
                    'default'    => 'amount',
                    'after'      => 'exchange_rate',
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
                ],
            ]);
        }

        if (! $hasAmount && ! $hasPercent) {
            return;
        }

        $amountExpr  = $hasAmount ? 'COALESCE(profit_amount, 0)' : '0';
        $percentExpr = $hasPercent ? 'COALESCE(profit_percent, 0)' : '0';

        $this->db->query(
            "UPDATE packages SET profit_value = CASE WHEN profit_mode = 'percent' THEN {$percentExpr} ELSE {$amountExpr} END"
        );

        if ($hasPercent) {
            $this->forge->dropColumn('packages', 'profit_percent');
        }
        if ($hasAmount) {
            $this->forge->dropColumn('packages', 'profit_amount');
        }
    }

    public function down(): void
    {
        if (! $this->db->fieldExists('profit_amount', 'packages')) {
            $this->forge->addColumn('packages', [
                'profit_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => false,
                    'default'    => 0.00,
                    'after'      => 'profit_mode',
                ],
            ]);
        }

        if (! $this->db->fieldExists('profit_percent', 'packages')) {
            $this->forge->addColumn('packages', [
                'profit_percent' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '8,2',
                    'null'       => false,
                    'default'    => 0.00,
                    'after'      => 'profit_amount',
                ],
            ]);
        }

        $this->db->query(
            "UPDATE packages SET profit_amount = CASE WHEN profit_mode = 'percent' THEN 0 ELSE profit_value END,"
            . " profit_percent = CASE WHEN profit_mode = 'percent' THEN profit_value ELSE 0 END"
        );
    }
}
