<?php

namespace App\Models;

class SalesModel extends SeasonScopedModel
{
    protected $table = 'sales';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'season_id',
        'sales_category_id',
        'sale_date',
        'customer_type',
        'agent_id',
        'customer_name',
        'amount',
        'payment_method',
        'reference_no',
        'note',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
