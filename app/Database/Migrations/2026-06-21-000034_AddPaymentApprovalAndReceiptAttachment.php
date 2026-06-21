<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentApprovalAndReceiptAttachment extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('payments')) {
            return;
        }

        if (! $this->db->fieldExists('receipt_attachment_path', 'payments')) {
            $this->forge->addColumn('payments', [
                'receipt_attachment_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'note',
                ],
            ]);
        }

        if (! $this->db->fieldExists('receipt_attachment_name', 'payments')) {
            $this->forge->addColumn('payments', [
                'receipt_attachment_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'receipt_attachment_path',
                ],
            ]);
        }

        if (! $this->db->fieldExists('approved_by', 'payments')) {
            $this->forge->addColumn('payments', [
                'approved_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'receipt_attachment_name',
                ],
            ]);
        }

        if (! $this->db->fieldExists('approved_at', 'payments')) {
            $this->forge->addColumn('payments', [
                'approved_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'approved_by',
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('payments')) {
            return;
        }

        foreach (['approved_at', 'approved_by', 'receipt_attachment_name', 'receipt_attachment_path'] as $column) {
            if ($this->db->fieldExists($column, 'payments')) {
                $this->forge->dropColumn('payments', $column);
            }
        }
    }
}
