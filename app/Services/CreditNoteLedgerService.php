<?php

namespace App\Services;

use App\Services\AgentLedgerService;

class CreditNoteLedgerService
{
    private $ledgerTableName = null;
    private $ledgerColumns = null;

    public function syncCreditNoteLedger(int $creditNoteId, int $seasonId): void
    {
        $ledgerTable = $this->ledgerTable();
        if ($creditNoteId < 1 || $seasonId < 1 || $ledgerTable === '') {
            return;
        }

        $db = db_connect();
        $oldRows = $db->table($ledgerTable)
            ->select('id, agent_id')
            ->where('reference_type', 'credit_note')
            ->where('reference_id', $creditNoteId)
            ->get()
            ->getResultArray();

        $affectedAgentIds = [];
        foreach ($oldRows as $oldRow) {
            $affectedAgentIds[] = (int) ($oldRow['agent_id'] ?? 0);
            $db->table($ledgerTable)->where('id', (int) ($oldRow['id'] ?? 0))->delete();
        }

        $creditNote = $db->table('credit_notes')
            ->select('id, season_id, sale_type, sale_id, agent_id, invoice_no, sale_date, credit_note_date, amount, status')
            ->where('id', $creditNoteId)
            ->where('season_id', $seasonId)
            ->get()
            ->getRowArray();

        if (! empty($creditNote)) {
            $amount = (float) ($creditNote['amount'] ?? 0);
            $agentId = (int) ($creditNote['agent_id'] ?? 0);
            $status = (string) ($creditNote['status'] ?? 'posted');
            if ($agentId > 0 && $amount > 0 && $status !== 'voided') {
                $saleType = (string) ($creditNote['sale_type'] ?? 'visa');
                $invoiceNo = trim((string) ($creditNote['invoice_no'] ?? ''));
                $saleId = (int) ($creditNote['sale_id'] ?? 0);

                $description = 'Credit note';
                if ($invoiceNo !== '') {
                    $description .= ' ' . $invoiceNo;
                }
                if ($saleId > 0) {
                    $description .= ' against sale #' . $saleId;
                }
                $description .= ' [' . $saleType . ']';

                $entryDate = (string) ($creditNote['credit_note_date'] ?? '');
                if ($entryDate === '') {
                    $entryDate = (string) ($creditNote['sale_date'] ?? date('Y-m-d'));
                }

                $this->insertLedgerEntry([
                    'agent_id' => $agentId,
                    'entry_date' => $entryDate,
                    'entry_type' => 'sale_credit_note',
                    'debit_amount' => 0,
                    'credit_amount' => $amount,
                    'reference_type' => 'credit_note',
                    'reference_id' => $creditNoteId,
                    'description' => $description,
                    'created_by' => session('user_id') ? (int) session('user_id') : null,
                ]);

                $affectedAgentIds[] = $agentId;
            }
        }

        $affectedAgentIds = array_values(array_unique(array_filter($affectedAgentIds)));
        if ($affectedAgentIds === []) {
            return;
        }

        $agentLedgerService = new AgentLedgerService();
        foreach ($affectedAgentIds as $affectedAgentId) {
            $agentLedgerService->recalculateAgentBalance((int) $affectedAgentId);
        }
    }

    public function removeCreditNoteLedger(int $creditNoteId): void
    {
        $ledgerTable = $this->ledgerTable();
        if ($creditNoteId < 1 || $ledgerTable === '') {
            return;
        }

        $db = db_connect();
        $rows = $db->table($ledgerTable)
            ->select('id, agent_id')
            ->where('reference_type', 'credit_note')
            ->where('reference_id', $creditNoteId)
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return;
        }

        $affectedAgentIds = [];
        foreach ($rows as $row) {
            $affectedAgentIds[] = (int) ($row['agent_id'] ?? 0);
            $db->table($ledgerTable)->where('id', (int) ($row['id'] ?? 0))->delete();
        }

        $affectedAgentIds = array_values(array_unique(array_filter($affectedAgentIds)));
        $agentLedgerService = new AgentLedgerService();
        foreach ($affectedAgentIds as $affectedAgentId) {
            $agentLedgerService->recalculateAgentBalance((int) $affectedAgentId);
        }
    }

    private function insertLedgerEntry(array $payload): void
    {
        $ledgerTable = $this->ledgerTable();
        if ($ledgerTable === '') {
            return;
        }

        $row = [
            'agent_id' => (int) ($payload['agent_id'] ?? 0),
        ];

        if ($row['agent_id'] < 1) {
            return;
        }

        if ($this->hasLedgerColumn('entry_date')) {
            $row['entry_date'] = (string) ($payload['entry_date'] ?? date('Y-m-d'));
        }
        if ($this->hasLedgerColumn('entry_type')) {
            $row['entry_type'] = (string) ($payload['entry_type'] ?? 'adjustment');
        }
        if ($this->hasLedgerColumn('debit_amount')) {
            $row['debit_amount'] = (float) ($payload['debit_amount'] ?? 0);
        } elseif ($this->hasLedgerColumn('debit')) {
            $row['debit'] = (float) ($payload['debit_amount'] ?? 0);
        }
        if ($this->hasLedgerColumn('credit_amount')) {
            $row['credit_amount'] = (float) ($payload['credit_amount'] ?? 0);
        } elseif ($this->hasLedgerColumn('credit')) {
            $row['credit'] = (float) ($payload['credit_amount'] ?? 0);
        }
        if ($this->hasLedgerColumn('reference_type')) {
            $row['reference_type'] = (string) ($payload['reference_type'] ?? 'credit_note');
        }
        if ($this->hasLedgerColumn('reference_id')) {
            $row['reference_id'] = (int) ($payload['reference_id'] ?? 0);
        }
        if ($this->hasLedgerColumn('description')) {
            $row['description'] = (string) ($payload['description'] ?? '');
        }
        if ($this->hasLedgerColumn('created_by')) {
            $row['created_by'] = isset($payload['created_by']) ? (int) $payload['created_by'] : null;
        }
        if ($this->hasLedgerColumn('created_at')) {
            $row['created_at'] = date('Y-m-d H:i:s');
        }

        db_connect()->table($ledgerTable)->insert($row);
    }

    private function ledgerTable(): string
    {
        if (is_string($this->ledgerTableName)) {
            return $this->ledgerTableName;
        }

        if ($this->tableExists('agent_ledger_entries')) {
            $this->ledgerTableName = 'agent_ledger_entries';
            return $this->ledgerTableName;
        }

        if ($this->tableExists('agent_ledger')) {
            $this->ledgerTableName = 'agent_ledger';
            return $this->ledgerTableName;
        }

        $this->ledgerTableName = '';
        return $this->ledgerTableName;
    }

    private function ledgerColumns(): array
    {
        if (is_array($this->ledgerColumns)) {
            return $this->ledgerColumns;
        }

        $ledgerTable = $this->ledgerTable();
        if ($ledgerTable === '') {
            $this->ledgerColumns = [];
            return $this->ledgerColumns;
        }

        try {
            $this->ledgerColumns = db_connect()->getFieldNames($ledgerTable);
        } catch (\Throwable $e) {
            $this->ledgerColumns = [];
        }

        return $this->ledgerColumns;
    }

    private function hasLedgerColumn(string $column): bool
    {
        return in_array($column, $this->ledgerColumns(), true);
    }

    private function tableExists(string $table): bool
    {
        try {
            return db_connect()->tableExists($table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
