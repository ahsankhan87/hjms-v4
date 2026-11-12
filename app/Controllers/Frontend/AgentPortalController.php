<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\BookingModel;
use App\Models\Frontend\AgentClientModel;
use App\Models\PackageModel;
use App\Services\Frontend\AgentPricingService;
use App\Services\PackagePricingService;

class AgentPortalController extends BaseController
{
    public function dashboard()
    {
        $agentUserId = (int) session('user_id');
        $rows = $this->agentBookingsQuery($agentUserId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $pending = 0;
        foreach ($rows as $row) {
            if (strtolower((string) ($row['status'] ?? 'pending')) === 'pending') {
                $pending++;
            }
        }

        return view('frontend/portal/agent/dashboard', [
            'title' => 'Agent Portal | HJMS Travel',
            'agentActive' => 'dashboard',
            'summary' => [
                'total' => count($rows),
                'pending' => $pending,
            ],
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function bookings()
    {
        $agentUserId = (int) session('user_id');
        $rows = $this->agentBookingsQuery($agentUserId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $details = $this->decodeDetails($row);
            $row['booking_type'] = 'package';
            $row['item_id'] = (int) ($row['package_id'] ?? 0);
            $row['display_item'] = (string) ($details['item_name'] ?? ('Package #' . (int) ($row['package_id'] ?? 0)));
            $row['display_date'] = (string) ($details['travel_date'] ?? $details['check_in'] ?? ($row['created_at'] ?? ''));
            $row['client_name'] = (string) ($details['client_name'] ?? '-');
        }

        return view('frontend/portal/agent/bookings', [
            'title' => 'Agent Bookings | HJMS Travel',
            'agentActive' => 'bookings',
            'rows' => $rows,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function book()
    {
        $agentId = (int) session('user_id');
        $bookingType = 'package';

        $clients = (new AgentClientModel())
            ->where('agent_id', $agentId)
            ->orderBy('id', 'DESC')
            ->findAll();

        $items = $this->listBookableItems($bookingType);

        return view('frontend/portal/agent/book', [
            'title' => 'Book for Client | HJMS Travel',
            'agentActive' => 'book',
            'bookingType' => $bookingType,
            'clients' => $clients,
            'items' => $items,
            'errors' => session()->getFlashdata('errors') ?? [],
            'error' => session()->getFlashdata('error'),
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function storeBooking()
    {
        $agentId = (int) session('user_id');
        $payload = [
            'booking_type' => strtolower(trim((string) $this->request->getPost('booking_type'))),
            'item_id' => (int) $this->request->getPost('item_id'),
            'client_id' => (int) $this->request->getPost('client_id'),
            'travelers_count' => (int) $this->request->getPost('travelers_count'),
            'travel_date' => trim((string) $this->request->getPost('travel_date')),
            'check_in' => trim((string) $this->request->getPost('check_in')),
            'check_out' => trim((string) $this->request->getPost('check_out')),
            'special_requests' => trim((string) $this->request->getPost('special_requests')),
        ];

        $rules = [
            'booking_type' => 'required|in_list[package]',
            'item_id' => 'required|integer|greater_than[0]',
            'client_id' => 'required|integer|greater_than[0]',
            'travelers_count' => 'required|integer|greater_than[0]|less_than_equal_to[50]',
            'travel_date' => 'permit_empty|valid_date',
            'special_requests' => 'permit_empty|max_length[2000]',
        ];
        $rules['travel_date'] = 'required|valid_date';

        if (! $this->validateData($payload, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $client = (new AgentClientModel())
            ->where('id', $payload['client_id'])
            ->where('agent_id', $agentId)
            ->first();
        if (! is_array($client)) {
            return redirect()->back()->withInput()->with('error', 'Selected client not found.');
        }

        $item = $this->resolveItem($payload['booking_type'], $payload['item_id']);
        if ($item === null) {
            return redirect()->back()->withInput()->with('error', 'Selected item not found.');
        }

        $pricePerUnit = (float) ($item['agent_price'] ?? 0);
        $amount = $this->calculateAmount('package', $pricePerUnit, $payload['travelers_count'], '', '');

        $details = [
            'item_name' => (string) ($item['name'] ?? ''),
            'client_id' => (int) $client['id'],
            'client_name' => (string) ($client['name'] ?? ''),
            'client_passport_no' => (string) ($client['passport_no'] ?? ''),
            'client_phone' => (string) ($client['phone'] ?? ''),
            'client_email' => (string) ($client['email'] ?? ''),
            'travelers_count' => $payload['travelers_count'],
            'travel_date' => $payload['travel_date'],
            'special_requests' => $payload['special_requests'],
            'price_per_unit' => $pricePerUnit,
        ];

        if (! function_exists('active_season_id') || (int) active_season_id() < 1) {
            return redirect()->back()->withInput()->with('error', 'Active season is required before creating bookings.');
        }

        $travelersCount = max(1, (int) $payload['travelers_count']);
        $unitPrice = round($amount / $travelersCount, 2);
        $bookingNo = 'ABKG-' . date('YmdHis') . '-' . mt_rand(100, 999);
        $db = db_connect();
        $bookingData = [
            'booking_no' => $bookingNo,
            'package_id' => (int) $payload['item_id'],
            'status' => 'pending',
            'pricing_tier' => 'sharing',
            'unit_price' => $unitPrice,
            'total_amount' => $amount,
            'pricing_source' => 'frontend_agent_portal',
            'price_locked_at' => date('Y-m-d H:i:s'),
            'total_pilgrims' => $travelersCount,
            'remarks' => json_encode($details),
            'created_by' => $agentId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($db->fieldExists('frontend_user_id', 'bookings')) {
            $bookingData['frontend_user_id'] = $agentId;
        }

        (new BookingModel())->insert($bookingData);

        return redirect()->to('/portal/agent/bookings')->with('success', 'Agent booking request created for client.');
    }

    public function clients()
    {
        $agentId = (int) session('user_id');
        $editId = (int) ($this->request->getGet('edit') ?? 0);

        $rows = (new AgentClientModel())
            ->where('agent_id', $agentId)
            ->orderBy('id', 'DESC')
            ->findAll();

        $editRow = null;
        if ($editId > 0) {
            foreach ($rows as $row) {
                if ((int) ($row['id'] ?? 0) === $editId) {
                    $editRow = $row;
                    break;
                }
            }
        }

        return view('frontend/portal/agent/clients', [
            'title' => 'Agent Clients | HJMS Travel',
            'agentActive' => 'clients',
            'rows' => $rows,
            'editRow' => $editRow,
            'errors' => session()->getFlashdata('errors') ?? [],
            'error' => session()->getFlashdata('error'),
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function storeClient()
    {
        $agentId = (int) session('user_id');
        $payload = [
            'name' => trim((string) $this->request->getPost('name')),
            'passport_no' => trim((string) $this->request->getPost('passport_no')),
            'phone' => trim((string) $this->request->getPost('phone')),
            'email' => trim((string) $this->request->getPost('email')),
        ];

        if (! $this->validateData($payload, [
            'name' => 'required|min_length[2]|max_length[160]',
            'passport_no' => 'permit_empty|max_length[80]',
            'phone' => 'permit_empty|max_length[40]',
            'email' => 'permit_empty|valid_email|max_length[191]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new AgentClientModel())->insert([
            'agent_id' => $agentId,
            'name' => $payload['name'],
            'passport_no' => $payload['passport_no'] !== '' ? $payload['passport_no'] : null,
            'phone' => $payload['phone'] !== '' ? $payload['phone'] : null,
            'email' => $payload['email'] !== '' ? strtolower($payload['email']) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/portal/agent/clients')->with('success', 'Client added successfully.');
    }

    public function updateClient(int $id)
    {
        $agentId = (int) session('user_id');
        if ($id < 1) {
            return redirect()->to('/portal/agent/clients')->with('error', 'Client not found.');
        }

        $existing = (new AgentClientModel())
            ->where('id', $id)
            ->where('agent_id', $agentId)
            ->first();
        if (! is_array($existing)) {
            return redirect()->to('/portal/agent/clients')->with('error', 'Client not found.');
        }

        $payload = [
            'name' => trim((string) $this->request->getPost('name')),
            'passport_no' => trim((string) $this->request->getPost('passport_no')),
            'phone' => trim((string) $this->request->getPost('phone')),
            'email' => trim((string) $this->request->getPost('email')),
        ];

        if (! $this->validateData($payload, [
            'name' => 'required|min_length[2]|max_length[160]',
            'passport_no' => 'permit_empty|max_length[80]',
            'phone' => 'permit_empty|max_length[40]',
            'email' => 'permit_empty|valid_email|max_length[191]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new AgentClientModel())->update($id, [
            'name' => $payload['name'],
            'passport_no' => $payload['passport_no'] !== '' ? $payload['passport_no'] : null,
            'phone' => $payload['phone'] !== '' ? $payload['phone'] : null,
            'email' => $payload['email'] !== '' ? strtolower($payload['email']) : null,
        ]);

        return redirect()->to('/portal/agent/clients')->with('success', 'Client updated successfully.');
    }

    public function deleteClient(int $id)
    {
        $agentId = (int) session('user_id');
        if ($id < 1) {
            return redirect()->to('/portal/agent/clients')->with('error', 'Client not found.');
        }

        (new AgentClientModel())
            ->where('id', $id)
            ->where('agent_id', $agentId)
            ->delete();

        return redirect()->to('/portal/agent/clients')->with('success', 'Client deleted successfully.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function listBookableItems(string $bookingType): array
    {
        $agentPricing = new AgentPricingService();
        $rows = (new PackageModel())->where('is_active', 1)->orderBy('id', 'DESC')->findAll(100);
        $pricing = new PackagePricingService();
        $items = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $summary = $pricing->summarizePackage($id);
            $regular = 0.0;
            if (($summary['mode'] ?? 'tiered') === 'flat') {
                $regular = (float) ($summary['flat_price'] ?? 0);
            } else {
                $map = (array) ($summary['price_map'] ?? []);
                if ($map !== []) {
                    $regular = min(array_map('floatval', $map));
                }
            }
            if ($regular <= 0) {
                $regular = (float) ($row['selling_price'] ?? 0);
            }
            $agent = $agentPricing->resolveForRow('packages', $id, $regular);
            $items[] = [
                'id' => $id,
                'name' => (string) ($row['name'] ?? ('Package #' . $id)),
                'regular_price' => round($regular, 2),
                'agent_price' => round($agent, 2),
            ];
        }

        return $items;
    }

    private function resolveItem(string $bookingType, int $itemId): ?array
    {
        if ($itemId < 1) {
            return null;
        }

        $items = $this->listBookableItems($bookingType);
        foreach ($items as $item) {
            if ((int) ($item['id'] ?? 0) === $itemId) {
                return $item;
            }
        }

        return null;
    }

    private function calculateAmount(string $bookingType, float $pricePerUnit, int $travelers, string $checkIn, string $checkOut): float
    {
        if ($pricePerUnit <= 0) {
            return 0.0;
        }

        return round($pricePerUnit * max(1, $travelers), 2);
    }

    private function decodeDetails(array $row): array
    {
        $decoded = json_decode((string) ($row['remarks'] ?? ''), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function agentBookingsQuery(int $agentUserId)
    {
        $db = db_connect();
        $builder = $db->table('bookings');
        if ($db->fieldExists('frontend_user_id', 'bookings')) {
            $builder->where('frontend_user_id', $agentUserId);
        } else {
            $builder->where('created_by', $agentUserId);
        }

        return $builder;
    }
}
