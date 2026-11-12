<?php
/**
 * Shared form for the unified sale entry (create + edit).
 *
 * @var array  $row
 * @var array  $saleItems
 * @var array  $agents
 * @var array  $pilgrims
 * @var array  $hotels
 * @var array  $transports
 * @var array  $packages
 * @var array  $suppliers
 */

$row = $row ?? [];
$saleItems = $saleItems ?? ['visa' => [], 'ticket' => [], 'hotel' => [], 'transport' => [], 'package' => [], 'general' => []];
$isEdit = ! empty($row);
$formAction = $isEdit ? site_url('/admin/sales/update') : site_url('/admin/sales');

$visaItem = $saleItems['visa'] ?? [];
$ticketItem = $saleItems['ticket'] ?? [];
$packageItem = $saleItems['package'] ?? [];
$generalItem = $saleItems['general'] ?? [];

$visaTypes = \App\Config\VisaOptions::TYPES;

$headerValue = static function (string $key, $default = '') use ($row) {
    $old = old($key);
    if ($old !== null) {
        return $old;
    }

    return $row[$key] ?? $default;
};

$itemValue = static function (string $oldKey, array $item, string $field, $default = '') {
    $old = old($oldKey);
    if ($old !== null) {
        return $old;
    }

    return $item[$field] ?? $default;
};

$oldSelectedPilgrimIds = old('pilgrim_ids');
if (! is_array($oldSelectedPilgrimIds)) {
    $oldSelectedPilgrimIds = array_map('intval', explode(',', (string) ($row['pilgrim_ids'] ?? '')));
    $singlePilgrimId = (int) ($row['pilgrim_id'] ?? 0);
    if ($singlePilgrimId > 0) {
        $oldSelectedPilgrimIds[] = $singlePilgrimId;
    }
}
$oldSelectedPilgrimIds = array_map('intval', $oldSelectedPilgrimIds);

$customerType = (string) $headerValue('customer_type', 'walk_in');

$buildHotelRows = static function () use ($saleItems): array {
    $oldNames = old('hotel_name');
    if (is_array($oldNames)) {
        $rows = [];
        foreach ($oldNames as $index => $name) {
            $rows[] = [
                'hotel_name' => (string) $name,
                'check_in_date' => (string) old('hotel_check_in.' . $index),
                'check_out_date' => (string) old('hotel_check_out.' . $index),
                'room_qty' => (string) old('hotel_room_qty.' . $index, '1'),
                'room_type' => (string) old('hotel_room_type.' . $index),
                'amount' => (string) old('hotel_amount.' . $index),
                'currency' => (string) old('hotel_currency.' . $index, 'PKR'),
                'exchange_rate' => (string) old('hotel_exchange_rate.' . $index, '1'),
                'supplier_id' => (string) old('hotel_supplier_id.' . $index),
                'purchase_amount' => (string) old('hotel_purchase_amount.' . $index),
                'attachment' => '',
            ];
        }

        return $rows === [] ? [[]] : $rows;
    }

    $rows = $saleItems['hotel'] ?? [];

    return $rows === [] ? [[]] : $rows;
};

$buildTransportRows = static function () use ($saleItems): array {
    $oldTypes = old('transport_type');
    if (is_array($oldTypes)) {
        $rows = [];
        foreach ($oldTypes as $index => $type) {
            $rows[] = [
                'transport_id' => (string) old('transport_id.' . $index),
                'transport_type' => (string) $type,
                'route' => (string) old('transport_route.' . $index),
                'amount' => (string) old('transport_amount.' . $index),
                'currency' => (string) old('transport_currency.' . $index, 'PKR'),
                'exchange_rate' => (string) old('transport_exchange_rate.' . $index, '1'),
                'supplier_id' => (string) old('transport_supplier_id.' . $index),
                'purchase_amount' => (string) old('transport_purchase_amount.' . $index),
                'is_ziarat' => (string) old('transport_ziarat.' . $index, '0'),
                'attachment' => '',
            ];
        }

        return $rows;
    }

    return $saleItems['transport'] ?? [];
};

$hotelRows = $buildHotelRows();
$transportRows = $buildTransportRows();
if ($transportRows === []) {
    $transportRows = [[]];
}

