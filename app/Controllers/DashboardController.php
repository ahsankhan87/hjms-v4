<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/app/seasons')->with('error', 'Please create and activate a season first.');
        }

        $db = db_connect();

        $totalsRow = $db->table('payments')
            ->selectSum('amount', 'total_paid')
            ->where('season_id', $seasonId)
            ->get()
            ->getRowArray();

        $totalPaid = isset($totalsRow['total_paid']) ? (float) $totalsRow['total_paid'] : 0.0;

        $recentPilgrims = $db->table('pilgrims')
            ->select("CONCAT(first_name, ' ', last_name) AS full_name, passport_no, phone", false)
            ->where('season_id', $seasonId)
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $recentPayments = $db->table('payments p')
            ->select('b.booking_no AS booking_ref, p.amount, p.channel AS method')
            ->join('bookings b', 'b.id = p.booking_id', 'left')
            ->where('p.season_id', $seasonId)
            ->orderBy('p.id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $stats = [
            'pilgrims'  => (int) $db->table('pilgrims')->where('season_id', $seasonId)->countAllResults(),
            'bookings'  => (int) $db->table('bookings')->where('season_id', $seasonId)->countAllResults(),
            'payments'  => (int) $db->table('payments')->where('season_id', $seasonId)->countAllResults(),
            'visas'     => (int) $db->table('visas')->where('season_id', $seasonId)->countAllResults(),
            'totalPaid' => $totalPaid,
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
