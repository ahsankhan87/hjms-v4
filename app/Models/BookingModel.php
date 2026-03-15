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
        'package_variant_id',
        'agent_id',
        'branch_id',
        'company_id',
        'status',
        'pricing_tier',
        'unit_price',
        'total_amount',
        'pricing_source',
        'price_locked_at',
        'total_pilgrims',
        'remarks',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
