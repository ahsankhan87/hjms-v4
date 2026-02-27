<?php

namespace App\Models;

use CodeIgniter\Model;

class TransportModel extends Model
{
    protected $table = 'transports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'transport_name',
        'provider_name',
        'vehicle_type',
        'driver_name',
        'driver_phone',
        'seat_capacity',
        'created_at',
        'updated_at',
    ];
}
