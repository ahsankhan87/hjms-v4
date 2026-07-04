<?php

namespace App\Models;

class CreditNoteModel extends SeasonScopedModel
{
    protected $table = 'credit_notes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'season_id',
        'sale_type',
        'sale_id',
        'agent_id',
        'invoice_no',
        'sale_date',
        'credit_note_date',
        'amount',
        'reason',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
