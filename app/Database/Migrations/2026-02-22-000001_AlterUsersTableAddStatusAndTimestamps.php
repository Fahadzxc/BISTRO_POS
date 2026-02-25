<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersTableAddStatusAndTimestamps extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
                'null'       => false,
                'after'      => 'role',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'status');
        $this->forge->dropColumn('users', 'created_at');
    }
}
