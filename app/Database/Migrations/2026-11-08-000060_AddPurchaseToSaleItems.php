<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPurchaseToSaleItems extends Migration
{
    public function up(): void
    {
        $fields = [];

        if (! $this->db->fieldExists('supplier_id', 'sale_items')) {
            $fields['supplier_id'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'attachment',
                'comment'    => 'Supplier the component was purchased from',
            ];
        }

        if (! $this->db->fieldExists('purchase_amount', 'sale_items')) {
            $fields['purchase_amount'] = [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'null'       => false,
                'default'    => 0.00,
                'after'      => 'supplier_id',
                'comment'    => 'Purchase price per component in its own currency',
            ];
        }

        if (! $this->db->fieldExists('purchase_amount_pkr', 'sale_items')) {
            $fields['purchase_amount_pkr'] = [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'null'       => false,
                'default'    => 0.00,
                'after'      => 'purchase_amount',
                'comment'    => 'Purchase price converted to PKR with the sale exchange rate',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('sale_items', $fields);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('sale_items', 'purchase_amount_pkr');
        $this->forge->dropColumn('sale_items', 'purchase_amount');
        $this->forge->dropColumn('sale_items', 'supplier_id');
    }
}
