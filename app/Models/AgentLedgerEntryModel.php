<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentLedgerEntryModel extends Model
{
    protected $table = 'agent_ledger_entries';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'agent_id',
        'entry_date',
        'entry_type',
        'debit_amount',
        'credit_amount',
        'reference_type',
        'reference_id',
        'description',
        'created_at',
    ];

    protected function initialize()
    {
        if ($this->tableExists('agent_ledger_entries')) {
            $this->table = 'agent_ledger_entries';
            return;
        }

        if ($this->tableExists('agent_ledger')) {
            $this->table = 'agent_ledger';
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            db_connect()->query('SELECT 1 FROM ' . $table . ' LIMIT 1');

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
