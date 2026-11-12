<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleItemModel extends Model
{
    protected $table = 'sale_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'sale_id',
        'item_type',
        'sort_order',
        'description',
        'amount',
        'currency',
        'exchange_rate',
        'amount_pkr',
        'attachment',
        'supplier_id',
        'purchase_amount',
        'purchase_amount_pkr',
        'visa_type',
        'visa_no',
        'pnr',
        'sector',
        'ticket_no',
        'hotel_name',
        'check_in_date',
        'check_out_date',
        'no_of_nights',
        'room_qty',
        'room_type',
        'transport_id',
        'transport_type',
        'route',
        'is_ziarat',
        'package_id',
        'package_name',
        'departure_date',
        'arrival_date',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
