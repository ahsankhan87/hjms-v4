<?php

namespace App\Services;

class SalesLedgerService
{
    private $ledgerTableName = null;
    private $ledgerColumns = null;
    const AUTO_ENTRY_TYPES = ['sale_receivable'];

    public function syncSaleLedger(int $saleId, int $seasonId): void
    {
        $ledgerTable = $this->ledgerTable();
        if ($saleId < 1 || $seasonId < 1 || $ledgerTable === '') {
            return;
        }

        $db = db_connect();
        $oldEntriesQuery = $db->table($ledgerTable)
            ->select('id, agent_id')
            ->where('reference_type', 'sale')
            ->where('reference_id', $saleId);

        if ($this->hasLedgerColumn('entry_type')) {
            $oldEntriesQuery->whereIn('entry_type', self::AUTO_ENTRY_TYPES);
        }

        $oldEntries = $oldEntriesQuery->get()->getResultArray();

        $affectedAgentIds = [];
        foreach ($oldEntries as $entry) {
            $affectedAgentIds[] = (int) ($entry['agent_id'] ?? 0);
            $db->table($ledgerTable)->where('id', (int) ($entry['id'] ?? 0))->delete();
        }

        $saleQuery = $db->table('sales s')
            ->select('s.id, s.season_id, s.sales_category_id, s.sale_type, s.sale_date, s.customer_type, s.agent_id, s.amount, s.status, s.reference_no, s.note, sc.name AS sales_category_name')
            ->join('sales_categories sc', 'sc.id = s.sales_category_id AND sc.season_id = s.season_id', 'left')
            ->where('s.id', $saleId)
            ->where('s.season_id', $seasonId)
            ->get();

        $sale = is_object($saleQuery) ? $saleQuery->getRowArray() : [];

        if (! empty($sale)) {
            $status = (string) ($sale['status'] ?? 'posted');
            $customerType = (string) ($sale['customer_type'] ?? 'walk_in');
            $amount = (float) ($sale['amount'] ?? 0);
            $agentId = (int) ($sale['agent_id'] ?? 0);

            // Sale receivable must remain tied to the sale itself. Payment recovery should
            // be recorded as separate ledger credit entries. Payments are handled separately
            // in the agent ledger/payment flow.
            if ($status !== 'voided' && $customerType === 'agent' && $agentId > 0 && $amount > 0) {
                $description = $this->buildSaleDescription($sale);
                $saleNote = trim((string) ($sale['note'] ?? ''));
                $txnType = trim((string) ($sale['sales_category_name'] ?? ''));
                if ($txnType === '') {
                    $txnType = ucfirst((string) ($sale['sale_type'] ?? 'sale'));
                }

                $this->insertLedgerEntry([
                    'agent_id' => $agentId,
                    'entry_date' => (string) ($sale['sale_date'] ?? date('Y-m-d')),
                    'entry_type' => 'sale_receivable',
                    'txn_type' => $txnType,
                    'debit_amount' => $amount,
                    'credit_amount' => 0,
                    'reference_type' => 'sale',
                    'reference_id' => $saleId,
                    'description' => $description,
                    'sale_note' => $saleNote,
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
        if ($this->hasLedgerColumn('txn_type')) {
            $row['txn_type'] = trim((string) ($payload['txn_type'] ?? '')) !== ''
                ? trim((string) ($payload['txn_type'] ?? ''))
                : (string) ($payload['entry_type'] ?? 'adjustment');
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
            $row['reference_type'] = (string) ($payload['reference_type'] ?? 'sale');
        }
        if ($this->hasLedgerColumn('reference_id')) {
            $row['reference_id'] = (int) ($payload['reference_id'] ?? 0);
        }
        if ($this->hasLedgerColumn('description')) {
            $row['description'] = (string) ($payload['description'] ?? '');
        }
        if ($this->hasLedgerColumn('note')) {
            $row['note'] = trim((string) ($payload['sale_note'] ?? '')) !== ''
                ? trim((string) ($payload['sale_note'] ?? ''))
                : (string) ($payload['description'] ?? '');
        }
        if ($this->hasLedgerColumn('decs')) {
            $row['decs'] = trim((string) ($payload['sale_note'] ?? '')) !== ''
                ? trim((string) ($payload['sale_note'] ?? ''))
                : (string) ($payload['description'] ?? '');
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

    private function buildSaleDescription(array $sale): string
    {
        $saleType = (string) ($sale['sale_type'] ?? '');
        $base = 'Sale receivable';

        if ($saleType === 'visa') {
            $base = 'Visa sale receivable';
        } elseif ($saleType === 'hotel') {
            $base = 'Hotel sale receivable';
        } elseif ($saleType === 'ticket') {
            $base = 'Ticket sale receivable';
        }

        $referenceNo = trim((string) ($sale['reference_no'] ?? ''));
        if ($referenceNo !== '') {
            $base .= ' [' . $referenceNo . ']';
        }

        $saleNote = trim((string) ($sale['note'] ?? ''));
        if ($saleNote === '') {
            return $base;
        }

        $saleNote = preg_replace('/\s+/', ' ', $saleNote) ?? $saleNote;
        if (strlen($saleNote) > 160) {
            $saleNote = substr($saleNote, 0, 157) . '...';
        }

        return $base . ' | Note: ' . $saleNote;
    }
}
