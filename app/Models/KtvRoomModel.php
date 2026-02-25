<?php

namespace App\Models;

use CodeIgniter\Model;

class KtvRoomModel extends Model
{
    protected $table            = 'ktv_rooms';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['room_name', 'hourly_rate', 'status'];
}
