<?php

namespace App\Models;

class PackageModel extends SeasonScopedModel
{
    protected $table = 'packages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'season_id',
        'code',
        'name',
        'package_type',
        'airline',
        'airline_logo',
        'departure_date',
        'arrival_date',
        'duration_days',
        'makkah_hotel',
        'makkah_hotel_link',
        'madina_hotel',
        'madina_hotel_link',
        'sharing_types',
        'selling_price',
        'purchase_price_total',
        'purchase_price_visa',
        'purchase_price_ticket',
        'purchase_price_transport',
        'purchase_price_makkah',
        'purchase_price_madina',
        'passport_attachment',
        'total_seats',
        'is_active',
        'notes',
        'created_at',
        'updated_at',
    ];
}
