<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCurrencyFieldsToSupplierLedgerEntries extends Migration
{
    public function up()
    {
        $this->forge->addColumn('supplier_ledger_entries', [
            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => false,
                'default' => 'PKR',
                'after' => 'entry_type',
                'comment' => 'Entry currency: PKR or SAR',
            ],
            'exchange_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '14,4',
                'null' => false,
                'default' => 1,
                'after' => 'currency',
                'comment' => 'SAR to PKR exchange rate (1 for PKR entries)',
            ],
            'sar_debit_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'null' => false,
                'default' => 0,
                'after' => 'debit_amount',
                'comment' => 'Debit amount in SAR (0 for PKR entries)',
            ],
            'sar_credit_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '14,2',
                'null' => false,
                'default' => 0,
                'after' => 'credit_amount',
                'comment' => 'Credit amount in SAR (0 for PKR entries)',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('supplier_ledger_entries', 'currency');
        $this->forge->dropColumn('supplier_ledger_entries', 'exchange_rate');
        $this->forge->dropColumn('supplier_ledger_entries', 'sar_debit_amount');
        $this->forge->dropColumn('supplier_ledger_entries', 'sar_credit_amount');
    }
}
