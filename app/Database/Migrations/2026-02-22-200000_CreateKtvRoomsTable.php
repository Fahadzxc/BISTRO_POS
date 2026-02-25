<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKtvRoomsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'room_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'hourly_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'occupied', 'cleaning'],
                'default'    => 'available',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('ktv_rooms');
    }

    public function down()
    {
        $this->forge->dropTable('ktv_rooms');
    }
}
