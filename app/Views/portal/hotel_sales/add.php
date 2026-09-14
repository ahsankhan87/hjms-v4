<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <h3 class="text-sm font-semibold text-slate-800">Create Hotel Sale</h3>
        <p class="text-xs text-slate-500">Create hotel sale records for pilgrims.</p>
    </article>

    <article class="rounded-xl border border-slate-200 bg-white p-4">
        <?php if (empty($categories)): ?><div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">Create a sales category first before posting sales.</div><?php endif; ?>
        <form method="post" action="<?= site_url('/hotel-sales') ?>" enctype="multipart/form-data" class="space-y-3">
            <?= csrf_field() ?>
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Invoice #</label>
                    <input name="invoice_no" value="<?= esc(old('invoice_no')) ?>" placeholder="Auto: HS-0001" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Inv Date</label>
                    <input type="date" name="sale_date" value="<?= esc(old('sale_date', date('Y-m-d'))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Category</label>
                    <select name="sales_category_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select Category</option>
                        <?php foreach (($categories ?? []) as $category): ?>
                            <option value="<?= esc((string) $category['id']) ?>" <?= old('sales_category_id') === (string) $category['id'] || strtolower($category["name"]) == "hotel" ? 'selected' : '' ?>><?= esc((string) $category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Hotel Name</label>
                    <?php $selectedHotelName = (string) old('hotel_name'); ?>
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
                    <input type="date" id="checkin_date" name="checkin_date" value="<?= esc(old('checkin_date')) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Checkout Date</label>
                    <input type="date" id="checkout_date" name="checkout_date" value="<?= esc(old('checkout_date')) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">No Of Nights</label>
                    <input id="no_of_nights" name="no_of_nights" value="<?= esc(old('no_of_nights')) ?>" readonly class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Room Qty</label>
                    <input name="room_qty" value="<?= esc(old('room_qty', '1')) ?>" type="number" min="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Room</label>
                    <input name="room_name" value="<?= esc(old('room_name')) ?>" placeholder="Quad / Triple / Double" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
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
                <?php $oldPilgrimIds = array_map('intval', (array) old('pilgrim_ids')); ?>
                <select id="pilgrim_ids" name="pilgrim_ids[]" multiple required class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <?php foreach (($pilgrims ?? []) as $item): ?>
                        <?php $displayName = trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? '')); ?>
                        <option value="<?= esc((string) $item['id']) ?>" <?= in_array((int) $item['id'], $oldPilgrimIds, true) ? 'selected' : '' ?>>
                            #<?= esc((string) $item['id']) ?> - <?= esc($displayName !== '' ? $displayName : ('Pilgrim #' . $item['id'])) ?><?= !empty($item['passport_no']) ? ' (' . esc((string) $item['passport_no']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Customer Type</label>
                    <?php $customerType = old('customer_type', 'walk_in'); ?>
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
                            <option value="<?= esc((string) $agent['id']) ?>" <?= old('agent_id') === (string) $agent['id'] ? 'selected' : '' ?>><?= esc((string) $agent['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="walkin_wrap" class="<?= $customerType === 'walk_in' ? '' : 'hidden' ?>">
                <label class="mb-1 block text-xs font-medium text-slate-600">Walk-In Customer Name</label>
                <input id="customer_name" name="customer_name" value="<?= esc(old('customer_name')) ?>" placeholder="Customer name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Total Amount</label>
                <input name="amount" value="<?= esc(old('amount')) ?>" placeholder="0.00" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'card' => 'Card', 'online' => 'Online', 'other' => 'Other'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= old('payment_method', 'cash') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="posted" <?= old('status', 'posted') === 'posted' ? 'selected' : '' ?>>Posted</option>
                        <option value="pending" <?= old('status') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="voided" <?= old('status') === 'voided' ? 'selected' : '' ?>>Voided</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Reference Number</label>
                    <input name="reference_no" value="<?= esc(old('reference_no')) ?>" placeholder="Voucher / Receipt / Ref No" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div></div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Attachment 1 (Image/PDF)</label>
                <input type="file" name="attachment" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <p class="text-xs text-slate-500 mt-1">Accepted: JPG, PNG, PDF, DOC, DOCX (Max 5MB)</p>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Attachment 2 (Image/PDF)</label>
                <input type="file" name="attachment2" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <p class="text-xs text-slate-500 mt-1">Accepted: JPG, PNG, PDF, DOC, DOCX (Max 5MB)</p>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Attachment 3 (Image/PDF)</label>
                <input type="file" name="attachment3" accept="image/*,.pdf,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <p class="text-xs text-slate-500 mt-1">Accepted: JPG, PNG, PDF, DOC, DOCX (Max 5MB)</p>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Remarks</label>
                <textarea name="note" rows="3" placeholder="Optional internal note" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('note')) ?></textarea>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <a href="<?= site_url('/hotel-sales') ?>" class="btn btn-md btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-md btn-primary" <?= empty($categories) ? 'disabled' : '' ?>><i class="fa-solid fa-check"></i><span>Create Hotel Sale</span></button>
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

        customerType.on('change', toggleCustomerFields);
        checkinDate.on('change', syncNights);
        checkoutDate.on('change', syncNights);

        toggleCustomerFields();
        syncNights();
    }(jQuery));
</script>
<?php $this->endSection() ?>