<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Keeps the supplier ledger in step with the purchase prices captured on sales.
 *
 * Every sale posts supplier `bill` entries (credit = money we owe) tagged with
 * reference_type `sale`. Entries are split by supplier and purchase currency/rate
 * so supplier balances preserve PKR/SAR values and exchange rates.
 */
class SupplierLedgerService
{
    private const REFERENCE_TYPE = 'sale';

    private BaseConnection $db;
    private array $supplierCategoryCache = [];
    private array $pilgrimDisplayCacheBySeason = [];

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Rebuilds the supplier bills for one sale from its stored components.
     */
    public function syncSalePurchases(int $saleId): void
    {
        if ($saleId < 1 || ! $this->tablesReady()) {
            return;
        }

        $this->removeSalePurchases($saleId);

        $saleQuery = $this->db->table('sales s')
            ->select('s.id, s.season_id, s.invoice_no, s.sale_date, s.status, s.sale_type, s.reference_no, s.note, s.pilgrim_id, s.pilgrim_ids, s.pax_name')
            ->where('s.id', $saleId);

        if ($this->db->tableExists('sales_categories')) {
            $saleQuery->select('sc.name AS sales_category_name');
            $saleQuery->join('sales_categories sc', 'sc.id = s.sales_category_id', 'left');
        }

        $sale = $saleQuery->get()->getRowArray();

        if (empty($sale) || strtolower((string) ($sale['status'] ?? 'posted')) === 'voided') {
            return;
        }

        $items = $this->db->table('sale_items')
            ->select('item_type, supplier_id, purchase_amount, purchase_amount_pkr, currency, exchange_rate')
            ->where('sale_id', $saleId)
            ->where('supplier_id IS NOT NULL', null, false)
            ->where('supplier_id >', 0)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if ($items === []) {
            return;
        }

        $purchases = [];
        foreach ($items as $item) {
            $supplierId = (int) ($item['supplier_id'] ?? 0);
            $currency = strtoupper(trim((string) ($item['currency'] ?? 'PKR')));
            $rate = (float) ($item['exchange_rate'] ?? 0);
            if ($currency !== 'SAR') {
                $currency = 'PKR';
                $rate = 1.0;
            } elseif ($rate <= 0) {
                $rate = 0.0;
            }

            $purchaseAmount = round((float) ($item['purchase_amount'] ?? 0), 2);
            $purchaseAmountPkr = round((float) ($item['purchase_amount_pkr'] ?? 0), 2);
            if ($purchaseAmountPkr <= 0 && $purchaseAmount > 0) {
                $purchaseAmountPkr = round($purchaseAmount * ($rate > 0 ? $rate : 1.0), 2);
            }
            if ($currency === 'SAR' && $rate <= 0 && $purchaseAmount > 0 && $purchaseAmountPkr > 0) {
                $rate = round($purchaseAmountPkr / $purchaseAmount, 4);
            }

            if ($supplierId < 1 || $purchaseAmount <= 0 || $purchaseAmountPkr <= 0) {
                continue;
            }

            $groupKey = $supplierId . '|' . $currency . '|' . number_format($rate > 0 ? $rate : 1.0, 4, '.', '');
            if (! isset($purchases[$groupKey])) {
                $purchases[$groupKey] = [
                    'supplier_id' => $supplierId,
                    'currency' => $currency,
                    'exchange_rate' => $rate > 0 ? $rate : 1.0,
                    'purchase_total' => 0.0,
                    'purchase_total_pkr' => 0.0,
                ];
            }

            $purchases[$groupKey]['purchase_total'] += $purchaseAmount;
            $purchases[$groupKey]['purchase_total_pkr'] += $purchaseAmountPkr;
        }

        if ($purchases === []) {
            return;
        }

        $entryDate = (string) ($sale['sale_date'] ?? '') !== '' ? (string) $sale['sale_date'] : date('Y-m-d');
        $categoryName = trim((string) ($sale['sales_category_name'] ?? ''));
        if ($categoryName === '') {
            $categoryName = ucfirst((string) ($sale['sale_type'] ?? 'sale'));
        }
        $categoryId = $this->resolveSupplierCategoryId($categoryName);
        $description = $this->buildLedgerDescription($sale);
        $now = date('Y-m-d H:i:s');

        $rows = [];
        foreach ($purchases as $purchase) {
            $totalPkr = round((float) ($purchase['purchase_total_pkr'] ?? 0), 2);
            $total = round((float) ($purchase['purchase_total'] ?? 0), 2);
            if ($totalPkr <= 0 || $total <= 0) {
                continue;
            }

            $row = [
                'supplier_id' => (int) ($purchase['supplier_id'] ?? 0),
                'entry_date' => $entryDate,
                'entry_type' => 'bill',
                'currency' => (string) ($purchase['currency'] ?? 'PKR'),
                'exchange_rate' => (float) ($purchase['exchange_rate'] ?? 1.0),
                'debit_amount' => 0.00,
                'credit_amount' => $totalPkr,
                'sar_debit_amount' => 0.00,
                'sar_credit_amount' => strtoupper((string) ($purchase['currency'] ?? 'PKR')) === 'SAR' ? $total : 0.00,
                'reference_type' => self::REFERENCE_TYPE,
                'reference_id' => $saleId,
                'description' => $description,
                'created_at' => $now,
            ];

            if ($this->tableHasColumn('category_id') && $categoryId > 0) {
                $row['category_id'] = $categoryId;
            }

            $rows[] = $row;
        }

        if ($rows !== []) {
            $this->db->table('supplier_ledger_entries')->insertBatch($rows);
        }
    }

