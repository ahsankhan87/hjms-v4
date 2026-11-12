<?php

namespace App\Controllers;

use App\Models\CreditNoteModel;
use App\Services\CreditNoteLedgerService;

class CreditNoteController extends BaseController
{
    private $pilgrimDisplayCacheBySeason = [];

    public function visaIndex()
    {
        return $this->indexByType('visa');
    }

    public function hotelIndex()
    {
        return $this->indexByType('hotel');
    }

    public function ticketIndex()
    {
        return $this->indexByType('ticket');
    }

    public function packageIndex()
    {
        return $this->indexByType('package');
    }

    public function visaAdd()
    {
        return $this->addByType('visa');
    }

    public function hotelAdd()
    {
        return $this->addByType('hotel');
    }

    public function ticketAdd()
    {
        return $this->addByType('ticket');
    }

    public function packageAdd()
    {
        return $this->addByType('package');
    }

    public function createVisa()
    {
        return $this->createByType('visa');
    }

    public function createHotel()
    {
        return $this->createByType('hotel');
    }

    public function createPackage()
    {
        return $this->createByType('package');
    }

    public function createTicket()
    {
        return $this->createByType('ticket');
    }

    public function deleteVisa()
    {
        return $this->deleteByType('visa');
    }

    public function deleteHotel()
    {
        return $this->deleteByType('hotel');
    }

    public function deletePackage()
    {
        return $this->deleteByType('package');
    }

    public function deleteTicket()
    {
        return $this->deleteByType('ticket');
    }

