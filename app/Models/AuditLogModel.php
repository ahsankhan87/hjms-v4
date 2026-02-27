<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'user_email',
        'http_method',
        'request_path',
        'action_label',
        'status_code',
        'ip_address',
        'user_agent',
        'payload_json',
        'created_at',
    ];
}
