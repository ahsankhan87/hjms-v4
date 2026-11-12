<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\Frontend\HotelFrontendModel;
use App\Models\Frontend\TicketFrontendModel;
use App\Models\Frontend\TransportFrontendModel;

class PublicSearchController extends BaseController
{
    public function tickets()
    {
        $filters = [
            'from_city' => trim((string) $this->request->getGet('from_city')),
            'to_city' => trim((string) $this->request->getGet('to_city')),
            'departure_date' => trim((string) $this->request->getGet('departure_date')),
            'travel_class' => trim((string) $this->request->getGet('class')),
            'limit' => 150,
        ];

        $rows = (new TicketFrontendModel())->searchTickets($filters);

        return view('frontend/tickets/index', [
            'title' => 'Tickets | HJMS Travel',
            'activePage' => 'tickets',
            'filters' => $filters,
            'rows' => $rows,
            'searched' => $this->hasAnyFilter($filters, ['from_city', 'to_city', 'departure_date', 'travel_class']),
        ]);
    }

    public function hotels()
    {
        $filters = [
            'city' => trim((string) $this->request->getGet('city')),
            'check_in' => trim((string) $this->request->getGet('check_in')),
            'check_out' => trim((string) $this->request->getGet('check_out')),
            'guests' => (int) $this->request->getGet('guests'),
            'limit' => 150,
        ];
        if ($filters['guests'] < 1) {
            $filters['guests'] = 1;
        }

        $rows = (new HotelFrontendModel())->searchHotels($filters);

        return view('frontend/hotels/index', [
            'title' => 'Hotels | HJMS Travel',
            'activePage' => 'hotels',
            'filters' => $filters,
            'rows' => $rows,
            'searched' => $this->hasAnyFilter($filters, ['city', 'check_in', 'check_out']) || $filters['guests'] > 1,
        ]);
    }

    public function hotelDetails(int $id)
    {
        if ($id < 1) {
            return redirect()->to('/hotels')->with('error', 'Hotel not found.');
        }

        $db = db_connect();
        $hotel = $db->table('hotels')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (! is_array($hotel)) {
            return redirect()->to('/hotels')->with('error', 'Hotel not found.');
        }

        $rooms = $db->table('hotel_rooms')
            ->where('hotel_id', $id)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return view('frontend/hotels/show', [
            'title' => ((string) ($hotel['name'] ?? 'Hotel')) . ' | HJMS Travel',
            'activePage' => 'hotels',
            'hotel' => $hotel,
            'rooms' => $rooms,
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function transport()
    {
        $filters = [
            'from_city' => trim((string) $this->request->getGet('from_city')),
            'to_city' => trim((string) $this->request->getGet('to_city')),
            'limit' => 150,
        ];

        $rows = (new TransportFrontendModel())->listRoutes($filters);

        return view('frontend/transport/index', [
            'title' => 'Transport | HJMS Travel',
            'activePage' => 'transport',
            'filters' => $filters,
            'rows' => $rows,
            'searched' => $this->hasAnyFilter($filters, ['from_city', 'to_city']),
        ]);
    }

    private function hasAnyFilter(array $filters, array $keys): bool
    {
        foreach ($keys as $key) {
            $value = $filters[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return true;
            }
            if (is_numeric($value) && (float) $value > 0) {
                return true;
            }
        }

        return false;
    }
}
