<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCreatedAtToCategoriesAndProducts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('categories', [
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'name',
            ],
        ]);
        $this->forge->addColumn('products', [
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'image',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('categories', 'created_at');
        $this->forge->dropColumn('products', 'created_at');
    }
}
