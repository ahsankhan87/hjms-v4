<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ReportController extends BaseController
{
    public function index(): string
    {
        $filters = [
            'from_date' => (string) $this->request->getGet('from_date'),
            'to_date'   => (string) $this->request->getGet('to_date'),
        ];

        $filterErrors = [];
        if (! $this->validateData($filters, [
            'from_date' => 'permit_empty|valid_date[Y-m-d]',
            'to_date'   => 'permit_empty|valid_date[Y-m-d]',
        ])) {
            $filterErrors = $this->validator->getErrors();
            $filters = ['from_date' => '', 'to_date' => ''];
        }

        $db = db_connect();

        $dashboard = [
            'total_pilgrims'       => (int) $db->table('pilgrims')->countAllResults(),
            'total_bookings'       => (int) $db->table('bookings')->countAllResults(),
            'confirmed_bookings'   => (int) $db->table('bookings')->where('status', 'confirmed')->countAllResults(),
            'pending_visas'        => (int) $db->table('visas')->whereIn('status', ['draft', 'submitted'])->countAllResults(),
            'overdue_installments' => (int) $db->table('booking_installments')->where('status !=', 'paid')->where('due_date <', date('Y-m-d'))->countAllResults(),
        ];

        $paymentQuery = $db->table('payments')->select('payment_type, amount, payment_date');
        if ($filters['from_date'] !== '') {
            $paymentQuery->where('DATE(payment_date) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $paymentQuery->where('DATE(payment_date) <=', $filters['to_date']);
        }
        $payments = $paymentQuery->get()->getResultArray();

        $grossCollections = 0.0;
        $refunds = 0.0;
        foreach ($payments as $payment) {
            if (($payment['payment_type'] ?? 'payment') === 'refund') {
                $refunds += (float) $payment['amount'];
            } else {
                $grossCollections += (float) $payment['amount'];
            }
        }

        $expenseQuery = $db->table('expenses')->select('amount');
        if ($filters['from_date'] !== '') {
            $expenseQuery->where('expense_date >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $expenseQuery->where('expense_date <=', $filters['to_date']);
        }
        $expenseRows = $expenseQuery->get()->getResultArray();
        $totalExpenses = 0.0;
        foreach ($expenseRows as $row) {
            $totalExpenses += (float) $row['amount'];
        }

        $netCollections = $grossCollections - $refunds;
        $financialSummary = [
            'from_date'         => $filters['from_date'] !== '' ? $filters['from_date'] : null,
            'to_date'           => $filters['to_date'] !== '' ? $filters['to_date'] : null,
            'gross_collections' => round($grossCollections, 2),
            'refunds'           => round($refunds, 2),
            'net_collections'   => round($netCollections, 2),
            'total_expenses'    => round($totalExpenses, 2),
            'cash_surplus'      => round($netCollections - $totalExpenses, 2),
        ];

        $bookingStatusRows = $db->table('bookings')
            ->select('status, COUNT(*) AS booking_count, COALESCE(SUM(total_pilgrims),0) AS pilgrim_count')

            ->groupBy('status')
            ->get()
            ->getResultArray();

        $visaStatusRows = $db->table('visas')
            ->select('status, COUNT(*) AS visa_count')

            ->groupBy('status')
            ->get()
            ->getResultArray();

        $channelQuery = $db->table('payments')
            ->select('channel, COUNT(*) AS payment_count, COALESCE(SUM(amount),0) AS total_amount')
            ->where('status', 'posted')
            ->where('payment_type', 'payment')
            ->groupBy('channel');
        if ($filters['from_date'] !== '') {
            $channelQuery->where('DATE(payment_date) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $channelQuery->where('DATE(payment_date) <=', $filters['to_date']);
        }
        $collectionByChannel = $channelQuery->get()->getResultArray();

        $receivableQuery = $db->table('bookings b')
            ->select('b.agent_id, a.name AS agent_name, COUNT(*) AS booking_count, COALESCE(SUM(b.total_amount), 0) AS receivable_amount')
            ->join('agents a', 'a.id = b.agent_id', 'left')
            ->where('b.agent_id IS NOT NULL', null, false)
            ->where('b.status !=', 'cancelled')
            ->groupBy('b.agent_id')
            ->groupBy('a.name');
        if ($filters['from_date'] !== '') {
            $receivableQuery->where('DATE(b.created_at) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $receivableQuery->where('DATE(b.created_at) <=', $filters['to_date']);
        }
        $receivableRows = $receivableQuery->get()->getResultArray();

        $collectedQuery = $db->table('payments p')
            ->select('b.agent_id, a.name AS agent_name, COALESCE(SUM(CASE WHEN p.payment_type = "refund" THEN -p.amount ELSE p.amount END), 0) AS collected_amount')
            ->join('bookings b', 'b.id = p.booking_id', 'left')
            ->join('agents a', 'a.id = b.agent_id', 'left')
            ->where('p.status', 'posted')
            ->where('b.agent_id IS NOT NULL', null, false)
            ->groupBy('b.agent_id')
            ->groupBy('a.name');
        if ($filters['from_date'] !== '') {
            $collectedQuery->where('DATE(p.payment_date) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $collectedQuery->where('DATE(p.payment_date) <=', $filters['to_date']);
        }
        $collectedRows = $collectedQuery->get()->getResultArray();

        $agentMetrics = [];
        foreach ($receivableRows as $row) {
            $agentId = (int) ($row['agent_id'] ?? 0);
            if ($agentId < 1) {
                continue;
            }

            $agentMetrics[$agentId] = [
                'agent_id' => $agentId,
                'agent_name' => (string) ($row['agent_name'] ?? ('Agent #' . $agentId)),
                'booking_count' => (int) ($row['booking_count'] ?? 0),
                'receivable_amount' => (float) ($row['receivable_amount'] ?? 0),
                'collected_amount' => 0.0,
                'outstanding_amount' => 0.0,
            ];
        }

        foreach ($collectedRows as $row) {
            $agentId = (int) ($row['agent_id'] ?? 0);
            if ($agentId < 1) {
                continue;
            }

            if (! isset($agentMetrics[$agentId])) {
                $agentMetrics[$agentId] = [
                    'agent_id' => $agentId,
                    'agent_name' => (string) ($row['agent_name'] ?? ('Agent #' . $agentId)),
                    'booking_count' => 0,
                    'receivable_amount' => 0.0,
                    'collected_amount' => 0.0,
                    'outstanding_amount' => 0.0,
                ];
            }

            $agentMetrics[$agentId]['collected_amount'] = (float) ($row['collected_amount'] ?? 0);
        }

        $agentTotals = [
            'receivable' => 0.0,
            'collected' => 0.0,
            'outstanding' => 0.0,
            'active_agents' => 0,
        ];

        foreach ($agentMetrics as &$metric) {
            $metric['outstanding_amount'] = max(0, (float) $metric['receivable_amount'] - (float) $metric['collected_amount']);
            $agentTotals['receivable'] += (float) $metric['receivable_amount'];
            $agentTotals['collected'] += (float) $metric['collected_amount'];
            $agentTotals['outstanding'] += (float) $metric['outstanding_amount'];
            if ((float) $metric['receivable_amount'] > 0 || (float) $metric['collected_amount'] !== 0.0) {
                $agentTotals['active_agents']++;
            }
        }
        unset($metric);

        $agentLedgerRows = array_values($agentMetrics);
        usort($agentLedgerRows, static function (array $a, array $b) {
            $aOutstanding = (float) ($a['outstanding_amount'] ?? 0);
            $bOutstanding = (float) ($b['outstanding_amount'] ?? 0);
            if ($aOutstanding === $bOutstanding) {
                return strcmp((string) ($a['agent_name'] ?? ''), (string) ($b['agent_name'] ?? ''));
            }

            return $aOutstanding < $bOutstanding ? 1 : -1;
        });

        $topOutstandingAgents = array_slice($agentLedgerRows, 0, 10);

        return view('portal/reports/index', [
            'title'               => 'HJMS ERP | Reports',
            'headerTitle'         => 'Business Reports',
            'activePage'          => 'reports',
            'userEmail'           => (string) session('user_email'),
            'filters'             => $filters,
            'filterErrors'        => $filterErrors,
            'dashboard'           => $dashboard,
            'financialSummary'    => $financialSummary,
            'bookingStatus'       => $bookingStatusRows,
            'visaStatus'          => $visaStatusRows,
            'collectionByChannel' => $collectionByChannel,
            'agentTotals'         => $agentTotals,
            'topOutstandingAgents' => $topOutstandingAgents,
        ]);
    }
}
