<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgentModel;
use App\Services\AgentLedgerService;

class AgentController extends BaseController
{
    private $tableColumnsCache = [];
    private $pilgrimDisplayCacheBySeason = [];

    public function index(): string
    {
        $db = db_connect();
        $model = new AgentModel();
        $linkedAgentId = $this->linkedAgentId();

        if ($linkedAgentId !== null) {
            $model->where('id', $linkedAgentId);
        }

        return view('portal/agents/index', [
            'title'       => 'HJMS ERP | Agents',
            'headerTitle' => 'Agent Management',
            'activePage'  => 'agents',
            'userEmail'   => (string) session('user_email'),
            'rows'        => $model->orderBy('id', 'DESC')->findAll(),
            'branches'    => $db->table('branches')->orderBy('name', 'ASC')->get()->getResultArray(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function add(): string
    {
        $db = db_connect();

        return view('portal/agents/add', [
            'title'       => 'HJMS ERP | Add Agent',
            'headerTitle' => 'Agent Management',
            'activePage'  => 'agents',
            'userEmail'   => (string) session('user_email'),
            'branches'    => $db->table('branches')->orderBy('name', 'ASC')->get()->getResultArray(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function edit(int $id)
    {
        $linkedAgentId = $this->linkedAgentId();
        if ($linkedAgentId !== null && $id !== $linkedAgentId) {
            return redirect()->to('/unauthorized')->with('error', 'You can only access your own agent record.');
        }

        $model = new AgentModel();
        $row = $model->find($id);
        if (empty($row)) {
            return redirect()->to('/agents')->with('error', 'Agent not found.');
        }

        $db = db_connect();

        return view('portal/agents/edit', [
            'title'       => 'HJMS ERP | Edit Agent',
            'headerTitle' => 'Agent Management',
            'activePage'  => 'agents',
            'userEmail'   => (string) session('user_email'),
            'row'         => $row,
            'branches'    => $db->table('branches')->orderBy('name', 'ASC')->get()->getResultArray(),
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function ledger(int $id)
    {
        $linkedAgentId = $this->linkedAgentId();
        if ($linkedAgentId !== null) {
            $id = $linkedAgentId;
        }

        $agent = (new AgentModel())->find($id);
        if (empty($agent)) {
            return redirect()->to('/agents')->with('error', 'Agent not found.');
        }

        $fromDate = $this->normalizeDateFilter((string) $this->request->getGet('from'));
        $toDate = $this->normalizeDateFilter((string) $this->request->getGet('to'));

        $ledgerData = $this->buildLedgerViewData($id, $fromDate, $toDate);

        return view('portal/agents/ledger', [
            'title'       => 'HJMS ERP | Agent Ledger',
            'headerTitle' => 'Agent Management',
            'activePage'  => 'agents',
            'userEmail'   => (string) session('user_email'),
            'agent'       => $agent,
            'rows'        => $ledgerData['rows'],
            'entryCount'  => $ledgerData['entryCount'],
            'periodFrom'  => $ledgerData['periodFrom'],
            'periodTo'    => $ledgerData['periodTo'],
            'totalDebit'  => $ledgerData['totalDebit'],
            'totalCredit' => $ledgerData['totalCredit'],
            'closingBalance' => $ledgerData['closingBalance'],
            'filterFrom'  => $ledgerData['filterFrom'],
            'filterTo'    => $ledgerData['filterTo'],
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
            'errors'      => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function ledgerPrint(int $id)
    {
        $linkedAgentId = $this->linkedAgentId();
        if ($linkedAgentId !== null) {
            $id = $linkedAgentId;
        }

        $agent = (new AgentModel())->find($id);
        if (empty($agent)) {
            return redirect()->to('/agents')->with('error', 'Agent not found.');
        }

        $fromDate = $this->normalizeDateFilter((string) $this->request->getGet('from'));
        $toDate = $this->normalizeDateFilter((string) $this->request->getGet('to'));
        $autoPrint = (string) $this->request->getGet('autoprint') === '1';

        $ledgerData = $this->buildLedgerViewData($id, $fromDate, $toDate);

        return view('portal/agents/ledger_print', [
            'title'          => 'Agent Ledger Print',
            'agent'          => $agent,
            'rows'           => $ledgerData['rows'],
            'entryCount'     => $ledgerData['entryCount'],
            'periodFrom'     => $ledgerData['periodFrom'],
            'periodTo'       => $ledgerData['periodTo'],
            'totalDebit'     => $ledgerData['totalDebit'],
            'totalCredit'    => $ledgerData['totalCredit'],
            'closingBalance' => $ledgerData['closingBalance'],
            'autoPrint'      => $autoPrint,
        ]);
    }

    public function ledger2(int $id)
    {
        $linkedAgentId = $this->linkedAgentId();
        if ($linkedAgentId !== null) {
            $id = $linkedAgentId;
        }

        $agent = (new AgentModel())->find($id);
        if (empty($agent)) {
            return redirect()->to('/agents')->with('error', 'Agent not found.');
        }

        $fromDate = $this->normalizeDateFilter((string) $this->request->getGet('from'));
        $toDate = $this->normalizeDateFilter((string) $this->request->getGet('to'));

        $detailData = $this->buildLedgerDetailViewData($id, $fromDate, $toDate);

        return view('portal/agents/ledger2', [
            'title'          => 'HJMS ERP | Agent Ledger Detail',
            'headerTitle'    => 'Agent Management',
            'activePage'     => 'agents',
            'userEmail'      => (string) session('user_email'),
            'agent'          => $agent,
            'summary'        => $detailData['summary'],
            'tableTotals'    => $detailData['tableTotals'],
            'ticketSales'    => $detailData['ticketSales'],
            'hotelSales'     => $detailData['hotelSales'],
            'visaSales'      => $detailData['visaSales'],
            'generalSales'   => $detailData['generalSales'],
            'vouchers'       => $detailData['vouchers'],
            'creditNotes'    => $detailData['creditNotes'],
            'filterFrom'     => $detailData['filterFrom'],
            'filterTo'       => $detailData['filterTo'],
            'success'        => session()->getFlashdata('success'),
            'error'          => session()->getFlashdata('error'),
            'errors'         => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function ledger2Print(int $id)
    {
        $linkedAgentId = $this->linkedAgentId();
        if ($linkedAgentId !== null) {
            $id = $linkedAgentId;
        }

        $agent = (new AgentModel())->find($id);
        if (empty($agent)) {
            return redirect()->to('/agents')->with('error', 'Agent not found.');
        }

        $fromDate = $this->normalizeDateFilter((string) $this->request->getGet('from'));
        $toDate = $this->normalizeDateFilter((string) $this->request->getGet('to'));
        $autoPrint = (string) $this->request->getGet('autoprint') === '1';

        $detailData = $this->buildLedgerDetailViewData($id, $fromDate, $toDate);

        return view('portal/agents/ledger2_print', [
            'title'        => 'Agent Ledger 2 Print',
            'agent'        => $agent,
            'summary'      => $detailData['summary'],
            'tableTotals'  => $detailData['tableTotals'],
            'ticketSales'  => $detailData['ticketSales'],
            'hotelSales'   => $detailData['hotelSales'],
            'visaSales'    => $detailData['visaSales'],
            'generalSales' => $detailData['generalSales'],
            'vouchers'     => $detailData['vouchers'],
            'creditNotes'  => $detailData['creditNotes'],
            'filterFrom'   => $detailData['filterFrom'],
            'filterTo'     => $detailData['filterTo'],
            'autoPrint'    => $autoPrint,
        ]);
    }

    public function manualEntry(int $id)
    {
        $linkedAgentId = $this->linkedAgentId();
        if ($linkedAgentId !== null) {
            $id = $linkedAgentId;
        }

        $agent = (new AgentModel())->find($id);
        if (empty($agent)) {
            return redirect()->to('/agents')->with('error', 'Agent not found.');
        }

        $ledgerService = new AgentLedgerService();
        $rows = $ledgerService->getAgentLedgerRows($id);

        $runningBalance = 0.0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($rows as $item) {
            $debitAmount = (float) ($item['debit_amount'] ?? 0);
            $creditAmount = (float) ($item['credit_amount'] ?? 0);
            $runningBalance += $debitAmount;
            $runningBalance -= $creditAmount;
            $totalDebit += $debitAmount;
            $totalCredit += $creditAmount;
        }

        return view('portal/agents/ledger_entry', [
            'title'          => 'HJMS ERP | Post Ledger Entry',
            'headerTitle'    => 'Agent Management',
            'activePage'     => 'agents',
            'userEmail'      => (string) session('user_email'),
            'agent'          => $agent,
            'entryCount'     => count($rows),
            'totalDebit'     => $totalDebit,
            'totalCredit'    => $totalCredit,
            'closingBalance' => $runningBalance,
            'success'        => session()->getFlashdata('success'),
            'error'          => session()->getFlashdata('error'),
            'errors'         => session()->getFlashdata('errors') ?: [],
        ]);
    }

    public function createLedgerEntry()
    {
        $linkedAgentId = $this->linkedAgentId();
        $payload = [
            'agent_id' => (int) $this->request->getPost('agent_id'),
            'entry_date' => (string) $this->request->getPost('entry_date'),
            'entry_type' => (string) $this->request->getPost('entry_type'),
            'amount' => (string) $this->request->getPost('amount'),
            'description' => trim((string) $this->request->getPost('description')),
        ];

        if ($linkedAgentId !== null) {
            $payload['agent_id'] = $linkedAgentId;
        }

        if (! $this->validateData($payload, [
            'agent_id' => 'required|integer',
            'entry_date' => 'required|valid_date[Y-m-d]',
            'entry_type' => 'required|in_list[debit,credit,adjustment]',
            'amount' => 'required|decimal',
            'description' => 'permit_empty|max_length[255]',
        ])) {
            return redirect()->to('/agents/' . $payload['agent_id'] . '/ledger/manual-entry')->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $ledgerService = new AgentLedgerService();
            $ledgerService->createManualEntry($payload);
            $ledgerService->recalculateAgentBalance((int) $payload['agent_id']);

            return redirect()->to('/agents/' . $payload['agent_id'] . '/ledger')->with('success', 'Ledger entry posted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/agents/' . $payload['agent_id'] . '/ledger/manual-entry')->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteLedgerEntry($agentId = null)
    {
        $linkedAgentId = $this->linkedAgentId();
        $ledgerId = (int) $this->request->getPost('ledger_id');
        $agentId = (int) ($agentId ?? $this->request->getGet('agent_id') ?? 0);

        if ($linkedAgentId !== null) {
            $agentId = $linkedAgentId;
        }

        if ($ledgerId < 1) {
            return redirect()->to('/agents')->with('error', 'Invalid ledger entry ID.');
        }

        if ($agentId < 1) {
            return redirect()->to('/agents')->with('error', 'Invalid agent ID.');
        }

        try {
            $agent = (new AgentModel())->find($agentId);
            if (empty($agent)) {
                return redirect()->to('/agents')->with('error', 'Agent not found.');
            }

            $ledgerService = new AgentLedgerService();
            $ledgerService->deleteLedgerEntry($ledgerId);

            return redirect()->to('/agents/' . $agentId . '/ledger')->with('success', 'Ledger entry deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/agents/' . $agentId . '/ledger')->with('error', $e->getMessage());
        }
    }

    private function buildLedgerViewData(int $agentId, string $fromDate = '', string $toDate = ''): array
    {
        $rows = (new AgentLedgerService())->getAgentLedgerRows($agentId);

        $filteredRows = [];
        foreach ($rows as $row) {
            $entryDate = (string) ($row['entry_date'] ?? '');
            if ($fromDate !== '' && $entryDate < $fromDate) {
                continue;
            }
            if ($toDate !== '' && $entryDate > $toDate) {
                continue;
            }

            $filteredRows[] = $row;
        }

        $runningBalance = 0.0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $periodFrom = '';
        $periodTo = '';

        $dates = array_values(array_filter(array_map(static function (array $item): string {
            return (string) ($item['entry_date'] ?? '');
        }, $filteredRows)));
        if (! empty($dates)) {
            sort($dates);
            $periodFrom = (string) $dates[0];
            $periodTo = (string) $dates[count($dates) - 1];
        }

        foreach ($filteredRows as &$item) {
            $debitAmount = (float) ($item['debit_amount'] ?? 0);
            $creditAmount = (float) ($item['credit_amount'] ?? 0);
            $runningBalance += $debitAmount;
            $runningBalance -= $creditAmount;
            $item['running_balance'] = $runningBalance;
            $totalDebit += $debitAmount;
            $totalCredit += $creditAmount;
        }
        unset($item);

        return [
            'rows'           => $filteredRows,
            'entryCount'     => count($filteredRows),
            'periodFrom'     => $periodFrom,
            'periodTo'       => $periodTo,
            'totalDebit'     => $totalDebit,
            'totalCredit'    => $totalCredit,
            'closingBalance' => $runningBalance,
            'filterFrom'     => $fromDate,
            'filterTo'       => $toDate,
        ];
    }

    private function buildLedgerDetailViewData(int $agentId, string $fromDate = '', string $toDate = ''): array
    {
        $db = db_connect();
        $seasonId = $this->activeSeasonId();

        $ledgerRows = (new AgentLedgerService())->getAgentLedgerRows($agentId);
        $openingBalance = 0.0;
        $periodNet = 0.0;

        foreach ($ledgerRows as $row) {
            $entryDate = (string) ($row['entry_date'] ?? '');
            $delta = (float) ($row['debit_amount'] ?? 0) - (float) ($row['credit_amount'] ?? 0);

            if ($fromDate !== '' && $entryDate < $fromDate) {
                $openingBalance += $delta;
                continue;
            }
            if ($toDate !== '' && $entryDate > $toDate) {
                continue;
            }

            $periodNet += $delta;
        }
        $closingBalance = $openingBalance + $periodNet;

        $ticketSalesQuery = $db->table('sales s')
            ->select('s.id, s.invoice_no, s.sale_date, s.sector, s.pax_name, s.pilgrim_id, s.pilgrim_ids, s.pnr, s.ticket_no, s.reference_no, s.note, s.amount')
            ->where('s.agent_id', $agentId)
            ->where('s.customer_type', 'agent')
            ->where('s.sale_type', 'ticket')
            ->orderBy('s.sale_date', 'ASC')
            ->orderBy('s.id', 'ASC');

        if ($seasonId !== null && $this->tableHasColumn('sales', 'season_id')) {
            $ticketSalesQuery->where('s.season_id', $seasonId);
        }
        if ($this->tableHasColumn('sales', 'status')) {
            $ticketSalesQuery->where('s.status !=', 'voided');
        }
        if ($fromDate !== '') {
            $ticketSalesQuery->where('DATE(s.sale_date) >=', $fromDate);
        }
        if ($toDate !== '') {
            $ticketSalesQuery->where('DATE(s.sale_date) <=', $toDate);
        }
        $ticketSales = $ticketSalesQuery->get()->getResultArray();
        $ticketSales = $this->normalizePaxDisplayRows($ticketSales, $seasonId);
        $ticketSaleTotal = 0.0;
        foreach ($ticketSales as $row) {
            $ticketSaleTotal += (float) ($row['amount'] ?? 0);
        }

        $hotelSalesQuery = $db->table('sales s')
            ->select('s.id, s.invoice_no, s.sale_date, s.hotel_name, s.checkin_date, s.checkout_date, s.no_of_nights, s.room_name, s.room_qty, s.pax_name, s.pilgrim_id, s.pilgrim_ids, s.reference_no, s.note, s.amount')
            ->where('s.agent_id', $agentId)
            ->where('s.customer_type', 'agent')
            ->where('s.sale_type', 'hotel')
            ->orderBy('s.sale_date', 'ASC')
            ->orderBy('s.id', 'ASC');

        if ($seasonId !== null && $this->tableHasColumn('sales', 'season_id')) {
            $hotelSalesQuery->where('s.season_id', $seasonId);
        }
        if ($this->tableHasColumn('sales', 'status')) {
            $hotelSalesQuery->where('s.status !=', 'voided');
        }
        if ($fromDate !== '') {
            $hotelSalesQuery->where('DATE(s.sale_date) >=', $fromDate);
        }
        if ($toDate !== '') {
            $hotelSalesQuery->where('DATE(s.sale_date) <=', $toDate);
        }
        $hotelSales = $hotelSalesQuery->get()->getResultArray();
        $hotelSales = $this->normalizePaxDisplayRows($hotelSales, $seasonId);
        $hotelSaleTotal = 0.0;
        foreach ($hotelSales as $row) {
            $hotelSaleTotal += (float) ($row['amount'] ?? 0);
        }

        $visaSalesQuery = $db->table('sales s')
            ->select('s.id, s.invoice_no, s.sale_date, sc.name AS category_name, s.pax_name, s.pilgrim_id, s.pilgrim_ids, s.reference_no, s.note, s.visa_type, s.visa_agency, s.amount')
            ->join('sales_categories sc', 'sc.id = s.sales_category_id', 'left')
            ->where('s.agent_id', $agentId)
            ->where('s.customer_type', 'agent')
            ->where('s.sale_type', 'visa')
            ->orderBy('s.sale_date', 'ASC')
            ->orderBy('s.id', 'ASC');

        if ($seasonId !== null && $this->tableHasColumn('sales', 'season_id')) {
            $visaSalesQuery->where('s.season_id', $seasonId);
        }
        if ($this->tableHasColumn('sales', 'status')) {
            $visaSalesQuery->where('s.status !=', 'voided');
        }
        if ($fromDate !== '') {
            $visaSalesQuery->where('DATE(s.sale_date) >=', $fromDate);
        }
        if ($toDate !== '') {
            $visaSalesQuery->where('DATE(s.sale_date) <=', $toDate);
        }
        $visaSales = $visaSalesQuery->get()->getResultArray();
        $visaSales = $this->normalizePaxDisplayRows($visaSales, $seasonId);
        $visaSaleTotal = 0.0;
        foreach ($visaSales as $row) {
            $visaSaleTotal += (float) ($row['amount'] ?? 0);
        }

        $generalSalesQuery = $db->table('bookings b')
            ->select(
                "b.id, b.booking_no, DATE(b.created_at) AS inv_date, p.name AS category_name,
                COALESCE((
                    SELECT GROUP_CONCAT(
                        TRIM(
                            CONCAT(
                                COALESCE(pl.first_name, ''),
                                ' ',
                                COALESCE(pl.last_name, ''),
                                CASE
                                    WHEN TRIM(COALESCE(pl.passport_no, '')) != '' THEN CONCAT(' (', pl.passport_no, ')')
                                    ELSE ''
                                END
                            )
                        )
                        SEPARATOR ', '
                    )
                    FROM booking_pilgrims bp
                    LEFT JOIN pilgrims pl ON pl.id = bp.pilgrim_id
                    WHERE bp.booking_id = b.id
                ), '') AS pax_name,
                b.booking_no AS reference_number,
                b.remarks,
                DATE(b.created_at) AS trans_date,
                c.name AS vendor_name,
                b.total_amount",
                false
            )
            ->join('packages p', 'p.id = b.package_id', 'left')
            ->join('companies c', 'c.id = b.company_id', 'left')
            ->where('b.agent_id', $agentId)
            ->orderBy('b.created_at', 'ASC')
            ->orderBy('b.id', 'ASC');

        if ($seasonId !== null && $this->tableHasColumn('bookings', 'season_id')) {
            $generalSalesQuery->where('b.season_id', $seasonId);
        }
        if ($this->tableHasColumn('bookings', 'status')) {
            $generalSalesQuery->where('b.status !=', 'cancelled');
        }
        if ($fromDate !== '') {
            $generalSalesQuery->where('DATE(b.created_at) >=', $fromDate);
        }
        if ($toDate !== '') {
            $generalSalesQuery->where('DATE(b.created_at) <=', $toDate);
        }
        $generalSales = $generalSalesQuery->get()->getResultArray();
        $bookingSaleTotal = 0.0;
        foreach ($generalSales as $row) {
            $bookingSaleTotal += (float) ($row['total_amount'] ?? 0);
        }

        $voucherQuery = $db->table('payments p')
            ->select('p.id, p.payment_no, p.payment_date, p.gateway_reference, p.amount, p.payment_type, p.note')
            ->join('bookings b', 'b.id = p.booking_id', 'left')
            ->where('b.agent_id', $agentId)
            ->orderBy('p.payment_date', 'ASC')
            ->orderBy('p.id', 'ASC');

        if ($seasonId !== null && $this->tableHasColumn('payments', 'season_id')) {
            $voucherQuery->where('p.season_id', $seasonId);
        }
        if ($this->tableHasColumn('payments', 'status')) {
            $voucherQuery->where('p.status', 'posted');
        }
        if ($fromDate !== '') {
            $voucherQuery->where('DATE(p.payment_date) >=', $fromDate);
        }
        if ($toDate !== '') {
            $voucherQuery->where('DATE(p.payment_date) <=', $toDate);
        }
        $vouchers = $voucherQuery->get()->getResultArray();

        foreach ($ledgerRows as $ledgerRow) {
            $entryDate = (string) ($ledgerRow['entry_date'] ?? '');
            if ($fromDate !== '' && $entryDate < $fromDate) {
                continue;
            }
            if ($toDate !== '' && $entryDate > $toDate) {
                continue;
            }

            $referenceType = trim((string) ($ledgerRow['reference_type'] ?? ''));
            if ($referenceType !== '') {
                continue;
            }

            $entryType = trim((string) ($ledgerRow['entry_type'] ?? ''));
            if (! in_array($entryType, ['debit', 'credit', 'adjustment'], true)) {
                continue;
            }

            $debitAmount = (float) ($ledgerRow['debit_amount'] ?? 0);
            $creditAmount = (float) ($ledgerRow['credit_amount'] ?? 0);
            $signedAmount = $debitAmount - $creditAmount;

            $vouchers[] = [
                'id' => (int) ($ledgerRow['id'] ?? 0),
                'payment_no' => 'ML-' . (int) ($ledgerRow['id'] ?? 0),
                'payment_date' => $entryDate,
                'gateway_reference' => '-',
                'amount' => abs($signedAmount),
                'payment_type' => $signedAmount >= 0 ? 'refund' : 'payment',
                'note' => trim((string) ($ledgerRow['description'] ?? '')),
                'signed_amount' => $signedAmount,
            ];
        }

        usort($vouchers, static function (array $a, array $b): int {
            $dateA = (string) ($a['payment_date'] ?? '');
            $dateB = (string) ($b['payment_date'] ?? '');
            if ($dateA !== $dateB) {
                return strcmp($dateA, $dateB);
            }

            return ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
        });

        $voucherTotal = 0.0;
        foreach ($vouchers as &$voucher) {
            $signedAmount = isset($voucher['signed_amount'])
                ? (float) ($voucher['signed_amount'] ?? 0)
                : ((((string) ($voucher['payment_type'] ?? 'payment')) === 'refund')
                    ? (float) ($voucher['amount'] ?? 0)
                    : ((float) ($voucher['amount'] ?? 0) * -1));
            $voucher['signed_amount'] = $signedAmount;
            $voucherTotal += $signedAmount;
        }
        unset($voucher);

        $baseFareExpr = $this->tableHasColumn('sales', 'base_fare') ? 's.base_fare' : '0';
        $taxExpr = $this->tableHasColumn('sales', 'tax') ? 's.tax' : ($this->tableHasColumn('sales', 'tax_amount') ? 's.tax_amount' : '0');
        $discountExpr = $this->tableHasColumn('sales', 'discount') ? 's.discount' : '0';
        $psfExpr = $this->tableHasColumn('sales', 'psf_wht') ? 's.psf_wht' : ($this->tableHasColumn('sales', 'psf') ? 's.psf' : '0');

        $creditNotesQuery = $db->table('credit_notes cn')
            ->select('cn.id, cn.invoice_no, cn.sale_date, cn.credit_note_date, cn.amount, s.sector, s.pax_name, s.pilgrim_id, s.pilgrim_ids, s.pnr, s.ticket_no', false)
            ->select($baseFareExpr . ' AS base_fare', false)
            ->select($taxExpr . ' AS tax', false)
            ->select($discountExpr . ' AS discount', false)
            ->select($psfExpr . ' AS psf_wht', false)
            ->join('sales s', 's.id = cn.sale_id', 'left')
            ->where('cn.agent_id', $agentId)
            ->orderBy('cn.credit_note_date', 'ASC')
            ->orderBy('cn.id', 'ASC');

        if ($seasonId !== null && $this->tableHasColumn('credit_notes', 'season_id')) {
            $creditNotesQuery->where('cn.season_id', $seasonId);
        }
        if ($this->tableHasColumn('credit_notes', 'status')) {
            $creditNotesQuery->where('cn.status !=', 'voided');
        }
        if ($fromDate !== '') {
            $creditNotesQuery->where('cn.credit_note_date >=', $fromDate);
        }
        if ($toDate !== '') {
            $creditNotesQuery->where('cn.credit_note_date <=', $toDate);
        }
        $creditNotes = $creditNotesQuery->get()->getResultArray();
        $creditNotes = $this->normalizePaxDisplayRows($creditNotes, $seasonId);

        $creditNoteTotal = 0.0;
        $creditNoteBaseFareTotal = 0.0;
        $creditNoteTaxTotal = 0.0;
        $creditNoteDiscountTotal = 0.0;
        $creditNotePsfWhtTotal = 0.0;
        foreach ($creditNotes as $creditNote) {
            $creditNoteTotal += (float) ($creditNote['amount'] ?? 0);
            $creditNoteBaseFareTotal += (float) ($creditNote['base_fare'] ?? 0);
            $creditNoteTaxTotal += (float) ($creditNote['tax'] ?? 0);
            $creditNoteDiscountTotal += (float) ($creditNote['discount'] ?? 0);
            $creditNotePsfWhtTotal += (float) ($creditNote['psf_wht'] ?? 0);
        }

        return [
            'summary' => [
                'openingBalance' => $openingBalance,
                'closingBalance' => $closingBalance,
                'ticketSaleTotal' => $ticketSaleTotal,
                'hotelSaleTotal' => $hotelSaleTotal,
                'visaSaleTotal' => $visaSaleTotal,
                'bookingSaleTotal' => $bookingSaleTotal,
                'creditNoteTotal' => $creditNoteTotal,
                'voucherTotal' => $voucherTotal,
            ],
            'tableTotals' => [
                'ticketSales' => $ticketSaleTotal,
                'hotelSales' => $hotelSaleTotal,
                'visaSales' => $visaSaleTotal,
                'generalSales' => $bookingSaleTotal,
                'vouchers' => $voucherTotal,
                'creditNotes' => $creditNoteTotal,
                'creditNoteBaseFare' => $creditNoteBaseFareTotal,
                'creditNoteTax' => $creditNoteTaxTotal,
                'creditNoteDiscount' => $creditNoteDiscountTotal,
                'creditNotePsfWht' => $creditNotePsfWhtTotal,
            ],
            'ticketSales' => $ticketSales,
            'hotelSales' => $hotelSales,
            'visaSales' => $visaSales,
            'generalSales' => $generalSales,
            'vouchers' => $vouchers,
            'creditNotes' => $creditNotes,
            'filterFrom' => $fromDate,
            'filterTo' => $toDate,
        ];
    }

    private function normalizeDateFilter(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return '';
        }

        return $value;
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        if (! isset($this->tableColumnsCache[$table])) {
            try {
                $this->tableColumnsCache[$table] = db_connect()->getFieldNames($table);
            } catch (\Throwable $e) {
                $this->tableColumnsCache[$table] = [];
            }
        }

        return in_array($column, $this->tableColumnsCache[$table], true);
    }

    private function normalizePaxDisplayRows(array $rows, $seasonId): array
    {
        $seasonId = (int) $seasonId;
        if ($seasonId < 1 || empty($rows)) {
            return $rows;
        }

        $pilgrimMap = $this->pilgrimDisplayMap($seasonId);
        if ($pilgrimMap === []) {
            return $rows;
        }

        foreach ($rows as &$row) {
            $display = $this->buildPaxDisplayFromRow((array) $row, $pilgrimMap);
            if ($display !== '') {
                $row['pax_name'] = $display;
            }
        }
        unset($row);

        return $rows;
    }

    private function buildPaxDisplayFromRow(array $row, array $pilgrimMap): string
    {
        $ids = [];
        $csv = trim((string) ($row['pilgrim_ids'] ?? ''));
        if ($csv !== '') {
            foreach (explode(',', $csv) as $part) {
                $id = (int) trim($part);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        $singleId = (int) ($row['pilgrim_id'] ?? 0);
        if ($singleId > 0) {
            $ids[] = $singleId;
        }

        $ids = array_values(array_unique($ids));
        $items = [];
        foreach ($ids as $id) {
            if (isset($pilgrimMap[$id])) {
                $items[] = (string) $pilgrimMap[$id];
            }
        }

        if ($items !== []) {
            return implode(', ', $items);
        }

        return trim((string) ($row['pax_name'] ?? ''));
    }

    private function pilgrimDisplayMap(int $seasonId): array
    {
        if (isset($this->pilgrimDisplayCacheBySeason[$seasonId]) && is_array($this->pilgrimDisplayCacheBySeason[$seasonId])) {
            return $this->pilgrimDisplayCacheBySeason[$seasonId];
        }

        $db = db_connect();
        if (! $db->tableExists('pilgrims')) {
            $this->pilgrimDisplayCacheBySeason[$seasonId] = [];
            return [];
        }

        $rows = $db->table('pilgrims')
            ->select('id, first_name, last_name, passport_no')
            ->where('season_id', $seasonId)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1) {
                continue;
            }

            $name = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));
            $passport = trim((string) ($row['passport_no'] ?? ''));

            if ($name === '' && $passport === '') {
                continue;
            }

            if ($passport !== '') {
                $map[$id] = ($name !== '' ? $name : 'Pax') . ' (' . $passport . ')';
            } else {
                $map[$id] = $name;
            }
        }

        $this->pilgrimDisplayCacheBySeason[$seasonId] = $map;
        return $map;
    }

    public function createAgent()
    {
        $payload = [
            'type'              => trim((string) $this->request->getPost('type')),
            'name'              => trim((string) $this->request->getPost('name')),
            'email'             => strtolower(trim((string) $this->request->getPost('email'))),
            'phone'             => trim((string) $this->request->getPost('phone')),
            'commission_type'   => trim((string) $this->request->getPost('commission_type')),
            'commission_value'  => trim((string) $this->request->getPost('commission_value')),
            'credit_limit'      => trim((string) $this->request->getPost('credit_limit')),
            'branch_id'         => $this->request->getPost('branch_id') !== '' ? (int) $this->request->getPost('branch_id') : null,
        ];

        if (! $this->validateData($payload, [
            'type'             => 'required|in_list[agent,customer]',
            'name'             => 'required|min_length[3]|max_length[150]',
            'email'            => 'permit_empty|valid_email',
            'phone'            => 'permit_empty|max_length[30]',
            'commission_type'  => 'permit_empty|in_list[percentage,fixed]',
            'commission_value' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'credit_limit'     => 'permit_empty|decimal|greater_than_equal_to[0]',
            'branch_id'        => 'permit_empty|integer',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $model = new AgentModel();

            if ($payload['email'] !== '' && $this->isEmailTaken($payload['email'])) {
                return redirect()->back()->withInput()->with('error', 'Agent email already exists.');
            }

            if ($payload['branch_id'] !== null && ! $this->branchExists($payload['branch_id'])) {
                return redirect()->back()->withInput()->with('error', 'Selected branch does not exist.');
            }

            $model->insert([
                'branch_id'         => $payload['branch_id'],
                'type'              => $payload['type'],
                'name'              => $payload['name'],
                'email'             => $payload['email'] !== '' ? $payload['email'] : null,
                'phone'             => $payload['phone'] !== '' ? $payload['phone'] : null,
                'commission_type'   => $payload['commission_type'] !== '' ? $payload['commission_type'] : 'percentage',
                'commission_value'  => $payload['commission_value'] !== '' ? (float) $payload['commission_value'] : 0,
                'credit_limit'      => $payload['credit_limit'] !== '' ? (float) $payload['credit_limit'] : 0,
                'current_balance'   => 0,
                'is_active'         => 1,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

            $agentId = $model->getInsertID();
            $prefix = $payload['type'] === 'customer' ? 'CST' : 'AGT';
            $code = $prefix . '-' . str_pad((string) $agentId, 4, '0', STR_PAD_LEFT);

            $model->update($agentId, ['code' => $code]);

            return redirect()->to('/agents')->with('success', 'Agent created successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateAgent()
    {
        $agentId = (int) $this->request->getPost('agent_id');
        $payload = [
            'type'             => trim((string) $this->request->getPost('type')),
            'name'             => trim((string) $this->request->getPost('name')),
            'email'            => strtolower(trim((string) $this->request->getPost('email'))),
            'phone'            => trim((string) $this->request->getPost('phone')),
            'commission_type'  => trim((string) $this->request->getPost('commission_type')),
            'commission_value' => trim((string) $this->request->getPost('commission_value')),
            'credit_limit'     => trim((string) $this->request->getPost('credit_limit')),
            'branch_id'        => trim((string) $this->request->getPost('branch_id')),
            'is_active'        => trim((string) $this->request->getPost('is_active')),
        ];

        if ($agentId < 1) {
            return redirect()->back()->withInput()->with('error', 'Valid agent ID is required.');
        }

        if (! $this->validateData($payload, [
            'type'             => 'permit_empty|in_list[agent,customer]',
            'name'             => 'permit_empty|min_length[3]|max_length[150]',
            'email'            => 'permit_empty|valid_email',
            'phone'            => 'permit_empty|max_length[30]',
            'commission_type'  => 'permit_empty|in_list[percentage,fixed]',
            'commission_value' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'credit_limit'     => 'permit_empty|decimal|greater_than_equal_to[0]',
            'branch_id'        => 'permit_empty|integer',
            'is_active'        => 'permit_empty|in_list[0,1]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new AgentModel();
        $existing = $model->find($agentId);
        if (empty($existing)) {
            return redirect()->to('/agents')->withInput()->with('error', 'Agent not found.');
        }

        $data = array_filter($payload, static function ($value) {
            return $value !== '';
        });

        if ($data === []) {
            return redirect()->back()->withInput()->with('error', 'Provide at least one field to update for agent.');
        }

        if (isset($data['commission_value'])) {
            $data['commission_value'] = (float) $data['commission_value'];
        }
        if (isset($data['credit_limit'])) {
            $data['credit_limit'] = (float) $data['credit_limit'];
        }
        if (isset($data['branch_id'])) {
            $data['branch_id'] = (int) $data['branch_id'];
            if (! $this->branchExists($data['branch_id'])) {
                return redirect()->back()->withInput()->with('error', 'Selected branch does not exist.');
            }
        }
        if (isset($data['is_active'])) {
            $data['is_active'] = (int) $data['is_active'];
        }

        if (isset($data['email']) && $data['email'] !== '' && $this->isEmailTaken($data['email'], $agentId)) {
            return redirect()->back()->withInput()->with('error', 'Agent email already exists.');
        }

        try {
            $model->update($agentId, $data + ['updated_at' => date('Y-m-d H:i:s')]);

            return redirect()->to('/agents')->with('success', 'Agent updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteAgent()
    {
        $agentId = (int) $this->request->getPost('agent_id');
        if ($agentId < 1) {
            return redirect()->to('/agents')->with('error', 'Valid agent ID is required for delete.');
        }

        try {
            $model = new AgentModel();
            $existing = $model->find($agentId);
            if (empty($existing)) {
                return redirect()->to('/agents')->with('error', 'Agent not found or already removed.');
            }

            $bookingCount = db_connect()->table('bookings')->where('agent_id', $agentId)->countAllResults();
            if ($bookingCount > 0) {
                return redirect()->to('/agents')->with('error', 'Cannot delete this agent because it is linked to bookings.');
            }

            $deleted = $model->delete($agentId);

            if (! $deleted) {
                return redirect()->to('/agents')->with('error', 'Agent not found or already removed.');
            }

            return redirect()->to('/agents')->with('success', 'Agent deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/agents')->with('error', $e->getMessage());
        }
    }

    private function branchExists(int $branchId): bool
    {
        return db_connect()->table('branches')->where('id', $branchId)->countAllResults() > 0;
    }

    private function isCodeTaken(string $code, $excludeAgentId = null): bool
    {
        $builder = db_connect()->table('agents')->where('code', $code);
        if ($excludeAgentId !== null) {
            $builder->where('id !=', $excludeAgentId);
        }

        return $builder->countAllResults() > 0;
    }

    private function isEmailTaken(string $email, $excludeAgentId = null): bool
    {
        $builder = db_connect()->table('agents')->where('email', $email);
        if ($excludeAgentId !== null) {
            $builder->where('id !=', $excludeAgentId);
        }

        return $builder->countAllResults() > 0;
    }
}
