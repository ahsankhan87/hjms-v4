<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageCostSheetModel extends Model
{
    protected $table = 'package_cost_sheets';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'package_id',
        'version_no',
        'is_published',
        'visa_sar',
        'visa_ex_rate',
        'transport_sar',
        'transport_ex_rate',
        'ticket_pkr',
        'makkah_room_rate_sar',
        'makkah_ex_rate',
        'makkah_nights',
        'madina_room_rate_sar',
        'madina_ex_rate',
        'madina_nights',
        'other_pkr',
        'profit_pkr',
        'notes',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
