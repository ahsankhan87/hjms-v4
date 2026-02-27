<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageCostModel extends Model
{
    protected $table = 'package_costs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'package_id',
        'cost_type',
        'cost_amount',
        'supplier_id',
        'description',
        'created_at',
    ];
}
