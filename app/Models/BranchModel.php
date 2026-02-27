<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $table = 'branches';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'code',
        'name',
        'address',
        'phone',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
