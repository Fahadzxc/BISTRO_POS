<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToOrders extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
                'after'      => 'change_amount',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', 'status');
    }
}
