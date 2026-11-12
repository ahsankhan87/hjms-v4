<?php

namespace App\Controllers\Api\Frontend;

use App\Controllers\BaseController;
use App\Services\Frontend\CatalogService;

class CatalogController extends BaseController
{
    private CatalogService $catalog;

    public function __construct()
    {
        $this->catalog = new CatalogService();
    }

    public function home()
    {
        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $this->catalog->homeData(service('frontendAuthService')->currentUser()),
        ]);
    }

    public function packages()
    {
        $filters = [
            'package_type' => (string) $this->request->getGet('package_type'),
            'search' => (string) $this->request->getGet('search'),
            'tier' => (string) $this->request->getGet('tier'),
            'limit' => (int) ($this->request->getGet('limit') ?? 20),
        ];

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $this->catalog->listPackages($filters, service('frontendAuthService')->currentUser()),
        ]);
    }

    public function packageDetails(int $id)
    {
        $row = $this->catalog->packageDetails($id, service('frontendAuthService')->currentUser());
        if ($row === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Package not found.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $row,
        ]);
    }

    public function flights()
    {
        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $this->catalog->listFlights([
                'search' => (string) $this->request->getGet('search'),
                'limit' => (int) ($this->request->getGet('limit') ?? 20),
            ]),
        ]);
    }

    public function flightDetails(int $id)
    {
        $row = $this->catalog->flightDetails($id);
        if ($row === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Flight not found.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $row,
        ]);
    }

    public function hotels()
    {
        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $this->catalog->listHotels([
                'search' => (string) $this->request->getGet('search'),
                'city' => (string) $this->request->getGet('city'),
                'limit' => (int) ($this->request->getGet('limit') ?? 20),
            ]),
        ]);
    }

    public function hotelDetails(int $id)
    {
        $row = $this->catalog->hotelDetails($id);
        if ($row === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Hotel not found.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $row,
        ]);
    }

    public function transports()
    {
        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $this->catalog->listTransports([
                'search' => (string) $this->request->getGet('search'),
                'limit' => (int) ($this->request->getGet('limit') ?? 20),
            ]),
        ]);
    }

    public function transportDetails(int $id)
    {
        $row = $this->catalog->transportDetails($id);
        if ($row === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Transport not found.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $row,
        ]);
    }
}

