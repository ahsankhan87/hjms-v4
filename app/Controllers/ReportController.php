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

    public function ksaStatus(): string
    {
        $statusDate = (string) $this->request->getGet('status_date');
        if ($statusDate === '') {
            $statusDate = date('Y-m-d');
        }

        $errors = [];
        if (! $this->validateData(['status_date' => $statusDate], [
            'status_date' => 'required|valid_date[Y-m-d]',
        ])) {
            $errors = $this->validator->getErrors();
            $statusDate = date('Y-m-d');
        }

        $db = db_connect();
        $seasonId = $this->activeSeasonId();

        $query = $db->table('bookings b')
            ->select(
                "b.id,
                COALESCE(NULLIF(b.total_pilgrims, 0), 1) AS total_pilgrims,
                DATE_FORMAT(COALESCE(
                    (SELECT MIN(ph.check_in_date) FROM package_hotels ph WHERE ph.package_id = b.package_id),
                    (SELECT DATE(MIN(pf.arrival_at)) FROM package_flights pf WHERE pf.package_id = b.package_id)
                ), '%Y-%m-%d') AS ksa_arrival_date,
                DATE_FORMAT(COALESCE(
                    (SELECT MAX(ph.check_out_date) FROM package_hotels ph WHERE ph.package_id = b.package_id),
                    (SELECT DATE(MAX(pf.departure_at)) FROM package_flights pf WHERE pf.package_id = b.package_id)
                ), '%Y-%m-%d') AS ksa_return_date",
                false
            )
            ->where('b.status !=', 'cancelled');

        if ($seasonId !== null) {
            $query->where('b.season_id', $seasonId);
        }

        $rows = $query
            ->get()
            ->getResultArray();

        $metrics = [
            'arrival' => 0,
            'departure' => 0,
            'makkah_checkin' => 0,
            'makkah_checkout' => 0,
            'madinah_checkin' => 0,
            'madinah_checkout' => 0,
            'inside_ksa' => 0,
            'in_makkah' => 0,
            'in_madinah' => 0,
        ];

        foreach ($rows as $row) {
            $arrivalDate = (string) ($row['ksa_arrival_date'] ?? '');
            $returnDate = (string) ($row['ksa_return_date'] ?? '');
            if (! $this->isDateYmd($arrivalDate) || ! $this->isDateYmd($returnDate) || $arrivalDate > $returnDate) {
                continue;
            }

            $pilgrims = max(1, (int) ($row['total_pilgrims'] ?? 0));

            if ($arrivalDate === $statusDate) {
                $metrics['arrival'] += $pilgrims;
                $metrics['makkah_checkin'] += $pilgrims;
            }
            if ($returnDate === $statusDate) {
                $metrics['departure'] += $pilgrims;
                $metrics['madinah_checkout'] += $pilgrims;
            }

            if ($arrivalDate <= $statusDate && $returnDate >= $statusDate) {
                $metrics['inside_ksa'] += $pilgrims;

                $daysTotal = max(1, (int) ((strtotime($returnDate) - strtotime($arrivalDate)) / 86400) + 1);
                $makkahDays = max(1, (int) ceil($daysTotal * 0.55));
                $madinahStartTs = strtotime($arrivalDate . ' +' . $makkahDays . ' days');
                $madinahStart = date('Y-m-d', $madinahStartTs);

                if ($statusDate < $madinahStart) {
                    $metrics['in_makkah'] += $pilgrims;
                } else {
                    $metrics['in_madinah'] += $pilgrims;
                }

                if ($statusDate === $madinahStart) {
                    $metrics['makkah_checkout'] += $pilgrims;
                    $metrics['madinah_checkin'] += $pilgrims;
                }
            }
        }

        return view('portal/reports/ksa_status', [
            'title' => 'HJMS ERP | KSA Status Report',
            'headerTitle' => 'Business Reports',
            'activePage' => 'reports',
            'userEmail' => (string) session('user_email'),
            'statusDate' => $statusDate,
            'metrics' => $metrics,
            'errors' => $errors,
        ]);
    }

    public function finance(): string
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

        $paymentsQuery = $db->table('payments p')
            ->select('p.payment_no, p.payment_date, p.channel, p.payment_type, p.amount, p.status, b.booking_no, a.name AS agent_name')
            ->join('bookings b', 'b.id = p.booking_id', 'left')
            ->join('agents a', 'a.id = b.agent_id', 'left')
            ->where('p.status', 'posted')
            ->orderBy('p.payment_date', 'DESC')
            ->orderBy('p.id', 'DESC');
        if ($filters['from_date'] !== '') {
            $paymentsQuery->where('DATE(p.payment_date) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $paymentsQuery->where('DATE(p.payment_date) <=', $filters['to_date']);
        }
        $paymentRows = $paymentsQuery->get()->getResultArray();

        $grossCollections = 0.0;
        $refunds = 0.0;
        foreach ($paymentRows as $payment) {
            if ((string) ($payment['payment_type'] ?? 'payment') === 'refund') {
                $refunds += (float) ($payment['amount'] ?? 0);
            } else {
                $grossCollections += (float) ($payment['amount'] ?? 0);
            }
        }
        $netCollections = $grossCollections - $refunds;

        $channelQuery = $db->table('payments')
            ->select('channel, COUNT(*) AS payment_count, COALESCE(SUM(CASE WHEN payment_type = "refund" THEN -amount ELSE amount END), 0) AS net_amount', false)
            ->where('status', 'posted')
            ->groupBy('channel');
        if ($filters['from_date'] !== '') {
            $channelQuery->where('DATE(payment_date) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $channelQuery->where('DATE(payment_date) <=', $filters['to_date']);
        }
        $channelSummary = $channelQuery->get()->getResultArray();

        $receivableQuery = $db->table('bookings b')
            ->select('b.agent_id, a.name AS agent_name, COALESCE(SUM(b.total_amount), 0) AS receivable_amount')
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
            ->select('b.agent_id, a.name AS agent_name, COALESCE(SUM(CASE WHEN p.payment_type = "refund" THEN -p.amount ELSE p.amount END), 0) AS collected_amount', false)
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

        $agentSummaryMap = [];
        foreach ($receivableRows as $row) {
            $agentId = (int) ($row['agent_id'] ?? 0);
            if ($agentId < 1) {
                continue;
            }
            $agentSummaryMap[$agentId] = [
                'agent_name' => (string) ($row['agent_name'] ?? ('Agent #' . $agentId)),
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
            if (! isset($agentSummaryMap[$agentId])) {
                $agentSummaryMap[$agentId] = [
                    'agent_name' => (string) ($row['agent_name'] ?? ('Agent #' . $agentId)),
                    'receivable_amount' => 0.0,
                    'collected_amount' => 0.0,
                    'outstanding_amount' => 0.0,
                ];
            }
            $agentSummaryMap[$agentId]['collected_amount'] = (float) ($row['collected_amount'] ?? 0);
        }

        $agentSummary = array_values($agentSummaryMap);
        foreach ($agentSummary as &$item) {
            $item['outstanding_amount'] = max(0, (float) $item['receivable_amount'] - (float) $item['collected_amount']);
        }
        unset($item);
        usort($agentSummary, static function (array $a, array $b) {
            return (float) ($b['outstanding_amount'] ?? 0) <=> (float) ($a['outstanding_amount'] ?? 0);
        });

        return view('portal/reports/finance', [
            'title' => 'HJMS ERP | Finance Reports',
            'headerTitle' => 'Business Reports',
            'activePage' => 'reports',
            'userEmail' => (string) session('user_email'),
            'filters' => $filters,
            'filterErrors' => $filterErrors,
            'grossCollections' => $grossCollections,
            'refunds' => $refunds,
            'netCollections' => $netCollections,
            'channelSummary' => $channelSummary,
            'paymentRows' => $paymentRows,
            'agentSummary' => array_slice($agentSummary, 0, 20),
        ]);
    }

    public function operations(): string
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
        $seasonId = $this->activeSeasonId();

        $bookingStatusQuery = $db->table('bookings b')
            ->select('b.status, COUNT(*) AS booking_count, COALESCE(SUM(b.total_pilgrims), 0) AS pilgrim_count')
            ->groupBy('b.status');
        if ($seasonId !== null && $this->tableHasColumn($db, 'bookings', 'season_id')) {
            $bookingStatusQuery->where('b.season_id', $seasonId);
        }
        if ($filters['from_date'] !== '') {
            $bookingStatusQuery->where('DATE(b.created_at) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $bookingStatusQuery->where('DATE(b.created_at) <=', $filters['to_date']);
        }
        $bookingStatusRows = $bookingStatusQuery->get()->getResultArray();

        $tierMixQuery = $db->table('bookings b')
            ->select('b.pricing_tier, COUNT(*) AS booking_count, COALESCE(SUM(b.total_pilgrims), 0) AS pilgrim_count')
            ->where('b.pricing_tier IS NOT NULL', null, false)
            ->where('b.pricing_tier !=', '')
            ->groupBy('b.pricing_tier');
        if ($seasonId !== null && $this->tableHasColumn($db, 'bookings', 'season_id')) {
            $tierMixQuery->where('b.season_id', $seasonId);
        }
        if ($filters['from_date'] !== '') {
            $tierMixQuery->where('DATE(b.created_at) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $tierMixQuery->where('DATE(b.created_at) <=', $filters['to_date']);
        }
        $tierMixRows = $tierMixQuery->get()->getResultArray();

        $visaStatusQuery = $db->table('visas v')
            ->select('v.status, COUNT(*) AS visa_count')
            ->groupBy('v.status');
        if ($seasonId !== null && $this->tableHasColumn($db, 'visas', 'season_id')) {
            $visaStatusQuery->where('v.season_id', $seasonId);
        }
        if ($filters['from_date'] !== '') {
            $visaStatusQuery->where('DATE(v.submission_date) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $visaStatusQuery->where('DATE(v.submission_date) <=', $filters['to_date']);
        }
        $visaStatusRows = $visaStatusQuery->get()->getResultArray();

        $genderQuery = $db->table('pilgrims pl')
            ->select('COALESCE(NULLIF(LOWER(pl.gender), ""), "unknown") AS gender_key, COUNT(*) AS pilgrim_count', false)
            ->groupBy('gender_key');
        if ($seasonId !== null && $this->tableHasColumn($db, 'pilgrims', 'season_id')) {
            $genderQuery->where('pl.season_id', $seasonId);
        }
        if ($filters['from_date'] !== '') {
            $genderQuery->where('DATE(pl.created_at) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $genderQuery->where('DATE(pl.created_at) <=', $filters['to_date']);
        }
        $genderRows = $genderQuery->get()->getResultArray();

        $recentBookingsQuery = $db->table('bookings b')
            ->select('b.id, b.booking_no, b.status, b.pricing_tier, b.total_pilgrims, b.created_at, a.name AS agent_name, c.name AS company_name')
            ->join('agents a', 'a.id = b.agent_id', 'left')
            ->join('companies c', 'c.id = b.company_id', 'left')
            ->orderBy('b.id', 'DESC')
            ->limit(50);
        if ($seasonId !== null && $this->tableHasColumn($db, 'bookings', 'season_id')) {
            $recentBookingsQuery->where('b.season_id', $seasonId);
        }
        if ($filters['from_date'] !== '') {
            $recentBookingsQuery->where('DATE(b.created_at) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $recentBookingsQuery->where('DATE(b.created_at) <=', $filters['to_date']);
        }
        $recentBookings = $recentBookingsQuery->get()->getResultArray();

        $pendingVisaRows = $db->table('visas v')
            ->select('v.id, v.visa_no, v.status, v.submission_date, v.approval_date, p.first_name, p.last_name, b.booking_no')
            ->join('pilgrims p', 'p.id = v.pilgrim_id', 'left')
            ->join('bookings b', 'b.id = v.booking_id', 'left')
            ->whereIn('v.status', ['draft', 'submitted'])
            ->orderBy('v.submission_date', 'ASC')
            ->orderBy('v.id', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        $totals = [
            'total_bookings' => 0,
            'total_pilgrims' => 0,
            'pending_visas' => 0,
            'visa_approved' => 0,
        ];
        foreach ($bookingStatusRows as $row) {
            $totals['total_bookings'] += (int) ($row['booking_count'] ?? 0);
            $totals['total_pilgrims'] += (int) ($row['pilgrim_count'] ?? 0);
        }
        foreach ($visaStatusRows as $row) {
            $status = strtolower((string) ($row['status'] ?? ''));
            $count = (int) ($row['visa_count'] ?? 0);
            if ($status === 'approved') {
                $totals['visa_approved'] += $count;
            }
            if ($status === 'draft' || $status === 'submitted') {
                $totals['pending_visas'] += $count;
            }
        }

        return view('portal/reports/operations', [
            'title' => 'HJMS ERP | Operations Reports',
            'headerTitle' => 'Business Reports',
            'activePage' => 'reports',
            'userEmail' => (string) session('user_email'),
            'filters' => $filters,
            'filterErrors' => $filterErrors,
            'totals' => $totals,
            'bookingStatusRows' => $bookingStatusRows,
            'visaStatusRows' => $visaStatusRows,
            'tierMixRows' => $tierMixRows,
            'genderRows' => $genderRows,
            'recentBookings' => $recentBookings,
            'pendingVisaRows' => $pendingVisaRows,
        ]);
    }

    private function isDateYmd(string $value): bool
    {
        if ($value === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        return true;
    }

    private function tableHasColumn($db, string $table, string $column): bool
    {
        static $columnMap = [];

        if (! isset($columnMap[$table])) {
            $columnMap[$table] = array_map('strtolower', $db->getFieldNames($table));
        }

        return in_array(strtolower($column), $columnMap[$table], true);
    }
}
