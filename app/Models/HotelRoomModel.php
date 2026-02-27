<?php

namespace App\Models;

use CodeIgniter\Model;

class HotelRoomModel extends Model
{
    protected $table = 'hotel_rooms';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'hotel_id',
        'room_type',
        'total_rooms',
        'allocated_rooms',
        'created_at',
        'updated_at',
    ];
}
