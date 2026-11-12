<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFrontendTestimonialsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('frontend_testimonials')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
            ],
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'rating' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 5,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['is_active', 'sort_order'], false, false, 'idx_frontend_testimonials_active_sort');
        $this->forge->createTable('frontend_testimonials', true);
    }

    public function down()
    {
        $this->forge->dropTable('frontend_testimonials', true);
    }
}

