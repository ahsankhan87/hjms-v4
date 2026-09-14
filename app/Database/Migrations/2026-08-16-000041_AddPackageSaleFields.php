<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPackageSaleFields extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE sales
            ADD COLUMN package_name VARCHAR(180) NULL AFTER ticket_no,
            ADD COLUMN package_details TEXT NULL AFTER package_name,
            ADD COLUMN attachment VARCHAR(255) NULL AFTER package_details,
            ADD COLUMN attachment2 VARCHAR(255) NULL AFTER attachment,
            ADD COLUMN attachment3 VARCHAR(255) NULL AFTER attachment2
        ");
    }

    public function down()
    {
        $fields = ['package_name', 'package_details', 'attachment', 'attachment2', 'attachment3'];

        foreach ($fields as $field) {
            if ($this->db->fieldExists($field, 'sales')) {
                $this->forge->dropColumn('sales', $field);
            }
        }
    }
}
