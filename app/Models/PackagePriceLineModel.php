<?php

namespace App\Models;

use CodeIgniter\Model;

class PackagePriceLineModel extends Model
{
    protected $table = 'package_price_lines';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'cost_sheet_id',
        'sharing_type',
        'total_cost_pkr',
        'sell_price_pkr',
        'created_at',
    ];
}
