<?php

namespace App\Models;

class VisaModel extends SeasonScopedModel
{
    protected $table = 'visas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'season_id',
        'booking_id',
        'pilgrim_id',
        'visa_no',
        'visa_type',
        'status',
        'submission_date',
        'approval_date',
        'rejection_reason',
        'visa_file_name',
        'visa_file_path',
        'notes',
        'created_at',
        'updated_at',
    ];
}
