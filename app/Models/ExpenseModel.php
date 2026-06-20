<?php

namespace App\Models;

class ExpenseModel extends SeasonScopedModel
{
    protected $table = 'expenses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'season_id',
        'expense_category_id',
        'expense_date',
        'amount',
        'paid_to',
        'payment_method',
        'reference_no',
        'note',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
