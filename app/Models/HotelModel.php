<?php

namespace App\Models;

use CodeIgniter\Model;

class HotelModel extends Model
{
    protected $table = 'hotels';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'city',
        'distance_m',
        'star_rating',
        'address',
        'image_url',
        'image_gallery',
        'video_url',
        'youtube_url',
        'map_url',
        'created_at',
        'updated_at',
    ];
}
