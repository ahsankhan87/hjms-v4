<?php

namespace App\Models;

class PaymentModel extends SeasonScopedModel
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'season_id',
        'booking_id',
        'installment_id',
        'payment_no',
        'payment_date',
        'amount',
        'payment_type',
        'channel',
        'gateway_reference',
        'status',
        'note',
        'created_by',
        'created_at',
    ];
}
