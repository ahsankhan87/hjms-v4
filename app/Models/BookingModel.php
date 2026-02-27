<?php

namespace App\Models;

class BookingModel extends SeasonScopedModel
{
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'season_id',
        'booking_no',
        'package_id',
        'agent_id',
        'branch_id',
        'status',
        'total_pilgrims',
        'remarks',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
