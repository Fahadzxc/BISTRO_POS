<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name'     => 'Admin User',
                'email'    => 'admin@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role'     => 'admin',
                'status'   => 'active',
            ],
            [
                'name'     => 'Cashier User',
                'email'    => 'cashier@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role'     => 'cashier',
                'status'   => 'active',
            ],
            [
                'name'     => 'Staff User',
                'email'    => 'staff@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role'     => 'staff',
                'status'   => 'active',
            ],
        ];

        foreach ($users as $user) {
            $this->db->table('users')->insert($user);
        }
    }
}
