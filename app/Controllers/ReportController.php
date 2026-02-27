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
            
            ->where('payment_type', 'payment')
            ->groupBy('channel');
        if ($filters['from_date'] !== '') {
            $channelQuery->where('DATE(payment_date) >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $channelQuery->where('DATE(payment_date) <=', $filters['to_date']);
        }
        $collectionByChannel = $channelQuery->get()->getResultArray();

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
        ]);
    }
}
