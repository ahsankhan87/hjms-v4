<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $seasonId = $this->activeSeasonId();
        $linkedAgentId = $this->linkedAgentId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $db = db_connect();

        $totalsQuery = $db->table('payments p')
            ->selectSum('p.amount', 'total_paid')
            ->where('p.season_id', $seasonId);

        if ($linkedAgentId !== null) {
            $totalsQuery
                ->join('bookings b', 'b.id = p.booking_id', 'inner')
                ->where('b.agent_id', $linkedAgentId);
        }

        $totalsRow = $totalsQuery->get()->getRowArray();

        $totalPaid = isset($totalsRow['total_paid']) ? (float) $totalsRow['total_paid'] : 0.0;
        $revenueCardAmount = $totalPaid;
        $revenueCardLabel = 'Total Revenue';
        $revenueCardSubtext = 'Total posted collections';

        if ($linkedAgentId !== null) {
            $agentRow = $db->table('agents')
                ->select('current_balance')
                ->where('id', $linkedAgentId)
                ->get()
                ->getRowArray();

            $revenueCardAmount = isset($agentRow['current_balance'])
                ? (float) $agentRow['current_balance']
                : 0.0;
            $revenueCardLabel = 'Closing Balance';
            $revenueCardSubtext = 'Your current ledger balance';
        }

        $recentPilgrimsQuery = $db->table('pilgrims')
            ->select("CONCAT(first_name, ' ', last_name) AS full_name, passport_no, phone", false)
            ->where('season_id', $seasonId);

        if ($linkedAgentId !== null) {
            $recentPilgrimsQuery->where('agent_id', $linkedAgentId);
        }

        $recentPilgrims = $recentPilgrimsQuery
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $recentPaymentsQuery = $db->table('payments p')
            ->select('b.booking_no AS booking_ref, p.amount, p.channel AS method')
            ->join('bookings b', 'b.id = p.booking_id', 'left')
            ->where('p.season_id', $seasonId);

        if ($linkedAgentId !== null) {
            $recentPaymentsQuery->where('b.agent_id', $linkedAgentId);
        }

        $recentPayments = $recentPaymentsQuery
            ->orderBy('p.id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $pilgrimsCountQuery = $db->table('pilgrims')->where('season_id', $seasonId);
        if ($linkedAgentId !== null) {
            $pilgrimsCountQuery->where('agent_id', $linkedAgentId);
        }

        $bookingsCountQuery = $db->table('bookings')->where('season_id', $seasonId);
        if ($linkedAgentId !== null) {
            $bookingsCountQuery->where('agent_id', $linkedAgentId);
        }

        $paymentsCountQuery = $db->table('payments p')->where('p.season_id', $seasonId);
        if ($linkedAgentId !== null) {
            $paymentsCountQuery
                ->join('bookings b', 'b.id = p.booking_id', 'inner')
                ->where('b.agent_id', $linkedAgentId);
        }

        $visasCountQuery = $db->table('visas v')->where('v.season_id', $seasonId);
        if ($linkedAgentId !== null) {
            $visasCountQuery
                ->join('pilgrims pl', 'pl.id = v.pilgrim_id', 'inner')
                ->where('pl.agent_id', $linkedAgentId);
        }

        $stats = [
            'pilgrims'  => (int) $pilgrimsCountQuery->countAllResults(),
            'bookings'  => (int) $bookingsCountQuery->countAllResults(),
            'payments'  => (int) $paymentsCountQuery->countAllResults(),
            'visas'     => (int) $visasCountQuery->countAllResults(),
            'totalPaid' => $totalPaid,
            'revenueCardAmount' => $revenueCardAmount,
            'revenueCardLabel' => $revenueCardLabel,
            'revenueCardSubtext' => $revenueCardSubtext,
        ];

        return view('portal/dashboard/index', [
            'title'          => 'HJMS ERP | Dashboard',
            'headerTitle'    => 'Operations Dashboard',
            'activePage'     => 'dashboard',
            'userEmail'      => (string) session('user_email'),
            'stats'          => $stats,
            'recentPilgrims' => $recentPilgrims,
            'recentPayments' => $recentPayments,
        ]);
    }
}
