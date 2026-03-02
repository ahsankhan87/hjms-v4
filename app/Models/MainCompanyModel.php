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
        'voucher_instructions_ur',
        'voucher_instructions_en',
        'makkah_contact',
        'makkah_contact_ur',
        'makkah_contact_en',
        'madina_contact',
        'madina_contact_ur',
        'madina_contact_en',
        'transport_contact',
        'transport_contact_ur',
        'transport_contact_en',
        'created_at',
        'updated_at',
    ];
}
