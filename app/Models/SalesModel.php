<?php

namespace App\Models;

class SalesModel extends SeasonScopedModel
{
    protected $table = 'sales';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'season_id',
        'sale_type',
        'invoice_no',
        'sales_category_id',
        'sale_date',
        'customer_type',
        'agent_id',
        'pilgrim_id',
        'pilgrim_ids',
        'pax_name',
        'customer_name',
        'amount',
        'payment_method',
        'reference_no',
        'visa_type',
        'visa_agency',
        'hotel_name',
        'checkin_date',
        'checkout_date',
        'no_of_nights',
        'room_name',
        'room_qty',
        'pnr',
        'sector',
        'ticket_no',
        'package_name',
        'package_details',
        'attachment',
        'attachment2',
        'attachment3',
        'note',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
