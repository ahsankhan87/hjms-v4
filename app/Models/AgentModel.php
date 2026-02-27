<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentModel extends Model
{
    protected $table = 'agents';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'branch_id',
        'code',
        'name',
        'email',
        'phone',
        'commission_type',
        'commission_value',
        'credit_limit',
        'current_balance',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
