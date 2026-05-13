<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KtvRoomSeeder extends Seeder
{
    public function run()
    {
        $rooms = [
            ['room_name' => 'KTV Room 1', 'hourly_rate' => 199.00, 'capacity' => 5,  'status' => 'available'],
            ['room_name' => 'KTV Room 2', 'hourly_rate' => 299.00, 'capacity' => 8,  'status' => 'available'],
            ['room_name' => 'KTV Room 3', 'hourly_rate' => 499.00, 'capacity' => 12, 'status' => 'available'],
        ];
        $this->db->table('ktv_rooms')->insertBatch($rooms);
    }
}
