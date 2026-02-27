<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageHotelModel extends Model
{
    protected $table = 'package_hotels';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'package_id',
        'hotel_id',
        'hotel_room_id',
        'hotel_name',
        'check_in_date',
        'check_out_date',
        'room_type',
        'created_at',
    ];
}
