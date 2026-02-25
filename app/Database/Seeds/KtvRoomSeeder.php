<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KtvRoomSeeder extends Seeder
{
    public function run()
    {
        $rooms = [
            ['room_name' => 'Room 1', 'hourly_rate' => 500.00, 'status' => 'available'],
            ['room_name' => 'Room 2', 'hourly_rate' => 500.00, 'status' => 'available'],
            ['room_name' => 'Room 3', 'hourly_rate' => 600.00, 'status' => 'available'],
            ['room_name' => 'Room 4', 'hourly_rate' => 600.00, 'status' => 'available'],
            ['room_name' => 'VIP 1', 'hourly_rate' => 1000.00, 'status' => 'available'],
        ];
        $this->db->table('ktv_rooms')->insertBatch($rooms);
    }
}
