<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'supplier_name',
        'supplier_type',
        'contact_person',
        'phone',
        'email',
        'address',
        'opening_balance',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
