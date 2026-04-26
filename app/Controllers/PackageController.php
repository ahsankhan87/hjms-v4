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
use App\Models\PackageHotelStayModel;
use App\Models\PackageModel;
use App\Models\PackagePriceLineModel;
use App\Models\PackageTransportModel;
use App\Models\SupplierLedgerEntryModel;
use App\Models\SupplierModel;
use App\Services\PackagePricingService;
use App\Models\TransportModel;

class PackageController extends BaseController
{
    public function index()
    {
        if ($this->activeSeasonId() === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $autoInactivated = $this->autoDeactivateDepartedPackages();
        $model = new PackageModel();
        $rows = $model->where('is_active', 1)->orderBy('id', 'DESC')->findAll();
        $inactiveCount = (int) (new PackageModel())->where('is_active', 0)->countAllResults();

        $successMessage = session()->getFlashdata('success');
        if ($autoInactivated > 0 && empty($successMessage)) {
            $successMessage = $autoInactivated . ' departed package(s) were auto-inactivated.';
        }

        return view('portal/packages/index', [
            'title'       => 'HJMS ERP | Packages',
            'headerTitle' => 'Package Management',
            'activePage'  => 'packages',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $rows,
            'cards'       => $this->buildPackageCards($rows),
            'inactiveCount' => $inactiveCount,
            'success'     => $successMessage,
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function inactive()
    {
        if ($this->activeSeasonId() === null) {
            return redirect()->to('/seasons')->with('error', 'Please create and activate a season first.');
        }

        $this->autoDeactivateDepartedPackages();

        $model = new PackageModel();
        $rows = $model->where('is_active', 0)->orderBy('departure_date', 'DESC')->orderBy('id', 'DESC')->findAll();

        return view('portal/packages/inactive', [
            'title'       => 'HJMS ERP | Inactive Packages',
            'headerTitle' => 'Package Management',
            'activePage'  => 'packages',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $rows,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function setPackageStatus()
    {
        $payload = [
            'package_id' => (int) $this->request->getPost('package_id'),
            'is_active' => (string) $this->request->getPost('is_active'),
            'redirect_to' => trim((string) $this->request->getPost('redirect_to')),
        ];

        if (! $this->validateData($payload, [
            'package_id' => 'required|integer',
            'is_active' => 'required|in_list[0,1]',
            'redirect_to' => 'permit_empty|max_length[120]',
        ])) {
            return redirect()->to('/packages')->withInput()->with('errors', $this->validator->getErrors());
        }

        $redirectTo = '/packages';
        if ($payload['redirect_to'] === 'packages/inactive') {
            $redirectTo = '/packages/inactive';
        }

        try {
            $model = new PackageModel();
            $row = $model->find($payload['package_id']);

            if (empty($row)) {
                return redirect()->to($redirectTo)->with('error', 'Package not found.');
            }

            $model->update($payload['package_id'], [
                'is_active' => (int) $payload['is_active'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $message = (int) $payload['is_active'] === 1
                ? 'Package activated successfully.'
                : 'Package moved to inactive list.';

            return redirect()->to($redirectTo)->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->to($redirectTo)->with('error', $e->getMessage());
        }
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

        $hotelRows = $this->packageHotelCardRows($packageIds, $db);

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

        $bookedRows = $db->table('booking_pilgrims bp')
            ->select('b.package_id, COUNT(bp.id) AS booked_count')
            ->join('bookings b', 'b.id = bp.booking_id', 'inner')
            ->whereIn('b.package_id', $packageIds)
            ->where('b.status !=', 'cancelled')
            ->groupBy('b.package_id')
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

        $bookedByPackage = [];
        foreach ($bookedRows as $item) {
            $bookedByPackage[(int) ($item['package_id'] ?? 0)] = (int) ($item['booked_count'] ?? 0);
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

            $hotelSeatCaps = [];
            foreach ($linkedHotels as $hotel) {
                $hotelSeats = (int) ($hotel['total_seats'] ?? 0);
                if ($hotelSeats > 0) {
                    $hotelSeatCaps[] = $hotelSeats;
                }
            }
            $seatCapacity = $hotelSeatCaps !== []
                ? min($hotelSeatCaps)
                : (int) ($row['total_seats'] ?? 0);
            $bookedSeats = (int) ($bookedByPackage[$packageId] ?? 0);
            $availableSeats = max(0, $seatCapacity - $bookedSeats);

            $priceMap = [];
            foreach ($linkedCosts as $cost) {
                $type = strtolower(trim((string) ($cost['cost_type'] ?? '')));
                if ($type === '') {
                    continue;
                }
                $priceMap[$type] = (float) ($cost['cost_amount'] ?? 0);
            }

            $packageMode = (int) ($row['include_hotel'] ?? 1) === 1 ? 'tiered' : 'flat';
            $flatPrice = null;
            if ($packageMode === 'flat') {
                foreach ($priceMap as $value) {
                    if ($value !== null && is_numeric($value)) {
                        $flatPrice = (float) $value;
                        break;
                    }
                }
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
                'available_seats' => $availableSeats,
                'seat_capacity' => $seatCapacity,
                'booked_seats' => $bookedSeats,
                'price_map' => $priceMap,
                'package_mode' => $packageMode,
                'flat_price' => $flatPrice,
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

    private function autoDeactivateDepartedPackages(): int
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return 0;
        }

        $db = db_connect();
        $todayStart = date('Y-m-d 00:00:00');

        $db->table('packages')
            ->set([
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->where('season_id', $seasonId)
            ->where('is_active', 1)
            ->where('departure_date <', $todayStart)
            ->update();

        return (int) $db->affectedRows();
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
        $stayDistributionValue = trim((string) old('stay_distribution', (string) session('package_stay_distribution_' . $id)));
        if ($stayDistributionValue === '') {
            $stayDistributionValue = trim((string) ($row['notes'] ?? ''));
        }

        $stayCheckIn = $this->lastPackageStayCheckout($id, $db);
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

        $hotelPricingRows = $db->table('package_hotels ph')
            ->select('ph.*, h.name AS hotel_master_name, h.city AS hotel_city')
            ->join('hotels h', 'h.id = ph.hotel_id', 'left')
            ->where('ph.package_id', $id)
            ->orderBy('ph.id', 'ASC')
            ->get()
            ->getResultArray();

        $hotelStayRows = $this->packageHotelStayRows($id, $db);
        $existingHotelPricing = [];
        foreach ($hotelPricingRows as $hotelPricingRow) {
            $hotelId = (int) ($hotelPricingRow['hotel_id'] ?? 0);
            if ($hotelId < 1 || isset($existingHotelPricing[$hotelId])) {
                continue;
            }

            $existingHotelPricing[$hotelId] = [
                'sharing_cost' => (float) ($hotelPricingRow['sharing_cost'] ?? 0),
                'quad_cost' => (float) ($hotelPricingRow['quad_cost'] ?? 0),
                'triple_cost' => (float) ($hotelPricingRow['triple_cost'] ?? 0),
                'double_cost' => (float) ($hotelPricingRow['double_cost'] ?? 0),
                'total_seats' => (int) ($hotelPricingRow['total_seats'] ?? 0),
                'hotel_name' => (string) ($hotelPricingRow['hotel_name'] ?: ($hotelPricingRow['hotel_master_name'] ?? '')),
                'hotel_city' => (string) ($hotelPricingRow['hotel_city'] ?? ''),
            ];
        }

        $pricingSummary = (new PackagePricingService($db))->summarizePackage($id);

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
            'hotelRows' => $hotelStayRows,
            'hotelPricingRows' => $hotelPricingRows,
            'existingHotelPricing' => $existingHotelPricing,
            'currentHotelStayCount' => count($hotelStayRows),
            'stayDistributionValue' => $stayDistributionValue,
            'stayCheckIn' => $stayCheckIn,
            'stayCheckOut' => $stayCheckOut,
            'packageStayStart' => $packageStayStart,
            'packageStayEnd' => $packageStayEnd,
            'flightRows' => $db->table('package_flights pf')
                ->select('pf.*, f.pnr, f.departure_airport, f.arrival_airport')
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
            'pricingSummary' => $pricingSummary,
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

            (new PackagePricingService())->recalculatePackage($packageId);

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

    public function quickCreateHotel()
    {
        $isAjax = $this->request->isAJAX();
        $payload = [
            'package_id' => (int) $this->request->getPost('package_id'),
            'hotel_name' => trim((string) $this->request->getPost('hotel_name')),
            'hotel_city' => trim((string) $this->request->getPost('hotel_city')),
            'hotel_star_rating' => trim((string) $this->request->getPost('hotel_star_rating')),
            'hotel_distance_m' => trim((string) $this->request->getPost('hotel_distance_m')),
            'hotel_address' => trim((string) $this->request->getPost('hotel_address')),
        ];

        if (! $this->validateData($payload, [
            'package_id' => 'required|integer',
            'hotel_name' => 'required|max_length[180]',
            'hotel_city' => 'permit_empty|max_length[100]',
            'hotel_star_rating' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[7]',
            'hotel_distance_m' => 'permit_empty|integer|greater_than_equal_to[0]',
            'hotel_address' => 'permit_empty',
        ])) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit#hotel-section')
                ->withInput()
                ->with('open_quick_modal', 'hotel')
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new HotelModel();
            $model->insert([
                'name' => $payload['hotel_name'],
                'city' => $payload['hotel_city'] !== '' ? $payload['hotel_city'] : null,
                'star_rating' => $payload['hotel_star_rating'] !== '' ? (int) $payload['hotel_star_rating'] : 3,
                'distance_m' => $payload['hotel_distance_m'] !== '' ? (int) $payload['hotel_distance_m'] : null,
                'address' => $payload['hotel_address'] !== '' ? $payload['hotel_address'] : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $createdId = (int) $model->getInsertID();

            if ($isAjax) {
                return $this->response->setJSON([
                    'status' => 'ok',
                    'message' => 'Hotel created successfully.',
                    'item' => [
                        'id' => $createdId,
                        'name' => $payload['hotel_name'],
                        'city' => $payload['hotel_city'],
                    ],
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit#hotel-section')
                ->with('quick_hotel_created_id', $createdId)
                ->with('success', 'Hotel created. You can now attach it to this package.');
        } catch (\Throwable $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit#hotel-section')
                ->withInput()
                ->with('open_quick_modal', 'hotel')
                ->with('error', $e->getMessage());
        }
    }

    public function quickCreateFlight()
    {
        $isAjax = $this->request->isAJAX();
        $payload = [
            'package_id' => (int) $this->request->getPost('package_id'),
            'outbound_airline' => trim((string) $this->request->getPost('outbound_airline')),
            'outbound_flight_no' => trim((string) $this->request->getPost('outbound_flight_no')),
            'outbound_pnr' => trim((string) $this->request->getPost('outbound_pnr')),
            'outbound_departure_airport' => trim((string) $this->request->getPost('outbound_departure_airport')),
            'outbound_arrival_airport' => trim((string) $this->request->getPost('outbound_arrival_airport')),
            'outbound_departure_at' => $this->normalizeDateTimeInput((string) $this->request->getPost('outbound_departure_at')),
            'outbound_arrival_at' => $this->normalizeDateTimeInput((string) $this->request->getPost('outbound_arrival_at')),
            'return_airline' => trim((string) $this->request->getPost('return_airline')),
            'return_flight_no' => trim((string) $this->request->getPost('return_flight_no')),
            'return_pnr' => trim((string) $this->request->getPost('return_pnr')),
            'return_departure_airport' => trim((string) $this->request->getPost('return_departure_airport')),
            'return_arrival_airport' => trim((string) $this->request->getPost('return_arrival_airport')),
            'return_departure_at' => $this->normalizeDateTimeInput((string) $this->request->getPost('return_departure_at')),
            'return_arrival_at' => $this->normalizeDateTimeInput((string) $this->request->getPost('return_arrival_at')),
        ];

        if (! $this->validateData($payload, [
            'package_id' => 'required|integer',
            'outbound_airline' => 'required|max_length[120]',
            'outbound_flight_no' => 'required|max_length[30]',
            'outbound_pnr' => 'permit_empty|max_length[30]',
            'outbound_departure_airport' => 'permit_empty|max_length[80]',
            'outbound_arrival_airport' => 'permit_empty|max_length[80]',
            'outbound_departure_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
            'outbound_arrival_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
            'return_airline' => 'required|max_length[120]',
            'return_flight_no' => 'required|max_length[30]',
            'return_pnr' => 'permit_empty|max_length[30]',
            'return_departure_airport' => 'permit_empty|max_length[80]',
            'return_arrival_airport' => 'permit_empty|max_length[80]',
            'return_departure_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
            'return_arrival_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        ])) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit#flight-section')
                ->withInput()
                ->with('open_quick_modal', 'flight')
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new FlightModel();
            $model->insert([
                'airline' => $payload['outbound_airline'],
                'flight_no' => $payload['outbound_flight_no'],
                'pnr' => $payload['outbound_pnr'] !== '' ? $payload['outbound_pnr'] : null,
                'departure_airport' => $payload['outbound_departure_airport'] !== '' ? $payload['outbound_departure_airport'] : null,
                'arrival_airport' => $payload['outbound_arrival_airport'] !== '' ? $payload['outbound_arrival_airport'] : null,
                'departure_at' => $payload['outbound_departure_at'] !== '' ? $payload['outbound_departure_at'] : null,
                'arrival_at' => $payload['outbound_arrival_at'] !== '' ? $payload['outbound_arrival_at'] : null,
                'return_airline' => $payload['return_airline'],
                'return_flight_no' => $payload['return_flight_no'],
                'return_pnr' => $payload['return_pnr'] !== '' ? $payload['return_pnr'] : null,
                'return_departure_airport' => $payload['return_departure_airport'] !== '' ? $payload['return_departure_airport'] : null,
                'return_arrival_airport' => $payload['return_arrival_airport'] !== '' ? $payload['return_arrival_airport'] : null,
                'return_departure_at' => $payload['return_departure_at'] !== '' ? $payload['return_departure_at'] : null,
                'return_arrival_at' => $payload['return_arrival_at'] !== '' ? $payload['return_arrival_at'] : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $createdId = (int) $model->getInsertID();

            if ($isAjax) {
                $outboundLabel = trim($payload['outbound_airline'] . ' ' . $payload['outbound_flight_no']);
                $outboundRoute = trim($payload['outbound_departure_airport'] . ' -> ' . $payload['outbound_arrival_airport'], ' ->');
                $returnLabel = trim($payload['return_airline'] . ' ' . $payload['return_flight_no']);
                $returnRoute = trim($payload['return_departure_airport'] . ' -> ' . $payload['return_arrival_airport'], ' ->');
                $optionText = 'OUT: ' . ($outboundLabel !== '' ? $outboundLabel : '-')
                    . ' | ' . ($outboundRoute !== '' ? $outboundRoute : '-')
                    . ' | ' . ($payload['outbound_departure_at'] !== '' ? $payload['outbound_departure_at'] : '-')
                    . ' || RET: ' . ($returnLabel !== '' ? $returnLabel : '-')
                    . ' | ' . ($returnRoute !== '' ? $returnRoute : '-')
                    . ' | ' . ($payload['return_departure_at'] !== '' ? $payload['return_departure_at'] : '-');

                return $this->response->setJSON([
                    'status' => 'ok',
                    'message' => 'Flight created successfully.',
                    'item' => [
                        'id' => $createdId,
                        'optionText' => $optionText,
                    ],
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit#flight-section')
                ->with('quick_flight_created_id', $createdId)
                ->with('success', 'Flight created. You can now attach it to this package.');
        } catch (\Throwable $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit#flight-section')
                ->withInput()
                ->with('open_quick_modal', 'flight')
                ->with('error', $e->getMessage());
        }
    }

    public function quickCreateTransport()
    {
        $isAjax = $this->request->isAJAX();
        $payload = [
            'package_id' => (int) $this->request->getPost('package_id'),
            'transport_name' => trim((string) $this->request->getPost('transport_name')),
            'provider_name' => trim((string) $this->request->getPost('provider_name')),
            'vehicle_type' => strtolower(trim((string) $this->request->getPost('vehicle_type'))),
            'seat_capacity' => trim((string) $this->request->getPost('seat_capacity')),
            'driver_name' => trim((string) $this->request->getPost('driver_name')),
            'driver_phone' => trim((string) $this->request->getPost('driver_phone')),
        ];

        if (! $this->validateData($payload, [
            'package_id' => 'required|integer',
            'transport_name' => 'required|max_length[180]',
            'provider_name' => 'required|max_length[180]',
            'vehicle_type' => 'required|in_list[self,coaster,car,bus,van,minibus,suv]',
            'seat_capacity' => 'permit_empty|integer|greater_than_equal_to[0]',
            'driver_name' => 'permit_empty|max_length[120]',
            'driver_phone' => 'permit_empty|max_length[40]',
        ])) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit#transport-section')
                ->withInput()
                ->with('open_quick_modal', 'transport')
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new TransportModel();
            $model->insert([
                'transport_name' => $payload['transport_name'],
                'provider_name' => $payload['provider_name'],
                'vehicle_type' => $payload['vehicle_type'],
                'seat_capacity' => $payload['seat_capacity'] !== '' ? (int) $payload['seat_capacity'] : 0,
                'driver_name' => $payload['driver_name'] !== '' ? $payload['driver_name'] : null,
                'driver_phone' => $payload['driver_phone'] !== '' ? $payload['driver_phone'] : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $createdId = (int) $model->getInsertID();

            if ($isAjax) {
                $optionText = $payload['transport_name']
                    . ' | ' . $payload['provider_name']
                    . ' | ' . ucfirst($payload['vehicle_type'])
                    . ' | Seats: ' . ($payload['seat_capacity'] !== '' ? $payload['seat_capacity'] : '0');

                return $this->response->setJSON([
                    'status' => 'ok',
                    'message' => 'Transport created successfully.',
                    'item' => [
                        'id' => $createdId,
                        'optionText' => $optionText,
                    ],
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit#transport-section')
                ->with('quick_transport_created_id', $createdId)
                ->with('success', 'Transport created. You can now attach it to this package.');
        } catch (\Throwable $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit#transport-section')
                ->withInput()
                ->with('open_quick_modal', 'transport')
                ->with('error', $e->getMessage());
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
        $isAjax = $this->request->isAJAX();
        $payload = [
            'package_id'    => (int) $this->request->getPost('package_id'),
            'hotel_id' => (int) $this->request->getPost('hotel_id'),
            'check_in_date' => (string) $this->request->getPost('check_in_date'),
            'check_out_date' => (string) $this->request->getPost('check_out_date'),
            'stay_distribution' => trim((string) $this->request->getPost('stay_distribution')),
            'sharing_cost' => trim((string) $this->request->getPost('sharing_cost')),
            'quad_cost' => trim((string) $this->request->getPost('quad_cost')),
            'triple_cost' => trim((string) $this->request->getPost('triple_cost')),
            'double_cost' => trim((string) $this->request->getPost('double_cost')),
            'total_seats' => trim((string) $this->request->getPost('total_seats')),
        ];

        if (! $this->validateData($payload, [
            'package_id'    => 'required|integer',
            'hotel_id' => 'required|integer',
            'check_in_date' => 'permit_empty|valid_date[Y-m-d]',
            'check_out_date' => 'permit_empty|valid_date[Y-m-d]',
            'stay_distribution' => 'permit_empty|max_length[120]',
            'sharing_cost' => 'permit_empty|decimal',
            'quad_cost' => 'permit_empty|decimal',
            'triple_cost' => 'permit_empty|decimal',
            'double_cost' => 'permit_empty|decimal',
            'total_seats' => 'permit_empty|integer|greater_than_equal_to[0]',
        ])) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $db = db_connect();
            $hotel = $db->table('hotels h')
                ->select('h.id, h.name AS hotel_name, h.city AS hotel_city')
                ->where('h.id', $payload['hotel_id'])
                ->get()
                ->getRowArray();

            if (empty($hotel)) {
                if ($isAjax) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error',
                        'message' => 'Selected hotel not found.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', 'Selected hotel not found.');
            }

            $package = (new PackageModel())->find($payload['package_id']);
            if (empty($package)) {
                if ($isAjax) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error',
                        'message' => 'Package not found.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages')->with('error', 'Package not found.');
            }

            if ($payload['stay_distribution'] === '') {
                $payload['stay_distribution'] = trim((string) session('package_stay_distribution_' . $payload['package_id']));
            }

            if ($payload['stay_distribution'] === '') {
                $payload['stay_distribution'] = trim((string) ($package['notes'] ?? ''));
            }

            $stayWindow = $this->packageStayWindow($package);
            $packageStayStart = (string) ($stayWindow['start'] ?? '');
            $packageStayEnd = (string) ($stayWindow['end'] ?? '');

            if ($packageStayStart === '' || $packageStayEnd === '') {
                if ($isAjax) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status' => 'error',
                        'message' => 'Package departure/arrival dates are required before hotel allocation.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Package departure/arrival dates are required before hotel allocation.');
            }

            $expectedCheckIn = $this->lastPackageStayCheckout($payload['package_id'], $db);
            if ($expectedCheckIn === '') {
                $expectedCheckIn = $packageStayStart;
            }
            if (strtotime($expectedCheckIn) >= strtotime($packageStayEnd)) {
                if ($isAjax) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status' => 'error',
                        'message' => 'Package hotel duration is already fully allocated.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Package hotel duration is already fully allocated.');
            }

            $existingStayCount = $this->packageHotelStayCount($payload['package_id'], $db);
            $window = $this->inferHotelStayWindow(
                $package,
                (string) ($hotel['hotel_city'] ?? ''),
                $payload['stay_distribution'],
                $existingStayCount,
                $expectedCheckIn,
                $packageStayEnd
            );

            $checkInDate = $payload['check_in_date'] !== '' ? $payload['check_in_date'] : $expectedCheckIn;
            $checkOutDate = $payload['check_out_date'];

            if ($checkInDate !== $expectedCheckIn) {
                if ($isAjax) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status' => 'error',
                        'message' => 'Next hotel check-in must be ' . $expectedCheckIn . ' to maintain sequence.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Next hotel check-in must be ' . $expectedCheckIn . ' to maintain sequence.');
            }

            if ($checkInDate === '' || $checkOutDate === '') {
                $checkInDate = $checkInDate !== '' ? $checkInDate : ($window['check_in_date'] ?? '');
                $checkOutDate = $checkOutDate !== '' ? $checkOutDate : ($window['check_out_date'] ?? '');
            }

            if (! empty($window['expected_city']) && ! ($window['city_matches'] ?? true)) {
                if ($isAjax) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status' => 'error',
                        'message' => 'The next stay segment is for ' . ucfirst((string) $window['expected_city']) . '. Please select a ' . ucfirst((string) $window['expected_city']) . ' hotel for this leg.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'The next stay segment is for ' . ucfirst((string) $window['expected_city']) . '. Please select a ' . ucfirst((string) $window['expected_city']) . ' hotel for this leg.');
            }

            if ($payload['stay_distribution'] !== '' && ! empty($window['check_out_date'])) {
                $checkOutDate = (string) $window['check_out_date'];
            }

            if ($checkInDate === '' || $checkOutDate === '') {
                if ($isAjax) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status' => 'error',
                        'message' => 'Check-in and check-out dates are required.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Check-in and check-out dates are required.');
            }

            if (strtotime($checkOutDate) <= strtotime($checkInDate)) {
                if ($isAjax) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status' => 'error',
                        'message' => 'Check-out date must be after check-in date.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Check-out date must be after check-in date.');
            }

            if (strtotime($checkOutDate) > strtotime($packageStayEnd)) {
                if ($isAjax) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status' => 'error',
                        'message' => 'Check-out cannot exceed package stay end date (' . $packageStayEnd . ').',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Check-out cannot exceed package stay end date (' . $packageStayEnd . ').');
            }

            $hotelModel = new PackageHotelModel();
            $hotelProfile = $hotelModel
                ->where('package_id', $payload['package_id'])
                ->where('hotel_id', (int) ($hotel['id'] ?? 0))
                ->orderBy('id', 'ASC')
                ->first();

            if (empty($hotelProfile)) {
                foreach (['sharing_cost', 'quad_cost', 'triple_cost', 'double_cost'] as $costField) {
                    if ($payload[$costField] === '') {
                        if ($isAjax) {
                            return $this->response->setStatusCode(422)->setJSON([
                                'status' => 'error',
                                'message' => 'Pricing is required the first time a hotel is attached to a package.',
                                'csrf' => [
                                    'tokenName' => csrf_token(),
                                    'hash' => csrf_hash(),
                                ],
                            ]);
                        }

                        return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Pricing is required the first time a hotel is attached to a package.');
                    }
                }

                if ($payload['total_seats'] === '' || (int) $payload['total_seats'] < 1) {
                    if ($isAjax) {
                        return $this->response->setStatusCode(422)->setJSON([
                            'status' => 'error',
                            'message' => 'Total seats are required the first time a hotel is attached to a package.',
                            'csrf' => [
                                'tokenName' => csrf_token(),
                                'hash' => csrf_hash(),
                            ],
                        ]);
                    }

                    return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Total seats are required the first time a hotel is attached to a package.');
                }

                $hotelModel->insert([
                    'package_id'    => $payload['package_id'],
                    'hotel_id'      => (int) ($hotel['id'] ?? 0),
                    'hotel_room_id' => null,
                    'hotel_name'    => (string) ($hotel['hotel_name'] ?? ''),
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                    'room_type'     => null,
                    'cost_amount'   => 0,
                    'sharing_cost'  => (float) $payload['sharing_cost'],
                    'quad_cost'     => (float) $payload['quad_cost'],
                    'triple_cost'   => (float) $payload['triple_cost'],
                    'double_cost'   => (float) $payload['double_cost'],
                    'total_seats'   => (int) $payload['total_seats'],
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
                $packageHotelId = (int) $hotelModel->getInsertID();
            } else {
                $packageHotelId = (int) ($hotelProfile['id'] ?? 0);
                if ($payload['total_seats'] !== '') {
                    $hotelModel->update($packageHotelId, [
                        'total_seats' => (int) $payload['total_seats'],
                    ]);
                    $hotelProfile['total_seats'] = (int) $payload['total_seats'];
                }
            }

            if ($packageHotelId < 1) {
                if ($isAjax) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'status' => 'error',
                        'message' => 'Unable to save the package hotel profile.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('error', 'Unable to save the package hotel profile.');
            }

            $createdStayId = null;
            if ($this->packageHotelStayTableExists($db)) {
                $stayModel = new PackageHotelStayModel();
                $stayModel->insert([
                    'package_id' => $payload['package_id'],
                    'package_hotel_id' => $packageHotelId,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $createdStayId = (int) $stayModel->getInsertID();
                $this->syncPackageHotelStayWindow($packageHotelId, $db);
            }

            if ($payload['stay_distribution'] !== '') {
                session()->set('package_stay_distribution_' . $payload['package_id'], $payload['stay_distribution']);
            }

            (new PackagePricingService($db))->recalculatePackage($payload['package_id']);

            if ($isAjax) {
                return $this->response->setJSON([
                    'status' => 'ok',
                    'message' => empty($hotelProfile) ? 'Hotel pricing and stay added successfully.' : 'Hotel stay added using existing package pricing.',
                    'item' => [
                        'id' => $createdStayId !== null ? $createdStayId : $packageHotelId,
                        'hotel_name' => (string) ($hotel['hotel_name'] ?? ''),
                        'hotel_city' => (string) ($hotel['hotel_city'] ?? ''),
                        'check_in_date' => (string) $checkInDate,
                        'check_out_date' => (string) $checkOutDate,
                        'sharing_cost' => (float) ($hotelProfile['sharing_cost'] ?? $payload['sharing_cost'] ?? 0),
                        'quad_cost' => (float) ($hotelProfile['quad_cost'] ?? $payload['quad_cost'] ?? 0),
                        'triple_cost' => (float) ($hotelProfile['triple_cost'] ?? $payload['triple_cost'] ?? 0),
                        'double_cost' => (float) ($hotelProfile['double_cost'] ?? $payload['double_cost'] ?? 0),
                        'total_seats' => (int) ($hotelProfile['total_seats'] ?? $payload['total_seats'] ?? 0),
                    ],
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('success', empty($hotelProfile) ? 'Hotel pricing and stay added successfully.' : 'Hotel stay added using existing package pricing.');
        } catch (\Throwable $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', $e->getMessage());
        }
    }

    public function deletePackageHotel()
    {
        $isAjax = $this->request->isAJAX();
        $rowId = (int) $this->request->getPost('package_hotel_id');
        $packageId = (int) $this->request->getPost('package_id');

        if ($rowId < 1 || $packageId < 1) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Valid package hotel and package IDs are required for delete.',
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages')->with('error', 'Valid package hotel and package IDs are required for delete.');
        }

        try {
            $db = db_connect();
            $deleted = false;

            if ($this->packageHotelStayTableExists($db)) {
                $stayModel = new PackageHotelStayModel();
                $stayRow = $stayModel->find($rowId);
                if (! empty($stayRow)) {
                    $profileId = (int) ($stayRow['package_hotel_id'] ?? 0);
                    $deleted = (bool) $stayModel->delete($rowId);
                    if ($deleted && $profileId > 0) {
                        $remainingStays = $stayModel->where('package_hotel_id', $profileId)->countAllResults();
                        if ($remainingStays > 0) {
                            $this->syncPackageHotelStayWindow($profileId, $db);
                        } else {
                            $deleted = (bool) (new PackageHotelModel())->delete($profileId);
                        }
                    }
                }
            }

            if (! $deleted) {
                $deleted = (bool) (new PackageHotelModel())->delete($rowId);
            }
            if (! $deleted) {
                if ($isAjax) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error',
                        'message' => 'Package hotel attachment not found or already removed.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $packageId . '/edit')->with('error', 'Package hotel attachment not found or already removed.');
            }

            (new PackagePricingService())->recalculatePackage($packageId);

            if ($isAjax) {
                return $this->response->setJSON([
                    'status' => 'ok',
                    'message' => 'Hotel link deleted successfully.',
                    'deleted_id' => $rowId,
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $packageId . '/edit')->with('success', 'Package hotel attachment deleted successfully.');
        } catch (\Throwable $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $packageId . '/edit')->with('error', $e->getMessage());
        }
    }

    public function createPackageFlight()
    {
        $isAjax = $this->request->isAJAX();
        $payload = [
            'package_id'   => (int) $this->request->getPost('package_id'),
            'flight_id'    => (int) $this->request->getPost('flight_id'),
            'cost_amount'  => (string) $this->request->getPost('cost_amount'),
        ];

        if (! $this->validateData($payload, [
            'package_id'  => 'required|integer',
            'flight_id'   => 'required|integer',
            'cost_amount' => 'required|decimal',
        ])) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $flightModel = new FlightModel();
            $flight = $flightModel->find($payload['flight_id']);

            if (empty($flight)) {
                if ($isAjax) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error',
                        'message' => 'Selected flight was not found.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', 'Selected flight was not found.');
            }

            $packageFlightModel = new PackageFlightModel();

            // Insert single package_flights row using flight data
            $packageFlightModel->insert([
                'package_id'   => $payload['package_id'],
                'flight_id'    => $payload['flight_id'],
                'airline'      => (string) ($flight['airline'] ?? ''),
                'flight_no'    => (string) ($flight['flight_no'] ?? ''),
                'departure_at' => (string) ($flight['departure_at'] ?? null),
                'arrival_at'   => (string) ($flight['arrival_at'] ?? null),
                'cost_amount'  => (float) $payload['cost_amount'],
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            $createdId = (int) $packageFlightModel->getInsertID();

            (new PackagePricingService())->recalculatePackage($payload['package_id']);

            if ($isAjax) {
                return $this->response->setJSON([
                    'status' => 'ok',
                    'message' => 'Flight attached to package successfully.',
                    'item' => [
                        'id' => $createdId,
                        'journey_label' => 'ROUND TRIP',
                        'airline' => (string) ($flight['airline'] ?? ''),
                        'flight_no' => (string) ($flight['flight_no'] ?? ''),
                        'departure_airport' => (string) ($flight['departure_airport'] ?? ''),
                        'arrival_airport' => (string) ($flight['arrival_airport'] ?? ''),
                        'departure_at' => (string) ($flight['departure_at'] ?? ''),
                        'arrival_at' => (string) ($flight['arrival_at'] ?? ''),
                        'pnr' => (string) ($flight['pnr'] ?? ''),
                        'cost_amount' => (float) $payload['cost_amount'],
                    ],
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('success', 'Flight attached to package successfully.');
        } catch (\Throwable $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', $e->getMessage());
        }
    }

    public function deletePackageFlight()
    {
        $isAjax = $this->request->isAJAX();
        $rowId = (int) $this->request->getPost('package_flight_id');
        $packageId = (int) $this->request->getPost('package_id');

        if ($rowId < 1 || $packageId < 1) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Valid package flight and package IDs are required for delete.',
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages')->with('error', 'Valid package flight and package IDs are required for delete.');
        }

        try {
            $deleted = (new PackageFlightModel())->delete($rowId);
            if (! $deleted) {
                if ($isAjax) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error',
                        'message' => 'Package flight attachment not found or already removed.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $packageId . '/edit')->with('error', 'Package flight attachment not found or already removed.');
            }

            (new PackagePricingService())->recalculatePackage($packageId);

            if ($isAjax) {
                return $this->response->setJSON([
                    'status' => 'ok',
                    'message' => 'Flight link deleted successfully.',
                    'deleted_id' => $rowId,
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $packageId . '/edit')->with('success', 'Package flight attachment deleted successfully.');
        } catch (\Throwable $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $packageId . '/edit')->with('error', $e->getMessage());
        }
    }

    public function createPackageTransport()
    {
        $isAjax = $this->request->isAJAX();
        $payload = [
            'package_id'   => (int) $this->request->getPost('package_id'),
            'transport_id' => (int) $this->request->getPost('transport_id'),
            'seat_capacity' => (string) $this->request->getPost('seat_capacity'),
            'cost_amount' => (string) $this->request->getPost('cost_amount'),
        ];

        if (! $this->validateData($payload, [
            'package_id'   => 'required|integer',
            'transport_id' => 'required|integer',
            'seat_capacity' => 'permit_empty|integer|greater_than_equal_to[0]',
            'cost_amount' => 'required|decimal',
        ])) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $transport = (new TransportModel())->find($payload['transport_id']);
            if (empty($transport)) {
                if ($isAjax) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error',
                        'message' => 'Selected transport not found.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', 'Selected transport not found.');
            }

            $seatCapacity = $payload['seat_capacity'] !== '' ? (int) $payload['seat_capacity'] : (int) ($transport['seat_capacity'] ?? 0);
            $model = new PackageTransportModel();
            $model->insert([
                'package_id'   => $payload['package_id'],
                'transport_id' => $payload['transport_id'],
                'provider_name' => (string) ($transport['provider_name'] ?? ''),
                'vehicle_type' => (string) ($transport['vehicle_type'] ?? ''),
                'seat_capacity' => $seatCapacity,
                'cost_amount' => (float) $payload['cost_amount'],
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            $createdId = (int) $model->getInsertID();

            (new PackagePricingService())->recalculatePackage($payload['package_id']);

            if ($isAjax) {
                return $this->response->setJSON([
                    'status' => 'ok',
                    'message' => 'Transport attached to package successfully.',
                    'item' => [
                        'id' => $createdId,
                        'transport_name' => (string) (($transport['transport_name'] ?? '') !== '' ? $transport['transport_name'] : '-'),
                        'provider_name' => (string) ($transport['provider_name'] ?? ''),
                        'vehicle_type' => (string) ($transport['vehicle_type'] ?? ''),
                        'seat_capacity' => $seatCapacity,
                        'cost_amount' => (float) $payload['cost_amount'],
                    ],
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('success', 'Transport attached to package successfully.');
        } catch (\Throwable $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $payload['package_id'] . '/edit')->with('error', $e->getMessage());
        }
    }

    public function deletePackageTransport()
    {
        $isAjax = $this->request->isAJAX();
        $rowId = (int) $this->request->getPost('package_transport_id');
        $packageId = (int) $this->request->getPost('package_id');

        if ($rowId < 1 || $packageId < 1) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => 'Valid package transport and package IDs are required for delete.',
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages')->with('error', 'Valid package transport and package IDs are required for delete.');
        }

        try {
            $deleted = (new PackageTransportModel())->delete($rowId);
            if (! $deleted) {
                if ($isAjax) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error',
                        'message' => 'Package transport attachment not found or already removed.',
                        'csrf' => [
                            'tokenName' => csrf_token(),
                            'hash' => csrf_hash(),
                        ],
                    ]);
                }

                return redirect()->to('/packages/' . $packageId . '/edit')->with('error', 'Package transport attachment not found or already removed.');
            }

            (new PackagePricingService())->recalculatePackage($packageId);

            if ($isAjax) {
                return $this->response->setJSON([
                    'status' => 'ok',
                    'message' => 'Transport link deleted successfully.',
                    'deleted_id' => $rowId,
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to('/packages/' . $packageId . '/edit')->with('success', 'Package transport attachment deleted successfully.');
        } catch (\Throwable $e) {
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'csrf' => [
                        'tokenName' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

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

    private function inferHotelStayWindow(array $package, string $hotelCity, string $stayDistribution = '', int $existingStayCount = 0, string $baseCheckIn = '', string $packageStayEnd = ''): array
    {
        $departureDate = (string) ($package['departure_date'] ?? '');
        $arrivalDate = (string) ($package['arrival_date'] ?? '');
        $durationDays = (int) ($package['duration_days'] ?? 0);
        $distribution = trim($stayDistribution);

        $arrivalDate = $arrivalDate !== '' ? $arrivalDate : ($this->deriveArrivalDate($departureDate, $durationDays) ?? '');
        if ($departureDate === '' || $arrivalDate === '') {
            return ['check_in_date' => '', 'check_out_date' => '', 'expected_city' => '', 'city_matches' => true];
        }

        $segments = $this->buildStaySegments($distribution, $durationDays);
        if ($segments === []) {
            $segments[] = ['city' => $this->normalizeStayCity($hotelCity), 'days' => max(1, $durationDays)];
        }

        $segmentIndex = $existingStayCount;
        if ($segmentIndex >= count($segments)) {
            $segmentIndex = count($segments) - 1;
        }
        if ($segmentIndex < 0) {
            $segmentIndex = 0;
        }

        $segment = $segments[$segmentIndex] ?? ['city' => $this->normalizeStayCity($hotelCity), 'days' => max(1, $durationDays)];
        $baseStart = $baseCheckIn !== '' ? $baseCheckIn : $departureDate;
        $start = strtotime($baseStart);
        if ($start === false) {
            return ['check_in_date' => '', 'check_out_date' => '', 'expected_city' => (string) ($segment['city'] ?? ''), 'city_matches' => true];
        }

        $segmentDays = max(1, (int) ($segment['days'] ?? 1));
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

        $expectedCity = (string) ($segment['city'] ?? '');
        $selectedCity = $this->normalizeStayCity($hotelCity);

        return [
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'expected_city' => $expectedCity,
            'city_matches' => $expectedCity === '' || $selectedCity === '' || $expectedCity === $selectedCity,
        ];
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

    private function buildStaySegments(string $distribution, int $durationDays): array
    {
        $text = strtolower(trim($distribution));
        if ($text !== '') {
            preg_match_all('/(makkah|madina|medina)\s*[:=\-]?\s*(\d+)/i', $text, $namedMatches, PREG_SET_ORDER);
            if ($namedMatches !== []) {
                $segments = [];
                foreach ($namedMatches as $match) {
                    $segments[] = [
                        'city' => $this->normalizeStayCity((string) ($match[1] ?? '')),
                        'days' => max(1, (int) ($match[2] ?? 0)),
                    ];
                }

                return $segments;
            }

            preg_match_all('/\d+/', $text, $numberMatches);
            $numbers = array_map('intval', $numberMatches[0] ?? []);
            if (count($numbers) >= 3) {
                return [
                    ['city' => 'makkah', 'days' => max(1, (int) $numbers[0])],
                    ['city' => 'madina', 'days' => max(1, (int) $numbers[1])],
                    ['city' => 'makkah', 'days' => max(1, (int) $numbers[2])],
                ];
            }

            if (count($numbers) === 2) {
                return [
                    ['city' => 'makkah', 'days' => max(1, (int) $numbers[0])],
                    ['city' => 'madina', 'days' => max(1, (int) $numbers[1])],
                ];
            }

            if (count($numbers) === 1) {
                return [
                    ['city' => 'makkah', 'days' => max(1, (int) $numbers[0])],
                ];
            }
        }

        return $durationDays > 0 ? [['city' => 'makkah', 'days' => $durationDays]] : [];
    }

    private function normalizeStayCity(string $city): string
    {
        $value = strtolower(trim($city));
        if ($value === '') {
            return '';
        }
        if (strpos($value, 'madina') !== false || strpos($value, 'medina') !== false || strpos($value, 'madinah') !== false) {
            return 'madina';
        }
        if (strpos($value, 'makkah') !== false || strpos($value, 'mecca') !== false) {
            return 'makkah';
        }

        return $value;
    }

    private function packageHotelStayTableExists($db = null): bool
    {
        static $exists = null;

        if ($exists !== null) {
            return $exists;
        }

        $db = $db ?? db_connect();
        $exists = $db->tableExists('package_hotel_stays');

        return $exists;
    }

    private function lastPackageStayCheckout(int $packageId, $db = null): string
    {
        $db = $db ?? db_connect();
        if ($this->packageHotelStayTableExists($db)) {
            $row = $db->table('package_hotel_stays')
                ->select('check_out_date')
                ->where('package_id', $packageId)
                ->orderBy('check_out_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            return (string) ($row['check_out_date'] ?? '');
        }

        $row = $db->table('package_hotels')
            ->select('check_out_date')
            ->where('package_id', $packageId)
            ->orderBy('check_out_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        return (string) ($row['check_out_date'] ?? '');
    }

    private function packageHotelStayCount(int $packageId, $db = null): int
    {
        $db = $db ?? db_connect();
        if ($this->packageHotelStayTableExists($db)) {
            return (int) $db->table('package_hotel_stays')->where('package_id', $packageId)->countAllResults();
        }

        return (int) $db->table('package_hotels')->where('package_id', $packageId)->countAllResults();
    }

    private function syncPackageHotelStayWindow(int $packageHotelId, $db = null): void
    {
        $db = $db ?? db_connect();
        if (! $this->packageHotelStayTableExists($db)) {
            return;
        }

        $range = $db->table('package_hotel_stays')
            ->select('MIN(check_in_date) AS min_check_in, MAX(check_out_date) AS max_check_out')
            ->where('package_hotel_id', $packageHotelId)
            ->get()
            ->getRowArray();

        if (empty($range)) {
            return;
        }

        (new PackageHotelModel())->update($packageHotelId, [
            'check_in_date' => (string) ($range['min_check_in'] ?? ''),
            'check_out_date' => (string) ($range['max_check_out'] ?? ''),
        ]);
    }

    private function packageHotelStayRows(int $packageId, $db = null): array
    {
        $db = $db ?? db_connect();
        if ($this->packageHotelStayTableExists($db)) {
            return $db->table('package_hotel_stays phs')
                ->select('phs.id, phs.package_id, phs.package_hotel_id, phs.check_in_date, phs.check_out_date, ph.hotel_id, ph.hotel_name, ph.sharing_cost, ph.quad_cost, ph.triple_cost, ph.double_cost, ph.total_seats, h.name AS hotel_master_name, h.city AS hotel_city')
                ->join('package_hotels ph', 'ph.id = phs.package_hotel_id', 'left')
                ->join('hotels h', 'h.id = ph.hotel_id', 'left')
                ->where('phs.package_id', $packageId)
                ->orderBy('phs.check_in_date', 'ASC')
                ->orderBy('phs.id', 'ASC')
                ->get()
                ->getResultArray();
        }

        return $db->table('package_hotels ph')
            ->select('ph.id, ph.package_id, ph.id AS package_hotel_id, ph.check_in_date, ph.check_out_date, ph.hotel_id, ph.hotel_name, ph.sharing_cost, ph.quad_cost, ph.triple_cost, ph.double_cost, ph.total_seats, h.name AS hotel_master_name, h.city AS hotel_city')
            ->join('hotels h', 'h.id = ph.hotel_id', 'left')
            ->where('ph.package_id', $packageId)
            ->orderBy('ph.check_in_date', 'ASC')
            ->orderBy('ph.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function packageHotelCardRows(array $packageIds, $db = null): array
    {
        $db = $db ?? db_connect();
        if ($packageIds === []) {
            return [];
        }

        if ($this->packageHotelStayTableExists($db)) {
            return $db->table('package_hotel_stays phs')
                ->select('phs.id, phs.package_id, phs.check_in_date, phs.check_out_date, ph.hotel_id, ph.hotel_name, ph.total_seats, h.name AS master_hotel_name, h.city')
                ->join('package_hotels ph', 'ph.id = phs.package_hotel_id', 'left')
                ->join('hotels h', 'h.id = ph.hotel_id', 'left')
                ->whereIn('phs.package_id', $packageIds)
                ->orderBy('phs.check_in_date', 'ASC')
                ->orderBy('phs.id', 'ASC')
                ->get()
                ->getResultArray();
        }

        return $db->table('package_hotels ph')
            ->select('ph.*, h.name AS master_hotel_name, h.city')
            ->join('hotels h', 'h.id = ph.hotel_id', 'left')
            ->whereIn('ph.package_id', $packageIds)
            ->orderBy('ph.id', 'ASC')
            ->get()
            ->getResultArray();
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
