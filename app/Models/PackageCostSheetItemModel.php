<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageCostSheetItemModel extends Model
{
    protected $table = 'package_cost_sheet_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'cost_sheet_id',
        'component_code',
        'supplier_id',
        'purchase_amount_pkr',
        'remarks',
        'created_at',
    ];
}
