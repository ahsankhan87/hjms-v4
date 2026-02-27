<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageTransportModel extends Model
{
    protected $table = 'package_transports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'package_id',
        'transport_id',
        'provider_name',
        'vehicle_type',
        'seat_capacity',
        'created_at',
    ];
}
