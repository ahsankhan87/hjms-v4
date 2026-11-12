<?php

namespace App\Models;

use CodeIgniter\Model;

class FrontendUserModel extends Model
{
    protected $table = 'frontend_users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'role',
        'name',
        'email',
        'password',
        'company_name',
        'license_number',
        'phone',
        'status',
        'created_at',
    ];
}