    public function removeSalePurchases(int $saleId): void
    {
        if ($saleId < 1 || ! $this->tablesReady()) {
            return;
        }

        $this->db->table('supplier_ledger_entries')
            ->where('reference_type', self::REFERENCE_TYPE)
            ->where('reference_id', $saleId)
            ->delete();
    }

    private function tablesReady(): bool
    {
        try {
            return $this->db->tableExists('supplier_ledger_entries') && $this->db->tableExists('sale_items');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function tableHasColumn(string $column): bool
    {
        try {
            return $this->db->fieldExists($column, 'supplier_ledger_entries');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function resolveSupplierCategoryId(string $categoryName): ?int
    {
        $categoryName = strtolower(trim($categoryName));
        if ($categoryName === '' || ! $this->db->tableExists('supplier_categories')) {
            return null;
        }

        if ($this->supplierCategoryCache === []) {
            $rows = $this->db->table('supplier_categories')
                ->select('id, name')
                ->get()
                ->getResultArray();
            foreach ((array) $rows as $row) {
                $name = strtolower(trim((string) ($row['name'] ?? '')));
                $id = (int) ($row['id'] ?? 0);
                if ($name !== '' && $id > 0) {
                    $this->supplierCategoryCache[$name] = $id;
                }
            }
        }

        return $this->supplierCategoryCache[$categoryName] ?? null;
    }

    private function buildLedgerDescription(array $sale): string
    {
        $parts = [];

        $paxDisplay = $this->buildPaxDisplay($sale);
        if ($paxDisplay !== '') {
            $parts[] = 'PAX: ' . $paxDisplay;
        }

        $saleNote = trim((string) ($sale['note'] ?? ''));
        if ($saleNote !== '') {
            $saleNote = preg_replace('/\s+/', ' ', $saleNote) ?? $saleNote;
            $parts[] = 'Note: ' . $saleNote;
        }

        $text = implode(' | ', $parts);
        if ($text !== '') {
            if (strlen($text) > 1000) {
                $text = substr($text, 0, 997) . '...';
            }

            return $text;
        }

        return $this->buildSaleDescriptionFallback($sale);
    }

    private function buildPaxDisplay(array $sale): string
    {
        $seasonId = (int) ($sale['season_id'] ?? 0);
        $map = $seasonId > 0 ? $this->pilgrimDisplayMap($seasonId) : [];
        $pilgrimIds = $this->extractPilgrimIds($sale);

        $items = [];
        foreach ($pilgrimIds as $pilgrimId) {
            if (isset($map[$pilgrimId]) && trim((string) $map[$pilgrimId]) !== '') {
                $items[] = (string) $map[$pilgrimId];
            }
        }

        if ($items !== []) {
            return implode(', ', $items);
        }

        return trim((string) ($sale['pax_name'] ?? ''));
    }

    private function extractPilgrimIds(array $sale): array
    {
        $ids = [];

        $pilgrimIdsCsv = trim((string) ($sale['pilgrim_ids'] ?? ''));
        if ($pilgrimIdsCsv !== '') {
            foreach (explode(',', $pilgrimIdsCsv) as $part) {
                $id = (int) trim($part);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        $singlePilgrimId = (int) ($sale['pilgrim_id'] ?? 0);
        if ($singlePilgrimId > 0) {
            $ids[] = $singlePilgrimId;
        }

        return array_values(array_unique(array_filter($ids, static function ($id) {
            return (int) $id > 0;
        })));
    }

    private function pilgrimDisplayMap(int $seasonId): array
    {
        if ($seasonId < 1 || ! $this->db->tableExists('pilgrims')) {
            return [];
        }

        if (array_key_exists($seasonId, $this->pilgrimDisplayCacheBySeason)) {
            return (array) $this->pilgrimDisplayCacheBySeason[$seasonId];
        }

        $rows = $this->db->table('pilgrims')
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
            $passportNo = trim((string) ($row['passport_no'] ?? ''));

            if ($name === '' && $passportNo === '') {
                continue;
            }

            $map[$id] = $passportNo !== '' ? (($name !== '' ? $name : 'Pax') . ' - (' . $passportNo . ')') : $name;
        }

        $this->pilgrimDisplayCacheBySeason[$seasonId] = $map;

        return $map;
    }

    private function buildSaleDescriptionFallback(array $sale): string
    {
        $saleType = (string) ($sale['sale_type'] ?? '');
        $base = 'Sale receivable';

        if ($saleType === 'visa') {
            $base = 'Visa sale receivable';
        } elseif ($saleType === 'hotel') {
            $base = 'Hotel sale receivable';
        } elseif ($saleType === 'ticket') {
            $base = 'Ticket sale receivable';
        } elseif ($saleType === 'transport') {
            $base = 'Transport sale receivable';
        } elseif ($saleType === 'package') {
            $base = 'Package sale receivable';
        } elseif ($saleType === 'general') {
            $base = 'General sale receivable';
        }

        $referenceNo = trim((string) ($sale['reference_no'] ?? ''));
        if ($referenceNo !== '') {
            $base .= ' [' . $referenceNo . ']';
        }

        $saleNote = trim((string) ($sale['note'] ?? ''));
        if ($saleNote === '') {
            return $base;
        }

        $saleNote = preg_replace('/\s+/', ' ', $saleNote) ?? $saleNote;
        if (strlen($saleNote) > 160) {
            $saleNote = substr($saleNote, 0, 157) . '...';
        }

        return $base . ' | Note: ' . $saleNote;
    }
}
