<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageFlightModel extends Model
{
    protected $table = 'package_flights';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'package_id',
        'flight_id',
        'airline',
        'flight_no',
        'departure_at',
        'arrival_at',
        'created_at',
    ];
}
