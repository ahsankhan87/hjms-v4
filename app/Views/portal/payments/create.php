<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">

    <?php if (!empty($error)): ?>
        <div class="flex items-start gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2">
            <i class="fa-solid fa-circle-exclamation mt-0.5 text-lg text-rose-500"></i>
            <p class="text-xs text-rose-700"><?= esc($error) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 space-y-1">
            <?php foreach ($errors as $err): ?>
                <p class="text-xs text-amber-700"><?= esc($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Two-column layout -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Form (2/3) -->
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-900">Payment Information</h2>
                    <p class="text-xs text-slate-500 mt-0.5">All fields marked <span class="text-rose-500">*</span> are required</p>
                </div>

                <form method="post" action="<?= site_url('/payments') ?>" class="p-6 space-y-5">
                    <?= csrf_field() ?>

                    <!-- Booking -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Booking <span class="text-rose-500">*</span>
                        </label>
                        <select id="create-booking-id" name="booking_id" required class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                            <option value="">Select a booking&hellip;</option>
                            <?php foreach ($bookings as $item): ?>
                                <option value="<?= esc($item['id']) ?>" <?= (string) old('booking_id') === (string) $item['id'] ? 'selected' : '' ?>>
                                    <?= esc($item['booking_no']) ?> &mdash; <?= esc($item['agent_name'] ?? 'No Agent') ?> (<?= esc(ucfirst($item['status'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Type + Channel -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Payment Type <span class="text-rose-500">*</span>
                            </label>
                            <select id="create-payment-type" name="payment_type" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                                <?php $oldType = old('payment_type', 'payment'); ?>
                                <option value="payment" <?= $oldType === 'payment' ? 'selected' : '' ?>>Payment (Incoming)</option>
                                <option value="refund" <?= $oldType === 'refund'  ? 'selected' : '' ?>>Refund (Outgoing)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Channel <span class="text-rose-500">*</span>
                            </label>
                            <select name="channel" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                                <?php $oldCh = old('channel', 'manual'); ?>
                                <option value="manual" <?= $oldCh === 'manual' ? 'selected' : '' ?>>Manual / Cash</option>
                                <option value="bank" <?= $oldCh === 'bank'   ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="online" <?= $oldCh === 'online' ? 'selected' : '' ?>>Online / Card</option>
                            </select>
                        </div>
                    </div>

                    <!-- Amount + Date -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Amount <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-400">SAR</span>
                                <input id="create-payment-amount" type="text" name="amount" required
                                    value="<?= esc(old('amount', '')) ?>"
                                    placeholder="0.00"
                                    class="w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Payment Date &amp; Time <span class="text-rose-500">*</span>
                            </label>
                            <input type="datetime-local" name="payment_date"
                                value="<?= esc(old('payment_date', date('Y-m-d\TH:i'))) ?>"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                        </div>
                    </div>

                    <!-- Gateway Ref + Installment -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Gateway / Reference No.</label>
                            <input type="text" name="gateway_reference"
                                value="<?= esc(old('gateway_reference', '')) ?>"
                                placeholder="Bank TXN, cheque no., etc."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Installment ID <span class="text-xs font-normal text-slate-400">(optional)</span>
                            </label>
                            <input type="number" name="installment_id"
                                value="<?= esc(old('installment_id', '')) ?>"
                                placeholder="Leave blank if not applicable"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                        </div>
                    </div>

                    <!-- Note -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Internal Note</label>
                        <textarea name="note" rows="3" placeholder="Any relevant notes for this payment&hellip;"
                            class="w-full resize-none rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100"><?= esc(old('note', '')) ?></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 border-t border-slate-100 pt-4">
                        <button type="submit" class="btn btn-md btn-primary">
                            <i class="fa-solid fa-check"></i><span>Post Payment</span>
                        </button>
                        <a href="<?= site_url('/payments') ?>" class="btn btn-md btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-4">

            <!-- Booking Snapshot -->
            <div class="sticky top-6 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-900">Booking Snapshot</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Updates when you select a booking</p>
                        </div>
                        <span id="snap-loader" class="hidden">
                            <i class="fa-solid fa-spinner fa-spin text-emerald-500 text-lg"></i>
                        </span>
                    </div>
                    <div class="p-5 space-y-4">
                        <!-- Status + Agent -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Status</p>
                                <p id="snap-status" class="mt-1 text-sm font-semibold text-slate-700">&mdash;</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Agent</p>
                                <p id="snap-agent" class="mt-1 text-sm font-semibold text-slate-700">&mdash;</p>
                            </div>
                        </div>

                        <!-- Financial -->
                        <div class="rounded-lg bg-slate-50 p-4 space-y-2.5">
                            <div class="flex justify-between">
                                <span class="text-xs text-slate-500">Booking Total</span>
                                <span id="snap-total" class="text-sm font-semibold text-slate-800">0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-slate-500">Amount Paid</span>
                                <span id="snap-paid" class="text-sm font-semibold text-emerald-600">0.00</span>
                            </div>
                            <div class="border-t border-slate-200 pt-2 flex justify-between">
                                <span class="text-xs font-semibold text-slate-600">Outstanding</span>
                                <span id="snap-outstanding" class="text-sm font-bold text-rose-600">0.00</span>
                            </div>
                        </div>

                        <!-- Projection (shown when amount > 0) -->
                        <div id="snap-projection" class="hidden rounded-lg border border-emerald-200 bg-emerald-50 p-4 space-y-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Payment Preview</p>
                            <div class="flex justify-between">
                                <span class="text-xs text-slate-600">Balance Impact</span>
                                <span id="snap-impact" class="text-sm font-semibold text-slate-800">0.00</span>
                            </div>
                            <div class="border-t border-emerald-200 pt-2 flex justify-between">
                                <span class="text-xs font-semibold text-slate-600">Remaining After</span>
                                <span id="snap-remaining" class="text-sm font-bold text-emerald-700">0.00</span>
                            </div>
                        </div>

                        <!-- Ledger link -->
                        <a id="snap-ledger-link" href="#" class="hidden inline-flex items-center gap-1 text-xs font-semibold text-sky-600 hover:underline">
                            <i class="fa-solid fa-book-open"></i> Open Agent Ledger
                        </a>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Guidelines</p>
                    <ul class="mt-2 space-y-1.5 text-xs text-blue-700">
                        <li class="flex items-start gap-1.5"><i class="fa-solid fa-circle-check mt-0.5 shrink-0"></i> Payment cannot exceed booking outstanding balance</li>
                        <li class="flex items-start gap-1.5"><i class="fa-solid fa-circle-check mt-0.5 shrink-0"></i> Refund cannot exceed total amount already paid</li>
                        <li class="flex items-start gap-1.5"><i class="fa-solid fa-circle-check mt-0.5 shrink-0"></i> Booking auto-confirms when fully paid (if enabled)</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    (function($) {
        var bookingData = <?= json_encode($bookings ?? [], JSON_UNESCAPED_UNICODE) ?>;
        var byId = {};
        bookingData.forEach(function(item) {
            byId[String(item.id)] = item;
        });

        var loader = document.getElementById('snap-loader');
        var amountInput = document.getElementById('create-payment-amount');
        var typeInput = document.getElementById('create-payment-type');

        function toMoney(v) {
            var n = Number(v || 0);
            return isNaN(n) ? '0.00' : n.toFixed(2);
        }

        function setText(id, val) {
            var el = document.getElementById(id);
            if (el) el.textContent = val;
        }

        function refresh(showLoader) {
            var bookingVal = $('#create-booking-id').val();
            var selected = byId[String(bookingVal)] || null;

            function doUpdate() {
                setText('snap-status', selected ? String(selected.status || '\u2014') : '\u2014');
                setText('snap-agent', selected ? String(selected.agent_name || '\u2014') : '\u2014');
                setText('snap-total', selected ? toMoney(selected.total_amount) : '0.00');
                setText('snap-paid', selected ? toMoney(selected.paid_amount) : '0.00');
                setText('snap-outstanding', selected ? toMoney(selected.outstanding_amount) : '0.00');

                var ledgerLink = document.getElementById('snap-ledger-link');
                if (selected && Number(selected.agent_id || 0) > 0) {
                    ledgerLink.href = '<?= site_url('/agents') ?>/' + selected.agent_id + '/ledger';
                    ledgerLink.classList.remove('hidden');
                } else {
                    ledgerLink.classList.add('hidden');
                }

                // Projection
                var amount = Number(amountInput ? amountInput.value : 0) || 0;
                var type = typeInput ? typeInput.value : 'payment';
                var sign = type === 'refund' ? -1 : 1;
                var impact = sign * amount;
                var outstanding = selected ? Number(selected.outstanding_amount || 0) : 0;
                var remaining = Math.max(0, outstanding - impact);
                var projDiv = document.getElementById('snap-projection');

                if (amount > 0 && selected) {
                    setText('snap-impact', (impact >= 0 ? '+' : '') + toMoney(impact));
                    setText('snap-remaining', toMoney(remaining));
                    projDiv.classList.remove('hidden');
                } else {
                    projDiv.classList.add('hidden');
                }

                if (loader) loader.classList.add('hidden');
            }

            if (showLoader && loader) {
                loader.classList.remove('hidden');
                setTimeout(doUpdate, 250);
            } else {
                doUpdate();
            }
        }

        // Select2 fires jQuery change — must use jQuery .on() here
        $('#create-booking-id').on('change', function() {
            refresh(true);
        });

        // Native elements are fine with vanilla listeners
        if (amountInput) amountInput.addEventListener('input', function() {
            refresh(false);
        });
        if (typeInput) typeInput.addEventListener('change', function() {
            refresh(false);
        });

        refresh(false);
    })(window.jQuery || {
        fn: {}
    });
</script>
<?php $this->endSection() ?>