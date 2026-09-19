<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierCategoryModel extends Model
{
    protected $table = 'supplier_categories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
