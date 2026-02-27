<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierLedgerEntryModel extends Model
{
    protected $table = 'supplier_ledger_entries';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'supplier_id',
        'entry_date',
        'entry_type',
        'debit_amount',
        'credit_amount',
        'reference_type',
        'reference_id',
        'description',
        'created_at',
    ];
}
