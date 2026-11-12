<?php

namespace App\Controllers\Api\Frontend;

use App\Controllers\BaseController;
use App\Services\Frontend\BookingService;

class AccountController extends BaseController
{
    public function me()
    {
        $profile = service('frontendAuthService')->currentUser();
        if ($profile === null) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $profile,
        ]);
    }

    public function dashboard()
    {
        $profile = service('frontendAuthService')->currentUser();
        if ($profile === null) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $bookings = (new BookingService())->listUserBookings($profile);

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => [
                'profile' => $profile,
                'stats' => [
                    'total_bookings' => count($bookings),
                    'confirmed' => count(array_filter($bookings, static fn(array $row): bool => strtolower((string) ($row['status'] ?? '')) === 'confirmed')),
                    'draft' => count(array_filter($bookings, static fn(array $row): bool => strtolower((string) ($row['status'] ?? '')) === 'draft')),
                ],
                'recent_bookings' => array_slice($bookings, 0, 8),
            ],
        ]);
    }
}

