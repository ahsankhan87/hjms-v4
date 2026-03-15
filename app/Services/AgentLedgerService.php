<?php

namespace App\Services;

class AgentLedgerService
{
    private $ledgerTableName = null;
    private $ledgerColumns = null;

    public function getAgentLedgerRows(int $agentId): array
    {
        $ledgerTable = $this->ledgerTable();
        if ($ledgerTable === '' || $agentId < 1) {
            return [];
        }

        $db = db_connect();
        $dateExpr = $this->hasLedgerColumn('entry_date') ? 'entry_date' : 'DATE(created_at)';
        $typeExpr = $this->hasLedgerColumn('entry_type') ? 'entry_type' : 'txn_type';
        $debitExpr = $this->debitColumn();
        $creditExpr = $this->creditColumn();
        $descExpr = $this->textColumn();
        $refTypeExpr = $this->hasLedgerColumn('reference_type') ? 'reference_type' : 'NULL';
        $refIdExpr = $this->hasLedgerColumn('reference_id') ? 'reference_id' : 'NULL';

        return $db->table($ledgerTable)
            ->select('id', false)
            ->select($dateExpr . ' AS entry_date', false)
            ->select($typeExpr . ' AS entry_type', false)
            ->select($debitExpr . ' AS debit_amount', false)
            ->select($creditExpr . ' AS credit_amount', false)
            ->select($descExpr . ' AS description', false)
            ->select($refTypeExpr . ' AS reference_type', false)
            ->select($refIdExpr . ' AS reference_id', false)
            ->where('agent_id', $agentId)
            ->orderBy($this->orderDateColumn(), 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function createManualEntry(array $payload): void
    {
        $amount = (float) ($payload['amount'] ?? 0);
        $entryType = (string) ($payload['entry_type'] ?? 'adjustment');

        $debit = 0.0;
        $credit = 0.0;
        if ($entryType === 'debit') {
            $debit = abs($amount);
        } elseif ($entryType === 'credit') {
            $credit = abs($amount);
        } elseif ($amount >= 0) {
            $debit = $amount;
        } else {
            $credit = abs($amount);
        }

        $this->insertLedgerEntry([
            'agent_id' => (int) ($payload['agent_id'] ?? 0),
            'entry_date' => (string) ($payload['entry_date'] ?? date('Y-m-d')),
            'entry_type' => $entryType,
            'debit_amount' => $debit,
            'credit_amount' => $credit,
            'description' => (string) ($payload['description'] ?? ''),
            'reference_type' => null,
            'reference_id' => null,
            'tenant_id' => $this->resolveTenantId((int) ($payload['agent_id'] ?? 0), null),
            'created_by' => session('user_id') ? (int) session('user_id') : null,
        ]);
    }

    public function removeBookingLedger(int $bookingId)
    {
        $ledgerTable = $this->ledgerTable();
        if ($bookingId < 1 || $ledgerTable === '') {
            return;
        }

        $db = db_connect();
        $rowsQuery = $db->table($ledgerTable)
            ->select('id, agent_id')
            ->groupStart()
            ->groupStart()
            ->where('reference_type', 'booking')
            ->where('reference_id', $bookingId)
            ->groupEnd();

        $textCol = $this->textColumn();
        if ($textCol !== 'NULL') {
            $rowsQuery->orGroupStart()
                ->where('reference_type', 'payment')
                ->like($textCol, '[booking:' . $bookingId . ']')
                ->groupEnd();
        }

        $rows = $rowsQuery->groupEnd()->get()->getResultArray();

        if ($rows === []) {
            return;
        }

        $affectedAgentIds = [];
        foreach ($rows as $row) {
            $affectedAgentIds[] = (int) ($row['agent_id'] ?? 0);
            $db->table($ledgerTable)->where('id', (int) ($row['id'] ?? 0))->delete();
        }

        $affectedAgentIds = array_values(array_unique(array_filter($affectedAgentIds)));
        foreach ($affectedAgentIds as $affectedAgentId) {
            $this->recalculateAgentBalance((int) $affectedAgentId);
        }
    }

    public function syncBookingLedger(int $bookingId, int $seasonId)
    {
        $this->syncBookingReceivable($bookingId, $seasonId);
        $this->syncBookingPayments($bookingId, $seasonId);
    }

    public function syncBookingReceivable(int $bookingId, int $seasonId)
    {
        if ($bookingId < 1 || $seasonId < 1 || ! $this->ledgerTableExists()) {
            return;
        }

        $db = db_connect();
        $booking = $db->table('bookings')
            ->select('id, season_id, tenant_id, agent_id, status, total_amount, pricing_tier, booking_no, updated_at')
            ->where('id', $bookingId)
            ->where('season_id', $seasonId)
            ->get()
            ->getRowArray();

        if (empty($booking)) {
            $this->removeBookingLedger($bookingId);
            return;
        }

        $ledgerTable = $this->ledgerTable();
        $oldEntries = $db->table($ledgerTable)
            ->select('id, agent_id')
            ->where('reference_type', 'booking')
            ->where('reference_id', $bookingId)
            ->get()
            ->getResultArray();
        $affectedAgentIds = [];
        foreach ($oldEntries as $entry) {
            $affectedAgentIds[] = (int) ($entry['agent_id'] ?? 0);
        }
        $affectedAgentIds = array_values(array_unique(array_filter($affectedAgentIds)));

        $db->table($ledgerTable)
            ->where('reference_type', 'booking')
            ->where('reference_id', $bookingId)
            ->delete();

        $agentId = (int) ($booking['agent_id'] ?? 0);
        $status = (string) ($booking['status'] ?? 'draft');
        $totalAmount = (float) ($booking['total_amount'] ?? 0);

        if ($agentId < 1 || $status === 'cancelled' || $totalAmount <= 0) {
            foreach ($affectedAgentIds as $affectedAgentId) {
                $this->recalculateAgentBalance((int) $affectedAgentId);
            }
            return;
        }

        $description = 'Booking receivable for ' . ((string) ($booking['booking_no'] ?? ('#' . $bookingId)));
        $tier = trim((string) ($booking['pricing_tier'] ?? ''));
        if ($tier !== '') {
            $description .= ' [' . ucfirst($tier) . ']';
        }

        $entryDate = date('Y-m-d');
        if (!empty($booking['updated_at'])) {
            $entryDate = date('Y-m-d', strtotime((string) $booking['updated_at']));
        }

        $this->insertLedgerEntry([
            'agent_id' => $agentId,
            'entry_date' => $entryDate,
            'entry_type' => 'booking_receivable',
            'debit_amount' => $totalAmount,
            'credit_amount' => 0,
            'reference_type' => 'booking',
            'reference_id' => $bookingId,
            'description' => $description,
            'tenant_id' => $this->resolveTenantId($agentId, isset($booking['tenant_id']) ? (int) $booking['tenant_id'] : null),
            'created_by' => null,
        ]);

        $affectedAgentIds[] = $agentId;
        $affectedAgentIds = array_values(array_unique(array_filter($affectedAgentIds)));
        foreach ($affectedAgentIds as $affectedAgentId) {
            $this->recalculateAgentBalance((int) $affectedAgentId);
        }
    }

    public function syncBookingPayments(int $bookingId, int $seasonId)
    {
        $ledgerTable = $this->ledgerTable();
        if ($bookingId < 1 || $seasonId < 1 || $ledgerTable === '') {
            return;
        }

        $db = db_connect();
        $booking = $db->table('bookings')
            ->select('id, season_id, tenant_id, agent_id, booking_no')
            ->where('id', $bookingId)
            ->where('season_id', $seasonId)
            ->get()
            ->getRowArray();

        if (empty($booking)) {
            $this->removeBookingLedger($bookingId);
            return;
        }

        $oldPaymentEntriesQuery = $db->table($ledgerTable)
            ->select('id, agent_id')
            ->where('reference_type', 'payment');

        $textCol = $this->textColumn();
        if ($textCol !== 'NULL') {
            $oldPaymentEntriesQuery->like($textCol, '[booking:' . $bookingId . ']');
        }

        $oldPaymentEntries = $oldPaymentEntriesQuery->get()->getResultArray();

        $affectedAgentIds = [];
        foreach ($oldPaymentEntries as $entry) {
            $affectedAgentIds[] = (int) ($entry['agent_id'] ?? 0);
            $db->table($ledgerTable)->where('id', (int) ($entry['id'] ?? 0))->delete();
        }

        $agentId = (int) ($booking['agent_id'] ?? 0);
        if ($agentId < 1) {
            $affectedAgentIds = array_values(array_unique(array_filter($affectedAgentIds)));
            foreach ($affectedAgentIds as $affectedAgentId) {
                $this->recalculateAgentBalance((int) $affectedAgentId);
            }
            return;
        }

        $paymentRows = $db->table('payments')
            ->where('booking_id', $bookingId)
            ->where('season_id', $seasonId)
            ->where('status', 'posted')
            ->orderBy('payment_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($paymentRows as $payment) {
            $amount = (float) ($payment['amount'] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            $isRefund = ((string) ($payment['payment_type'] ?? 'payment')) === 'refund';
            $entryDate = !empty($payment['payment_date'])
                ? date('Y-m-d', strtotime((string) $payment['payment_date']))
                : date('Y-m-d');

            $this->insertLedgerEntry([
                'agent_id' => $agentId,
                'entry_date' => $entryDate,
                'entry_type' => $isRefund ? 'refund' : 'payment_received',
                'debit_amount' => $isRefund ? $amount : 0,
                'credit_amount' => $isRefund ? 0 : $amount,
                'reference_type' => 'payment',
                'reference_id' => (int) ($payment['id'] ?? 0),
                'description' => ($isRefund ? 'Refund' : 'Payment received') . ' for ' . (string) ($booking['booking_no'] ?? ('#' . $bookingId)) . ' [booking:' . $bookingId . ']',
                'tenant_id' => $this->resolveTenantId($agentId, isset($booking['tenant_id']) ? (int) $booking['tenant_id'] : null),
                'created_by' => null,
            ]);
        }

        $affectedAgentIds[] = $agentId;
        $affectedAgentIds = array_values(array_unique(array_filter($affectedAgentIds)));
        foreach ($affectedAgentIds as $affectedAgentId) {
            $this->recalculateAgentBalance((int) $affectedAgentId);
        }
    }

    public function recalculateAgentBalance(int $agentId)
    {
        $ledgerTable = $this->ledgerTable();
        if ($agentId < 1 || $ledgerTable === '') {
            return;
        }

        $db = db_connect();
        $agent = $db->table('agents')->select('id, credit_limit')->where('id', $agentId)->get()->getRowArray();
        if (empty($agent)) {
            return;
        }

        $debitCol = $this->debitColumn();
        $creditCol = $this->creditColumn();

        $sum = $db->table($ledgerTable)
            ->select('COALESCE(SUM(' . $debitCol . ' - ' . $creditCol . '), 0) AS balance', false)
            ->where('agent_id', $agentId)
            ->get()
            ->getRowArray();

        $balance = (float) ($sum['balance'] ?? 0);
        $db->table('agents')->where('id', $agentId)->update([
            'current_balance' => $balance,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function ledgerTableExists(): bool
    {
        return $this->ledgerTable() !== '';
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

    private function debitColumn(): string
    {
        if ($this->hasLedgerColumn('debit_amount')) {
            return 'debit_amount';
        }

        if ($this->hasLedgerColumn('debit')) {
            return 'debit';
        }

        return '0';
    }

    private function creditColumn(): string
    {
        if ($this->hasLedgerColumn('credit_amount')) {
            return 'credit_amount';
        }

        if ($this->hasLedgerColumn('credit')) {
            return 'credit';
        }

        return '0';
    }

    private function textColumn(): string
    {
        if ($this->hasLedgerColumn('description')) {
            return 'description';
        }

        if ($this->hasLedgerColumn('note')) {
            return 'note';
        }

        return 'NULL';
    }

    private function orderDateColumn(): string
    {
        if ($this->hasLedgerColumn('entry_date')) {
            return 'entry_date';
        }

        if ($this->hasLedgerColumn('created_at')) {
            return 'created_at';
        }

        return 'id';
    }

    private function resolveTenantId(int $agentId, $fallback = null)
    {
        if (! $this->hasLedgerColumn('tenant_id')) {
            return null;
        }

        if ($fallback !== null && $fallback > 0) {
            return $fallback;
        }

        if ($agentId > 0) {
            try {
                $agentRow = db_connect()->table('agents')->select('tenant_id')->where('id', $agentId)->get()->getRowArray();
                if (! empty($agentRow) && isset($agentRow['tenant_id']) && (int) $agentRow['tenant_id'] > 0) {
                    return (int) $agentRow['tenant_id'];
                }
            } catch (\Throwable $e) {
            }
        }

        if (session('tenant_id')) {
            return (int) session('tenant_id');
        }

        return 1;
    }

    private function insertLedgerEntry(array $entry): void
    {
        $ledgerTable = $this->ledgerTable();
        if ($ledgerTable === '') {
            return;
        }

        $insert = [];
        $entryType = (string) ($entry['entry_type'] ?? 'adjustment');

        if ($this->hasLedgerColumn('tenant_id')) {
            $insert['tenant_id'] = $this->resolveTenantId((int) ($entry['agent_id'] ?? 0), isset($entry['tenant_id']) ? (int) $entry['tenant_id'] : null);
        }

        $insert['agent_id'] = (int) ($entry['agent_id'] ?? 0);

        if ($this->hasLedgerColumn('entry_date')) {
            $insert['entry_date'] = (string) ($entry['entry_date'] ?? date('Y-m-d'));
        }

        if ($this->hasLedgerColumn('entry_type')) {
            $insert['entry_type'] = $entryType;
        } elseif ($this->hasLedgerColumn('txn_type')) {
            $insert['txn_type'] = $entryType;
        }

        if ($this->hasLedgerColumn('debit_amount')) {
            $insert['debit_amount'] = (float) ($entry['debit_amount'] ?? 0);
        } elseif ($this->hasLedgerColumn('debit')) {
            $insert['debit'] = (float) ($entry['debit_amount'] ?? 0);
        }

        if ($this->hasLedgerColumn('credit_amount')) {
            $insert['credit_amount'] = (float) ($entry['credit_amount'] ?? 0);
        } elseif ($this->hasLedgerColumn('credit')) {
            $insert['credit'] = (float) ($entry['credit_amount'] ?? 0);
        }

        if ($this->hasLedgerColumn('reference_type')) {
            $insert['reference_type'] = $entry['reference_type'] ?? null;
        }

        if ($this->hasLedgerColumn('reference_id')) {
            $insert['reference_id'] = $entry['reference_id'] ?? null;
        }

        if ($this->hasLedgerColumn('description')) {
            $insert['description'] = trim((string) ($entry['description'] ?? '')) !== '' ? trim((string) ($entry['description'] ?? '')) : null;
        } elseif ($this->hasLedgerColumn('note')) {
            $insert['note'] = trim((string) ($entry['description'] ?? '')) !== '' ? trim((string) ($entry['description'] ?? '')) : null;
        }

        if ($this->hasLedgerColumn('created_by')) {
            $insert['created_by'] = $entry['created_by'] ?? null;
        }

        if ($this->hasLedgerColumn('created_at')) {
            $timePart = date('H:i:s');
            $entryDate = (string) ($entry['entry_date'] ?? date('Y-m-d'));
            $insert['created_at'] = $entryDate . ' ' . $timePart;
        }

        db_connect()->table($ledgerTable)->insert($insert);
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
