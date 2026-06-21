<?php

namespace App\Models;

use CodeIgniter\Model;

class MainCompanyModel extends Model
{
    protected $table = 'main_company';
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
        'voucher_instructions',
        'makkah_contact_ur',
        'madina_contact_ur',
        'transport_contact_ur',
        'created_at',
        'updated_at',
    ];
}
