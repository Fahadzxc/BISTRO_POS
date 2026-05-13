<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameKtvRoomNamesRemoveSize extends Migration
{
    public function up()
    {
        $map = [
            'KTV Room 1 (Small)'  => 'KTV Room 1',
            'KTV Room 2 (Medium)' => 'KTV Room 2',
            'KTV Room 3 (Large)'  => 'KTV Room 3',
        ];
        foreach ($map as $from => $to) {
            $this->db->table('ktv_rooms')->where('room_name', $from)->update(['room_name' => $to]);
        }
    }

    public function down()
    {
        $map = [
            'KTV Room 1' => 'KTV Room 1 (Small)',
            'KTV Room 2' => 'KTV Room 2 (Medium)',
            'KTV Room 3' => 'KTV Room 3 (Large)',
        ];
        foreach ($map as $from => $to) {
            $this->db->table('ktv_rooms')->where('room_name', $from)->update(['room_name' => $to]);
        }
    }
}
