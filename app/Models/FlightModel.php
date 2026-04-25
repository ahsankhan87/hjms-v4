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
        'return_airline',
        'return_flight_no',
        'return_pnr',
        'return_departure_airport',
        'return_arrival_airport',
        'return_departure_at',
        'return_arrival_at',
        'return_ticket_file_name',
        'return_ticket_file_path',
        'created_at',
        'updated_at',
    ];
}
