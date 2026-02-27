<?php

namespace App\Models;

use CodeIgniter\Model;

class SeasonModel extends Model
{
    protected $table = 'seasons';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'year_start',
        'year_end',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