    private function indexByType(string $saleType)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/admin/seasons')->with('error', 'Please create and activate a season first.');
        }

        $filters = [
            'from_date' => (string) $this->request->getGet('from_date'),
            'to_date' => (string) $this->request->getGet('to_date'),
            'status' => (string) $this->request->getGet('status'),
        ];

        $filterErrors = [];
        if (! $this->validateData($filters, [
            'from_date' => 'permit_empty|valid_date[Y-m-d]',
            'to_date' => 'permit_empty|valid_date[Y-m-d]',
            'status' => 'permit_empty|in_list[posted,voided,pending]',
        ])) {
            $filterErrors = $this->validator->getErrors();
            $filters = ['from_date' => '', 'to_date' => '', 'status' => ''];
        }

        $db = db_connect();
        $query = $db->table('credit_notes cn')
            ->select('cn.*, s.invoice_no AS sale_invoice_no, s.reference_no, s.pax_name, s.customer_name, s.pilgrim_id, s.pilgrim_ids, a.name AS agent_name')
            ->select("CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, '')) AS pilgrim_name", false)
            ->select('p.passport_no AS pilgrim_passport_no')
            ->join('sales s', 's.id = cn.sale_id', 'left')
            ->join('pilgrims p', 'p.id = s.pilgrim_id', 'left')
            ->join('agents a', 'a.id = cn.agent_id', 'left')
            ->where('cn.season_id', $seasonId)
            ->where('cn.sale_type', $saleType);

        if ($filters['from_date'] !== '') {
            $query->where('cn.credit_note_date >=', $filters['from_date']);
        }
        if ($filters['to_date'] !== '') {
            $query->where('cn.credit_note_date <=', $filters['to_date']);
        }
        if ($filters['status'] !== '') {
            $query->where('cn.status', $filters['status']);
        }

        $rows = $query->orderBy('cn.credit_note_date', 'DESC')->orderBy('cn.id', 'DESC')->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['pax_display'] = $this->buildPaxDisplayForSaleRow($row, $seasonId);
        }
        unset($row);

        $totalAmount = 0.0;
        foreach ($rows as $row) {
            $totalAmount += (float) ($row['amount'] ?? 0);
        }

        $saleMeta = $this->saleTypeMeta($saleType);

        return view('portal/credit_notes/index', [
            'title' => 'HJMS ERP | ' . $saleMeta['title'] . ' Credit Notes',
            'headerTitle' => $saleMeta['title'] . ' Credit Notes',
            'activePage' => $saleMeta['activePage'],
            'userEmail' => (string) session('user_email'),
            'saleType' => $saleType,
            'rows' => $rows,
            'filters' => $filters,
            'filterErrors' => $filterErrors,
            'totalAmount' => $totalAmount,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?: [],
        ]);
    }

    private function addByType(string $saleType)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/admin/seasons')->with('error', 'Please create and activate a season first.');
        }

        $requestedSaleId = (int) $this->request->getGet('sale_id');
        $db = db_connect();
        $salesQuery = $db->table('sales s')
            ->select('s.id, s.invoice_no, s.sale_date, s.amount, s.agent_id, s.pax_name, s.customer_name, s.status, s.customer_type')
            ->where('s.season_id', $seasonId)
            ->where('s.sale_type', $saleType)
            ->where('s.status !=', 'voided')
            ->where('s.customer_type', 'agent')
            ->where('s.agent_id >', 0);

        if ($db->tableExists('credit_notes')) {
            $salesQuery
                ->select('COALESCE(SUM(CASE WHEN cn.status != "voided" THEN cn.amount ELSE 0 END), 0) AS credited_amount', false)
                ->join('credit_notes cn', 'cn.season_id = s.season_id AND cn.sale_type = s.sale_type AND cn.sale_id = s.id', 'left')
                ->groupBy('s.id, s.invoice_no, s.sale_date, s.amount, s.agent_id, s.pax_name, s.customer_name, s.status, s.customer_type')
                ->having('COALESCE(SUM(CASE WHEN cn.status != "voided" THEN cn.amount ELSE 0 END), 0) < s.amount', null, false);
        }

        $sales = $salesQuery
            ->orderBy('s.sale_date', 'DESC')
            ->orderBy('s.id', 'DESC')
            ->get()
            ->getResultArray();

        $normalizedSales = [];
        foreach ($sales as $sale) {
            $saleAmount = (float) ($sale['amount'] ?? 0);
            $creditedAmount = (float) ($sale['credited_amount'] ?? 0);

            if ($creditedAmount < 0) {
                $creditedAmount = 0.0;
            }

            if ($creditedAmount > $saleAmount) {
                $creditedAmount = $saleAmount;
            }

            $remainingAmount = max(0.0, $saleAmount - $creditedAmount);
            if ($remainingAmount <= 0) {
                continue;
            }

            $sale['credited_amount'] = $creditedAmount;
            $sale['remaining_amount'] = $remainingAmount;
            $normalizedSales[] = $sale;
        }

        $sales = $normalizedSales;

        $preselectedSaleId = 0;
        if ($requestedSaleId > 0) {
            foreach ($sales as $sale) {
                if ((int) ($sale['id'] ?? 0) === $requestedSaleId) {
                    $preselectedSaleId = $requestedSaleId;
                    break;
                }
            }
        }

        $saleMeta = $this->saleTypeMeta($saleType);

        return view('portal/credit_notes/add', [
            'title' => 'HJMS ERP | Add ' . $saleMeta['title'] . ' Credit Note',
            'headerTitle' => 'Add ' . $saleMeta['title'] . ' Credit Note',
            'activePage' => $saleMeta['activePage'],
            'userEmail' => (string) session('user_email'),
            'saleType' => $saleType,
            'creditPrefix' => (string) ($saleMeta['creditPrefix'] ?? 'CN'),
            'sales' => $sales,
            'preselectedSaleId' => $preselectedSaleId,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?: [],
        ]);
    }

    private function createByType(string $saleType)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/admin/seasons')->with('error', 'Please create and activate a season first.');
        }

        $payload = [
            'sale_id' => (string) $this->request->getPost('sale_id'),
            'credit_note_date' => (string) $this->request->getPost('credit_note_date'),
            'amount' => (string) $this->request->getPost('amount'),
            'status' => (string) ($this->request->getPost('status') ?: 'posted'),
            'reason' => trim((string) $this->request->getPost('reason')),
        ];

        if (! $this->validateData($payload, [
            'sale_id' => 'required|integer',
            'credit_note_date' => 'required|valid_date[Y-m-d]',
            'amount' => 'required|decimal|greater_than[0]',
            'status' => 'required|in_list[posted,pending,voided]',
            'reason' => 'permit_empty|max_length[5000]',
        ])) {
            return redirect()->to($this->saleTypeMeta($saleType)['creditAddPath'])
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $saleId = (int) $payload['sale_id'];
        $sale = db_connect()->table('sales')
            ->select('id, season_id, sale_type, sale_date, agent_id, customer_type, amount, status')
            ->where('id', $saleId)
            ->where('season_id', $seasonId)
            ->get()
            ->getRowArray();

        if (empty($sale)) {
            return redirect()->to($this->saleTypeMeta($saleType)['creditAddPath'])
                ->withInput()
                ->with('error', 'Selected sale was not found for active season.');
        }

        if ((string) ($sale['sale_type'] ?? '') !== $saleType) {
            return redirect()->to($this->saleTypeMeta($saleType)['creditAddPath'])
                ->withInput()
                ->with('error', 'Selected sale type does not match this credit note module.');
        }

        if ((string) ($sale['customer_type'] ?? '') !== 'agent' || (int) ($sale['agent_id'] ?? 0) < 1) {
            return redirect()->to($this->saleTypeMeta($saleType)['creditAddPath'])
                ->withInput()
                ->with('error', 'Credit note requires an agent sale.');
        }

        if ((string) ($sale['status'] ?? '') === 'voided') {
            return redirect()->to($this->saleTypeMeta($saleType)['creditAddPath'])
                ->withInput()
                ->with('error', 'Cannot create credit note against a voided sale.');
        }

        $db = db_connect();

        $saleAmount = (float) ($sale['amount'] ?? 0);
        $creditedAmount = 0.0;
        if ($db->tableExists('credit_notes')) {
            $creditedRow = $db->table('credit_notes')
                ->select('COALESCE(SUM(CASE WHEN status != "voided" THEN amount ELSE 0 END), 0) AS credited_amount', false)
                ->where('season_id', $seasonId)
                ->where('sale_type', $saleType)
                ->where('sale_id', $saleId)
                ->get()
                ->getRowArray();

            $creditedAmount = (float) ($creditedRow['credited_amount'] ?? 0);
        }

        $remainingAmount = max(0.0, $saleAmount - $creditedAmount);
        if ($remainingAmount <= 0) {
            return redirect()->to($this->saleTypeMeta($saleType)['creditAddPath'])
                ->withInput()
                ->with('error', 'This sale is already fully refunded.');
        }

        $creditAmount = (float) $payload['amount'];
        if ($creditAmount > $remainingAmount) {
            return redirect()->to($this->saleTypeMeta($saleType)['creditAddPath'])
                ->withInput()
                ->with('error', 'Credit note amount cannot exceed remaining refundable amount (' . number_format($remainingAmount, 2) . ').');
        }

        $model = new CreditNoteModel();

        try {
            $db->transStart();

            $invoiceNo = $this->generateCreditNoteInvoiceNo($seasonId, $saleType);
            $model->insert([
                'season_id' => $seasonId,
                'sale_type' => $saleType,
                'sale_id' => $saleId,
                'agent_id' => (int) ($sale['agent_id'] ?? 0),
                'invoice_no' => $invoiceNo,
                'sale_date' => (string) ($sale['sale_date'] ?? date('Y-m-d')),
                'credit_note_date' => $payload['credit_note_date'],
                'amount' => $creditAmount,
                'reason' => $payload['reason'] !== '' ? $payload['reason'] : null,
                'status' => $payload['status'],
                'created_by' => session('user_id') ? (int) session('user_id') : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $creditNoteId = (int) $model->getInsertID();
            (new CreditNoteLedgerService())->syncCreditNoteLedger($creditNoteId, $seasonId);

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Unable to create credit note at the moment.');
            }

            return redirect()->to($this->saleTypeMeta($saleType)['creditIndexPath'])
                ->with('success', 'Credit note created successfully.');
        } catch (\Throwable $e) {
            return redirect()->to($this->saleTypeMeta($saleType)['creditAddPath'])
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    private function deleteByType(string $saleType)
    {
        $seasonId = $this->activeSeasonId();
        if ($seasonId === null) {
            return redirect()->to('/admin/seasons')->with('error', 'Please create and activate a season first.');
        }

        $creditNoteId = (int) $this->request->getPost('credit_note_id');
        if ($creditNoteId < 1) {
            return redirect()->to($this->saleTypeMeta($saleType)['creditIndexPath'])
                ->with('error', 'Valid credit note ID is required.');
        }

        $model = new CreditNoteModel();
        $row = $model->where('season_id', $seasonId)->find($creditNoteId);
        if (empty($row)) {
            return redirect()->to($this->saleTypeMeta($saleType)['creditIndexPath'])
                ->with('error', 'Credit note not found.');
        }

        if ((string) ($row['sale_type'] ?? '') !== $saleType) {
            return redirect()->to($this->saleTypeMeta($saleType)['creditIndexPath'])
                ->with('error', 'Invalid credit note type selected.');
        }

        $db = db_connect();
        try {
            $db->transStart();

            $model->delete($creditNoteId);
            (new CreditNoteLedgerService())->removeCreditNoteLedger($creditNoteId);

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException('Unable to delete credit note at the moment.');
            }

            return redirect()->to($this->saleTypeMeta($saleType)['creditIndexPath'])
                ->with('success', 'Credit note deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to($this->saleTypeMeta($saleType)['creditIndexPath'])
                ->with('error', $e->getMessage());
        }
    }

    private function saleTypeMeta(string $saleType): array
    {
        if ($saleType === 'hotel') {
            return [
                'title' => 'Hotel',
                'activePage' => 'hotel_sales',
                'creditIndexPath' => '/hotel-sales/credit-notes',
                'creditAddPath' => '/hotel-sales/credit-notes/add',
                'creditPrefix' => 'CNH',
            ];
        }

        if ($saleType === 'ticket') {
            return [
                'title' => 'Ticket',
                'activePage' => 'ticket_sales',
                'creditIndexPath' => '/ticket-sales/credit-notes',
                'creditAddPath' => '/ticket-sales/credit-notes/add',
                'creditPrefix' => 'CNT',
            ];
        }

        return [
            'title' => 'Visa',
            'activePage' => 'visa_sales',
            'creditIndexPath' => '/sales/credit-notes',
            'creditAddPath' => '/sales/credit-notes/add',
            'creditPrefix' => 'CNV',
        ];
    }

    private function generateCreditNoteInvoiceNo(int $seasonId, string $saleType): string
    {
        $saleMeta = $this->saleTypeMeta($saleType);
        $prefix = (string) ($saleMeta['creditPrefix'] ?? 'CNV');
        $rows = db_connect()->table('credit_notes')
            ->select('invoice_no')
            ->where('season_id', $seasonId)
            ->where('sale_type', $saleType)
            ->like('invoice_no', $prefix, 'after')
            ->get()
            ->getResultArray();

        $max = 0;
        foreach ($rows as $row) {
            $invoiceNo = (string) ($row['invoice_no'] ?? '');
            if (strpos($invoiceNo, $prefix) !== 0) {
                continue;
            }

            $suffix = substr($invoiceNo, strlen($prefix));
            if (! ctype_digit($suffix)) {
                continue;
            }

            $number = (int) $suffix;
            if ($number > $max) {
                $max = $number;
            }
        }

        return $prefix . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    private function buildPaxDisplayForSaleRow(array $row, int $seasonId): string
    {
        $map = $this->pilgrimDisplayMap($seasonId);
        $pilgrimIds = $this->extractPilgrimIdsFromSaleRow($row);

        $items = [];
        foreach ($pilgrimIds as $pilgrimId) {
            if (isset($map[$pilgrimId]) && trim((string) $map[$pilgrimId]) !== '') {
                $items[] = (string) $map[$pilgrimId];
            }
        }

        if ($items !== []) {
            return implode(', ', $items);
        }

        $paxName = trim((string) ($row['pax_name'] ?? ''));
        $passportNo = trim((string) ($row['pilgrim_passport_no'] ?? ''));
        if ($paxName !== '') {
            return $passportNo !== '' ? ($paxName . ' (' . $passportNo . ')') : $paxName;
        }

        $pilgrimName = trim((string) ($row['pilgrim_name'] ?? ''));
        if ($pilgrimName !== '') {
            return $passportNo !== '' ? ($pilgrimName . ' (' . $passportNo . ')') : $pilgrimName;
        }

        $customerName = trim((string) ($row['customer_name'] ?? ''));

        return $customerName !== '' ? $customerName : '-';
    }

    private function extractPilgrimIdsFromSaleRow(array $row): array
    {
        $ids = [];

        $pilgrimIdsCsv = trim((string) ($row['pilgrim_ids'] ?? ''));
        if ($pilgrimIdsCsv !== '') {
            foreach (explode(',', $pilgrimIdsCsv) as $part) {
                $id = (int) trim($part);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        $singlePilgrimId = (int) ($row['pilgrim_id'] ?? 0);
        if ($singlePilgrimId > 0) {
            $ids[] = $singlePilgrimId;
        }

        $ids = array_values(array_unique(array_filter($ids, static function ($id) {
            return (int) $id > 0;
        })));

        return $ids;
    }

    private function pilgrimDisplayMap(int $seasonId): array
    {
        if (isset($this->pilgrimDisplayCacheBySeason[$seasonId]) && is_array($this->pilgrimDisplayCacheBySeason[$seasonId])) {
            return $this->pilgrimDisplayCacheBySeason[$seasonId];
        }

        $db = db_connect();
        if (! $db->tableExists('pilgrims')) {
            $this->pilgrimDisplayCacheBySeason[$seasonId] = [];
            return $this->pilgrimDisplayCacheBySeason[$seasonId];
        }

        $rows = $db->table('pilgrims')
            ->select('id, first_name, last_name, passport_no')
            ->where('season_id', $seasonId)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $pilgrimId = (int) ($row['id'] ?? 0);
            if ($pilgrimId < 1) {
                continue;
            }

            $name = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));
            $passportNo = trim((string) ($row['passport_no'] ?? ''));

            if ($name === '' && $passportNo === '') {
                continue;
            }

            $map[$pilgrimId] = $passportNo !== '' ? (($name !== '' ? $name : 'Pax') . ' (' . $passportNo . ')') : $name;
        }

        $this->pilgrimDisplayCacheBySeason[$seasonId] = $map;

        return $this->pilgrimDisplayCacheBySeason[$seasonId];
    }
}
