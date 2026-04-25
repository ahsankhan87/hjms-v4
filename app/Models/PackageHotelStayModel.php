<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageHotelStayModel extends Model
{
    protected $table = 'package_hotel_stays';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'package_id',
        'package_hotel_id',
        'check_in_date',
        'check_out_date',
        'created_at',
    ];
}
