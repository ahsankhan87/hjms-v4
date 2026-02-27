<?php

namespace App\Models;

use CodeIgniter\Model;

class TransportLegModel extends Model
{
    protected $table = 'transport_legs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'transport_id',
        'seq_no',
        'from_code',
        'to_code',
        'is_ziarat',
        'ziarat_site',
        'notes',
        'created_at',
        'updated_at',
    ];
}
