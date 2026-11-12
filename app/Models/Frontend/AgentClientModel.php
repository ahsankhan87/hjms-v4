<?php

namespace App\Models\Frontend;

use CodeIgniter\Model;

class AgentClientModel extends Model
{
    protected $table = 'agent_clients';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'agent_id',
        'name',
        'passport_no',
        'phone',
        'email',
        'created_at',
    ];
}

