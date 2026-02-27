<?php

namespace App\Models;

use CodeIgniter\Model;

class FlightModel extends Model
{
    protected $table = 'flights';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'airline',
        'flight_no',
        'pnr',
        'departure_airport',
        'arrival_airport',
        'departure_at',
        'arrival_at',
        'ticket_file_name',
        'ticket_file_path',
        'created_at',
        'updated_at',
    ];
}
