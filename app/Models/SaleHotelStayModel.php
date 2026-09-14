<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleHotelStayModel extends Model
{
    protected $table = 'sale_hotel_stays';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'sale_id',
        'hotel_name',
        'check_in_date',
        'check_out_date',
        'room_name',
        'room_qty',
        'created_at',
    ];
}
