<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\FrontendUserModel;

class PortalController extends BaseController
{
    public function dashboard()
    {
        $user = $this->currentCustomer();
        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Please login to continue.');
        }

        $rows = $this->bookingBaseQuery((int) $user['id'])
            ->orderBy('id', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        $today = date('Y-m-d');
        $upcoming = [];
        $pendingCount = 0;
        $confirmedCount = 0;

        foreach ($rows as $row) {
            $status = strtolower((string) ($row['status'] ?? 'pending'));
            if ($status === 'pending') {
                $pendingCount++;
            }
            if ($status === 'confirmed') {
                $confirmedCount++;
            }

            $details = $this->decodeBookingDetails($row);
            $row['booking_type'] = 'package';
            $travelDate = (string) ($details['travel_date'] ?? $details['check_in'] ?? '');
            if ($travelDate !== '' && $travelDate >= $today && in_array($status, ['pending', 'confirmed'], true)) {
                $row['parsed_details'] = $details;
                $row['display_date'] = $travelDate;
                $upcoming[] = $row;
            }
        }

        usort($upcoming, static fn(array $a, array $b): int => strcmp((string) ($a['display_date'] ?? ''), (string) ($b['display_date'] ?? '')));
        $upcoming = array_slice($upcoming, 0, 5);

        return view('frontend/portal/dashboard', [
            'title' => 'Customer Portal | HJMS Travel',
            'portalActive' => 'dashboard',
            'user' => $user,
            'upcoming' => $upcoming,
            'summary' => [
                'total' => count($rows),
                'pending' => $pendingCount,
                'confirmed' => $confirmedCount,
            ],
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function bookings()
    {
        $user = $this->currentCustomer();
        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Please login to continue.');
        }

        $rows = $this->bookingBaseQuery((int) $user['id'])
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $details = $this->decodeBookingDetails($row);
            $row['booking_type'] = 'package';
            $row['item_id'] = (int) ($row['package_id'] ?? 0);
            $row['parsed_details'] = $details;
            $row['display_item'] = (string) ($details['item_name'] ?? ('Item #' . (int) ($row['item_id'] ?? 0)));
            $row['display_date'] = (string) ($details['travel_date'] ?? $details['check_in'] ?? ($row['created_at'] ?? ''));
        }

        return view('frontend/portal/bookings', [
            'title' => 'My Bookings | HJMS Travel',
            'portalActive' => 'bookings',
            'user' => $user,
            'rows' => $rows,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function bookingShow(int $id)
    {
        $user = $this->currentCustomer();
        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Please login to continue.');
        }

        if ($id < 1) {
            return redirect()->to('/portal/bookings')->with('error', 'Booking not found.');
        }

        $row = $this->bookingBaseQuery((int) $user['id'])
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            return redirect()->to('/portal/bookings')->with('error', 'Booking not found.');
        }

        $details = $this->decodeBookingDetails($row);
        $row['booking_type'] = 'package';
        $row['item_id'] = (int) ($row['package_id'] ?? 0);
        $timeline = $this->buildStatusTimeline((string) ($row['status'] ?? 'pending'), (string) ($row['created_at'] ?? ''));

        return view('frontend/portal/booking_show', [
            'title' => 'Booking Details | HJMS Travel',
            'portalActive' => 'bookings',
            'user' => $user,
            'booking' => $row,
            'details' => $details,
            'timeline' => $timeline,
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function profile()
    {
        $user = $this->currentCustomer();
        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Please login to continue.');
        }

        return view('frontend/portal/profile', [
            'title' => 'My Profile | HJMS Travel',
            'portalActive' => 'profile',
            'user' => $user,
            'errors' => session()->getFlashdata('errors') ?? [],
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function updateProfile()
    {
        $user = $this->currentCustomer();
        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Please login to continue.');
        }

        $name = trim((string) $this->request->getPost('name'));
        $phone = trim((string) $this->request->getPost('phone'));
        $currentPassword = (string) $this->request->getPost('current_password');
        $newPassword = (string) $this->request->getPost('new_password');
        $confirmPassword = (string) $this->request->getPost('confirm_password');

        $rules = [
            'name' => 'required|min_length[2]|max_length[160]',
            'phone' => 'permit_empty|max_length[40]',
        ];
        $payload = [
            'name' => $name,
            'phone' => $phone,
        ];

        $isPasswordChange = $currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '';
        if ($isPasswordChange) {
            $rules['current_password'] = 'required|min_length[8]';
            $rules['new_password'] = 'required|min_length[8]';
            $rules['confirm_password'] = 'required|matches[new_password]';
            $payload['current_password'] = $currentPassword;
            $payload['new_password'] = $newPassword;
            $payload['confirm_password'] = $confirmPassword;
        }

        if (! $this->validateData($payload, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $update = [
            'name' => $name,
            'phone' => $phone !== '' ? $phone : null,
        ];

        if ($isPasswordChange) {
            if (! password_verify($currentPassword, (string) ($user['password'] ?? ''))) {
                return redirect()->back()->withInput()->with('error', 'Current password is incorrect.');
            }
            $update['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        (new FrontendUserModel())->update((int) $user['id'], $update);
        session()->set('name', $name);

        return redirect()->to('/portal/profile')->with('success', 'Profile updated successfully.');
    }

    private function currentCustomer(): ?array
    {
        $userId = (int) session('user_id');
        $role = strtolower((string) session('role'));
        if ($userId < 1 || $role !== 'customer') {
            return null;
        }

        $user = (new FrontendUserModel())
            ->where('id', $userId)
            ->where('status', 'active')
            ->first();

        return is_array($user) ? $user : null;
    }

    private function bookingBaseQuery(int $frontendUserId)
    {
        $db = db_connect();
        $builder = $db->table('bookings');
        if ($db->fieldExists('frontend_user_id', 'bookings')) {
            $builder->where('frontend_user_id', $frontendUserId);
        } else {
            $builder->where('created_by', $frontendUserId);
        }

        return $builder;
    }

    private function decodeBookingDetails(array $row): array
    {
        $raw = trim((string) ($row['remarks'] ?? ''));
        if ($raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function buildStatusTimeline(string $status, string $createdAt): array
    {
        $status = strtolower(trim($status));
        if (! in_array($status, ['pending', 'confirmed', 'cancelled'], true)) {
            $status = 'pending';
        }

        $steps = [
            ['key' => 'pending', 'title' => 'Request Submitted', 'note' => 'Your booking request has been received.', 'at' => $createdAt],
            ['key' => 'confirmed', 'title' => 'Confirmed', 'note' => 'Our team confirmed availability and booking details.', 'at' => ''],
            ['key' => 'cancelled', 'title' => 'Cancelled', 'note' => 'Booking was cancelled by customer or operations team.', 'at' => ''],
        ];

        foreach ($steps as &$step) {
            $step['state'] = 'upcoming';
            if ($status === 'pending') {
                if ($step['key'] === 'pending') {
                    $step['state'] = 'current';
                }
                continue;
            }
            if ($status === 'confirmed') {
                if (in_array($step['key'], ['pending', 'confirmed'], true)) {
                    $step['state'] = $step['key'] === 'confirmed' ? 'current' : 'done';
                }
                continue;
            }
            if ($status === 'cancelled') {
                if (in_array($step['key'], ['pending', 'cancelled'], true)) {
                    $step['state'] = $step['key'] === 'cancelled' ? 'current' : 'done';
                }
            }
        }

        return $steps;
    }
}
