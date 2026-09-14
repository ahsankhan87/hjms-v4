<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <h3 class="text-sm font-semibold text-slate-800">Edit Hotel Sale</h3>
        <p class="text-xs text-slate-500">Update hotel sale details and keep agent credit ledger synced.</p>
    </article>

    <article class="rounded-xl border border-slate-200 bg-white p-4">
        <form method="post" action="<?= site_url('/hotel-sales/update') ?>" enctype="multipart/form-data" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="sale_id" value="<?= esc((string) $row['id']) ?>">
            <?php $customerType = old('customer_type', (string) ($row['customer_type'] ?? 'walk_in')); ?>
            <?php
            $storedPilgrimIds = array_values(array_filter(array_map('intval', explode(',', (string) ($row['pilgrim_ids'] ?? ''))), static function ($id) {
                return $id > 0;
            }));
            if ($storedPilgrimIds === [] && (int) ($row['pilgrim_id'] ?? 0) > 0) {
                $storedPilgrimIds[] = (int) $row['pilgrim_id'];
            }
            $selectedPilgrimIds = array_map('intval', (array) old('pilgrim_ids', $storedPilgrimIds));
            ?>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Invoice #</label>
                    <input name="invoice_no" value="<?= esc(old('invoice_no', (string) ($row['invoice_no'] ?? ''))) ?>" placeholder="Auto: HS-0001" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Inv Date</label>
                    <input type="date" name="sale_date" value="<?= esc(old('sale_date', (string) $row['sale_date'])) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Category</label>
                    <select name="sales_category_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select Category</option>
                        <?php foreach (($categories ?? []) as $category): ?>
                            <option value="<?= esc((string) $category['id']) ?>" <?= (string) old('sales_category_id', (string) $row['sales_category_id']) === (string) $category['id'] ? 'selected' : '' ?>><?= esc((string) $category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Hotel Name</label>
                    <?php $selectedHotelName = (string) old('hotel_name', (string) ($row['hotel_name'] ?? '')); ?>
                    <select name="hotel_name" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select Hotel</option>
                        <?php foreach (($hotels ?? []) as $hotel): ?>
                            <?php $hotelName = (string) ($hotel['name'] ?? ''); ?>
                            <?php if ($hotelName === '') {
                                continue;
                            } ?>
                            <?php $hotelCity = trim((string) ($hotel['city'] ?? '')); ?>
                            <option value="<?= esc($hotelName) ?>" <?= $selectedHotelName === $hotelName ? 'selected' : '' ?>>
                                <?= esc($hotelName) ?><?= $hotelCity !== '' ? ' (' . esc($hotelCity) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if ($selectedHotelName !== ''): ?>
                            <?php
                            $selectedExists = false;
                            foreach (($hotels ?? []) as $hotel) {
                                if ((string) ($hotel['name'] ?? '') === $selectedHotelName) {
                                    $selectedExists = true;
                                    break;
                                }
                            }
                            ?>
                            <?php if (! $selectedExists): ?>
                                <option value="<?= esc($selectedHotelName) ?>" selected><?= esc($selectedHotelName) ?></option>
                            <?php endif; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">CheckIn Date</label>
                    <input type="date" id="checkin_date" name="checkin_date" value="<?= esc(old('checkin_date', (string) ($row['checkin_date'] ?? ''))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Checkout Date</label>
                    <input type="date" id="checkout_date" name="checkout_date" value="<?= esc(old('checkout_date', (string) ($row['checkout_date'] ?? ''))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">No Of Nights</label>
                    <input id="no_of_nights" name="no_of_nights" value="<?= esc(old('no_of_nights', (string) ($row['no_of_nights'] ?? ''))) ?>" readonly class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Room Qty</label>
                    <input name="room_qty" value="<?= esc(old('room_qty', (string) ($row['room_qty'] ?? '1'))) ?>" type="number" min="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Room</label>
                    <input name="room_name" value="<?= esc(old('room_name', (string) ($row['room_name'] ?? ''))) ?>" placeholder="Quad / Triple / Double" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <!-- Hotel Stays Section -->
            <div class="rounded-xl border border-slate-200 p-3 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Hotel Stays (Multiple Check-in/Check-out)</label>
                        <p class="text-xs text-slate-500">Add multiple stay periods for this hotel sale.</p>
                    </div>
                    <button type="button" id="btn-add-stay" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                        <i class="fa-solid fa-plus"></i>
                        Add Stay
                    </button>
                </div>

                <div id="stays-list" class="space-y-2">
                    <?php foreach (($hotelStays ?? []) as $stay): ?>
                        <div class="flex items-start justify-between rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <div class="flex-1">
                                <div class="text-xs font-medium text-slate-700">
                                    <span><?= esc((string) ($stay['hotel_name'] ?? 'N/A')) ?></span>
                                    <span class="text-slate-500">•</span>
                                    <span><?= date('M d, Y', strtotime((string) $stay['check_in_date'])) ?> - <?= date('M d, Y', strtotime((string) $stay['check_out_date'])) ?></span>
                                </div>
                                <div class="text-xs text-slate-600 mt-1">
                                    <span><?= esc((string) ($stay['room_name'] ?? '-')) ?></span>
                                    <?php if ((int) ($stay['room_qty'] ?? 0) > 0): ?>
                                        <span class="text-slate-500">•</span>
                                        <span><?= esc((string) $stay['room_qty']) ?> room(s)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button type="button" class="btn-edit-stay inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50" data-stay-id="<?= esc((string) $stay['id']) ?>" data-hotel="<?= esc((string) ($stay['hotel_name'] ?? '')) ?>" data-checkin="<?= esc((string) $stay['check_in_date']) ?>" data-checkout="<?= esc((string) $stay['check_out_date']) ?>" data-room="<?= esc((string) ($stay['room_name'] ?? '')) ?>" data-qty="<?= esc((string) ($stay['room_qty'] ?? '1')) ?>">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn-delete-stay inline-flex items-center gap-1 rounded-lg border border-rose-300 bg-white px-2 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50" data-stay-id="<?= esc((string) $stay['id']) ?>" onclick="return confirm('Delete this stay?')">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($hotelStays)): ?>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-center">
                            <p class="text-xs text-slate-500">No stays added yet. Click "Add Stay" to create one.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Modal for Add/Edit Stay -->
            <div id="modal-hotel-stay" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true">
                <div class="w-full max-w-lg rounded-xl border border-slate-200 bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <h4 id="modal-title" class="text-sm font-semibold text-slate-800">Add Hotel Stay</h4>
                        <button type="button" id="btn-close-stay-modal" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 text-slate-500 hover:bg-slate-100">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <form id="form-hotel-stay" class="grid gap-3 p-4">
                        <?= csrf_field() ?>
                        <input type="hidden" id="stay_id" name="stay_id" value="">
                        <input type="hidden" name="sale_id" value="<?= esc((string) $row['id']) ?>">

                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Hotel Name <span class="text-rose-500">*</span></label>
                            <input type="text" id="stay_hotel_name" name="hotel_name" required placeholder="Hotel name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Check-In Date <span class="text-rose-500">*</span></label>
                                <input type="date" id="stay_checkin" name="check_in_date" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Check-Out Date <span class="text-rose-500">*</span></label>
                                <input type="date" id="stay_checkout" name="check_out_date" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Room Type</label>
                                <input type="text" id="stay_room" name="room_name" placeholder="Quad / Triple / Double" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Room Qty</label>
                                <input type="number" id="stay_qty" name="room_qty" value="1" min="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>

                        <div id="modal-errors" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"></div>

                        <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                            <button type="button" id="btn-cancel-stay" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Cancel</button>
                            <button type="submit" id="btn-submit-stay" class="btn btn-primary px-3 py-2 text-xs font-semibold">
                                <i class="fa-solid fa-check mr-1"></i>Save Stay
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 p-3">
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
                        <option value="<?= esc((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedPilgrimIds, true) ? 'selected' : '' ?>>
                            #<?= esc((string) $item['id']) ?> - <?= esc($displayName !== '' ? $displayName : ('Pilgrim #' . $item['id'])) ?><?= !empty($item['passport_no']) ? ' (' . esc((string) $item['passport_no']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Customer Type</label>
                    <select id="customer_type" name="customer_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="walk_in" <?= $customerType === 'walk_in' ? 'selected' : '' ?>>Walk-In</option>
                        <option value="agent" <?= $customerType === 'agent' ? 'selected' : '' ?>>Agent</option>
                    </select>
                </div>
                <div id="agent_wrap" class="<?= $customerType === 'agent' ? '' : 'hidden' ?>">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Agent (Required for credit sale)</label>
                    <select name="agent_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select Agent</option>
                        <?php foreach (($agents ?? []) as $agent): ?>
                            <option value="<?= esc((string) $agent['id']) ?>" <?= (string) old('agent_id', (string) ($row['agent_id'] ?? '')) === (string) $agent['id'] ? 'selected' : '' ?>><?= esc((string) $agent['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="walkin_wrap" class="<?= $customerType === 'walk_in' ? '' : 'hidden' ?>">
                <label class="mb-1 block text-xs font-medium text-slate-600">Walk-In Customer Name</label>
                <input id="customer_name" name="customer_name" value="<?= esc(old('customer_name', (string) ($row['customer_name'] ?? ''))) ?>" placeholder="Customer name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Total Amount</label>
                <input name="amount" value="<?= esc(old('amount', (string) $row['amount'])) ?>" placeholder="0.00" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Payment Method</label>
                    <?php $paymentMethod = (string) old('payment_method', (string) ($row['payment_method'] ?? 'cash')); ?>
                    <select id="payment_method" name="payment_method" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'card' => 'Card', 'online' => 'Online', 'other' => 'Other'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= $paymentMethod === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                    <?php $status = (string) old('status', (string) ($row['status'] ?? 'posted')); ?>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="posted" <?= $status === 'posted' ? 'selected' : '' ?>>Posted</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="voided" <?= $status === 'voided' ? 'selected' : '' ?>>Voided</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Reference Number</label>
                    <input name="reference_no" value="<?= esc(old('reference_no', (string) ($row['reference_no'] ?? ''))) ?>" placeholder="Voucher / Receipt / Ref No" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div></div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Attachment 1 (Image/PDF)</label>
                <?php if (!empty($row['attachment'])): ?>
                    <div class="mb-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs text-slate-600">Current: <strong><?= esc((string) $row['attachment']) ?></strong></p>
                    </div>
                <?php endif; ?>
                <input type="file" name="attachment" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <p class="text-xs text-slate-500 mt-1">Accepted: JPG, PNG, PDF, DOC, DOCX (Max 5MB)</p>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Attachment 2 (Image/PDF)</label>
                <?php if (!empty($row['attachment2'])): ?>
                    <div class="mb-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs text-slate-600">Current: <strong><?= esc((string) $row['attachment2']) ?></strong></p>
                    </div>
                <?php endif; ?>
                <input type="file" name="attachment2" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <p class="text-xs text-slate-500 mt-1">Accepted: JPG, PNG, PDF, DOC, DOCX (Max 5MB)</p>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Attachment 3 (Image/PDF)</label>
                <?php if (!empty($row['attachment3'])): ?>
                    <div class="mb-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs text-slate-600">Current: <strong><?= esc((string) $row['attachment3']) ?></strong></p>
                    </div>
                <?php endif; ?>
                <input type="file" name="attachment3" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <p class="text-xs text-slate-500 mt-1">Accepted: JPG, PNG, PDF, DOC, DOCX (Max 5MB)</p>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Remarks</label>
                <textarea name="note" rows="3" placeholder="Optional internal note" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('note', (string) ($row['note'] ?? ''))) ?></textarea>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <a href="<?= site_url('/hotel-sales') ?>" class="btn btn-md btn-secondary">Back</a>
                <button type="submit" class="btn btn-md btn-primary"><i class="fa-solid fa-check"></i><span>Update Hotel Sale</span></button>
            </div>
        </form>
    </article>
</main>

<div id="quick-add-pilgrim-modal" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="quick-add-pilgrim-title">
    <div class="w-full max-w-2xl rounded-xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <div>
                <h4 id="quick-add-pilgrim-title" class="text-sm font-semibold text-slate-800">Add New Pilgrim</h4>
                <p class="text-[11px] text-slate-500">Create a pilgrim without leaving hotel sale form.</p>
            </div>
            <button type="button" id="close-add-pilgrim-modal" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 text-slate-500 hover:bg-slate-100" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="quick-add-pilgrim-form" method="post" action="<?= site_url('/bookings/pilgrims/quick-create') ?>" class="grid gap-3 p-4 md:grid-cols-2">
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
        var customerType = $('#customer_type');
        var agentWrap = $('#agent_wrap');
        var walkinWrap = $('#walkin_wrap');
        var customerName = $('#customer_name');
        var paymentMethod = $('#payment_method');
        var checkinDate = $('#checkin_date');
        var checkoutDate = $('#checkout_date');
        var nightsField = $('#no_of_nights');
        var $pilgrims = $('#pilgrim_ids');
        var $quickPilgrimModal = $('#quick-add-pilgrim-modal');
        var $quickPilgrimForm = $('#quick-add-pilgrim-form');
        var $quickPilgrimErrors = $('#quick-add-pilgrim-errors');
        var $quickPilgrimSubmit = $('#quick-add-pilgrim-submit');

        function toggleCustomerFields() {
            var isAgent = customerType.val() === 'agent';
            agentWrap.toggleClass('hidden', !isAgent);
            walkinWrap.toggleClass('hidden', isAgent);

            if (!isAgent) {
                if (customerName.val().trim() === '') {
                    customerName.val('Walk-In');
                }
                paymentMethod.val('cash');
            }
        }

        function syncNights() {
            if (!checkinDate.val() || !checkoutDate.val()) {
                return;
            }

            var inDate = new Date(checkinDate.val() + 'T00:00:00');
            var outDate = new Date(checkoutDate.val() + 'T00:00:00');
            if (isNaN(inDate.getTime()) || isNaN(outDate.getTime())) {
                return;
            }

            var diff = Math.floor((outDate - inDate) / (1000 * 60 * 60 * 24));
            nightsField.val(String(Math.max(1, diff)));
        }

        function showQuickPilgrimModal() {
            $quickPilgrimErrors.addClass('hidden').empty();
            $quickPilgrimModal.removeClass('hidden').addClass('flex');
            $('body').addClass('overflow-hidden');
            setTimeout(function() {
                $quickPilgrimForm.find('input[name="first_name"]').trigger('focus');
            }, 30);
        }

        function hideQuickPilgrimModal() {
            $quickPilgrimModal.addClass('hidden').removeClass('flex');
            $('body').removeClass('overflow-hidden');
        }

        function applyCsrfToken(csrf) {
            if (!csrf || !csrf.tokenName || !csrf.hash) {
                return;
            }
            $('input[name="' + csrf.tokenName + '"]').val(csrf.hash);
        }

        $('#open-add-pilgrim-modal').on('click', showQuickPilgrimModal);
        $('#close-add-pilgrim-modal, #cancel-add-pilgrim-modal').on('click', hideQuickPilgrimModal);

        $quickPilgrimModal.on('click', function(e) {
            if (e.target === this) {
                hideQuickPilgrimModal();
            }
        });

        $quickPilgrimForm.on('submit', function(e) {
            e.preventDefault();
            $quickPilgrimErrors.addClass('hidden').empty();
            $quickPilgrimSubmit.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

            $.ajax({
                url: $quickPilgrimForm.attr('action'),
                method: 'POST',
                data: $quickPilgrimForm.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).done(function(resp) {
                applyCsrfToken(resp && resp.csrf ? resp.csrf : null);
                if (!resp || resp.status !== 'ok' || !resp.item || !resp.item.id) {
                    $quickPilgrimErrors.removeClass('hidden').text((resp && resp.message) ? resp.message : 'Unable to create pilgrim.');
                    return;
                }

                var newId = String(resp.item.id);
                var optionText = resp.item.optionText || ('#' + newId + ' - New Pilgrim');
                var existing = $pilgrims.find('option[value="' + newId + '"]');
                if (existing.length === 0) {
                    var newOption = new Option(optionText, newId, true, true);
                    $pilgrims.append(newOption);
                } else {
                    existing.prop('selected', true);
                }

                $pilgrims.trigger('change');
                $quickPilgrimForm[0].reset();
                hideQuickPilgrimModal();
            }).fail(function(xhr) {
                var resp = xhr.responseJSON || {};
                applyCsrfToken(resp && resp.csrf ? resp.csrf : null);
                if (resp.errors && typeof resp.errors === 'object') {
                    var list = Object.values(resp.errors).filter(Boolean);
                    $quickPilgrimErrors.removeClass('hidden').html(list.join('<br>'));
                } else {
                    $quickPilgrimErrors.removeClass('hidden').text(resp.message || 'Failed to create pilgrim. Please check details and try again.');
                }
            }).always(function() {
                $quickPilgrimSubmit.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            });
        });

        // Hotel Stays Management
        var $modal = $('#modal-hotel-stay');
        var $form = $('#form-hotel-stay');
        var $modalTitle = $('#modal-title');
        var $stayId = $('#stay_id');
        var $hotelName = $('#stay_hotel_name');
        var $checkin = $('#stay_checkin');
        var $checkout = $('#stay_checkout');
        var $room = $('#stay_room');
        var $qty = $('#stay_qty');
        var $modalErrors = $('#modal-errors');
        var $submitBtn = $('#btn-submit-stay');
        var saleId = '<?= esc((string) $row['id']) ?>';

        function openStayModal(isEdit, data) {
            $modalErrors.addClass('hidden').empty();
            $stayId.val('');
            $hotelName.val('');
            $checkin.val('');
            $checkout.val('');
            $room.val('');
            $qty.val('1');

            if (isEdit && data) {
                $modalTitle.text('Edit Hotel Stay');
                $submitBtn.html('<i class="fa-solid fa-check mr-1"></i>Update Stay');
                $stayId.val(data.stayId);
                $hotelName.val(data.hotelName);
                $checkin.val(data.checkin);
                $checkout.val(data.checkout);
                $room.val(data.room);
                $qty.val(data.qty);
            } else {
                $modalTitle.text('Add Hotel Stay');
                $submitBtn.html('<i class="fa-solid fa-check mr-1"></i>Save Stay');
            }

            $modal.removeClass('hidden').addClass('flex');
            $('body').addClass('overflow-hidden');
            setTimeout(function() {
                $hotelName.trigger('focus');
            }, 30);
        }

        function closeStayModal() {
            $modal.addClass('hidden').removeClass('flex');
            $('body').removeClass('overflow-hidden');
        }

        $('#btn-add-stay').on('click', function() {
            openStayModal(false);
        });

        $('.btn-edit-stay').on('click', function() {
            openStayModal(true, {
                stayId: $(this).data('stay-id'),
                hotelName: $(this).data('hotel'),
                checkin: $(this).data('checkin'),
                checkout: $(this).data('checkout'),
                room: $(this).data('room'),
                qty: $(this).data('qty')
            });
        });

        $('.btn-delete-stay').on('click', function() {
            var $btn = $(this);
            var stayId = $btn.data('stay-id');
            var csrfTokenName = '<?= csrf_token() ?>';
            var csrfTokenHash = $('input[name="' + csrfTokenName + '"]').val() || '<?= csrf_hash() ?>';

            var data = {
                stay_id: stayId
            };
            data[csrfTokenName] = csrfTokenHash;

            $.ajax({
                url: '<?= site_url('/hotel-sales/stay/delete') ?>',
                method: 'POST',
                data: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).done(function(resp) {
                if (resp.success) {
                    location.reload();
                } else {
                    alert(resp.error || 'Failed to delete stay.');
                }
            }).fail(function(xhr) {
                var resp = xhr.responseJSON || {};
                alert(resp.error || 'Failed to delete stay.');
            });
        });

        $('#btn-close-stay-modal, #btn-cancel-stay').on('click', closeStayModal);

        $modal.on('click', function(e) {
            if (e.target === this) {
                closeStayModal();
            }
        });

        $form.on('submit', function(e) {
            e.preventDefault();
            $modalErrors.addClass('hidden').empty();
            $submitBtn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

            var isEdit = $stayId.val() !== '';
            var url = isEdit ? '<?= site_url('/hotel-sales/stay/update') ?>' : '<?= site_url('/hotel-sales/stay/create') ?>';
            var formData = new FormData($form[0]);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).done(function(resp) {
                if (resp.success) {
                    closeStayModal();
                    location.reload();
                } else {
                    if (resp.errors && typeof resp.errors === 'object') {
                        var list = Object.values(resp.errors).filter(Boolean);
                        $modalErrors.removeClass('hidden').html(list.join('<br>'));
                    } else {
                        $modalErrors.removeClass('hidden').text(resp.error || 'Failed to save stay.');
                    }
                }
            }).fail(function(xhr) {
                var resp = xhr.responseJSON || {};
                if (resp.errors && typeof resp.errors === 'object') {
                    var list = Object.values(resp.errors).filter(Boolean);
                    $modalErrors.removeClass('hidden').html(list.join('<br>'));
                } else {
                    $modalErrors.removeClass('hidden').text(resp.error || 'Failed to save stay.');
                }
            }).always(function() {
                $submitBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            });
        });

        customerType.on('change', toggleCustomerFields);
        checkinDate.on('change', syncNights);
        checkoutDate.on('change', syncNights);

        toggleCustomerFields();
        syncNights();
    }(jQuery));
</script>
<?php $this->endSection() ?>