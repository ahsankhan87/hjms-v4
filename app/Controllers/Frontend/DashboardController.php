<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function customerDashboard()
    {
        $role = strtolower((string) session('role'));
        if ($role === 'agent') {
            return redirect()->to('/portal/agent/dashboard');
        }

        return view('frontend/portal/customer_dashboard', [
            'title' => 'Customer Dashboard | HJMS Travel',
            'name' => (string) session('name'),
            'role' => $role !== '' ? $role : 'customer',
        ]);
    }

    public function agentDashboard()
    {
        $role = strtolower((string) session('role'));
        if ($role !== 'agent') {
            return redirect()->to('/portal/dashboard')
                ->with('error', 'Agent dashboard is only available for B2B accounts.');
        }

        return view('frontend/portal/agent_dashboard', [
            'title' => 'Agent Dashboard | HJMS Travel',
            'name' => (string) session('name'),
            'role' => $role,
            'canSeeAgentPricing' => $role === 'agent',
        ]);
    }
}
