<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMinStockToProductsAndCreateStockLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('products', [
            'min_stock' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'stock',
            ],
        ]);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'qty_before' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'qty_change' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'qty_after' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'action_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('product_id', 'products', 'id');
        $this->forge->addForeignKey('user_id', 'users', 'user_id');
        $this->forge->createTable('stock_logs');
    }

    public function down()
    {
        $this->forge->dropColumn('products', 'min_stock');
        $this->forge->dropTable('stock_logs');
    }
}
