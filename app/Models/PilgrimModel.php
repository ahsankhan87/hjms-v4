<?php

namespace App\Models;

class PilgrimModel extends SeasonScopedModel
{
    protected $table = 'pilgrims';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'season_id',
        'branch_id',
        'agent_id',
        'first_name',
        'last_name',
        'father_name',
        'cnic',
        'country',
        'gender',
        'date_of_birth',
        'passport_issue_date',
        'city',
        'mobile_no',
        'mehram',
        'description',
        'pilgrim_image_name',
        'pilgrim_image_path',
        'passport_image_name',
        'passport_image_path',
        'phone',
        'email',
        'passport_no',
        'passport_expiry_date',
        'mahram_pilgrim_id',
        'medical_notes',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
