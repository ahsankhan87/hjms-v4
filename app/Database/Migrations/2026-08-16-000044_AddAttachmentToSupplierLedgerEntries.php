<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAttachmentToSupplierLedgerEntries extends Migration
{
    public function up()
    {
        $this->forge->addColumn('supplier_ledger_entries', [
            'attachment' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'description',
                'comment' => 'Attachment filename (image/pdf)',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('supplier_ledger_entries', 'attachment');
    }
}
