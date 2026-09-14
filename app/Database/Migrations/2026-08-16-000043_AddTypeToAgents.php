<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTypeToAgents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('agents', [
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['agent', 'customer'],
                'default' => 'agent',
                'after' => 'code',
                'comment' => 'Type of agent: agent or customer',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('agents', 'type');
    }
}
