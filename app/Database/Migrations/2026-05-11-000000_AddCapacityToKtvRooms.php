<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCapacityToKtvRooms extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ktv_rooms', [
            'capacity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 12,
                'after'      => 'hourly_rate',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ktv_rooms', 'capacity');
    }
}
