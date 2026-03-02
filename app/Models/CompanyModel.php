<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'tagline',
        'address',
        'phone',
        'email',
        'website',
        'logo_url',
        'ntn',
        'strn',
        'saudi_partner',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
