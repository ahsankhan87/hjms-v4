<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingPilgrimModel extends Model
{
    protected $table = 'booking_pilgrims';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'booking_id',
        'pilgrim_id',
        'created_at',
    ];
}
