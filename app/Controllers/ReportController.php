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

        $activeSeasonId = $this->activeSeasonId();
        $expenseQuery = $db->table('expenses')->select('amount');
        if ($activeSeasonId !== null && $db->fieldExists('season_id', 'expenses')) {
            $expenseQuery->where('season_id', $activeSeasonId);
        }
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

        $stayStartExpr = $db->tableExists('package_hotel_stays')
            ? '(SELECT MIN(phs.check_in_date) FROM package_hotel_stays phs WHERE phs.package_id = b.package_id)'
            : '(SELECT MIN(ph.check_in_date) FROM package_hotels ph WHERE ph.package_id = b.package_id)';
        $stayEndExpr = $db->tableExists('package_hotel_stays')
            ? '(SELECT MAX(phs.check_out_date) FROM package_hotel_stays phs WHERE phs.package_id = b.package_id)'
            : '(SELECT MAX(ph.check_out_date) FROM package_hotels ph WHERE ph.package_id = b.package_id)';

        $query = $db->table('bookings b')
            ->select(
                "b.id,
                COALESCE(NULLIF(b.total_pilgrims, 0), 1) AS total_pilgrims,
                DATE_FORMAT(COALESCE(
                    " . $stayStartExpr . ",
                    (SELECT DATE(MIN(pf.arrival_at)) FROM package_flights pf WHERE pf.package_id = b.package_id)
                ), '%Y-%m-%d') AS ksa_arrival_date,
                DATE_FORMAT(COALESCE(
                    " . $stayEndExpr . ",
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

    public function suppliersPayable(): string
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

        $suppliers = $db->table('suppliers s')
            ->select('s.id, s.supplier_code, s.supplier_name, s.supplier_type, s.opening_balance, s.is_active')
            ->orderBy('s.supplier_name', 'ASC')
            ->get()
            ->getResultArray();

        $ledgerQuery = $db->table('supplier_ledger_entries sle')
            ->select('sle.supplier_id, COALESCE(SUM(sle.debit_amount), 0) AS total_debit, COALESCE(SUM(sle.credit_amount), 0) AS total_credit', false)
            ->groupBy('sle.supplier_id');
        if ($filters['from_date'] !== '') {
            $ledgerQuery->where('sle.entry_date >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $ledgerQuery->where('sle.entry_date <=', $filters['to_date']);
        }
        $ledgerAggRows = $ledgerQuery->get()->getResultArray();

        $ledgerMap = [];
        foreach ($ledgerAggRows as $row) {
            $supplierId = (int) ($row['supplier_id'] ?? 0);
            if ($supplierId < 1) {
                continue;
            }
            $ledgerMap[$supplierId] = [
                'total_debit' => (float) ($row['total_debit'] ?? 0),
                'total_credit' => (float) ($row['total_credit'] ?? 0),
            ];
        }

        $payableRows = [];
        $totals = [
            'opening' => 0.0,
            'debit' => 0.0,
            'credit' => 0.0,
            'closing' => 0.0,
            'payable' => 0.0,
        ];

        foreach ($suppliers as $supplier) {
            $supplierId = (int) ($supplier['id'] ?? 0);
            $opening = (float) ($supplier['opening_balance'] ?? 0);
            $debit = (float) ($ledgerMap[$supplierId]['total_debit'] ?? 0);
            $credit = (float) ($ledgerMap[$supplierId]['total_credit'] ?? 0);
            $closing = $opening + $credit - $debit;

            $payableRows[] = [
                'supplier_id' => $supplierId,
                'supplier_code' => (string) ($supplier['supplier_code'] ?? ''),
                'supplier_name' => (string) ($supplier['supplier_name'] ?? ''),
                'supplier_type' => (string) ($supplier['supplier_type'] ?? ''),
                'is_active' => (int) ($supplier['is_active'] ?? 0),
                'opening_balance' => $opening,
                'total_debit' => $debit,
                'total_credit' => $credit,
                'closing_balance' => $closing,
                'payable_balance' => max(0, $closing),
            ];

            $totals['opening'] += $opening;
            $totals['debit'] += $debit;
            $totals['credit'] += $credit;
            $totals['closing'] += $closing;
            $totals['payable'] += max(0, $closing);
        }

        usort($payableRows, static function (array $a, array $b): int {
            return (float) ($b['payable_balance'] ?? 0) <=> (float) ($a['payable_balance'] ?? 0);
        });

        return view('portal/reports/suppliers_payable', [
            'title' => 'HJMS ERP | Supplier Payables',
            'headerTitle' => 'Business Reports',
            'activePage' => 'reports',
            'userEmail' => (string) session('user_email'),
            'filters' => $filters,
            'filterErrors' => $filterErrors,
            'rows' => $payableRows,
            'totals' => $totals,
        ]);
    }

    public function expensesByCategory(): string
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

        $categoryQuery = $db->table('expenses e')
            ->select('ec.name AS category, COUNT(*) AS expense_count, COALESCE(SUM(e.amount), 0) AS category_total', false)
            ->join('expense_categories ec', 'ec.id = e.expense_category_id', 'left')
            ->groupBy('e.expense_category_id')
            ->groupBy('ec.name');

        if ($seasonId !== null && $this->tableHasColumn($db, 'expenses', 'season_id')) {
            $categoryQuery->where('e.season_id', $seasonId);
        }
        if ($this->tableHasColumn($db, 'expenses', 'status')) {
            $categoryQuery->where('e.status !=', 'voided');
        }
        if ($filters['from_date'] !== '') {
            $categoryQuery->where('e.expense_date >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $categoryQuery->where('e.expense_date <=', $filters['to_date']);
        }

        $categoryRows = $categoryQuery->orderBy('category_total', 'DESC')->get()->getResultArray();

        $totalExpense = 0.0;
        foreach ($categoryRows as $row) {
            $totalExpense += (float) ($row['category_total'] ?? 0);
        }

        $categoryData = [];
        foreach ($categoryRows as $row) {
            $categoryAmount = (float) ($row['category_total'] ?? 0);
            $categoryData[] = [
                'category' => (string) ($row['category'] ?? 'Uncategorized'),
                'expense_count' => (int) ($row['expense_count'] ?? 0),
                'category_total' => $categoryAmount,
                'percentage' => $totalExpense > 0 ? round(($categoryAmount / $totalExpense) * 100, 2) : 0,
            ];
        }

        // Get detailed expense listing
        $detailedQuery = $db->table('expenses e')
            ->select('e.id, e.expense_date, ec.name AS category, e.amount, e.paid_to, e.note, e.status')
            ->join('expense_categories ec', 'ec.id = e.expense_category_id', 'left')
            ->orderBy('e.expense_date', 'DESC')
            ->orderBy('e.id', 'DESC');

        if ($seasonId !== null && $this->tableHasColumn($db, 'expenses', 'season_id')) {
            $detailedQuery->where('e.season_id', $seasonId);
        }
        if ($this->tableHasColumn($db, 'expenses', 'status')) {
            $detailedQuery->where('e.status !=', 'voided');
        }
        if ($filters['from_date'] !== '') {
            $detailedQuery->where('e.expense_date >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $detailedQuery->where('e.expense_date <=', $filters['to_date']);
        }

        $detailedRows = $detailedQuery->limit(200)->get()->getResultArray();

        return view('portal/reports/expenses_by_category', [
            'title' => 'HJMS ERP | Expenses by Category',
            'headerTitle' => 'Business Reports',
            'activePage' => 'reports',
            'userEmail' => (string) session('user_email'),
            'filters' => $filters,
            'filterErrors' => $filterErrors,
            'categoryData' => $categoryData,
            'totalExpense' => $totalExpense,
            'detailedRows' => $detailedRows,
        ]);
    }

    public function profitLoss(): string
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

        $salesQuery = $db->table('sales')->select('COALESCE(SUM(amount), 0) AS total_amount', false);
        if ($seasonId !== null && $this->tableHasColumn($db, 'sales', 'season_id')) {
            $salesQuery->where('season_id', $seasonId);
        }
        if ($this->tableHasColumn($db, 'sales', 'status')) {
            $salesQuery->where('status !=', 'voided');
        }
        if ($filters['from_date'] !== '') {
            $salesQuery->where('sale_date >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $salesQuery->where('sale_date <=', $filters['to_date']);
        }
        $salesAmount = (float) (($salesQuery->get()->getRowArray()['total_amount'] ?? 0));

        $bookingQuery = $db->table('bookings')->select('COALESCE(SUM(total_amount), 0) AS total_amount', false);
        if ($seasonId !== null && $this->tableHasColumn($db, 'bookings', 'season_id')) {
            $bookingQuery->where('season_id', $seasonId);
        }
        if ($this->tableHasColumn($db, 'bookings', 'status')) {
            $bookingQuery->where('status !=', 'cancelled');
        }
        if ($filters['from_date'] !== '') {
            $bookingQuery->where('DATE(created_at) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $bookingQuery->where('DATE(created_at) <=', $filters['to_date']);
        }
        $packageAmount = (float) (($bookingQuery->get()->getRowArray()['total_amount'] ?? 0));

        $expenseQuery = $db->table('expenses')->select('COALESCE(SUM(amount), 0) AS total_amount', false);
        if ($seasonId !== null && $this->tableHasColumn($db, 'expenses', 'season_id')) {
            $expenseQuery->where('season_id', $seasonId);
        }
        if ($this->tableHasColumn($db, 'expenses', 'status')) {
            $expenseQuery->where('status !=', 'voided');
        }
        if ($filters['from_date'] !== '') {
            $expenseQuery->where('expense_date >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $expenseQuery->where('expense_date <=', $filters['to_date']);
        }
        $expenseAmount = (float) (($expenseQuery->get()->getRowArray()['total_amount'] ?? 0));

        $suppliers = $db->table('suppliers s')
            ->select('s.id, s.opening_balance')
            ->get()
            ->getResultArray();

        $supplierLedgerQuery = $db->table('supplier_ledger_entries sle')
            ->select('sle.supplier_id, COALESCE(SUM(sle.debit_amount), 0) AS total_debit, COALESCE(SUM(sle.credit_amount), 0) AS total_credit', false)
            ->groupBy('sle.supplier_id');
        if ($filters['from_date'] !== '') {
            $supplierLedgerQuery->where('sle.entry_date >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $supplierLedgerQuery->where('sle.entry_date <=', $filters['to_date']);
        }
        $supplierLedgerRows = $supplierLedgerQuery->get()->getResultArray();

        $supplierLedgerMap = [];
        foreach ($supplierLedgerRows as $row) {
            $supplierId = (int) ($row['supplier_id'] ?? 0);
            if ($supplierId < 1) {
                continue;
            }

            $supplierLedgerMap[$supplierId] = [
                'total_debit' => (float) ($row['total_debit'] ?? 0),
                'total_credit' => (float) ($row['total_credit'] ?? 0),
            ];
        }

        $purchaseAmount = 0.0;
        foreach ($suppliers as $supplier) {
            $supplierId = (int) ($supplier['id'] ?? 0);
            if ($supplierId < 1) {
                continue;
            }

            $opening = (float) ($supplier['opening_balance'] ?? 0);
            $debit = (float) ($supplierLedgerMap[$supplierId]['total_debit'] ?? 0);
            $credit = (float) ($supplierLedgerMap[$supplierId]['total_credit'] ?? 0);
            $closing = $opening + $credit - $debit;

            $purchaseAmount += max(0, $closing);
        }

        $totalSales = $salesAmount + $packageAmount;
        $grossProfit = $totalSales - $purchaseAmount;
        $netProfit = $grossProfit - $expenseAmount;

        return view('portal/reports/profit_loss', [
            'title' => 'HJMS ERP | Profit & Loss',
            'headerTitle' => 'Business Reports',
            'activePage' => 'reports',
            'userEmail' => (string) session('user_email'),
            'filters' => $filters,
            'filterErrors' => $filterErrors,
            'salesAmount' => $salesAmount,
            'packageAmount' => $packageAmount,
            'totalSales' => $totalSales,
            'purchaseAmount' => $purchaseAmount,
            'grossProfit' => $grossProfit,
            'expenseAmount' => $expenseAmount,
            'netProfit' => $netProfit,
        ]);
    }

    /**
     * How many visas, tickets, hotel stays, transport legs and packages were sold.
     */
    public function saleComponents(): string
    {
        $componentTypes = ['visa' => 'Visa', 'ticket' => 'Ticket', 'hotel' => 'Hotel', 'transport' => 'Transport', 'package' => 'Package', 'general' => 'General'];

        $filters = [
            'from_date' => (string) $this->request->getGet('from_date'),
            'to_date'   => (string) $this->request->getGet('to_date'),
            'component' => (string) $this->request->getGet('component'),
        ];

        $filterErrors = [];
        if (! $this->validateData($filters, [
            'from_date' => 'permit_empty|valid_date[Y-m-d]',
            'to_date'   => 'permit_empty|valid_date[Y-m-d]',
            'component' => 'permit_empty|in_list[visa,ticket,hotel,transport,package,general]',
        ])) {
            $filterErrors = $this->validator->getErrors();
            $filters = ['from_date' => '', 'to_date' => '', 'component' => ''];
        }

        $db = db_connect();
        $seasonId = $this->activeSeasonId();
        $hasItems = $db->tableExists('sale_items');
        $itemsHavePkr = $hasItems && $this->tableHasColumn($db, 'sale_items', 'amount_pkr');
        $salesHavePkr = $this->tableHasColumn($db, 'sales', 'amount_pkr');
        $itemAmountPkr = $itemsHavePkr ? 'si.amount_pkr' : 'si.amount';
        $saleAmountPkr = $salesHavePkr ? 'COALESCE(NULLIF(s.amount_pkr, 0), s.amount)' : 's.amount';

        $applySaleFilters = static function ($query) use ($filters, $seasonId): void {
            $query->where('s.status !=', 'voided');
            if ($seasonId !== null) {
                $query->where('s.season_id', $seasonId);
            }
            if ($filters['from_date'] !== '') {
                $query->where('DATE(s.sale_date) >=', $filters['from_date']);
            }
            if ($filters['to_date'] !== '') {
                $query->where('DATE(s.sale_date) <=', $filters['to_date']);
            }
        };

        $summary = [];
        foreach ($componentTypes as $type => $label) {
            $summary[$type] = ['label' => $label, 'count' => 0, 'total_pkr' => 0.0, 'currencies' => []];
        }

        if ($hasItems) {
            $itemsQuery = $db->table('sale_items si')
                ->select('si.item_type, si.currency, COUNT(*) AS item_count, COALESCE(SUM(' . $itemAmountPkr . '), 0) AS total_pkr', false)
                ->join('sales s', 's.id = si.sale_id', 'inner')
                ->groupBy('si.item_type, si.currency');
            $applySaleFilters($itemsQuery);
            if ($filters['component'] !== '') {
                $itemsQuery->where('si.item_type', $filters['component']);
            }

            foreach ($itemsQuery->get()->getResultArray() as $row) {
                $type = (string) ($row['item_type'] ?? '');
                if (! isset($summary[$type])) {
                    continue;
                }
                $currency = strtoupper((string) ($row['currency'] ?? 'PKR'));
                $summary[$type]['count'] += (int) ($row['item_count'] ?? 0);
                $summary[$type]['total_pkr'] += (float) ($row['total_pkr'] ?? 0);
                $summary[$type]['currencies'][$currency] = ($summary[$type]['currencies'][$currency] ?? 0) + (int) ($row['item_count'] ?? 0);
            }
        }

        // Sales created before component rows existed only carry the flat sale_type columns.
        $legacyQuery = $db->table('sales s')
            ->select('s.sale_type, s.currency, COUNT(*) AS item_count, COALESCE(SUM(' . $saleAmountPkr . '), 0) AS total_pkr', false)
            ->groupBy('s.sale_type, s.currency');
        $applySaleFilters($legacyQuery);
        if ($hasItems) {
            $legacyQuery->where('s.id NOT IN (SELECT DISTINCT sale_id FROM sale_items)', null, false);
        }
        if ($filters['component'] !== '') {
            $legacyQuery->where('s.sale_type', $filters['component']);
        }

        foreach ($legacyQuery->get()->getResultArray() as $row) {
            $type = (string) ($row['sale_type'] ?? '');
            if (! isset($summary[$type])) {
                continue;
            }
            $currency = strtoupper((string) ($row['currency'] ?? 'PKR'));
            $summary[$type]['count'] += (int) ($row['item_count'] ?? 0);
            $summary[$type]['total_pkr'] += (float) ($row['total_pkr'] ?? 0);
            $summary[$type]['currencies'][$currency] = ($summary[$type]['currencies'][$currency] ?? 0) + (int) ($row['item_count'] ?? 0);
        }

        $totalComponents = 0;
        $totalPkr = 0.0;
        foreach ($summary as $row) {
            $totalComponents += $row['count'];
            $totalPkr += $row['total_pkr'];
        }

        $detailLabel = static function (array $row): string {
            $type = (string) ($row['item_type'] ?? '');

            if ($type === 'visa') {
                $parts = array_filter([(string) ($row['visa_type'] ?? ''), (string) ($row['visa_no'] ?? '')]);
            } elseif ($type === 'ticket') {
                $parts = array_filter([(string) ($row['pnr'] ?? ''), (string) ($row['sector'] ?? ''), (string) ($row['ticket_no'] ?? '')]);
            } elseif ($type === 'hotel') {
                $nights = (int) ($row['no_of_nights'] ?? 0);
                $parts = array_filter([
                    (string) ($row['hotel_name'] ?? ''),
                    trim((string) ($row['check_in_date'] ?? '') . ' → ' . (string) ($row['check_out_date'] ?? ''), ' →'),
                    $nights > 0 ? $nights . ' night' . ($nights > 1 ? 's' : '') : '',
                    (string) ($row['room_type'] ?? ''),
                ]);
            } elseif ($type === 'transport') {
                $parts = array_filter([
                    (string) ($row['transport_type'] ?? ''),
                    (string) ($row['route'] ?? ''),
                    (int) ($row['is_ziarat'] ?? 0) === 1 ? 'Ziarat' : '',
                ]);
            } else {
                $parts = array_filter([(string) ($row['package_name'] ?? '')]);
            }

            return $parts === [] ? '-' : implode(' · ', $parts);
        };

        $detailRows = [];
        if ($hasItems) {
            $detailQuery = $db->table('sale_items si')
                ->select('s.id AS sale_id, s.invoice_no, s.sale_date, s.pax_name, si.item_type, si.amount, si.currency, si.amount_pkr, si.visa_type, si.visa_no, si.pnr, si.sector, si.ticket_no, si.hotel_name, si.check_in_date, si.check_out_date, si.no_of_nights, si.room_type, si.transport_type, si.route, si.is_ziarat, si.package_name')
                ->join('sales s', 's.id = si.sale_id', 'inner')
                ->orderBy('s.sale_date', 'DESC')
                ->orderBy('s.id', 'DESC');
            $applySaleFilters($detailQuery);
            if ($filters['component'] !== '') {
                $detailQuery->where('si.item_type', $filters['component']);
            }

            foreach ($detailQuery->limit(300)->get()->getResultArray() as $row) {
                $detailRows[] = [
                    'sale_id' => (int) ($row['sale_id'] ?? 0),
                    'invoice_no' => (string) ($row['invoice_no'] ?? ''),
                    'sale_date' => (string) ($row['sale_date'] ?? ''),
                    'pax_name' => (string) ($row['pax_name'] ?? ''),
                    'item_type' => (string) ($row['item_type'] ?? ''),
                    'detail' => $detailLabel($row),
                    'currency' => strtoupper((string) ($row['currency'] ?? 'PKR')),
                    'amount' => (float) ($row['amount'] ?? 0),
                    'amount_pkr' => (float) ($row['amount_pkr'] ?? $row['amount'] ?? 0),
                ];
            }
        }

        $legacyDetailQuery = $db->table('sales s')
            ->select('s.id AS sale_id, s.invoice_no, s.sale_date, s.pax_name, s.sale_type AS item_type, s.currency, ' . $saleAmountPkr . ' AS amount_pkr, s.visa_type, s.pnr, s.sector, s.ticket_no, s.hotel_name, s.checkin_date AS check_in_date, s.checkout_date AS check_out_date, s.no_of_nights, s.room_name AS room_type, s.package_name', false)
            ->orderBy('s.sale_date', 'DESC')
            ->orderBy('s.id', 'DESC');
        $applySaleFilters($legacyDetailQuery);
        if ($hasItems) {
            $legacyDetailQuery->where('s.id NOT IN (SELECT DISTINCT sale_id FROM sale_items)', null, false);
        }
        if ($filters['component'] !== '') {
            $legacyDetailQuery->where('s.sale_type', $filters['component']);
        }

        foreach ($legacyDetailQuery->limit(300)->get()->getResultArray() as $row) {
            $detailRows[] = [
                'sale_id' => (int) ($row['sale_id'] ?? 0),
                'invoice_no' => (string) ($row['invoice_no'] ?? ''),
                'sale_date' => (string) ($row['sale_date'] ?? ''),
                'pax_name' => (string) ($row['pax_name'] ?? ''),
                'item_type' => (string) ($row['item_type'] ?? ''),
                'detail' => $detailLabel($row),
                'currency' => 'PKR',
                'amount' => (float) ($row['amount_pkr'] ?? 0),
                'amount_pkr' => (float) ($row['amount_pkr'] ?? 0),
            ];
        }

        usort($detailRows, static function (array $a, array $b): int {
            return [$b['sale_date'], $b['sale_id']] <=> [$a['sale_date'], $a['sale_id']];
        });
        $detailRows = array_slice($detailRows, 0, 300);

        return view('portal/reports/sale_components', [
            'title' => 'HJMS ERP | Sales Components Report',
            'headerTitle' => 'Business Reports',
            'activePage' => 'reports',
            'userEmail' => (string) session('user_email'),
            'filters' => $filters,
            'filterErrors' => $filterErrors,
            'componentTypes' => $componentTypes,
            'summary' => $summary,
            'totalComponents' => $totalComponents,
            'totalPkr' => $totalPkr,
            'detailRows' => $detailRows,
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
