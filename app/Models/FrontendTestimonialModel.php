<?php

namespace App\Models;

use CodeIgniter\Model;

class FrontendTestimonialModel extends Model
{
    protected $table = 'frontend_testimonials';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'role',
        'message',
        'rating',
        'is_active',
        'sort_order',
        'created_at',
    ];
}

