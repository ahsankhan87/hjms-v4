<?php

namespace App\Models;

class ExpenseCategoryModel extends SeasonScopedModel
{
    protected $table = 'expense_categories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'season_id',
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
