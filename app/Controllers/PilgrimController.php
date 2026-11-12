<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PilgrimModel;

class PilgrimController extends BaseController
{
    const MAHRAM_OPTIONS = [
        '',
        'Grand Father',
        'Father',
        'Son',
        'Grand Son',
        'Brother',
        'Nephew',
        'Uncle',
        'Husband',
        'Father in law',
        'Son-in-law',
        'Stepfather (Mother\'s husband)',
        'Stepson (Husband\'s son)',
        'Self',
        'Women Group',
    ];

    const BLOOD_GROUP_OPTIONS = [
        'A+',
        'A-',
        'B+',
        'B-',
        'AB+',
        'AB-',
        'O+',
        'O-',
    ];

    const PASSPORT_MESSAGES = [
        'passport_no' => [
            'required'      => 'Passport number is required.',
            'min_length'    => 'Passport number must be at least 5 characters.',
            'max_length'    => 'Passport number must not exceed 20 characters.',
            'alpha_numeric' => 'Passport number may contain only letters and numbers.',
        ],
    ];
    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));
        $seasonId = $this->activeSeasonId();
        $linkedAgentId = $this->linkedAgentId();

        if ($seasonId === null) {
            return redirect()->to('/admin/seasons')->with('error', 'Please create and activate a season first.');
        }

        $rows = [];
        try {
            $db = db_connect();
            $seasonSql = (int) $seasonId;
            $builder = $db->table('pilgrims p')
                ->select('p.*, v.status AS latest_visa_status, v.visa_type AS latest_visa_type, v.visa_no AS latest_visa_no, bk.id AS booking_id, bk.booking_no, bk.status AS booking_status')
                ->join('visas v', 'v.id = (SELECT MAX(v2.id) FROM visas v2 WHERE v2.pilgrim_id = p.id AND v2.season_id = p.season_id)', 'left')
                ->join('(SELECT bp.pilgrim_id, MAX(b.id) AS booking_id
                    FROM booking_pilgrims bp
                    INNER JOIN bookings b ON b.id = bp.booking_id
                    WHERE b.season_id = ' . $seasonSql . '
                    GROUP BY bp.pilgrim_id) pb', 'pb.pilgrim_id = p.id', 'left')
                ->join('bookings bk', 'bk.id = pb.booking_id', 'left')
                ->where('p.season_id', $seasonId);

            if ($linkedAgentId !== null) {
                $builder->where('p.agent_id', $linkedAgentId);
            }

            if ($q !== '') {
                $builder->groupStart()
                    ->like('p.first_name', $q)
                    ->orLike('p.last_name', $q)
                    ->orLike('p.passport_no', $q)
                    ->orLike('p.cnic', $q)
                    ->orLike('p.phone', $q)
                    ->orLike('p.mobile_no', $q)
                    ->orLike('p.email', $q)
                    ->orLike('v.visa_no', $q)
                    ->groupEnd();
            }

            $rows = $builder->orderBy('p.id', 'DESC')->get()->getResultArray();
        } catch (\Throwable $e) {
            $model = new PilgrimModel();
            if ($q !== '') {
                $model->groupStart()
                    ->like('first_name', $q)
                    ->orLike('last_name', $q)
                    ->orLike('passport_no', $q)
                    ->orLike('cnic', $q)
                    ->orLike('phone', $q)
                    ->orLike('mobile_no', $q)
                    ->orLike('email', $q)
                    ->groupEnd();
            }
            if ($linkedAgentId !== null) {
                $model->where('agent_id', $linkedAgentId);
            }
            $rows = $model->orderBy('id', 'DESC')->findAll();
        }

        $pilgrims = array_map(static function (array $row): array {
            $row['full_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

            return $row;
        }, $rows);

        return view('portal/pilgrims/index', [
            'title'       => 'HJMS ERP | Pilgrims',
            'headerTitle' => 'Pilgrim Management',
            'activePage'  => 'pilgrims',
            'userEmail'   => (string) session('user_email'),
            'pilgrims'    => $pilgrims,
            'q'           => $q,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function importPage()
    {
        return view('portal/pilgrims/import', [
            'title'       => 'HJMS ERP | Import Pilgrims',
            'headerTitle' => 'Pilgrim Management',
            'activePage'  => 'pilgrims',
            'userEmail'   => (string) session('user_email'),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add()
    {
        return view('portal/pilgrims/add', [
            'title'       => 'HJMS ERP | Add Pilgrim',
            'headerTitle' => 'Pilgrim Management',
            'activePage'  => 'pilgrims',
            'userEmail'   => (string) session('user_email'),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $linkedAgentId = $this->linkedAgentId();
        $model = new PilgrimModel();
        $row = $model->where('id', $id)->where('season_id', $this->activeSeasonId())->first();

        if ($linkedAgentId !== null && (int) ($row['agent_id'] ?? 0) !== $linkedAgentId) {
            $row = null;
        }

        if (empty($row)) {
            return redirect()->to('/admin/pilgrims')->with('error', 'Pilgrim not found.');
        }

        $row['full_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

        return view('portal/pilgrims/edit', [
            'title'       => 'HJMS ERP | Edit Pilgrim',
            'headerTitle' => 'Pilgrim Management',
            'activePage'  => 'pilgrims',
            'userEmail'   => (string) session('user_email'),
            'row'         => $row,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function show(int $id)
    {
        $seasonId = $this->activeSeasonId();
        $linkedAgentId = $this->linkedAgentId();
        if ($seasonId === null) {
            return redirect()->to('/admin/seasons')->with('error', 'Please create and activate a season first.');
        }

        $db = db_connect();
        $row = null;
        try {
            $builder = $db->table('pilgrims p')
                ->select('p.*')
                ->where('p.id', $id)
                ->where('p.season_id', $seasonId);
            if ($linkedAgentId !== null) {
                $builder->where('p.agent_id', $linkedAgentId);
            }
            $row = $builder->get()->getRowArray();
        } catch (\Throwable $e) {
            $row = null;
        }

        if (empty($row)) {
            return redirect()->to('/admin/pilgrims')->with('error', 'Pilgrim not found.');
        }

        // Agent name
        $agentName = '';
        if (! empty($row['agent_id'])) {
            try {
                $agent = $db->table('agents')->select('name')->where('id', (int) $row['agent_id'])->get()->getRowArray();
                $agentName = (string) ($agent['name'] ?? '');
            } catch (\Throwable $e) {
                $agentName = '';
            }
        }

        // Latest visa record
        $visa = null;
        try {
            $visa = $db->table('visas')
                ->where('pilgrim_id', $id)
                ->where('season_id', $seasonId)
                ->orderBy('id', 'DESC')
                ->get()->getRowArray();
        } catch (\Throwable $e) {
            $visa = null;
        }

        // Linked bookings
        $bookings = [];
        try {
            $bookings = $db->table('booking_pilgrims bp')
                ->select('b.id, b.booking_no, b.status, b.total_amount, b.total_pilgrims, b.created_at AS booking_date')
                ->join('bookings b', 'b.id = bp.booking_id', 'inner')
                ->where('bp.pilgrim_id', $id)
                ->orderBy('b.id', 'DESC')
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            $bookings = [];
        }

        // Availability for the active season: a pilgrim is "availed" when a confirmed
        // booking OR a non-voided sale exists for this season that was created after
        // their last release.
        $releasedAt = trim((string) ($row['sale_released_at'] ?? ''));
        $isAvailed = false;
        $availedSource = null;

        try {
            $confirmedRows = $db->table('booking_pilgrims bp')
                ->select('b.id, b.booking_no, b.created_at')
                ->join('bookings b', 'b.id = bp.booking_id', 'inner')
                ->where('bp.pilgrim_id', $id)
                ->where('b.season_id', $seasonId)
                ->where('b.status', 'confirmed')
                ->orderBy('b.id', 'DESC')
                ->get()->getResultArray();

            foreach ($confirmedRows as $confirmedRow) {
                if ($releasedAt === '' || strtotime((string) ($confirmedRow['created_at'] ?? '')) > strtotime($releasedAt)) {
                    $isAvailed = true;
                    $availedSource = [
                        'type'  => 'booking',
                        'label' => (string) ($confirmedRow['booking_no'] ?? ('Booking #' . ($confirmedRow['id'] ?? ''))),
                    ];
                    break;
                }
            }
        } catch (\Throwable $e) {
            $isAvailed = false;
            $availedSource = null;
        }

        if (! $isAvailed && $db->tableExists('sales')) {
            try {
                $saleRows = $db->query(
                    'SELECT s.id, s.invoice_no, s.sale_type, s.created_at
                     FROM sales s
                     WHERE s.season_id = ' . (int) $seasonId . '
                       AND IFNULL(s.status, "posted") != "voided"
                       AND (s.pilgrim_id = ' . (int) $id . ' OR FIND_IN_SET(' . (int) $id . ', s.pilgrim_ids))
                     ORDER BY s.id DESC'
                )->getResultArray();

                foreach ($saleRows as $saleRow) {
                    if ($releasedAt === '' || strtotime((string) ($saleRow['created_at'] ?? '')) > strtotime($releasedAt)) {
                        $isAvailed = true;
                        $availedSource = [
                            'type'  => 'sale',
                            'label' => (string) ($saleRow['invoice_no'] ?? ('Sale #' . ($saleRow['id'] ?? ''))),
                        ];
                        break;
                    }
                }
            } catch (\Throwable $e) {
                // keep whatever the booking check produced
            }
        }

        // Travel history across all seasons, matched by passport number.
        $travelHistory = [];
        $historySummary = ['trips' => 0, 'completed' => 0, 'billed' => 0.0, 'paid' => 0.0, 'outstanding' => 0.0];
        $passportNo = trim((string) ($row['passport_no'] ?? ''));
        if ($passportNo !== '') {
            try {
                $identityIds = array_values(array_unique(array_filter(array_map('intval', array_column(
                    $db->table('pilgrims')->select('id')->where('passport_no', $passportNo)->get()->getResultArray(),
                    'id'
                )))));

                if ($identityIds !== []) {
                    $travelHistory = $db->table('booking_pilgrims bp')
                        ->select('b.id, b.booking_no, b.status, b.total_amount, b.total_pilgrims, b.created_at AS booking_date, b.season_id,
                                  p.name AS package_name, p.code AS package_code, p.package_type, p.departure_date, p.arrival_date,
                                  s.name AS season_name, s.year_start, s.year_end,
                                  pl.id AS pilgrim_row_id, pl.season_id AS pilgrim_season_id')
                        ->join('bookings b', 'b.id = bp.booking_id', 'inner')
                        ->join('packages p', 'p.id = b.package_id', 'left')
                        ->join('seasons s', 's.id = b.season_id', 'left')
                        ->join('pilgrims pl', 'pl.id = bp.pilgrim_id', 'left')
                        ->whereIn('bp.pilgrim_id', $identityIds)
                        ->orderBy('b.created_at', 'DESC')
                        ->orderBy('b.id', 'DESC')
                        ->get()->getResultArray();

                    $bookingIds = array_values(array_unique(array_map(static function (array $item): int {
                        return (int) ($item['id'] ?? 0);
                    }, $travelHistory)));

                    $paidByBooking = [];
                    if ($bookingIds !== [] && $db->tableExists('payments')) {
                        $paymentRows = $db->table('payments')
                            ->select('booking_id, payment_type, amount, status')
                            ->whereIn('booking_id', $bookingIds)
                            ->get()->getResultArray();
                        foreach ($paymentRows as $paymentRow) {
                            if (strtolower(trim((string) ($paymentRow['status'] ?? 'posted'))) !== 'posted') {
                                continue;
                            }
                            $amount = (float) ($paymentRow['amount'] ?? 0);
                            if ((string) ($paymentRow['payment_type'] ?? 'payment') === 'refund') {
                                $amount = -$amount;
                            }
                            $bookingKey = (int) ($paymentRow['booking_id'] ?? 0);
                            $paidByBooking[$bookingKey] = ($paidByBooking[$bookingKey] ?? 0.0) + $amount;
                        }
                    }

                    $visaMap = [];
                    if ($db->tableExists('visas')) {
                        $visaRows = $db->table('visas')->whereIn('pilgrim_id', $identityIds)->get()->getResultArray();
                        foreach ($visaRows as $visaRow) {
                            $visaMap[(int) ($visaRow['pilgrim_id'] ?? 0) . '-' . (int) ($visaRow['season_id'] ?? 0)] = $visaRow;
                        }
                    }

                    foreach ($travelHistory as &$trip) {
                        $paid = (float) ($paidByBooking[(int) ($trip['id'] ?? 0)] ?? 0.0);
                        $billed = (float) ($trip['total_amount'] ?? 0);
                        $trip['paid_amount'] = $paid;
                        $trip['outstanding_amount'] = max(0, $billed - $paid);
                        $trip['visa'] = $visaMap[(int) ($trip['pilgrim_row_id'] ?? 0) . '-' . (int) ($trip['season_id'] ?? 0)] ?? null;

                        $historySummary['trips']++;
                        if (strtolower(trim((string) ($trip['status'] ?? ''))) === 'confirmed') {
                            $historySummary['completed']++;
                        }
                        $historySummary['billed'] += $billed;
                        $historySummary['paid'] += $paid;
                    }
                    unset($trip);

                    $historySummary['outstanding'] = max(0, $historySummary['billed'] - $historySummary['paid']);
                }
            } catch (\Throwable $e) {
                $travelHistory = [];
            }
        }

        $row['full_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

        return view('portal/pilgrims/show', [
            'title'       => 'HJMS ERP | ' . $row['full_name'],
            'headerTitle' => 'Pilgrim Management',
            'activePage'  => 'pilgrims',
            'userEmail'   => (string) session('user_email'),
            'row'         => $row,
            'agentName'   => $agentName,
            'visa'        => $visa,
            'bookings'    => $bookings,
            'isAvailed'      => $isAvailed,
            'availedSource'  => $availedSource,
            'travelHistory'  => $travelHistory,
            'historySummary' => $historySummary,
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createPilgrim()
    {
        $seasonId = $this->activeSeasonId();
        $linkedAgentId = $this->linkedAgentId();
        if ($seasonId === null) {
            return redirect()->to('/admin/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = [
            'first_name'           => trim((string) $this->request->getPost('first_name')),
            'last_name'            => trim((string) $this->request->getPost('last_name')),
            'father_name'          => trim((string) $this->request->getPost('father_name')),
            'passport_no'          => $this->normalizePassportNo((string) $this->request->getPost('passport_no')),
            'cnic'                 => trim((string) $this->request->getPost('cnic')),
            'country'              => trim((string) $this->request->getPost('country')),
            'gender'               => trim((string) $this->request->getPost('gender')),
            'date_of_birth'        => trim((string) $this->request->getPost('date_of_birth')),
            'passport_issue_date'  => trim((string) $this->request->getPost('passport_issue_date')),
            'passport_expiry_date' => trim((string) $this->request->getPost('passport_expiry_date')),
            'city'                 => trim((string) $this->request->getPost('city')),
            'mobile_no'            => trim((string) $this->request->getPost('mobile_no')),
            'mehram'               => trim((string) $this->request->getPost('mehram')),
            'description'          => trim((string) $this->request->getPost('description')),
            'phone'                => trim((string) $this->request->getPost('phone')),
            'email'                => trim((string) $this->request->getPost('email')),
            'reference'            => trim((string) $this->request->getPost('reference')),
            'blood_group'          => trim((string) $this->request->getPost('blood_group')),
        ];

        if (! $this->validateData($payload, [
            'first_name'           => 'required|min_length[2]|max_length[120]',
            'last_name'            => 'permit_empty|max_length[120]',
            'father_name'          => 'permit_empty|max_length[120]',
            'passport_no'          => 'required|min_length[5]|max_length[20]|alpha_numeric',
            'cnic'                 => 'permit_empty|max_length[30]',
            'country'              => 'permit_empty|in_list[Pakistan,Others]',
            'gender'               => 'permit_empty|in_list[male,female]',
            'date_of_birth'        => 'permit_empty|valid_date[Y-m-d]',
            'passport_issue_date'  => 'permit_empty|valid_date[Y-m-d]',
            'passport_expiry_date' => 'permit_empty|valid_date[Y-m-d]',
            'city'                 => 'permit_empty|max_length[100]',
            'mobile_no'            => 'permit_empty|max_length[30]',
            'mehram'               => 'permit_empty|in_list[' . implode(',', self::MAHRAM_OPTIONS) . ']',
            'description'          => 'permit_empty|max_length[1000]',
            'phone'                => 'permit_empty|max_length[30]',
            'email'                => 'permit_empty|valid_email',
            'reference'            => 'permit_empty|max_length[200]',
            'blood_group'          => 'permit_empty|in_list[' . implode(',', self::BLOOD_GROUP_OPTIONS) . ']',
        ], self::PASSPORT_MESSAGES)) {
            return redirect()->to('/admin/pilgrims/add')->withInput()->with('errors', $this->validator->getErrors());
        }

        $ruleErrors = $this->pilgrimRuleErrors($payload, $seasonId);
        if ($ruleErrors !== []) {
            return redirect()->to('/admin/pilgrims/add')->withInput()->with('errors', $ruleErrors);
        }

        $imageData = $this->handlePilgrimImageUploads();
        if (isset($imageData['error'])) {
            return redirect()->to('/admin/pilgrims/add')->withInput()->with('error', (string) $imageData['error']);
        }

        try {
            $model = new PilgrimModel();
            $insertData = [
                'season_id'            => $seasonId,
                'first_name'           => $payload['first_name'],
                'last_name'            => $payload['last_name'] !== '' ? $payload['last_name'] : '-',
                'father_name'          => $payload['father_name'] !== '' ? $payload['father_name'] : null,
                'passport_no'          => $payload['passport_no'],
                'cnic'                 => $payload['cnic'] !== '' ? $payload['cnic'] : null,
                'country'              => $payload['country'] !== '' ? $payload['country'] : 'Pakistan',
                'gender'               => $payload['gender'] !== '' ? $payload['gender'] : null,
                'date_of_birth'        => $payload['date_of_birth'] !== '' ? $payload['date_of_birth'] : null,
                'passport_issue_date'  => $payload['passport_issue_date'] !== '' ? $payload['passport_issue_date'] : null,
                'passport_expiry_date' => $payload['passport_expiry_date'] !== '' ? $payload['passport_expiry_date'] : null,
                'city'                 => $payload['city'] !== '' ? $payload['city'] : null,
                'mobile_no'            => $payload['mobile_no'] !== '' ? $payload['mobile_no'] : null,
                'mehram'               => $payload['mehram'] !== '' ? $payload['mehram'] : null,
                'description'          => $payload['description'] !== '' ? $payload['description'] : null,
                'phone'                => $payload['phone'] !== '' ? $payload['phone'] : null,
                'email'                => $payload['email'] !== '' ? $payload['email'] : null,
                'reference'            => $payload['reference'] !== '' ? $payload['reference'] : null,
                'blood_group'          => $payload['blood_group'] !== '' ? $payload['blood_group'] : null,
                'is_active'            => 1,
                'created_at'           => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s'),
            ] + $imageData;

            if ($linkedAgentId !== null) {
                $insertData['agent_id'] = $linkedAgentId;
            }

            $model->insert($insertData);

            return redirect()->to('/admin/pilgrims')->with('success', 'Pilgrim created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/admin/pilgrims')->withInput()->with('error', $e->getMessage());
        }
    }

    public function updatePilgrim()
    {
        $pilgrimId = (int) $this->request->getPost('pilgrim_id');
        $seasonId = $this->activeSeasonId();
        $linkedAgentId = $this->linkedAgentId();
        if ($seasonId === null) {
            return redirect()->to('/admin/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = [
            'first_name'           => trim((string) $this->request->getPost('first_name')),
            'last_name'            => trim((string) $this->request->getPost('last_name')),
            'father_name'          => trim((string) $this->request->getPost('father_name')),
            'passport_no'          => $this->normalizePassportNo((string) $this->request->getPost('passport_no')),
            'cnic'                 => trim((string) $this->request->getPost('cnic')),
            'country'              => trim((string) $this->request->getPost('country')),
            'gender'               => trim((string) $this->request->getPost('gender')),
            'date_of_birth'        => trim((string) $this->request->getPost('date_of_birth')),
            'passport_issue_date'  => trim((string) $this->request->getPost('passport_issue_date')),
            'passport_expiry_date' => trim((string) $this->request->getPost('passport_expiry_date')),
            'city'                 => trim((string) $this->request->getPost('city')),
            'mobile_no'            => trim((string) $this->request->getPost('mobile_no')),
            'mehram'               => trim((string) $this->request->getPost('mehram')),
            'description'          => trim((string) $this->request->getPost('description')),
            'phone'                => trim((string) $this->request->getPost('phone')),
            'email'                => trim((string) $this->request->getPost('email')),
            'reference'            => trim((string) $this->request->getPost('reference')),
            'blood_group'          => trim((string) $this->request->getPost('blood_group')),
            'is_active'            => (string) $this->request->getPost('is_active'),
        ];

        $currentPilgrim = $pilgrimId > 0 ? (new PilgrimModel())->where('id', $pilgrimId)->where('season_id', $seasonId)->first() : null;
        $passportChanged = $currentPilgrim === null
            || $this->normalizePassportNo((string) ($currentPilgrim['passport_no'] ?? '')) !== $payload['passport_no'];
        if ($pilgrimId < 1) {
            return redirect()->to('/admin/pilgrims')->withInput()->with('error', 'Valid pilgrim ID is required.');
        }

        if (! $this->validateData($payload, [
            'first_name'           => 'required|min_length[2]|max_length[120]',
            'last_name'            => 'permit_empty|max_length[120]',
            'father_name'          => 'permit_empty|max_length[120]',
            'passport_no'          => $passportChanged ? 'required|min_length[5]|max_length[20]|alpha_numeric' : 'required|max_length[50]',
            'cnic'                 => 'permit_empty|max_length[30]',
            'country'              => 'permit_empty|in_list[Pakistan,Others]',
            'gender'               => 'permit_empty|in_list[male,female]',
            'date_of_birth'        => 'permit_empty|valid_date[Y-m-d]',
            'passport_issue_date'  => 'permit_empty|valid_date[Y-m-d]',
            'passport_expiry_date' => 'permit_empty|valid_date[Y-m-d]',
            'city'                 => 'permit_empty|max_length[100]',
            'mobile_no'            => 'permit_empty|max_length[30]',
            'mehram'               => 'permit_empty|in_list[' . implode(',', self::MAHRAM_OPTIONS) . ']',
            'description'          => 'permit_empty|max_length[1000]',
            'phone'                => 'permit_empty|max_length[30]',
            'email'                => 'permit_empty|valid_email',
            'reference'            => 'permit_empty|max_length[200]',
            'blood_group'          => 'permit_empty|in_list[' . implode(',', self::BLOOD_GROUP_OPTIONS) . ']',
            'is_active'            => 'permit_empty|in_list[0,1]',
        ], self::PASSPORT_MESSAGES)) {
            return redirect()->to('/admin/pilgrims/' . $pilgrimId . '/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        $ruleErrors = $this->pilgrimRuleErrors($payload, $seasonId, $pilgrimId, $passportChanged);
        if ($ruleErrors !== []) {
            return redirect()->to('/admin/pilgrims/' . $pilgrimId . '/edit')->withInput()->with('errors', $ruleErrors);
        }

        $imageData = $this->handlePilgrimImageUploads();
        if (isset($imageData['error'])) {
            return redirect()->to('/admin/pilgrims/' . $pilgrimId . '/edit')->withInput()->with('error', (string) $imageData['error']);
        }

        try {
            $model = new PilgrimModel();
            $existingQuery = $model->where('id', $pilgrimId)->where('season_id', $seasonId);
            if ($linkedAgentId !== null) {
                $existingQuery->where('agent_id', $linkedAgentId);
            }
            $existing = $existingQuery->first();
            if (empty($existing)) {
                return redirect()->to('/admin/pilgrims')->with('error', 'Pilgrim not found in active season.');
            }

            $removable = [
                'remove_pilgrim_image'  => ['path' => 'pilgrim_image_path', 'name' => 'pilgrim_image_name'],
                'remove_passport_image' => ['path' => 'passport_image_path', 'name' => 'passport_image_name'],
                'remove_attachment_1'   => ['path' => 'attachment_1'],
                'remove_attachment_2'   => ['path' => 'attachment_2'],
            ];
            $removeData = [];
            foreach ($removable as $flag => $cols) {
                if ((string) $this->request->getPost($flag) !== '1') {
                    continue;
                }
                if (isset($imageData[$cols['path']])) {
                    continue;
                }
                asset_upload_remove((string) ($existing[$cols['path']] ?? ''), 'pilgrims');
                $removeData[$cols['path']] = null;
                if (isset($cols['name'])) {
                    $removeData[$cols['name']] = null;
                }
            }

            $model->update($pilgrimId, [
                'first_name'           => $payload['first_name'],
                'last_name'            => $payload['last_name'] !== '' ? $payload['last_name'] : '-',
                'father_name'          => $payload['father_name'] !== '' ? $payload['father_name'] : null,
                'passport_no'          => $payload['passport_no'],
                'cnic'                 => $payload['cnic'] !== '' ? $payload['cnic'] : null,
                'country'              => $payload['country'] !== '' ? $payload['country'] : null,
                'gender'               => $payload['gender'] !== '' ? $payload['gender'] : null,
                'date_of_birth'        => $payload['date_of_birth'] !== '' ? $payload['date_of_birth'] : null,
                'passport_issue_date'  => $payload['passport_issue_date'] !== '' ? $payload['passport_issue_date'] : null,
                'passport_expiry_date' => $payload['passport_expiry_date'] !== '' ? $payload['passport_expiry_date'] : null,
                'city'                 => $payload['city'] !== '' ? $payload['city'] : null,
                'mobile_no'            => $payload['mobile_no'] !== '' ? $payload['mobile_no'] : null,
                'mehram'               => $payload['mehram'] !== '' ? $payload['mehram'] : null,
                'description'          => $payload['description'] !== '' ? $payload['description'] : null,
                'phone'                => $payload['phone'] !== '' ? $payload['phone'] : null,
                'email'                => $payload['email'] !== '' ? $payload['email'] : null,
                'reference'            => $payload['reference'] !== '' ? $payload['reference'] : null,
                'blood_group'          => $payload['blood_group'] !== '' ? $payload['blood_group'] : null,
                'is_active'            => $payload['is_active'] !== '' ? (int) $payload['is_active'] : 1,
                'updated_at'           => date('Y-m-d H:i:s'),
            ] + $imageData + $removeData);

            return redirect()->to('/admin/pilgrims')->with('success', 'Pilgrim updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/admin/pilgrims')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deletePilgrim()
    {
        $pilgrimId = (int) $this->request->getPost('pilgrim_id');
        $seasonId = $this->activeSeasonId();
        $linkedAgentId = $this->linkedAgentId();
        if ($seasonId === null) {
            return redirect()->to('/admin/seasons')->with('error', 'Please create and activate a season first.');
        }

        if ($pilgrimId < 1) {
            return redirect()->to('/admin/pilgrims')->with('error', 'Valid pilgrim ID is required for delete.');
        }

        try {
            $model = new PilgrimModel();
            $existingQuery = $model->where('id', $pilgrimId)->where('season_id', $seasonId);
            if ($linkedAgentId !== null) {
                $existingQuery->where('agent_id', $linkedAgentId);
            }
            $existing = $existingQuery->first();
            if (empty($existing)) {
                return redirect()->to('/admin/pilgrims')->with('error', 'Pilgrim not found in active season.');
            }

            $deleted = $model->delete($pilgrimId);

            if (! $deleted) {
                return redirect()->to('/admin/pilgrims')->with('error', 'Pilgrim not found or already removed.');
            }

            return redirect()->to('/admin/pilgrims')->with('success', 'Pilgrim deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/admin/pilgrims')->with('error', $e->getMessage());
        }
    }

    /**
     * Release a pilgrim so a returning pilgrim can be sold/booked again for a new trip.
     *
     * Past trips stay in the travel history; any confirmed booking created after this
     * release will mark the pilgrim as availed again.
     */
    public function releasePilgrim()
    {
        $pilgrimId = (int) $this->request->getPost('pilgrim_id');
        $seasonId = $this->activeSeasonId();
        $linkedAgentId = $this->linkedAgentId();

        if ($seasonId === null) {
            return redirect()->to('/admin/seasons')->with('error', 'Please create and activate a season first.');
        }

        if ($pilgrimId < 1) {
            return redirect()->to('/admin/pilgrims')->with('error', 'Valid pilgrim ID is required.');
        }

        try {
            $model = new PilgrimModel();
            $query = $model->where('id', $pilgrimId)->where('season_id', $seasonId);
            if ($linkedAgentId !== null) {
                $query->where('agent_id', $linkedAgentId);
            }
            $existing = $query->first();
            if (empty($existing)) {
                return redirect()->to('/admin/pilgrims')->with('error', 'Pilgrim not found in active season.');
            }

            $model->update($pilgrimId, [
                'sale_released_at' => date('Y-m-d H:i:s'),
                'sale_released_by' => session('user_id') ? (int) session('user_id') : null,
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/admin/pilgrims/' . $pilgrimId)->with('success', 'Pilgrim made available again. They can now be sold/booked for a new trip.');
        } catch (\Throwable $e) {
            return redirect()->to('/admin/pilgrims/' . $pilgrimId)->with('error', $e->getMessage());
        }
    }

    public function importMofaCsv()
    {
        $linkedAgentId = $this->linkedAgentId();
        if ($this->activeSeasonId() === null) {
            return redirect()->to('/admin/seasons')->with('error', 'Please create and activate a season first.');
        }

        $file = $this->request->getFile('mofa_csv');
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return redirect()->to('/admin/pilgrims')->with('error', 'Please choose a CSV file to import.');
        }

        if (! $file->isValid()) {
            return redirect()->to('/admin/pilgrims')->with('error', 'Uploaded file is invalid.');
        }

        $extension = strtolower((string) $file->getExtension());
        if ($extension !== 'csv') {
            return redirect()->to('/admin/pilgrims')->with('error', 'Only CSV files are allowed.');
        }

        try {
            $rows = $this->readMofaCsvRows($file->getTempName());
            if (count($rows) < 2) {
                return redirect()->to('/admin/pilgrims')->with('error', 'No data rows found in the uploaded file.');
            }

            $headerMap = $this->buildMofaHeaderMap($rows[0]);
            if (! isset($headerMap['mutamer_name']) || ! isset($headerMap['passport_no'])) {
                return redirect()->to('/admin/pilgrims')->with('error', 'Required MOFA columns are missing. Ensure Mutamer Name and Passport No exist.');
            }

            $model = new PilgrimModel();
            $existingRows = $model->select('passport_no')->findAll();
            $existingPassports = [];
            foreach ($existingRows as $existingRow) {
                $key = strtoupper(trim((string) ($existingRow['passport_no'] ?? '')));
                if ($key !== '') {
                    $existingPassports[$key] = true;
                }
            }

            $inserted = 0;
            $duplicates = 0;
            $skipped = 0;

            for ($i = 1, $total = count($rows); $i < $total; $i++) {
                $mapped = $this->mapMofaRowToPilgrim($rows[$i], $headerMap);
                if ($mapped === null) {
                    $skipped++;
                    continue;
                }

                if ($linkedAgentId !== null) {
                    $mapped['agent_id'] = $linkedAgentId;
                }

                $passportKey = strtoupper(trim((string) ($mapped['passport_no'] ?? '')));
                if ($passportKey === '') {
                    $skipped++;
                    continue;
                }

                if (isset($existingPassports[$passportKey])) {
                    $duplicates++;
                    continue;
                }

                $ok = $model->insert($mapped);
                if ($ok === false) {
                    $skipped++;
                    continue;
                }

                $existingPassports[$passportKey] = true;
                $inserted++;
            }

            return redirect()->to('/admin/pilgrims')->with('success', 'MOFA import complete. Added: ' . $inserted . ', duplicates skipped: ' . $duplicates . ', invalid/empty skipped: ' . $skipped . '.');
        } catch (\Throwable $e) {
            return redirect()->to('/admin/pilgrims')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function readMofaCsvRows(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'rb')) === false) {
            throw new \RuntimeException('Unable to open CSV file.');
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $data;
        }
        fclose($handle);

        return $rows;
    }

    private function buildMofaHeaderMap(array $headerRow): array
    {
        $aliases = [
            'group_code' => ['group_code'],
            'group_name' => ['group_name'],
            'mutamer_code' => ['mutamer_code'],
            'mutamer_name' => ['mutamer_name', 'name'],
            'mofa_no' => ['mofa_no', 'mofa'],
            'gender' => ['gender'],
            'date_of_birth' => ['date_of_birth', 'dob', 'date_ofbirth'],
            'nationality' => ['nationality'],
            'passport_no' => ['passport_no', 'passport'],
            'dependant' => ['dependant', 'dependan', 'dependent'],
            'mahram' => ['mahram', 'maharm'],
            'serial_no' => ['serial_no', 'serial'],
            'relation' => ['relation'],
            'moi_no' => ['moi_no', 'moi'],
        ];

        $normalizedHeaders = [];
        foreach ($headerRow as $index => $value) {
            $normalizedHeaders[(int) $index] = $this->normalizeMofaHeader((string) $value);
        }

        $map = [];
        foreach ($aliases as $canonical => $candidates) {
            foreach ($normalizedHeaders as $index => $normalizedHeader) {
                if (in_array($normalizedHeader, $candidates, true)) {
                    $map[$canonical] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    private function mapMofaRowToPilgrim(array $row, array $headerMap)
    {
        $name = trim((string) $this->valueByHeader($row, $headerMap, 'mutamer_name'));
        $passportNo = trim((string) $this->valueByHeader($row, $headerMap, 'passport_no'));

        if ($name === '' && $passportNo === '') {
            return null;
        }

        if ($passportNo === '') {
            return null;
        }

        list($firstName, $lastName) = $this->splitFullName($name);
        $gender = $this->mapGender((string) $this->valueByHeader($row, $headerMap, 'gender'));
        $country = $this->mapCountry((string) $this->valueByHeader($row, $headerMap, 'nationality'));
        $dateOfBirth = $this->parseMofaDate($this->valueByHeader($row, $headerMap, 'date_of_birth'));

        $relation = trim((string) $this->valueByHeader($row, $headerMap, 'relation'));
        $mehram = $this->mapMehram($relation);

        $descriptionParts = [];
        foreach (['group_code', 'group_name', 'mutamer_code', 'mofa_no', 'dependant', 'mahram', 'serial_no', 'relation', 'moi_no'] as $field) {
            $value = trim((string) $this->valueByHeader($row, $headerMap, $field));
            if ($value !== '') {
                $descriptionParts[] = strtoupper(str_replace('_', ' ', $field)) . ': ' . $value;
            }
        }

        return [
            'first_name'           => $firstName,
            'last_name'            => $lastName,
            'passport_no'          => $passportNo,
            'cnic'                 => trim((string) $this->valueByHeader($row, $headerMap, 'moi_no')) ?: null,
            'country'              => $country,
            'gender'               => $gender,
            'date_of_birth'        => $dateOfBirth,
            'mehram'               => $mehram,
            'description'          => $descriptionParts !== [] ? implode(' | ', $descriptionParts) : null,
            'is_active'            => 1,
            'created_at'           => date('Y-m-d H:i:s'),
            'updated_at'           => date('Y-m-d H:i:s'),
        ];
    }

    private function normalizeMofaHeader(string $header): string
    {
        $normalized = strtolower(trim($header));
        $normalized = preg_replace('/[^a-z0-9]+/i', '_', $normalized) ?? $normalized;

        return trim($normalized, '_');
    }

    private function valueByHeader(array $row, array $headerMap, string $key): string
    {
        if (! isset($headerMap[$key])) {
            return '';
        }

        $index = (int) $headerMap[$key];

        return isset($row[$index]) ? (string) $row[$index] : '';
    }

    private function splitFullName(string $fullName): array
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return ['Unknown', '-'];
        }

        $parts = preg_split('/\s+/', $fullName) ?: [];
        $first = array_shift($parts) ?: 'Unknown';
        $last = $parts !== [] ? implode(' ', $parts) : '-';

        return [mb_substr($first, 0, 120), mb_substr($last, 0, 120)];
    }

    private function mapGender(string $gender)
    {
        $value = strtolower(trim($gender));
        if ($value === 'male' || $value === 'm') {
            return 'male';
        }
        if ($value === 'female' || $value === 'f') {
            return 'female';
        }

        return null;
    }

    private function mapCountry(string $nationality): string
    {
        $value = strtolower(trim($nationality));

        return $value === 'pakistan' || $value === 'pakistani' ? 'Pakistan' : 'Others';
    }

    private function mapMehram(string $relation)
    {
        $normalized = strtolower(trim($relation));
        if ($normalized === '') {
            return null;
        }

        foreach (self::MAHRAM_OPTIONS as $option) {
            if (strtolower($option) === $normalized) {
                return $option;
            }
        }

        return null;
    }

    private function parseMofaDate($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (float) $value;
            if ($serial > 0) {
                $timestamp = (int) (($serial - 25569) * 86400);

                return gmdate('Y-m-d', $timestamp);
            }
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $raw);
            if ($date instanceof \DateTime) {
                return $date->format('Y-m-d');
            }
        }

        try {
            return (new \DateTime($raw))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizePassportNo(string $value): string
    {
        return PilgrimModel::normalizePassportNo($value);
    }

    // $checkPassport is false when editing a record whose passport number is unchanged,
    // so records that already have a duplicate can still be edited.
    private function pilgrimRuleErrors(array $payload, int $seasonId, ?int $excludeId = null, bool $checkPassport = true): array
    {
        $errors = [];
        $today = date('Y-m-d');

        if ($payload['date_of_birth'] !== '' && $payload['date_of_birth'] > $today) {
            $errors['date_of_birth'] = 'Date of birth cannot be in the future.';
        }

        if ($payload['passport_issue_date'] !== '' && $payload['passport_expiry_date'] !== ''
            && $payload['passport_expiry_date'] <= $payload['passport_issue_date']) {
            $errors['passport_expiry_date'] = 'Passport expiry date must be after the issue date.';
        }

        if ($checkPassport && (new PilgrimModel())->passportExists((string) $payload['passport_no'], $seasonId, $excludeId)) {
            $errors['passport_no'] = 'Passport number ' . $payload['passport_no'] . ' already exists in the current season.';
        }

        return $errors;
    }

    public function checkPassport()
    {
        $seasonId = $this->activeSeasonId();
        $passportNo = $this->normalizePassportNo((string) $this->request->getGet('passport_no'));
        $excludeId = (int) $this->request->getGet('exclude_id');

        $exists = $seasonId !== null
            && (new PilgrimModel())->passportExists($passportNo, $seasonId, $excludeId > 0 ? $excludeId : null);

        return $this->response->setJSON([
            'exists'  => $exists,
            'message' => $exists ? 'Passport number ' . $passportNo . ' already exists in the current season.' : '',
        ]);
    }
    private function handlePilgrimImageUploads(): array
    {
        $result = [];
        $fileFields = [
            'pilgrim_image' => [
                'path'       => 'pilgrim_image_path',
                'name'       => 'pilgrim_image_name',
                'label'      => 'Pilgrim Photo',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'max_size'   => 5 * 1024 * 1024,
            ],
            'passport_image' => [
                'path'       => 'passport_image_path',
                'name'       => 'passport_image_name',
                'label'      => 'Passport Copy',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'max_size'   => 5 * 1024 * 1024,
            ],
            'attachment_1' => [
                'path'       => 'attachment_1',
                'label'      => 'Attachment 1',
                'extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'],
                'max_size'   => 10 * 1024 * 1024,
            ],
            'attachment_2' => [
                'path'       => 'attachment_2',
                'label'      => 'Attachment 2',
                'extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'],
                'max_size'   => 10 * 1024 * 1024,
            ],
        ];

        foreach ($fileFields as $input => $mapping) {
            $file = $this->request->getFile($input);
            if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (! $file->isValid() || $file->hasMoved()) {
                return ['error' => $mapping['label'] . ' upload is invalid.'];
            }

            $extension = strtolower((string) $file->getExtension());
            if (! in_array($extension, $mapping['extensions'], true)) {
                return ['error' => $mapping['label'] . ' must be one of: ' . implode(', ', array_map('strtoupper', $mapping['extensions'])) . '.'];
            }

            if ($file->getSize() > $mapping['max_size']) {
                return ['error' => $mapping['label'] . ' size must be ' . (int) ($mapping['max_size'] / 1024 / 1024) . 'MB or less.'];
            }

            $uploadDir = asset_upload_dir('pilgrims');

            $storedName = $file->getRandomName();
            $file->move($uploadDir, $storedName);

            if (isset($mapping['name'])) {
                $result[$mapping['name']] = $file->getClientName();
            }
            $result[$mapping['path']] = $storedName;
        }

        return $result;
    }
}
