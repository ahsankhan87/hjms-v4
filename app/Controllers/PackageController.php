<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FlightModel;
use App\Models\HotelModel;
use App\Models\PackageCostModel;
use App\Models\PackageCostSheetItemModel;
use App\Models\PackageCostSheetModel;
use App\Models\PackageFlightModel;
use App\Models\PackageHotelModel;
use App\Models\PackageModel;
use App\Models\PackagePriceLineModel;
use App\Models\PackageTransportModel;
use App\Models\SupplierLedgerEntryModel;
use App\Models\SupplierModel;
use App\Models\TransportModel;

class PackageController extends BaseController
{
    public function index()
    {
        if ($this->activeSeasonId() === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $model = new PackageModel();
        $rows = $model->orderBy('id', 'DESC')->findAll();

        return view('portal/packages/index', [
            'title'       => 'HJMS ERP | Packages',
            'headerTitle' => 'Package Management',
            'activePage'  => 'packages',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $rows,
            'cards'       => $this->buildPackageCards($rows),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function publicIndex()
    {
        if ($this->activeSeasonId() === null) {
            return view('public/packages/index', [
                'title' => 'HJMS | Latest Packages',
                'cards' => [],
                'error' => 'No active season is configured yet.',
            ]);
        }

        $rows = (new PackageModel())
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('public/packages/index', [
            'title' => 'HJMS | Latest Packages',
            'cards' => $this->buildPackageCards($rows),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function register(int $packageId)
    {
        $package = (new PackageModel())->where('id', $packageId)->where('season_id', $this->activeSeasonId())->first();
        if (empty($package)) {
            return redirect()->to('/packages')->with('error', 'Selected package not found.');
        }

        return redirect()->to('/bookings?package_id=' . $packageId);
    }

    private function buildPackageCards(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $packageIds = array_map(static function (array $row): int {
            return (int) $row['id'];
        }, $rows);
        $db = db_connect();

        $flightRows = $db->table('package_flights pf')
            ->select('pf.*, f.departure_airport, f.arrival_airport, f.pnr')
            ->join('flights f', 'f.id = pf.flight_id', 'left')
            ->whereIn('pf.package_id', $packageIds)
            ->orderBy('pf.departure_at', 'ASC')
            ->orderBy('pf.id', 'ASC')
            ->get()
            ->getResultArray();

        $hotelRows = $db->table('package_hotels ph')
            ->select('ph.*, h.name AS master_hotel_name, h.city')
            ->join('hotels h', 'h.id = ph.hotel_id', 'left')
            ->whereIn('ph.package_id', $packageIds)
            ->orderBy('ph.id', 'ASC')
            ->get()
            ->getResultArray();

        $transportRows = $db->table('package_transports pt')
            ->select('pt.*, t.transport_name AS master_transport_name, t.vehicle_type AS master_vehicle_type')
            ->join('transports t', 't.id = pt.transport_id', 'left')
            ->whereIn('pt.package_id', $packageIds)
            ->orderBy('pt.id', 'ASC')
            ->get()
            ->getResultArray();

        $costRows = $db->table('package_costs pc')
            ->whereIn('pc.package_id', $packageIds)
            ->orderBy('pc.id', 'ASC')
            ->get()
            ->getResultArray();

        $flightsByPackage = [];
        foreach ($flightRows as $item) {
            $flightsByPackage[(int) $item['package_id']][] = $item;
        }

        $hotelsByPackage = [];
        foreach ($hotelRows as $item) {
            $hotelsByPackage[(int) $item['package_id']][] = $item;
        }

        $transportsByPackage = [];
        foreach ($transportRows as $item) {
            $transportsByPackage[(int) $item['package_id']][] = $item;
        }

        $costsByPackage = [];
        foreach ($costRows as $item) {
            $costsByPackage[(int) $item['package_id']][] = $item;
        }

        $cards = [];
        foreach ($rows as $row) {
            $packageId = (int) $row['id'];
            $linkedFlights = $flightsByPackage[$packageId] ?? [];
            $linkedHotels = $hotelsByPackage[$packageId] ?? [];
            $linkedTransports = $transportsByPackage[$packageId] ?? [];
            $linkedCosts = $costsByPackage[$packageId] ?? [];

            $routeLabel = '-';
            $airlineName = (string) ($row['airline'] ?? '');
            $travelDate = (string) ($row['departure_date'] ?? '');
            $departureDateTime = (string) ($row['departure_date'] ?? '');
            $returnArrivalDateTime = (string) ($row['arrival_date'] ?? '');
            $ticketRefs = [];
            $outboundFlight = null;
            $returnFlight = null;

            if ($linkedFlights !== []) {
                $firstFlight = $linkedFlights[0];
                $lastFlight = $linkedFlights[count($linkedFlights) - 1];
                $departureAirport = (string) ($firstFlight['departure_airport'] ?? '');
                $arrivalAirport = (string) ($firstFlight['arrival_airport'] ?? '');
                if ($departureAirport !== '' || $arrivalAirport !== '') {
                    $routeLabel = trim($departureAirport . '-' . $arrivalAirport, '-');
                }
                if ((string) ($firstFlight['airline'] ?? '') !== '') {
                    $airlineName = (string) $firstFlight['airline'];
                }

                $departureAt = (string) ($firstFlight['departure_at'] ?? '');
                if ($departureAt !== '') {
                    $travelDate = date('Y-m-d', strtotime($departureAt));
                    $departureDateTime = $departureAt;
                }

                $returnArrivalAt = (string) ($lastFlight['arrival_at'] ?? '');
                if ($returnArrivalAt !== '') {
                    $returnArrivalDateTime = $returnArrivalAt;
                }

                $outboundFlight = [
                    'airline' => (string) ($firstFlight['airline'] ?? ''),
                    'flight_no' => (string) ($firstFlight['flight_no'] ?? ''),
                    'pnr' => trim((string) ($firstFlight['pnr'] ?? '')),
                    'departure_airport' => (string) ($firstFlight['departure_airport'] ?? ''),
                    'arrival_airport' => (string) ($firstFlight['arrival_airport'] ?? ''),
                    'departure_at' => (string) ($firstFlight['departure_at'] ?? ''),
                    'arrival_at' => (string) ($firstFlight['arrival_at'] ?? ''),
                ];
                $returnFlight = [
                    'airline' => (string) ($lastFlight['airline'] ?? ''),
                    'flight_no' => (string) ($lastFlight['flight_no'] ?? ''),
                    'pnr' => trim((string) ($lastFlight['pnr'] ?? '')),
                    'departure_airport' => (string) ($lastFlight['departure_airport'] ?? ''),
                    'arrival_airport' => (string) ($lastFlight['arrival_airport'] ?? ''),
                    'departure_at' => (string) ($lastFlight['departure_at'] ?? ''),
                    'arrival_at' => (string) ($lastFlight['arrival_at'] ?? ''),
                ];

                foreach ($linkedFlights as $flight) {
                    $pnr = trim((string) ($flight['pnr'] ?? ''));
                    $flightNo = trim((string) ($flight['flight_no'] ?? ''));
                    if ($pnr !== '') {
                        $ticketRefs[] = $pnr;
                    } elseif ($flightNo !== '') {
                        $ticketRefs[] = $flightNo;
                    }
                }
            }

            $resolvedAirlineLogo = $this->resolveAirlineLogo(
                (string) ($row['airline_logo'] ?? ''),
                $airlineName
            );
            $returnAirlineName = trim((string) ($returnFlight['airline'] ?? $airlineName));
            $returnAirlineLogo = $this->resolveAirlineLogo('', $returnAirlineName);

            $hotelNames = [];
            $hotelStays = [];
            foreach ($linkedHotels as $hotel) {
                $hotelId = (int) ($hotel['hotel_id'] ?? 0);
                $name = trim((string) ($hotel['master_hotel_name'] ?? $hotel['hotel_name'] ?? ''));
                if ($name !== '') {
                    $hotelNames[] = $name;
                }

                $checkInDate = (string) ($hotel['check_in_date'] ?? '');
                $checkOutDate = (string) ($hotel['check_out_date'] ?? '');
                $city = trim((string) ($hotel['city'] ?? ''));

                $nights = 0;
                $checkInTs = strtotime($checkInDate);
                $checkOutTs = strtotime($checkOutDate);
                if ($checkInTs !== false && $checkOutTs !== false && $checkOutTs > $checkInTs) {
                    $nights = (int) floor(($checkOutTs - $checkInTs) / 86400);
                }

                if ($name !== '') {
                    $hotelStays[] = [
                        'id' => $hotelId > 0 ? $hotelId : null,
                        'name' => $name,
                        'city' => $city,
                        'nights' => $nights,
                    ];
                }
            }
            $hotelNames = array_values(array_unique($hotelNames));

            $uniqueHotelStays = [];
            foreach ($hotelStays as $stay) {
                $key = ($stay['id'] ?? 'null') . '|' . strtolower((string) ($stay['name'] ?? '')) . '|' . strtolower((string) ($stay['city'] ?? '')) . '|' . (int) ($stay['nights'] ?? 0);
                $uniqueHotelStays[$key] = $stay;
            }
            $hotelStays = array_values($uniqueHotelStays);

            $priceMap = [];
            foreach ($linkedCosts as $cost) {
                $type = strtolower(trim((string) ($cost['cost_type'] ?? '')));
                if ($type === '') {
                    continue;
                }
                $priceMap[$type] = (float) ($cost['cost_amount'] ?? 0);
            }

            $transportTypes = [];
            $transportNames = [];
            foreach ($linkedTransports as $transport) {
                $type = strtolower(trim((string) (($transport['vehicle_type'] ?? '') !== ''
                    ? $transport['vehicle_type']
                    : ($transport['master_vehicle_type'] ?? ''))));
                if ($type !== '') {
                    $transportTypes[] = ucfirst($type);
                }

                $name = trim((string) (($transport['master_transport_name'] ?? '') !== ''
                    ? $transport['master_transport_name']
                    : ($transport['provider_name'] ?? '')));
                if ($name !== '') {
                    $transportNames[] = $name;
                }
            }
            $transportTypes = array_values(array_unique($transportTypes));
            $transportNames = array_values(array_unique($transportNames));

            $cards[] = [
                'id' => $packageId,
                'code' => (string) ($row['code'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'airline_name' => $airlineName,
                'airline_logo' => $resolvedAirlineLogo,
                'return_airline_logo' => $returnAirlineLogo,
                'route_label' => $routeLabel,
                'ticket_refs' => array_slice(array_values(array_unique($ticketRefs)), 0, 2),
                'hotel_names' => array_slice($hotelNames, 0, 2),
                'hotel_stays' => $hotelStays,
                'travel_date' => $travelDate,
                'departure_datetime' => $departureDateTime,
                'return_arrival_datetime' => $returnArrivalDateTime,
                'outbound_flight' => $outboundFlight,
                'return_flight' => $returnFlight,
                'duration_days' => (int) ($row['duration_days'] ?? 0),
                'available_seats' => (int) ($row['total_seats'] ?? 0),
                'price_map' => $priceMap,
                'transport_count'   => count($linkedTransports),
                'transport_types'   => $transportTypes,
                'transport_names'   => array_slice($transportNames, 0, 3),
                'flight_count'      => count($linkedFlights),
                'hotel_count'       => count($linkedHotels),
                'include_hotel'     => (int) ($row['include_hotel']     ?? 1),
                'include_ticket'    => (int) ($row['include_ticket']    ?? 1),
                'include_transport' => (int) ($row['include_transport'] ?? 1),
                'package_departure_date' => (string) ($row['departure_date'] ?? ''),
                'package_arrival_date'   => (string) ($row['arrival_date']   ?? ''),
            ];
        }

        return $cards;
    }

    private function resolveAirlineLogo(string $logoUrl, string $airlineName): string
    {
        $logoUrl = trim($logoUrl);
        if ($logoUrl !== '') {
            return $logoUrl;
        }

        $airlineKey = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $airlineName));
        if ($airlineKey === '') {
            return '';
        }

        foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $ext) {
            $relativePath = 'assets/uploads/airlines/' . $airlineKey . '.' . $ext;
            $absolutePath = rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (is_file($absolutePath)) {
                return base_url($relativePath);
            }
        }

        return '';
    }

    public function add()
    {
        if ($this->activeSeasonId() === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        return view('portal/packages/add', [
            'title'       => 'HJMS ERP | Add Package',
            'headerTitle' => 'Add Package',
            'activePage'  => 'packages',
            'userEmail'   => (string) session('user_email'),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $model = new PackageModel();
        $row = $model->where('id', $id)->where('season_id', $this->activeSeasonId())->first();

        if (empty($row)) {
            return redirect()->to('/packages')->with('error', 'Package not found.');
        }

        $db = db_connect();

        $latestCostSheet = null;
        $costSheetItems = [];
        $costSheetLines = [];
        if ($db->tableExists('package_cost_sheets')) {
            $latestCostSheet = $db->table('package_cost_sheets')
                ->where('package_id', $id)
                ->orderBy('version_no', 'DESC')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if (! empty($latestCostSheet)) {
                $costSheetItems = $db->table('package_cost_sheet_items psi')
                    ->select('psi.*, s.supplier_name')
                    ->join('suppliers s', 's.id = psi.supplier_id', 'left')
                    ->where('psi.cost_sheet_id', (int) $latestCostSheet['id'])
                    ->orderBy('psi.id', 'ASC')
                    ->get()
                    ->getResultArray();

                $costSheetLines = $db->table('package_price_lines')
                    ->where('cost_sheet_id', (int) $latestCostSheet['id'])
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();
            }
        }

        $stayWindow = $this->packageStayWindow($row);
        $packageStayStart = (string) ($stayWindow['start'] ?? date('Y-m-d'));
        $packageStayEnd = (string) ($stayWindow['end'] ?? date('Y-m-d', strtotime('+1 day')));

        $lastHotelStay = $db->table('package_hotels')
            ->select('check_out_date')
            ->where('package_id', $id)
            ->orderBy('check_out_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        $stayCheckIn = (string) ($lastHotelStay['check_out_date'] ?? $packageStayStart);
        if ($stayCheckIn === '') {
            $stayCheckIn = $packageStayStart;
        }

        $stayStartTs = strtotime($stayCheckIn);
        $stayEndTs = strtotime($packageStayEnd);
        if ($stayStartTs !== false && $stayEndTs !== false && $stayStartTs < $stayEndTs) {
            $stayCheckOut = date('Y-m-d', strtotime('+1 day', $stayStartTs));
            if (strtotime($stayCheckOut) > $stayEndTs) {
                $stayCheckOut = $packageStayEnd;
            }
        } else {
            $stayCheckOut = date('Y-m-d', strtotime('+1 day', $stayStartTs ?: time()));
        }

        $hotelRoomOptions = $db->table('hotel_rooms hr')
            ->select('hr.id, hr.hotel_id, hr.room_type, hr.total_rooms, h.name AS hotel_name, h.city AS hotel_city')
            ->join('hotels h', 'h.id = hr.hotel_id', 'inner')
            ->orderBy('h.name', 'ASC')
            ->orderBy('hr.room_type', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($hotelRoomOptions as &$roomOption) {
            $occupied = $db->table('package_hotels ph')
                ->where('ph.hotel_room_id', (int) $roomOption['id'])
                ->where('ph.check_in_date <', $stayCheckOut)
                ->where('ph.check_out_date >', $stayCheckIn)
                ->countAllResults();

            $roomOption['occupied_rooms'] = (int) $occupied;
            $roomOption['available_rooms'] = max(0, (int) ($roomOption['total_rooms'] ?? 0) - (int) $occupied);
        }
        unset($roomOption);

        return view('portal/packages/edit', [
            'title'       => 'HJMS ERP | Edit Package',
            'headerTitle' => 'Edit Package',
            'activePage'  => 'packages',
            'userEmail'   => (string) session('user_email'),
            'row'         => $row,
            'hotelOptions' => (new HotelModel())->orderBy('name', 'ASC')->findAll(),
            'flightOptions' => (new FlightModel())->orderBy('departure_at', 'DESC')->findAll(),
            'transportOptions' => (new TransportModel())->orderBy('provider_name', 'ASC')->findAll(),
            'costRows' => $db->table('package_costs')
                ->where('package_id', $id)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray(),
            'hotelRows' => $db->table('package_hotels ph')
                ->select('ph.*, h.name AS hotel_master_name, h.city AS hotel_city, hr.room_type AS hotel_room_type')
                ->join('hotels h', 'h.id = ph.hotel_id', 'left')
                ->join('hotel_rooms hr', 'hr.id = ph.hotel_room_id', 'left')
                ->where('ph.package_id', $id)
                ->orderBy('ph.id', 'DESC')
                ->get()
                ->getResultArray(),
            'hotelRoomOptions' => $hotelRoomOptions,
            'stayCheckIn' => $stayCheckIn,
            'stayCheckOut' => $stayCheckOut,
            'packageStayStart' => $packageStayStart,
            'packageStayEnd' => $packageStayEnd,
            'flightRows' => $db->table('package_flights pf')
                ->select('pf.*, f.pnr')
                ->join('flights f', 'f.id = pf.flight_id', 'left')
                ->where('pf.package_id', $id)
                ->orderBy('pf.id', 'ASC')
                ->get()
                ->getResultArray(),
            'transportRows' => $db->table('package_transports pt')
                ->select('pt.*, t.transport_name AS master_transport_name')
                ->join('transports t', 't.id = pt.transport_id', 'left')
                ->where('pt.package_id', $id)
                ->orderBy('pt.id', 'DESC')
                ->get()
                ->getResultArray(),
            'supplierRows' => $db->tableExists('suppliers')
                ? $db->table('suppliers')->where('is_active', 1)->orderBy('supplier_name', 'ASC')->get()->getResultArray()
                : [],
            'latestCostSheet' => $latestCostSheet,
            'costSheetItems' => $costSheetItems,
            'costSheetLines' => $costSheetLines,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createPackage()
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        // Normalize datetime-local (Y-m-dTH:i) → Y-m-d H:i:s for DB
        $normDatetime = static function (string $v): string {
            if ($v === '') return '';
            return date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $v)));
        };

        $payload = [
            'code'           => (string) $this->request->getPost('code'),
            'name'           => (string) $this->request->getPost('name'),
            'package_type'   => (string) $this->request->getPost('package_type'),
            'duration_days'  => (string) $this->request->getPost('duration_days'),
            'departure_date' => $normDatetime((string) $this->request->getPost('departure_date')),
            'arrival_date'   => $normDatetime((string) $this->request->getPost('arrival_date')),
            'notes'          => (string) $this->request->getPost('notes'),
        ];

        if (! $this->validateData($payload, [
            'code'          => 'required|alpha_numeric_punct|min_length[2]|max_length[40]',
            'name'          => 'required|min_length[3]|max_length[180]',
            'package_type'  => 'required|in_list[hajj,umrah]',
            'duration_days' => 'required|integer|greater_than[0]',
            'departure_date' => 'required',
            'arrival_date'  => 'permit_empty',
            'notes'         => 'permit_empty',
        ])) {
            return redirect()->to('/packages/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $arrivalDate = $payload['arrival_date'] !== ''
                ? $payload['arrival_date']
                : $this->deriveArrivalDate($payload['departure_date'], (int) $payload['duration_days']);

            $model = new PackageModel();
            $model->insert([
                'season_id'      => $seasonId,
                'code'           => $payload['code'],
                'name'           => $payload['name'],
                'package_type'   => $payload['package_type'],
                'duration_days'  => (int) $payload['duration_days'],
                'departure_date' => $payload['departure_date'],
                'arrival_date'   => $arrivalDate,
                'is_active'      => 1,
                'notes'          => $payload['notes'] !== '' ? $payload['notes'] : null,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);


            return redirect()->to('/packages')->with('success', 'Package created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/packages/add')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updatePackage()
    {
        $packageId = (int) $this->request->getPost('package_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }
        // Normalize datetime-local (Y-m-dTH:i) → Y-m-d H:i:s for DB
        $normDatetime = static function (string $v): string {
            if ($v === '') return '';
            return date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $v)));
        };

        $payload = [
            'code'              => (string) $this->request->getPost('code'),
            'name'              => (string) $this->request->getPost('name'),
            'package_type'      => (string) $this->request->getPost('package_type'),
            'duration_days'     => (string) $this->request->getPost('duration_days'),
            'departure_date'    => $normDatetime((string) $this->request->getPost('departure_date')),
            'arrival_date'      => $normDatetime((string) $this->request->getPost('arrival_date')),
            'notes'             => (string) $this->request->getPost('notes'),
            'is_active'         => (string) $this->request->getPost('is_active'),
            'include_hotel'     => (string) ($this->request->getPost('include_hotel') ?? ''),
            'include_ticket'    => (string) ($this->request->getPost('include_ticket') ?? ''),
            'include_transport' => (string) ($this->request->getPost('include_transport') ?? ''),
        ];

        if ($packageId < 1) {
            return redirect()->to('/packages')->withInput()->with('error', 'Valid package ID is required.');
        }

        if (! $this->validateData($payload, [
            'code'          => 'permit_empty|alpha_numeric_punct|min_length[2]|max_length[40]',
            'name'          => 'permit_empty|min_length[3]|max_length[180]',
            'package_type'  => 'permit_empty|in_list[hajj,umrah]',
            'duration_days' => 'permit_empty|integer|greater_than[0]',
            'departure_date'    => 'permit_empty',
            'arrival_date'      => 'permit_empty',
            'notes'             => 'permit_empty',
            'is_active'         => 'permit_empty|in_list[0,1]',
            'include_hotel'     => 'permit_empty|in_list[0,1]',
            'include_ticket'    => 'permit_empty|in_list[0,1]',
            'include_transport' => 'permit_empty|in_list[0,1]',
        ])) {
            return redirect()->to('/packages/' . $packageId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = array_filter($payload, static function ($value) {
            return $value !== '';
        });

        if ($data === []) {
            return redirect()->to('/packages')->withInput()->with('error', 'Provide at least one field to update for package.');
        }

        if (isset($data['duration_days'])) {
            $data['duration_days'] = (int) $data['duration_days'];
        }

        try {
            $model = new PackageModel();
            $existing = $model->where('id', $packageId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/packages')->with('error', 'Package not found in active season.');
            }

            if (isset($data['departure_date']) || isset($data['duration_days'])) {
                $current = $existing;
                if (! empty($current)) {
                    $departureDate = (string) ($data['departure_date'] ?? ($current['departure_date'] ?? ''));
                    $durationDays = (int) ($data['duration_days'] ?? ($current['duration_days'] ?? 0));
                    $calculatedArrivalDate = $this->deriveArrivalDate($departureDate, $durationDays);
                    if ($calculatedArrivalDate !== null && ! isset($data['arrival_date'])) {
                        $data['arrival_date'] = $calculatedArrivalDate;
                    }
                }
            }

            if (isset($data['is_active'])) {
                $data['is_active'] = (int) $data['is_active'];
            }
            if (isset($data['include_hotel'])) {
                $data['include_hotel'] = (int) $data['include_hotel'];
            }
            if (isset($data['include_ticket'])) {
                $data['include_ticket'] = (int) $data['include_ticket'];
            }
            if (isset($data['include_transport'])) {
                $data['include_transport'] = (int) $data['include_transport'];
            }

            // Activation guard: when activating a package, verify included components have links
            if (isset($data['is_active']) && $data['is_active'] === 1) {
                $dbCheck = db_connect();
                $effectiveIncludeHotel     = (int) ($data['include_hotel']     ?? $existing['include_hotel']     ?? 1);
                $effectiveIncludeTicket    = (int) ($data['include_ticket']    ?? $existing['include_ticket']    ?? 1);
                $effectiveIncludeTransport = (int) ($data['include_transport'] ?? $existing['include_transport'] ?? 1);

                if ($effectiveIncludeHotel === 1) {
                    $hotelCount = $dbCheck->table('package_hotels')->where('package_id', $packageId)->countAllResults();
                    if ($hotelCount < 1) {
                        return redirect()->to('/packages/' . $packageId . '/edit')->withInput()->with('error', 'Package includes hotel accommodation but no hotel is linked. Please attach a hotel or uncheck "Hotel Accommodation" in Package Includes.');
                    }
                }
                if ($effectiveIncludeTicket === 1) {
                    $flightCount = $dbCheck->table('package_flights')->where('package_id', $packageId)->countAllResults();
                    if ($flightCount < 1) {
                        return redirect()->to('/packages/' . $packageId . '/edit')->withInput()->with('error', 'Package includes flights but no flight is linked. Please attach flights or uncheck "Flight / Ticket" in Package Includes.');
                    }
                }
                if ($effectiveIncludeTransport === 1) {
                    $transportCount = $dbCheck->table('package_transports')->where('package_id', $packageId)->countAllResults();
                    if ($transportCount < 1) {
                        return redirect()->to('/packages/' . $packageId . '/edit')->withInput()->with('error', 'Package includes transport but no transport is linked. Please attach a transport or uncheck "Transport" in Package Includes.');
                    }
                }
            }

            if ($data !== []) {
                $model->update($packageId, $data + ['updated_at' => date('Y-m-d H:i:s')]);
            }

            return redirect()->to('/packages')->with('success', 'Package updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/packages/' . $packageId . '/edit')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deletePackage()
    {
        $packageId = (int) $this->request->getPost('package_id');
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }
        if ($packageId < 1) {
            return redirect()->to('/packages')->with('error', 'Valid package ID is required for delete.');
        }

        try {
            $model = new PackageModel();
            $existing = $model->where('id', $packageId)->where('season_id', $seasonId)->first();
            if (empty($existing)) {
                return redirect()->to('/packages')->with('error', 'Package not found in active season.');
            }
            $deleted = $model->delete($packageId);

            if (! $deleted) {
                return redirect()->to('/packages')->with('error', 'Package not found or already removed.');
            }

            return redirect()->to('/packages')->with('success', 'Package deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/packages')->with('error', $e->getMessage());
        }
    }

    public function createPackageCost()
    {
        $payload = [
            'package_id'  => (int) $this->request->getPost('package_id'),
            'cost_type'   => (string) $this->request->getPost('cost_type'),
            'cost_amount' => (string) $this->request->getPost('cost_amount'),
            'seats_limit' => (string) $this->request->getPost('seats_limit'),
            'supplier_id' => (string) $this->request->getPost('supplier_id'),
            'description' => (string) $this->request->getPost('description'),
        ];

        if (! $this->validateData($payload, [
            'package_id'  => 'required|integer',
            'cost_type'   => 'required|in_list[sharing,quad,triple,double]|max_length[100]',
            'cost_amount' => 'required|decimal',
            'seats_limit' => 'permit_empty|integer|greater_than[0]',
            'supplier_id' => 'permit_empty|integer',
            'description' => 'permit_empty',
        ])) {
            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new PackageCostModel();
            $model->insert([
                'package_id'  => $payload['package_id'],
                'cost_type'   => $payload['cost_type'],
                'cost_amount' => (float) $payload['cost_amount'],
                'seats_limit' => $payload['seats_limit'] !== '' ? (int) $payload['seats_limit'] : null,
                'supplier_id' => $payload['supplier_id'] !== '' ? (int) $payload['supplier_id'] : null,
                'description' => $payload['description'] !== '' ? $payload['description'] : null,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('success', 'Package cost added successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', $e->getMessage());
        }
    }

    public function deletePackageCost()
    {
        $costId = (int) $this->request->getPost('package_cost_id');
        $packageId = (int) $this->request->getPost('package_id');

        if ($costId < 1 || $packageId < 1) {
            return redirect()->to('/packages')->with('error', 'Valid package cost and package IDs are required for delete.');
        }

        try {
            $deleted = (new PackageCostModel())->delete($costId);
            if (! $deleted) {
                return redirect()->to('/packages/' . $packageId . '/edit')->with('error', 'Package cost not found or already removed.');
            }

            return redirect()->to('/packages/' . $packageId . '/edit')->with('success', 'Package cost deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/packages/' . $packageId . '/edit')->with('error', $e->getMessage());
        }
    }

    public function createPackageHotel()
    {
        $payload = [
            'package_id'    => (int) $this->request->getPost('package_id'),
            'hotel_room_id' => (int) $this->request->getPost('hotel_room_id'),
            'check_in_date' => (string) $this->request->getPost('check_in_date'),
            'check_out_date' => (string) $this->request->getPost('check_out_date'),
            'stay_distribution' => trim((string) $this->request->getPost('stay_distribution')),
        ];

        if (! $this->validateData($payload, [
            'package_id'    => 'required|integer',
            'hotel_room_id' => 'required|integer',
            'check_in_date' => 'permit_empty|valid_date[Y-m-d]',
            'check_out_date' => 'permit_empty|valid_date[Y-m-d]',
            'stay_distribution' => 'permit_empty|max_length[120]',
        ])) {
            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $db = db_connect();
            $room = $db->table('hotel_rooms hr')
                ->select('hr.*, h.name AS hotel_name, h.city AS hotel_city')
                ->join('hotels h', 'h.id = hr.hotel_id', 'inner')
                ->where('hr.id', $payload['hotel_room_id'])
                ->get()
                ->getRowArray();

            if (empty($room)) {
                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', 'Selected hotel room type not found.');
            }

            $package = (new PackageModel())->find($payload['package_id']);
            if (empty($package)) {
                return redirect()->to('/packages')->with('error', 'Package not found.');
            }

            $stayWindow = $this->packageStayWindow($package);
            $packageStayStart = (string) ($stayWindow['start'] ?? '');
            $packageStayEnd = (string) ($stayWindow['end'] ?? '');

            if ($packageStayStart === '' || $packageStayEnd === '') {
                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Package departure/arrival dates are required before hotel allocation.');
            }

            $lastHotelStay = $db->table('package_hotels')
                ->select('check_out_date')
                ->where('package_id', $payload['package_id'])
                ->orderBy('check_out_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            $expectedCheckIn = (string) ($lastHotelStay['check_out_date'] ?? $packageStayStart);
            if (strtotime($expectedCheckIn) >= strtotime($packageStayEnd)) {
                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Package hotel duration is already fully allocated.');
            }

            $checkInDate = $payload['check_in_date'] !== '' ? $payload['check_in_date'] : $expectedCheckIn;
            $checkOutDate = $payload['check_out_date'];

            if ($checkInDate !== $expectedCheckIn) {
                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Next hotel check-in must be ' . $expectedCheckIn . ' to maintain sequence.');
            }

            if ($checkInDate === '' || $checkOutDate === '') {
                $window = $this->inferHotelStayWindow(
                    $package,
                    (string) ($room['hotel_city'] ?? ''),
                    $payload['stay_distribution'],
                    $checkInDate,
                    $packageStayEnd
                );
                $checkInDate = $checkInDate !== '' ? $checkInDate : ($window['check_in_date'] ?? '');
                $checkOutDate = $checkOutDate !== '' ? $checkOutDate : ($window['check_out_date'] ?? '');
            }

            if ($checkInDate === '' || $checkOutDate === '') {
                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Check-in and check-out dates are required.');
            }

            if (strtotime($checkOutDate) <= strtotime($checkInDate)) {
                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Check-out date must be after check-in date.');
            }

            if (strtotime($checkOutDate) > strtotime($packageStayEnd)) {
                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Check-out cannot exceed package stay end date (' . $packageStayEnd . ').');
            }

            $occupied = $db->table('package_hotels ph')
                ->where('ph.hotel_room_id', (int) $payload['hotel_room_id'])
                ->where('ph.check_in_date <', $checkOutDate)
                ->where('ph.check_out_date >', $checkInDate)
                ->countAllResults();

            $availableRooms = max(0, (int) ($room['total_rooms'] ?? 0) - (int) $occupied);
            if ($availableRooms < 1) {
                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'No room available for selected hotel room type in the selected date range.');
            }

            (new PackageHotelModel())->insert([
                'package_id'    => $payload['package_id'],
                'hotel_id'      => (int) ($room['hotel_id'] ?? 0),
                'hotel_room_id' => $payload['hotel_room_id'],
                'hotel_name'    => (string) ($room['hotel_name'] ?? ''),
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'room_type'     => (string) ($room['room_type'] ?? null),
                'created_at'    => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('success', 'Hotel attached to package successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', $e->getMessage());
        }
    }

    public function deletePackageHotel()
    {
        $rowId = (int) $this->request->getPost('package_hotel_id');
        $packageId = (int) $this->request->getPost('package_id');

        if ($rowId < 1 || $packageId < 1) {
            return redirect()->to('/packages')->with('error', 'Valid package hotel and package IDs are required for delete.');
        }

        try {
            $deleted = (new PackageHotelModel())->delete($rowId);
            if (! $deleted) {
                return redirect()->to('/packages/' . $packageId . '/edit')->with('error', 'Package hotel attachment not found or already removed.');
            }

            return redirect()->to('/packages/' . $packageId . '/edit')->with('success', 'Package hotel attachment deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/packages/' . $packageId . '/edit')->with('error', $e->getMessage());
        }
    }

    public function createPackageFlight()
    {
        $payload = [
            'package_id'             => (int) $this->request->getPost('package_id'),
            'outbound_flight_id'     => (int) $this->request->getPost('outbound_flight_id'),
            'outbound_departure_at'  => $this->normalizeDateTimeInput((string) $this->request->getPost('outbound_departure_at')),
            'outbound_arrival_at'    => $this->normalizeDateTimeInput((string) $this->request->getPost('outbound_arrival_at')),
            'return_flight_id'       => (int) $this->request->getPost('return_flight_id'),
            'return_departure_at'    => $this->normalizeDateTimeInput((string) $this->request->getPost('return_departure_at')),
            'return_arrival_at'      => $this->normalizeDateTimeInput((string) $this->request->getPost('return_arrival_at')),
        ];

        if (! $this->validateData($payload, [
            'package_id'            => 'required|integer',
            'outbound_flight_id'    => 'required|integer',
            'outbound_departure_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
            'outbound_arrival_at'   => 'permit_empty|valid_date[Y-m-d H:i:s]',
            'return_flight_id'      => 'required|integer',
            'return_departure_at'   => 'permit_empty|valid_date[Y-m-d H:i:s]',
            'return_arrival_at'     => 'permit_empty|valid_date[Y-m-d H:i:s]',
        ])) {
            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($payload['outbound_flight_id'] === $payload['return_flight_id']) {
            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Outbound and return flights must be different.');
        }

        try {
            $flightModel = new FlightModel();
            $outboundFlight = $flightModel->find($payload['outbound_flight_id']);
            $returnFlight = $flightModel->find($payload['return_flight_id']);

            if (empty($outboundFlight) || empty($returnFlight)) {
                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', 'Outbound and return flights must both be selected.');
            }

            $packageFlightModel = new PackageFlightModel();

            $packageFlightModel->insert([
                'package_id'   => $payload['package_id'],
                'flight_id'    => $payload['outbound_flight_id'],
                'airline'      => (string) ($outboundFlight['airline'] ?? ''),
                'flight_no'    => (string) ($outboundFlight['flight_no'] ?? ''),
                'departure_at' => $payload['outbound_departure_at'] !== '' ? $payload['outbound_departure_at'] : ($outboundFlight['departure_at'] ?? null),
                'arrival_at'   => $payload['outbound_arrival_at'] !== '' ? $payload['outbound_arrival_at'] : ($outboundFlight['arrival_at'] ?? null),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            $packageFlightModel->insert([
                'package_id'   => $payload['package_id'],
                'flight_id'    => $payload['return_flight_id'],
                'airline'      => (string) ($returnFlight['airline'] ?? ''),
                'flight_no'    => (string) ($returnFlight['flight_no'] ?? ''),
                'departure_at' => $payload['return_departure_at'] !== '' ? $payload['return_departure_at'] : ($returnFlight['departure_at'] ?? null),
                'arrival_at'   => $payload['return_arrival_at'] !== '' ? $payload['return_arrival_at'] : ($returnFlight['arrival_at'] ?? null),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('success', 'Outbound and return flights attached to package successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', $e->getMessage());
        }
    }

    public function deletePackageFlight()
    {
        $rowId = (int) $this->request->getPost('package_flight_id');
        $packageId = (int) $this->request->getPost('package_id');

        if ($rowId < 1 || $packageId < 1) {
            return redirect()->to('/packages')->with('error', 'Valid package flight and package IDs are required for delete.');
        }

        try {
            $deleted = (new PackageFlightModel())->delete($rowId);
            if (! $deleted) {
                return redirect()->to('/packages/' . $packageId . '/edit')->with('error', 'Package flight attachment not found or already removed.');
            }

            return redirect()->to('/packages/' . $packageId . '/edit')->with('success', 'Package flight attachment deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/packages/' . $packageId . '/edit')->with('error', $e->getMessage());
        }
    }

    public function createPackageTransport()
    {
        $payload = [
            'package_id'   => (int) $this->request->getPost('package_id'),
            'transport_id' => (int) $this->request->getPost('transport_id'),
            'seat_capacity' => (string) $this->request->getPost('seat_capacity'),
        ];

        if (! $this->validateData($payload, [
            'package_id'   => 'required|integer',
            'transport_id' => 'required|integer',
            'seat_capacity' => 'permit_empty|integer|greater_than_equal_to[0]',
        ])) {
            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $transport = (new TransportModel())->find($payload['transport_id']);
            if (empty($transport)) {
                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', 'Selected transport not found.');
            }

            (new PackageTransportModel())->insert([
                'package_id'   => $payload['package_id'],
                'transport_id' => $payload['transport_id'],
                'provider_name' => (string) ($transport['provider_name'] ?? ''),
                'vehicle_type' => (string) ($transport['vehicle_type'] ?? ''),
                'seat_capacity' => $payload['seat_capacity'] !== '' ? (int) $payload['seat_capacity'] : (int) ($transport['seat_capacity'] ?? 0),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('success', 'Transport attached to package successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', $e->getMessage());
        }
    }

    public function deletePackageTransport()
    {
        $rowId = (int) $this->request->getPost('package_transport_id');
        $packageId = (int) $this->request->getPost('package_id');

        if ($rowId < 1 || $packageId < 1) {
            return redirect()->to('/packages')->with('error', 'Valid package transport and package IDs are required for delete.');
        }

        try {
            $deleted = (new PackageTransportModel())->delete($rowId);
            if (! $deleted) {
                return redirect()->to('/packages/' . $packageId . '/edit')->with('error', 'Package transport attachment not found or already removed.');
            }

            return redirect()->to('/packages/' . $packageId . '/edit')->with('success', 'Package transport attachment deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/packages/' . $packageId . '/edit')->with('error', $e->getMessage());
        }
    }

    private function normalizeDateTimeInput(string $value): string
    {
        $value = trim(str_replace('T', ' ', $value));

        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value) === 1) {
            return $value . ':00';
        }

        return $value;
    }

    private function deriveArrivalDate(string $departureDate, int $durationDays)
    {
        if ($departureDate === '' || $durationDays < 1) {
            return null;
        }

        $timestamp = strtotime($departureDate);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', strtotime('+' . $durationDays . ' day', $timestamp));
    }

    private function inferHotelStayWindow(array $package, string $hotelCity, string $stayDistribution = '', string $baseCheckIn = '', string $packageStayEnd = ''): array
    {
        $departureDate = (string) ($package['departure_date'] ?? '');
        $arrivalDate = (string) ($package['arrival_date'] ?? '');
        $durationDays = (int) ($package['duration_days'] ?? 0);
        $distribution = trim($stayDistribution);

        $arrivalDate = $arrivalDate !== '' ? $arrivalDate : ($this->deriveArrivalDate($departureDate, $durationDays) ?? '');
        if ($departureDate === '' || $arrivalDate === '') {
            return ['check_in_date' => '', 'check_out_date' => ''];
        }

        $split = $this->parseDaysDistribution($distribution);
        $makkahDays = (int) ($split['makkah_days'] ?? 0);
        $madinaDays = (int) ($split['madina_days'] ?? 0);

        if ($makkahDays === 0 && $madinaDays === 0 && $durationDays > 0) {
            $makkahDays = $durationDays;
        }

        $isMadina = preg_match('/madina|medina/i', $hotelCity) === 1;
        $baseStart = $baseCheckIn !== '' ? $baseCheckIn : $departureDate;
        $start = strtotime($baseStart);
        if ($start === false) {
            return ['check_in_date' => '', 'check_out_date' => ''];
        }

        $segmentDays = $isMadina ? max(1, $madinaDays) : max(1, $makkahDays);
        $checkIn = date('Y-m-d', $start);
        $checkOut = date('Y-m-d', strtotime('+' . $segmentDays . ' day', $start));

        $stayEnd = $packageStayEnd !== '' ? $packageStayEnd : $arrivalDate;
        if ($stayEnd !== '' && strtotime($checkOut) > strtotime($stayEnd)) {
            $checkOut = $stayEnd;
        }

        if (strtotime($checkOut) <= strtotime($checkIn)) {
            $checkOut = date('Y-m-d', strtotime('+1 day', $start));
            if ($stayEnd !== '' && strtotime($checkOut) > strtotime($stayEnd)) {
                $checkOut = $stayEnd;
            }
        }

        return ['check_in_date' => $checkIn, 'check_out_date' => $checkOut];
    }

    private function packageStayWindow(array $package): array
    {
        $rawStart = (string) ($package['departure_date'] ?? '');
        $durationDays = (int) ($package['duration_days'] ?? 0);
        $rawEnd = (string) ($package['arrival_date'] ?? '');

        // Normalize to date-only (Y-m-d) — departure/arrival may be stored as DATETIME
        $toDate = static function (string $v): string {
            if ($v === '') return '';
            $ts = strtotime($v);
            return $ts !== false ? date('Y-m-d', $ts) : '';
        };

        $start = $toDate($rawStart);
        $end   = $toDate($rawEnd);

        if ($end === '') {
            $end = (string) ($this->deriveArrivalDate($start, $durationDays) ?? '');
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    private function parseDaysDistribution(string $distribution): array
    {
        $text = strtolower(trim($distribution));
        if ($text === '') {
            return ['makkah_days' => 0, 'madina_days' => 0];
        }

        $makkahDays = 0;
        $madinaDays = 0;

        if (preg_match('/makkah\s*[:=\-]?\s*(\d+)/i', $text, $makkahMatch) === 1) {
            $makkahDays = (int) ($makkahMatch[1] ?? 0);
        }
        if (preg_match('/(?:madina|medina)\s*[:=\-]?\s*(\d+)/i', $text, $madinaMatch) === 1) {
            $madinaDays = (int) ($madinaMatch[1] ?? 0);
        }

        if ($makkahDays > 0 || $madinaDays > 0) {
            return ['makkah_days' => $makkahDays, 'madina_days' => $madinaDays];
        }

        preg_match_all('/\d+/', $text, $matches);
        $numbers = array_map('intval', $matches[0] ?? []);

        if (count($numbers) >= 3) {
            return [
                'makkah_days' => max(0, (int) $numbers[0]) + max(0, (int) $numbers[2]),
                'madina_days' => max(0, (int) $numbers[1]),
            ];
        }

        if (count($numbers) === 2) {
            return [
                'makkah_days' => max(0, (int) $numbers[0]),
                'madina_days' => max(0, (int) $numbers[1]),
            ];
        }

        if (count($numbers) === 1) {
            return ['makkah_days' => max(0, (int) $numbers[0]), 'madina_days' => 0];
        }

        return ['makkah_days' => 0, 'madina_days' => 0];
    }

    private function calculateCostSheet(array $payload): array
    {
        $visa = (float) $payload['visa_sar'] * (float) $payload['visa_ex_rate'];
        $transport = (float) $payload['transport_sar'] * (float) $payload['transport_ex_rate'];
        $ticket = (float) $payload['ticket_pkr'];
        $makkah = (float) $payload['makkah_room_rate_sar'] * (float) $payload['makkah_ex_rate'] * (int) $payload['makkah_nights'];
        $madina = (float) $payload['madina_room_rate_sar'] * (float) $payload['madina_ex_rate'] * (int) $payload['madina_nights'];
        $other = $payload['other_pkr'] !== '' ? (float) $payload['other_pkr'] : 0.0;
        $profit = (float) $payload['profit_pkr'];

        $alloc = [
            'sharing4' => ['makkah' => 4, 'madina' => 4],
            'sharing5' => ['makkah' => 5, 'madina' => 5],
            'quad' => ['makkah' => 4, 'madina' => 4],
            'triple' => ['makkah' => 3, 'madina' => 3],
            'double' => ['makkah' => 2, 'madina' => 2],
        ];

        $lines = [];
        foreach ($alloc as $key => $divider) {
            $makkahShare = $divider['makkah'] > 0 ? $makkah / $divider['makkah'] : 0;
            $madinaShare = $divider['madina'] > 0 ? $madina / $divider['madina'] : 0;
            $totalCost = $visa + $transport + $ticket + $makkahShare + $madinaShare + $other;
            $lines[$key] = [
                'total_cost_pkr' => round($totalCost, 2),
                'sell_price_pkr' => round($totalCost + $profit, 2),
            ];
        }

        return [
            'componentTotals' => [
                'visa' => round($visa, 2),
                'transport' => round($transport, 2),
                'ticket' => round($ticket, 2),
                'makkah' => round($makkah, 2),
                'madina' => round($madina, 2),
                'other' => round($other, 2),
            ],
            'lines' => $lines,
        ];
    }
}
