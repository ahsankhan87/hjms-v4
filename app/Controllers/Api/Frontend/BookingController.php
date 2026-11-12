<?php

namespace App\Controllers\Api\Frontend;

use App\Controllers\BaseController;
use App\Services\Frontend\BookingService;
use DomainException;

class BookingController extends BaseController
{
    private BookingService $bookingService;

    public function __construct()
    {
        $this->bookingService = new BookingService();
    }

    public function index()
    {
        $viewer = service('frontendAuthService')->currentUser();
        if ($viewer === null) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $this->bookingService->listUserBookings($viewer),
        ]);
    }

    public function create()
    {
        $viewer = service('frontendAuthService')->currentUser();
        if ($viewer === null) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $payload = [
            'package_id' => (int) $this->request->getPost('package_id'),
            'pricing_tier' => strtolower(trim((string) $this->request->getPost('pricing_tier'))),
            'pilgrims_count' => (int) $this->request->getPost('pilgrims_count'),
            'remarks' => trim((string) $this->request->getPost('remarks')),
        ];

        if (! $this->validateData($payload, [
            'package_id' => 'required|integer|greater_than[0]',
            'pricing_tier' => 'permit_empty|in_list[sharing,quint,quad,triple,double]',
            'pilgrims_count' => 'required|integer|greater_than[0]|less_than_equal_to[100]',
            'remarks' => 'permit_empty|max_length[2000]',
        ])) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        try {
            $booking = $this->bookingService->createBooking($payload, $viewer);

            return $this->response->setJSON([
                'status' => 'ok',
                'message' => 'Booking created successfully.',
                'data' => $booking,
            ]);
        } catch (DomainException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}