$routeHints = [];
$typeHints = [];
foreach ($transports as $transport) {
    $route = trim((string) ($transport['route_hint'] ?? ''));
    if ($route !== '') {
        $routeHints[$route] = true;
    }
    $vehicle = trim((string) ($transport['vehicle_type'] ?? ''));
    if ($vehicle !== '') {
        $typeHints[$vehicle] = true;
    }
}
?>
    <?php if (! empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (! empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (! empty($errors)): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
            <?php foreach ((array) $errors as $err): ?><div><?= esc(is_array($err) ? implode(' ', $err) : (string) $err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-800"><?= $isEdit ? 'Edit Sale' : 'New Sale Entry' ?></h3>
                <p class="text-xs text-slate-500">One entry can carry visa, ticket, hotel, transport, package and general sale components for the same pilgrim group.</p>
            </div>
            <?php if ($isEdit): ?>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?= esc((string) ($row['invoice_no'] ?? '')) ?></span>
            <?php endif; ?>
        </div>
    </article>

    <form method="post" action="<?= $formAction ?>" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="sale_id" value="<?= esc((string) ($row['id'] ?? '')) ?>">
        <?php endif; ?>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="grid gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Entry Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="sale_date" value="<?= esc((string) $headerValue('sale_date', date('Y-m-d'))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Invoice #</label>
                    <input name="invoice_no" value="<?= esc((string) $headerValue('invoice_no')) ?>" placeholder="Auto generated when left blank" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php $statusValue = (string) $headerValue('status', 'posted'); ?>
                        <option value="posted" <?= $statusValue === 'posted' ? 'selected' : '' ?>>Posted</option>
                        <option value="pending" <?= $statusValue === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="voided" <?= $statusValue === 'voided' ? 'selected' : '' ?>>Voided</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Total Amount (PKR)</label>
                    <input id="sale-total-amount" value="<?= esc(number_format((float) ($row['amount'] ?? 0), 2, '.', '')) ?>" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold">
                </div>
            </div>

            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Customer Type</label>
                    <select id="customer_type" name="customer_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="walk_in" <?= $customerType === 'walk_in' ? 'selected' : '' ?>>Walk-In</option>
                        <option value="agent" <?= $customerType === 'agent' ? 'selected' : '' ?>>Agent</option>
                    </select>
                </div>
                <div id="agent_wrap" class="<?= $customerType === 'agent' ? '' : 'hidden' ?>">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Agent</label>
                    <select name="agent_id" class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select Agent</option>
                        <?php foreach (($agents ?? []) as $agent): ?>
                            <option value="<?= esc((string) $agent['id']) ?>" <?= (string) $headerValue('agent_id') === (string) $agent['id'] ? 'selected' : '' ?>><?= esc((string) $agent['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="walkin_wrap" class="<?= $customerType === 'walk_in' ? '' : 'hidden' ?>">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Walk-In Customer Name</label>
                    <input id="customer_name" name="customer_name" value="<?= esc((string) $headerValue('customer_name')) ?>" placeholder="Customer name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Description / Remarks</label>
                    <textarea name="note" rows="3" placeholder="Optional internal note" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc((string) $headerValue('note')) ?></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Attachment</label>
                    <input type="file" name="attachment" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-slate-500">Accepted: JPG, PNG, PDF, DOC, DOCX (Max 5MB)</p>
                    <?php if (! empty($row['attachment'])): ?>
                        <div class="mt-2 flex items-center gap-2 text-xs">
                            <a href="<?= esc(asset_upload_url((string) $row['attachment'], 'sales')) ?>" target="_blank" class="inline-flex items-center gap-1 rounded border border-slate-200 bg-slate-50 px-2 py-1 font-medium text-emerald-700 hover:bg-emerald-50">
                                <i class="fa-solid fa-file"></i><span>Current attachment</span>
                            </a>
                            <label class="inline-flex items-center gap-1 text-slate-600">
                                <input type="checkbox" name="remove_attachment" value="1" class="rounded border-slate-300"> Remove
                            </label>
                        </div>
                    <?php endif; ?>
                    <?php foreach (['attachment2' => 'Attachment 2', 'attachment3' => 'Attachment 3'] as $legacyAttachmentField => $legacyAttachmentLabel): ?>
                        <?php if (! empty($row[$legacyAttachmentField])): ?>
                            <a href="<?= esc(asset_upload_url((string) $row[$legacyAttachmentField], 'sales')) ?>" target="_blank" class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-emerald-700">
                                <i class="fa-solid fa-file"></i> <?= esc($legacyAttachmentLabel) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mt-3 rounded-xl border border-slate-200 p-3">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <label class="block text-xs font-semibold text-slate-700">Pilgrims <span class="text-rose-500">*</span></label>
                    <button type="button" id="open-add-pilgrim-modal" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                        <i class="fa-solid fa-plus"></i>
                        Add Pilgrim
                    </button>
                </div>
                <select id="pilgrim_ids" name="pilgrim_ids[]" multiple required class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <?php foreach (($pilgrims ?? []) as $item): ?>
                        <?php $displayName = trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? '')); ?>
                        <option value="<?= esc((string) $item['id']) ?>" <?= in_array((int) $item['id'], $oldSelectedPilgrimIds, true) ? 'selected' : '' ?>>
                            #<?= esc((string) $item['id']) ?> - <?= esc($displayName !== '' ? $displayName : ('Pilgrim #' . $item['id'])) ?><?= ! empty($item['passport_no']) ? ' (' . esc((string) $item['passport_no']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </article>

        <datalist id="hotel-options">
            <?php foreach (($hotels ?? []) as $hotel): ?>
                <?php $hotelName = (string) ($hotel['name'] ?? ''); ?>
                <?php if ($hotelName === '') {
                    continue;
                } ?>
                <option value="<?= esc($hotelName) ?>" label="<?= esc(trim((string) ($hotel['city'] ?? ''))) ?>"></option>
            <?php endforeach; ?>
        </datalist>
        <datalist id="visa-type-options">
            <?php foreach ($visaTypes as $visaTypeLabel): ?>
                <option value="<?= esc($visaTypeLabel) ?>"></option>
            <?php endforeach; ?>
        </datalist>
        <datalist id="transport-type-options">
            <?php foreach (array_keys($typeHints) as $typeHint): ?>
                <option value="<?= esc($typeHint) ?>"></option>
            <?php endforeach; ?>
        </datalist>
        <datalist id="transport-route-options">
            <?php foreach (array_keys($routeHints) as $routeHint): ?>
                <option value="<?= esc($routeHint) ?>"></option>
            <?php endforeach; ?>
        </datalist>

        <article class="rounded-xl border border-slate-200 bg-white p-4" data-amount-group>
            <div class="mb-3 flex items-center gap-2">
                <i class="fa-solid fa-passport text-emerald-600"></i>
                <h4 class="text-sm font-semibold text-slate-800">Visa</h4>
                <span class="text-xs text-slate-400">Leave blank if not required</span>
            </div>
            <div class="grid gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Visa Type</label>
                    <input name="visa_type" list="visa-type-options" value="<?= esc((string) $itemValue('visa_type', $visaItem, 'visa_type')) ?>" placeholder="Umrah / Hajj / Tourist" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Visa Number</label>
                    <input name="visa_no" value="<?= esc((string) $itemValue('visa_no', $visaItem, 'visa_no')) ?>" placeholder="Visa number" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Attachment</label>
                    <input type="file" name="visa_attachment" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <?php if (! empty($visaItem['attachment'])): ?>
                        <a href="<?= esc(asset_upload_url((string) $visaItem['attachment'], 'sales')) ?>" target="_blank" class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-emerald-700"><i class="fa-solid fa-file"></i> Current</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <?php $amountFields = [
                    'currencyName' => 'visa_currency',
                    'rateName' => 'visa_exchange_rate',
                    'amountName' => 'visa_amount',
                    'currencyValue' => (string) $itemValue('visa_currency', $visaItem, 'currency', 'PKR'),
                    'rateValue' => (string) $itemValue('visa_exchange_rate', $visaItem, 'exchange_rate', '1'),
                    'amountValue' => (string) $itemValue('visa_amount', $visaItem, 'amount'),
                ]; ?>
                <?php $this->setData(['amountFields' => $amountFields]); ?>
                <?= $this->include('portal/sales/_amount_fields') ?>
            </div>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <?php $this->setData(['purchaseFields' => [
                    'supplierName' => 'visa_supplier_id',
                    'purchaseName' => 'visa_purchase_amount',
                    'supplierValue' => (int) $itemValue('visa_supplier_id', $visaItem, 'supplier_id', 0),
                    'purchaseValue' => (string) $itemValue('visa_purchase_amount', $visaItem, 'purchase_amount'),
                    'suppliers' => $suppliers ?? [],
                ]]); ?>
                <?= $this->include('portal/sales/_purchase_fields') ?>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4" data-amount-group>
            <div class="mb-3 flex items-center gap-2">
                <i class="fa-solid fa-plane-departure text-emerald-600"></i>
                <h4 class="text-sm font-semibold text-slate-800">Ticket</h4>
                <span class="text-xs text-slate-400">Leave blank if not required</span>
            </div>
            <div class="grid gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">PNR</label>
                    <input name="ticket_pnr" value="<?= esc((string) $itemValue('ticket_pnr', $ticketItem, 'pnr')) ?>" placeholder="Booking PNR" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Sector</label>
                    <input name="ticket_sector" value="<?= esc((string) $itemValue('ticket_sector', $ticketItem, 'sector')) ?>" placeholder="PEW-JED" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Ticket No</label>
                    <input name="ticket_no" value="<?= esc((string) $itemValue('ticket_no', $ticketItem, 'ticket_no')) ?>" placeholder="Ticket number" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="mt-3 grid gap-3 md:grid-cols-4">
                <?php $amountFields = [
                    'currencyName' => 'ticket_currency',
                    'rateName' => 'ticket_exchange_rate',
                    'amountName' => 'ticket_amount',
                    'currencyValue' => (string) $itemValue('ticket_currency', $ticketItem, 'currency', 'PKR'),
                    'rateValue' => (string) $itemValue('ticket_exchange_rate', $ticketItem, 'exchange_rate', '1'),
                    'amountValue' => (string) $itemValue('ticket_amount', $ticketItem, 'amount'),
                ]; ?>
                <?php $this->setData(['amountFields' => $amountFields]); ?>
                <?= $this->include('portal/sales/_amount_fields') ?>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Attachment</label>
                    <input type="file" name="ticket_attachment" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <?php if (! empty($ticketItem['attachment'])): ?>
                        <a href="<?= esc(asset_upload_url((string) $ticketItem['attachment'], 'sales')) ?>" target="_blank" class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-emerald-700"><i class="fa-solid fa-file"></i> Current</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <?php $this->setData(['purchaseFields' => [
                    'supplierName' => 'ticket_supplier_id',
                    'purchaseName' => 'ticket_purchase_amount',
                    'supplierValue' => (int) $itemValue('ticket_supplier_id', $ticketItem, 'supplier_id', 0),
                    'purchaseValue' => (string) $itemValue('ticket_purchase_amount', $ticketItem, 'purchase_amount'),
                    'suppliers' => $suppliers ?? [],
                ]]); ?>
                <?= $this->include('portal/sales/_purchase_fields') ?>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="mb-3 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-hotel text-emerald-600"></i>
                    <h4 class="text-sm font-semibold text-slate-800">Hotel</h4>
                    <span class="text-xs text-slate-400">Leave blank if not required</span>
                </div>
                <button type="button" id="add-hotel-row" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                    <i class="fa-solid fa-plus"></i> Add Hotel
                </button>
            </div>
            <div id="hotel-rows" class="space-y-3">
                <?php foreach ($hotelRows as $hotelRow): ?>
                    <div class="hotel-row rounded-xl border border-slate-200 bg-slate-50/60 p-3" data-amount-group>
                        <div class="grid gap-3 md:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Hotel Name</label>
                                <input name="hotel_name[]" list="hotel-options" value="<?= esc((string) ($hotelRow['hotel_name'] ?? '')) ?>" placeholder="Hotel name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Check In</label>
                                <input type="date" name="hotel_check_in[]" value="<?= esc((string) ($hotelRow['check_in_date'] ?? '')) ?>" class="hotel-check-in w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Check Out</label>
                                <input type="date" name="hotel_check_out[]" value="<?= esc((string) ($hotelRow['check_out_date'] ?? '')) ?>" class="hotel-check-out w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">No of Nights</label>
                                <input value="<?= esc((string) ($hotelRow['no_of_nights'] ?? '')) ?>" readonly class="hotel-nights w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                            </div>
                        </div>
                        <div class="mt-3 grid gap-3 md:grid-cols-3 lg:grid-cols-6">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Room Qty</label>
                                <input type="number" min="1" name="hotel_room_qty[]" value="<?= esc((string) ($hotelRow['room_qty'] ?? '1')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Room Type</label>
                                <input name="hotel_room_type[]" value="<?= esc((string) ($hotelRow['room_type'] ?? ($hotelRow['room_name'] ?? ''))) ?>" placeholder="Quad / Triple / Double" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <?php $amountFields = [
                                'currencyName' => 'hotel_currency[]',
                                'rateName' => 'hotel_exchange_rate[]',
                                'amountName' => 'hotel_amount[]',
                                'currencyValue' => (string) ($hotelRow['currency'] ?? 'PKR'),
                                'rateValue' => (string) ($hotelRow['exchange_rate'] ?? '1'),
                                'amountValue' => (string) ($hotelRow['amount'] ?? ''),
                            ]; ?>
                            <?php $this->setData(['amountFields' => $amountFields]); ?>
                            <?= $this->include('portal/sales/_amount_fields') ?>
                            <div class="flex items-end">
                                <button type="button" class="remove-hotel-row inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                    <i class="fa-solid fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        <div class="mt-3 grid gap-3 md:grid-cols-3">
                            <?php $this->setData(['purchaseFields' => [
                                'supplierName' => 'hotel_supplier_id[]',
                                'purchaseName' => 'hotel_purchase_amount[]',
                                'supplierValue' => (int) ($hotelRow['supplier_id'] ?? 0),
                                'purchaseValue' => (string) ($hotelRow['purchase_amount'] ?? ''),
                                'suppliers' => $suppliers ?? [],
                            ]]); ?>
                            <?= $this->include('portal/sales/_purchase_fields') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <template id="supplier-options-template">
            <option value="">Not purchased / no supplier</option>
            <?php foreach (($suppliers ?? []) as $supplier): ?>
                <option value="<?= esc((string) (int) ($supplier['id'] ?? 0)) ?>">
                    <?= esc((string) ($supplier['supplier_name'] ?? '')) ?><?= trim((string) ($supplier['supplier_type'] ?? '')) !== '' ? ' — ' . esc(ucfirst((string) $supplier['supplier_type'])) : '' ?>
                </option>
            <?php endforeach; ?>
        </template>

        <template id="transport-master-template">
            <option value="">Free text</option>
            <?php foreach (($transports ?? []) as $transport): ?>
                <option value="<?= esc((string) $transport['id']) ?>"
                    data-vehicle-type="<?= esc((string) ($transport['vehicle_type'] ?? '')) ?>"
                    data-route="<?= esc((string) ($transport['route_hint'] ?? '')) ?>"
                    data-ziarat="<?= trim((string) ($transport['ziarat_hint'] ?? '')) !== '' ? '1' : '0' ?>">
                    <?= esc((string) $transport['transport_name']) ?><?= trim((string) ($transport['provider_name'] ?? '')) !== '' ? ' - ' . esc((string) $transport['provider_name']) : '' ?>
                </option>
            <?php endforeach; ?>
        </template>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="mb-3 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-bus text-emerald-600"></i>
                    <h4 class="text-sm font-semibold text-slate-800">Transport</h4>
                    <span class="text-xs text-slate-400">Leave blank if not required</span>
                </div>
                <button type="button" id="add-transport-row" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                    <i class="fa-solid fa-plus"></i> Add
                </button>
            </div>
            <div id="transport-rows" class="space-y-3">
                <?php foreach ($transportRows as $transportRow): ?>
                    <div class="transport-row rounded-xl border border-slate-200 bg-slate-50/60 p-3" data-amount-group>
                        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Transport</label>
                                <select name="transport_id[]" class="transport-master w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <option value="">Free text</option>
                                    <?php foreach (($transports ?? []) as $transport): ?>
                                        <option value="<?= esc((string) $transport['id']) ?>"
                                            data-vehicle-type="<?= esc((string) ($transport['vehicle_type'] ?? '')) ?>"
                                            data-route="<?= esc((string) ($transport['route_hint'] ?? '')) ?>"
                                            data-ziarat="<?= trim((string) ($transport['ziarat_hint'] ?? '')) !== '' ? '1' : '0' ?>"
                                            <?= (string) ($transportRow['transport_id'] ?? '') === (string) $transport['id'] ? 'selected' : '' ?>>
                                            <?= esc((string) $transport['transport_name']) ?><?= trim((string) ($transport['provider_name'] ?? '')) !== '' ? ' - ' . esc((string) $transport['provider_name']) : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Type</label>
                                <input name="transport_type[]" list="transport-type-options" value="<?= esc((string) ($transportRow['transport_type'] ?? '')) ?>" placeholder="Bus / Car / Train" class="transport-type w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Route</label>
                                <input name="transport_route[]" list="transport-route-options" value="<?= esc((string) ($transportRow['route'] ?? '')) ?>" placeholder="JED-MAK-MED" class="transport-route w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Attachment</label>
                                <input type="file" name="transport_attachment[]" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <?php if (! empty($transportRow['attachment'])): ?>
                                    <a href="<?= esc(asset_upload_url((string) $transportRow['attachment'], 'sales')) ?>" target="_blank" class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-emerald-700"><i class="fa-solid fa-file"></i> Current</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-3 grid gap-3 md:grid-cols-3 lg:grid-cols-5">
                            <?php $amountFields = [
                                'currencyName' => 'transport_currency[]',
                                'rateName' => 'transport_exchange_rate[]',
                                'amountName' => 'transport_amount[]',
                                'currencyValue' => (string) ($transportRow['currency'] ?? 'PKR'),
                                'rateValue' => (string) ($transportRow['exchange_rate'] ?? '1'),
                                'amountValue' => (string) ($transportRow['amount'] ?? ''),
                            ]; ?>
                            <?php $this->setData(['amountFields' => $amountFields]); ?>
                            <?= $this->include('portal/sales/_amount_fields') ?>
                            <div class="flex items-end">
                                <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
                                    <input type="checkbox" name="transport_ziarat[]" value="1" class="transport-ziarat h-4 w-4 rounded border-slate-300" <?= (int) ($transportRow['is_ziarat'] ?? 0) === 1 ? 'checked' : '' ?>>
                                    Ziarat
                                </label>
                            </div>
                            <div class="flex items-end">
                                <button type="button" class="remove-transport-row inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                    <i class="fa-solid fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        <div class="mt-3 grid gap-3 md:grid-cols-3">
                            <?php $this->setData(['purchaseFields' => [
                                'supplierName' => 'transport_supplier_id[]',
                                'purchaseName' => 'transport_purchase_amount[]',
                                'supplierValue' => (int) ($transportRow['supplier_id'] ?? 0),
                                'purchaseValue' => (string) ($transportRow['purchase_amount'] ?? ''),
                                'suppliers' => $suppliers ?? [],
                            ]]); ?>
                            <?= $this->include('portal/sales/_purchase_fields') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4" data-amount-group>
            <div class="mb-3 flex items-center gap-2">
                <i class="fa-solid fa-box-open text-emerald-600"></i>
                <h4 class="text-sm font-semibold text-slate-800">Package Sale</h4>
                <span class="text-xs text-slate-400">Leave blank if not required</span>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Package</label>
                    <select id="package-select" name="package_id" class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Free text</option>
                        <?php foreach (($packages ?? []) as $package): ?>
                            <option value="<?= esc((string) $package['id']) ?>"
                                data-package-name="<?= esc((string) ($package['name'] ?? '')) ?>"
                                data-departure="<?= esc(substr((string) ($package['departure_date'] ?? ''), 0, 10)) ?>"
                                data-arrival="<?= esc(substr((string) ($package['arrival_date'] ?? ''), 0, 10)) ?>"
                                data-price="<?= esc((string) ($package['selling_price'] ?? '')) ?>"
                                data-notes="<?= esc((string) ($package['notes'] ?? '')) ?>"
                                <?= (string) $itemValue('package_id', $packageItem, 'package_id') === (string) $package['id'] ? 'selected' : '' ?>>
                                <?= esc((string) ($package['code'] ?? '')) ?> - <?= esc((string) ($package['name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Package Name</label>
                    <input id="package-name" name="package_name" value="<?= esc((string) $itemValue('package_name', $packageItem, 'package_name')) ?>" placeholder="e.g. Umrah Plus" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Departure</label>
                    <input type="date" id="package-departure" name="departure_date" value="<?= esc((string) $itemValue('departure_date', $packageItem, 'departure_date')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Arrival</label>
                    <input type="date" id="package-arrival" name="arrival_date" value="<?= esc((string) $itemValue('arrival_date', $packageItem, 'arrival_date')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Attachment</label>
                    <input type="file" name="package_attachment" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <?php if (! empty($packageItem['attachment'])): ?>
                        <a href="<?= esc(asset_upload_url((string) $packageItem['attachment'], 'sales')) ?>" target="_blank" class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-emerald-700"><i class="fa-solid fa-file"></i> Current</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <?php $amountFields = [
                    'currencyName' => 'package_currency',
                    'rateName' => 'package_exchange_rate',
                    'amountName' => 'package_amount',
                    'currencyValue' => (string) $itemValue('package_currency', $packageItem, 'currency', 'PKR'),
                    'rateValue' => (string) $itemValue('package_exchange_rate', $packageItem, 'exchange_rate', '1'),
                    'amountValue' => (string) $itemValue('package_amount', $packageItem, 'amount'),
                ]; ?>
                <?php $this->setData(['amountFields' => $amountFields]); ?>
                <?= $this->include('portal/sales/_amount_fields') ?>
            </div>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <?php $this->setData(['purchaseFields' => [
                    'supplierName' => 'package_supplier_id',
                    'purchaseName' => 'package_purchase_amount',
                    'supplierValue' => (int) $itemValue('package_supplier_id', $packageItem, 'supplier_id', 0),
                    'purchaseValue' => (string) $itemValue('package_purchase_amount', $packageItem, 'purchase_amount'),
                    'suppliers' => $suppliers ?? [],
                ]]); ?>
                <?= $this->include('portal/sales/_purchase_fields') ?>
            </div>
            <div class="mt-3">
                <label class="mb-1 block text-xs font-medium text-slate-600">Package Detail</label>
                <textarea id="package-details" name="package_details" rows="2" placeholder="Inclusions, dates, hotel names" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc((string) $itemValue('package_details', $packageItem, 'description')) ?></textarea>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4" data-amount-group>
            <div class="mb-3 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i>
                <h4 class="text-sm font-semibold text-slate-800">Other / General Sale</h4>
                <span class="text-xs text-slate-400">Leave blank if not required</span>
            </div>
            <div class="grid gap-3 md:grid-cols-1">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Description</label>
                    <textarea name="general_description" rows="2" placeholder="Write particulars for general sale" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc((string) $itemValue('general_description', $generalItem, 'description')) ?></textarea>
                </div>
            </div>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <?php $amountFields = [
                    'currencyName' => 'general_currency',
                    'rateName' => 'general_exchange_rate',
                    'amountName' => 'general_amount',
                    'currencyValue' => (string) $itemValue('general_currency', $generalItem, 'currency', 'PKR'),
                    'rateValue' => (string) $itemValue('general_exchange_rate', $generalItem, 'exchange_rate', '1'),
                    'amountValue' => (string) $itemValue('general_amount', $generalItem, 'amount'),
                ]; ?>
                <?php $this->setData(['amountFields' => $amountFields]); ?>
                <?= $this->include('portal/sales/_amount_fields') ?>
            </div>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <?php $this->setData(['purchaseFields' => [
                    'supplierName' => 'general_supplier_id',
                    'purchaseName' => 'general_purchase_amount',
                    'supplierValue' => (int) $itemValue('general_supplier_id', $generalItem, 'supplier_id', 0),
                    'purchaseValue' => (string) $itemValue('general_purchase_amount', $generalItem, 'purchase_amount'),
                    'suppliers' => $suppliers ?? [],
                ]]); ?>
                <?= $this->include('portal/sales/_purchase_fields') ?>
            </div>
        </article>

        <div class="flex flex-wrap items-center justify-end gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3">
            <a href="<?= site_url('/admin/sales') ?>" class="btn btn-md btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-md btn-primary">
                <i class="fa-solid fa-check"></i><span><?= $isEdit ? 'Update Sale' : 'Save Sale' ?></span>
            </button>
        </div>
    </form>

    <div id="quick-add-pilgrim-modal" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="quick-add-pilgrim-title">
        <div class="w-full max-w-2xl rounded-xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <div>
                    <h4 id="quick-add-pilgrim-title" class="text-sm font-semibold text-slate-800">Add New Pilgrim</h4>
                    <p class="text-[11px] text-slate-500">Create a pilgrim without leaving the sale form.</p>
                </div>
                <button type="button" id="close-add-pilgrim-modal" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 text-slate-500 hover:bg-slate-100" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="quick-add-pilgrim-form" method="post" action="<?= site_url('/admin/bookings/pilgrims/quick-create') ?>" class="grid gap-3 p-4 md:grid-cols-2">
                <?= csrf_field() ?>
                <div id="quick-add-pilgrim-errors" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700 md:col-span-2"></div>
                <label class="block text-xs font-medium text-slate-600">First Name <span class="text-rose-500">*</span>
                    <input type="text" name="first_name" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Given name">
                </label>
                <label class="block text-xs font-medium text-slate-600">Last Name
                    <input type="text" name="last_name" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Family name">
                </label>
                <label class="block text-xs font-medium text-slate-600">Passport No <span class="text-rose-500">*</span>
                    <input type="text" name="passport_no" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="e.g. AB1234567">
                </label>
                <label class="block text-xs font-medium text-slate-600">Mobile No
                    <input type="text" name="mobile_no" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="03001234567">
                </label>
                <label class="block text-xs font-medium text-slate-600">Gender
                    <select name="gender" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </label>
                <label class="block text-xs font-medium text-slate-600">City
                    <input type="text" name="city" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="City">
                </label>
                <input type="hidden" name="country" value="Pakistan">
                <div class="md:col-span-2 flex items-center justify-end gap-2 pt-1">
                    <button type="button" id="cancel-add-pilgrim-modal" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Cancel</button>
                    <button type="submit" id="quick-add-pilgrim-submit" class="btn btn-primary px-3 py-2 text-xs font-semibold">
                        <i class="fa-solid fa-check mr-1"></i>Create & Select
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
    (function($) {
        var $hotelRows = $('#hotel-rows');
        var $transportRows = $('#transport-rows');
        var $totalAmount = $('#sale-total-amount');
        var $pilgrims = $('#pilgrim_ids');

        function numberValue(value) {
            var parsed = parseFloat(value);
            return isNaN(parsed) ? 0 : parsed;
        }

        function refreshGroup($group) {
            var isSar = ($group.find('.js-currency').val() || 'PKR').toUpperCase() === 'SAR';
            var $rate = $group.find('.js-rate');

            $rate.prop('disabled', !isSar);
            if (!isSar) {
                $rate.val('1');
            } else if (numberValue($rate.val()) <= 0) {
                $rate.val('');
            }

            var amount = numberValue($group.find('[data-amount]').val());
            var rate = isSar ? numberValue($rate.val()) : 1;
            var pkr = amount * rate;

            $group.find('.js-pkr-preview').text(isSar && amount > 0 ? 'PKR ' + pkr.toFixed(2) : '');

            return pkr;
        }

        function recalcTotal() {
            var total = 0;
            $('[data-amount-group]').each(function() {
                total += refreshGroup($(this));
            });
            $totalAmount.val(total.toFixed(2));
        }

        function syncNights($row) {
            var checkIn = $row.find('.hotel-check-in').val();
            var checkOut = $row.find('.hotel-check-out').val();
            var $nights = $row.find('.hotel-nights');

            if (!checkIn || !checkOut) {
                $nights.val('');
                return;
            }

            var inDate = new Date(checkIn + 'T00:00:00');
            var outDate = new Date(checkOut + 'T00:00:00');
            if (isNaN(inDate.getTime()) || isNaN(outDate.getTime())) {
                $nights.val('');
                return;
            }

            var diff = Math.floor((outDate - inDate) / (1000 * 60 * 60 * 24));
            $nights.val(String(Math.max(1, diff)));
        }

        function initSelect2($scope) {
            if (!$.fn.select2) {
                return;
            }

            $scope.find('.js-select2, .transport-master').each(function() {
                var $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) {
                    return;
                }
                var hasEmptyOption = $select.find('option[value=""]').length > 0;
                $select.select2({
                    width: '100%',
                    placeholder: hasEmptyOption ? 'Select option' : '',
                    allowClear: hasEmptyOption
                });
            });
        }

        function reindexTransportRows() {
            $transportRows.find('.transport-row').each(function(position) {
                var $row = $(this);
                $row.find('[name]').each(function() {
                    var name = $(this).attr('name') || '';
                    var base = name.replace(/\[\d*\]$/, '');
                    $(this).attr('name', base + '[' + position + ']');
                });
            });
        }

        function currencyOptionsHtml() {
            return '<option value="PKR">PKR</option><option value="SAR">SAR</option>';
        }

        function supplierOptionsHtml() {
            var template = document.getElementById('supplier-options-template');

            return template ? template.innerHTML : '<option value="">Not purchased / no supplier</option>';
        }

        function hotelRowHtml() {
            return '<div class="hotel-row rounded-xl border border-slate-200 bg-slate-50/60 p-3" data-amount-group>'
                + '<div class="grid gap-3 md:grid-cols-4">'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Hotel Name</label><input name="hotel_name[]" list="hotel-options" placeholder="Hotel name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Check In</label><input type="date" name="hotel_check_in[]" class="hotel-check-in w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Check Out</label><input type="date" name="hotel_check_out[]" class="hotel-check-out w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">No of Nights</label><input readonly class="hotel-nights w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></div>'
                + '</div>'
                + '<div class="mt-3 grid gap-3 md:grid-cols-3 lg:grid-cols-6">'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Room Qty</label><input type="number" min="1" name="hotel_room_qty[]" value="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Room Type</label><input name="hotel_room_type[]" placeholder="Quad / Triple / Double" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Currency</label><select name="hotel_currency[]" class="js-currency w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">' + currencyOptionsHtml() + '</select></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Exchange Rate</label><input name="hotel_exchange_rate[]" type="number" step="0.0001" min="0" value="1" placeholder="1" class="js-rate w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-100 disabled:text-slate-500"></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Amount</label><input type="number" step="0.01" min="0" name="hotel_amount[]" data-amount placeholder="0.00" class="js-amount w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><p class="js-pkr-preview mt-1 text-[11px] text-slate-500"></p></div>'
                + '<div class="flex items-end"><button type="button" class="remove-hotel-row inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100"><i class="fa-solid fa-trash"></i> Remove</button></div>'
                + '</div>'
                + '<div class="mt-3 grid gap-3 md:grid-cols-3">'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Supplier</label><select name="hotel_supplier_id[]" class="js-supplier w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">' + supplierOptionsHtml() + '</select></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Purchase Price</label><input type="number" step="0.01" min="0" name="hotel_purchase_amount[]" placeholder="0.00" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><p class="mt-1 text-[11px] text-slate-400">Same currency as the sale amount</p></div>'
                + '</div></div>';
        }

        function transportOptionsHtml() {
            var template = document.getElementById('transport-master-template');

            return template ? template.innerHTML : '<option value="">Free text</option>';
        }

        function transportRowHtml() {
            var position = $transportRows.find('.transport-row').length;
            return '<div class="transport-row rounded-xl border border-slate-200 bg-slate-50/60 p-3" data-amount-group>'
                + '<div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Transport</label><select name="transport_id[' + position + ']" class="transport-master w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">' + transportOptionsHtml() + '</select></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Type</label><input name="transport_type[' + position + ']" list="transport-type-options" placeholder="Bus / Car / Train" class="transport-type w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Route</label><input name="transport_route[' + position + ']" list="transport-route-options" placeholder="JED-MAK-MED" class="transport-route w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Attachment</label><input type="file" name="transport_attachment[' + position + ']" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>'
                + '</div>'
                + '<div class="mt-3 grid gap-3 md:grid-cols-3 lg:grid-cols-5">'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Currency</label><select name="transport_currency[' + position + ']" class="js-currency w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">' + currencyOptionsHtml() + '</select></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Exchange Rate</label><input name="transport_exchange_rate[' + position + ']" type="number" step="0.0001" min="0" value="1" placeholder="1" class="js-rate w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-100 disabled:text-slate-500"></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Amount</label><input type="number" step="0.01" min="0" name="transport_amount[' + position + ']" data-amount placeholder="0.00" class="js-amount w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><p class="js-pkr-preview mt-1 text-[11px] text-slate-500"></p></div>'
                + '<div class="flex items-end"><label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700"><input type="checkbox" name="transport_ziarat[' + position + ']" value="1" class="transport-ziarat h-4 w-4 rounded border-slate-300"> Ziarat</label></div>'
                + '<div class="flex items-end"><button type="button" class="remove-transport-row inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100"><i class="fa-solid fa-trash"></i> Remove</button></div>'
                + '</div>'
                + '<div class="mt-3 grid gap-3 md:grid-cols-3">'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Supplier</label><select name="transport_supplier_id[' + position + ']" class="js-supplier w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">' + supplierOptionsHtml() + '</select></div>'
                + '<div><label class="mb-1 block text-xs font-medium text-slate-600">Purchase Price</label><input type="number" step="0.01" min="0" name="transport_purchase_amount[' + position + ']" placeholder="0.00" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><p class="mt-1 text-[11px] text-slate-400">Same currency as the sale amount</p></div>'
                + '</div></div>';
        }

        function toggleCustomerFields() {
            var isAgent = $('#customer_type').val() === 'agent';
            $('#agent_wrap').toggleClass('hidden', !isAgent);
            $('#walkin_wrap').toggleClass('hidden', isAgent);
        }

        $('#customer_type').on('change', toggleCustomerFields);

        $('form').on('input', '.js-amount, .js-rate', recalcTotal);
        $('form').on('change', '.js-currency', recalcTotal);
        $('form').on('change', '.hotel-check-in, .hotel-check-out', function() {
            syncNights($(this).closest('.hotel-row'));
        });

        $('#add-hotel-row').on('click', function() {
            $hotelRows.append(hotelRowHtml());
        });

        $hotelRows.on('click', '.remove-hotel-row', function() {
            var $rows = $hotelRows.find('.hotel-row');
            if ($rows.length <= 1) {
                $rows.find('input').val('');
                recalcTotal();
                return;
            }
            $(this).closest('.hotel-row').remove();
            recalcTotal();
        });

        $('#add-transport-row').on('click', function() {
            var $row = $(transportRowHtml());
            $transportRows.append($row);
            initSelect2($row);
            reindexTransportRows();
        });

        $transportRows.on('click', '.remove-transport-row', function() {
            $(this).closest('.transport-row').remove();
            reindexTransportRows();
            recalcTotal();
        });

        $transportRows.on('change', '.transport-master', function() {
            var $option = $(this).find('option:selected');
            var $row = $(this).closest('.transport-row');

            var vehicleType = $option.data('vehicle-type') || '';
            var route = $option.data('route') || '';
            var ziarat = String($option.data('ziarat') || '0') === '1';

            if (vehicleType) {
                $row.find('.transport-type').val(vehicleType);
            }
            if (route) {
                $row.find('.transport-route').val(route);
            }
            $row.find('.transport-ziarat').prop('checked', ziarat);
        });

        $('#package-select').on('change', function() {
            var $option = $(this).find('option:selected');
            if (!$(this).val()) {
                return;
            }

            $('#package-name').val($option.data('package-name') || '');
            $('#package-departure').val($option.data('departure') || '');
            $('#package-arrival').val($option.data('arrival') || '');
            $('#package-details').val($option.data('notes') || '');

            var $packageAmount = $('input[name="package_amount"]');
            var price = $option.data('price');
            if (price !== undefined && $packageAmount.val() === '') {
                $packageAmount.val(price);
            }
            recalcTotal();
        });

        $('#open-add-pilgrim-modal').on('click', function() {
            $('#quick-add-pilgrim-errors').addClass('hidden').empty();
            $('#quick-add-pilgrim-modal').removeClass('hidden').addClass('flex');
            $('body').addClass('overflow-hidden');
        });

        $('#close-add-pilgrim-modal, #cancel-add-pilgrim-modal').on('click', function() {
            $('#quick-add-pilgrim-modal').addClass('hidden').removeClass('flex');
            $('body').removeClass('overflow-hidden');
        });

        $('#quick-add-pilgrim-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $errors = $('#quick-add-pilgrim-errors');
            var $submit = $('#quick-add-pilgrim-submit');

            $errors.addClass('hidden').empty();
            $submit.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).done(function(resp) {
                if (resp && resp.csrf && resp.csrf.tokenName && resp.csrf.hash) {
                    $('input[name="' + resp.csrf.tokenName + '"]').val(resp.csrf.hash);
                }
                if (!resp || resp.status !== 'ok' || !resp.item || !resp.item.id) {
                    $errors.removeClass('hidden').text((resp && resp.message) ? resp.message : 'Unable to create pilgrim.');
                    return;
                }

                var newId = String(resp.item.id);
                var optionText = resp.item.optionText || ('#' + newId + ' - New Pilgrim');
                var $existing = $pilgrims.find('option[value="' + newId + '"]');
                if ($existing.length === 0) {
                    $pilgrims.append(new Option(optionText, newId, true, true));
                } else {
                    $existing.prop('selected', true);
                }

                $pilgrims.trigger('change');
                $form[0].reset();
                $('#quick-add-pilgrim-modal').addClass('hidden').removeClass('flex');
                $('body').removeClass('overflow-hidden');
            }).fail(function(xhr) {
                var resp = xhr.responseJSON || {};
                if (resp && resp.csrf && resp.csrf.tokenName && resp.csrf.hash) {
                    $('input[name="' + resp.csrf.tokenName + '"]').val(resp.csrf.hash);
                }
                if (resp.errors && typeof resp.errors === 'object') {
                    $errors.removeClass('hidden').html(Object.values(resp.errors).filter(Boolean).join('<br>'));
                } else {
                    $errors.removeClass('hidden').text(resp.message || 'Failed to create pilgrim. Please check details and try again.');
                }
            }).always(function() {
                $submit.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            });
        });

        toggleCustomerFields();
        initSelect2($(document));
        reindexTransportRows();
        $hotelRows.find('.hotel-row').each(function() {
            syncNights($(this));
        });
        recalcTotal();
    }(jQuery));
</script>
