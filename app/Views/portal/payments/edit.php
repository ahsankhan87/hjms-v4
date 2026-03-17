<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<?php
$status = (string) ($payment['status'] ?? 'pending');
$statusClasses = [
    'posted'  => 'bg-emerald-100 text-emerald-700',
    'voided'  => 'bg-rose-100 text-rose-700',
    'pending' => 'bg-amber-100 text-amber-700',
    'failed'  => 'bg-slate-100 text-slate-600',
];
$stClass  = $statusClasses[$status] ?? 'bg-slate-100 text-slate-600';
$isVoided = $status === 'voided';
?>
<main class="space-y-4">

    <?php if (!empty($success)): ?>
        <div class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <p class="text-xs text-emerald-700"><?= esc($success) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2">
            <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
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

    <!-- Page Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="<?= site_url('/payments') ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900">Edit Payment</h1>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600"><?= esc($payment['payment_no'] ?? '') ?></span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold <?= $stClass ?>"><?= esc(ucfirst($status)) ?></span>
                </div>
                <p class="text-sm text-slate-500">Modify details — only filled fields will be updated</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= site_url('/payments/' . (int) ($payment['id'] ?? 0) . '/view') ?>" class="btn btn-sm btn-secondary">
                <i class="fa-solid fa-eye"></i><span>View</span>
            </a>
            <a href="<?= site_url('/payments/' . (int) ($payment['id'] ?? 0) . '/receipt') ?>" target="_blank" class="btn btn-sm btn-secondary">
                <i class="fa-solid fa-receipt"></i><span>Receipt</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Form column (2/3) -->
        <div class="lg:col-span-2 space-y-5">

            <!-- Edit form -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-900">Payment Details</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Leave a field blank to keep its current value unchanged</p>
                </div>

                <form method="post" action="<?= site_url('/payments/update') ?>" class="p-6 space-y-5">
                    <?= csrf_field() ?>
                    <input type="hidden" name="payment_id" value="<?= esc((string) ($payment['id'] ?? '')) ?>">

                    <!-- Booking -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Booking</label>
                        <select id="edit-booking-id" name="booking_id" class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                            <option value="">No change</option>
                            <?php foreach ($bookings as $item): ?>
                                <option value="<?= esc($item['id']) ?>" <?= (string) old('booking_id', (string) ($payment['booking_id'] ?? '')) === (string) $item['id'] ? 'selected' : '' ?>>
                                    <?= esc($item['booking_no']) ?> &mdash; <?= esc($item['agent_name'] ?? 'No Agent') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Type + Channel -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Type</label>
                            <select name="payment_type" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                                <?php $oldType = old('payment_type', (string) ($payment['payment_type'] ?? '')); ?>
                                <option value="">No change</option>
                                <option value="payment" <?= $oldType === 'payment' ? 'selected' : '' ?>>Payment (Incoming)</option>
                                <option value="refund" <?= $oldType === 'refund'  ? 'selected' : '' ?>>Refund (Outgoing)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Channel</label>
                            <select name="channel" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                                <?php $oldCh = old('channel', (string) ($payment['channel'] ?? '')); ?>
                                <option value="">No change</option>
                                <option value="manual" <?= $oldCh === 'manual' ? 'selected' : '' ?>>Manual / Cash</option>
                                <option value="bank" <?= $oldCh === 'bank'   ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="online" <?= $oldCh === 'online' ? 'selected' : '' ?>>Online / Card</option>
                            </select>
                        </div>
                    </div>

                    <!-- Amount + Status -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Amount</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-400">SAR</span>
                                <input type="text" name="amount"
                                    value="<?= esc(old('amount', (string) ($payment['amount'] ?? ''))) ?>"
                                    placeholder="Leave blank for no change"
                                    class="w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                                <?php $oldSt = old('status', (string) ($payment['status'] ?? '')); ?>
                                <option value="">No change</option>
                                <option value="posted" <?= $oldSt === 'posted'  ? 'selected' : '' ?>>Posted</option>
                                <option value="voided" <?= $oldSt === 'voided'  ? 'selected' : '' ?>>Voided</option>
                                <option value="pending" <?= $oldSt === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="failed" <?= $oldSt === 'failed'  ? 'selected' : '' ?>>Failed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Date + Gateway Ref -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Date &amp; Time</label>
                            <?php
                            $existingDate = (string) ($payment['payment_date'] ?? '');
                            $dateLocal    = $existingDate !== '' ? str_replace(' ', 'T', substr($existingDate, 0, 16)) : '';
                            ?>
                            <input type="datetime-local" name="payment_date"
                                value="<?= esc(old('payment_date', $dateLocal)) ?>"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Gateway / Reference No.</label>
                            <input type="text" name="gateway_reference"
                                value="<?= esc(old('gateway_reference', (string) ($payment['gateway_reference'] ?? ''))) ?>"
                                placeholder="Bank TXN, cheque no., etc."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
                        </div>
                    </div>

                    <!-- Note -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                        <textarea name="note" rows="3" placeholder="Add a note&hellip;"
                            class="w-full resize-none rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100"><?= esc(old('note', (string) ($payment['note'] ?? ''))) ?></textarea>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center gap-3 border-t border-slate-100 pt-4">
                        <button type="submit" class="btn btn-md btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i><span>Update Payment</span>
                        </button>
                        <a href="<?= site_url('/payments') ?>" class="btn btn-md btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- Danger zone — void (hidden when already voided) -->
            <?php if (!$isVoided): ?>
                <div class="rounded-xl border border-rose-200 bg-rose-50 shadow-sm">
                    <div class="border-b border-rose-100 px-6 py-4">
                        <h3 class="font-semibold text-rose-800">Danger Zone</h3>
                        <p class="text-xs text-rose-600 mt-0.5">Void this payment permanently. This action cannot be reversed.</p>
                    </div>
                    <form method="post" action="<?= site_url('/payments/delete') ?>" class="p-6">
                        <?= csrf_field() ?>
                        <input type="hidden" name="payment_id" value="<?= esc((string) ($payment['id'] ?? '')) ?>">
                        <div class="flex flex-wrap items-end gap-4">
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-rose-800 mb-1">
                                    Reason <span class="font-normal text-rose-500">(optional)</span>
                                </label>
                                <input type="text" name="void_reason"
                                    placeholder="Duplicate entry, incorrect amount, etc."
                                    class="w-full rounded-lg border border-rose-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-rose-400 focus:outline-none focus:ring focus:ring-rose-100">
                            </div>
                            <button type="submit" class="btn btn-md btn-danger shrink-0"
                                onclick="return confirm('Are you sure you want to void payment <?= esc($payment['payment_no'] ?? '') ?>? This cannot be undone.')">
                                <i class="fa-solid fa-ban"></i><span>Void Payment</span>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-4">

            <!-- Payment record (readonly) -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="font-semibold text-slate-900">Current Record</h3>
                </div>
                <dl class="divide-y divide-slate-100 p-5 space-y-0">
                    <div class="flex justify-between py-2.5">
                        <dt class="text-xs text-slate-500">Payment No</dt>
                        <dd class="text-xs font-semibold text-slate-800"><?= esc($payment['payment_no'] ?? '—') ?></dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-xs text-slate-500">Type</dt>
                        <?php $isRef = (string) ($payment['payment_type'] ?? '') === 'refund'; ?>
                        <dd class="text-xs font-semibold <?= $isRef ? 'text-rose-600' : 'text-emerald-600' ?>">
                            <?= esc(ucfirst((string) ($payment['payment_type'] ?? '—'))) ?>
                        </dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-xs text-slate-500">Amount</dt>
                        <dd class="text-sm font-bold text-slate-900">
                            SAR <?= esc(number_format((float) ($payment['amount'] ?? 0), 2)) ?>
                        </dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-xs text-slate-500">Channel</dt>
                        <dd class="text-xs font-semibold text-slate-700 capitalize"><?= esc((string) ($payment['channel'] ?? '—')) ?></dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-xs text-slate-500">Date</dt>
                        <dd class="text-xs text-slate-700"><?= esc((string) ($payment['payment_date'] ?? '—')) ?></dd>
                    </div>
                    <?php if (!empty($payment['created_at'])): ?>
                        <div class="flex justify-between pt-3 mt-1 border-t border-slate-100">
                            <dt class="text-xs text-slate-400">Posted On</dt>
                            <dd class="text-xs text-slate-400"><?= esc($payment['created_at']) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>

            <!-- Booking Financial Snapshot -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="font-semibold text-slate-900">Booking Snapshot</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Financials excluding this payment</p>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Booking</p>
                            <p class="mt-1 text-sm font-bold text-sky-700"><?= esc($payment['booking_no'] ?? '—') ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Agent</p>
                            <p class="mt-1 text-sm font-semibold text-slate-700"><?= esc($payment['agent_name'] ?? '—') ?></p>
                        </div>
                    </div>
                    <?php
                    $fTotal    = (float) ($financials['total_amount'] ?? 0);
                    $fPaid     = (float) ($financials['paid_amount']  ?? 0);
                    $fOutstand = max(0, $fTotal - $fPaid);
                    ?>
                    <div class="rounded-lg bg-slate-50 p-4 space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-xs text-slate-500">Booking Total</span>
                            <span class="text-sm font-semibold text-slate-800">SAR <?= number_format($fTotal, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-slate-500">Amount Paid</span>
                            <span class="text-sm font-semibold text-emerald-600">SAR <?= number_format($fPaid, 2) ?></span>
                        </div>
                        <div class="border-t border-slate-200 pt-2 flex justify-between">
                            <span class="text-xs font-semibold text-slate-600">Outstanding</span>
                            <span class="text-sm font-bold <?= $fOutstand > 0 ? 'text-rose-600' : 'text-emerald-600' ?>">
                                SAR <?= number_format($fOutstand, 2) ?>
                            </span>
                        </div>
                    </div>

                    <?php if ((int) ($payment['agent_id'] ?? 0) > 0): ?>
                        <a href="<?= site_url('/agents/' . (int) $payment['agent_id'] . '/ledger') ?>" class="inline-flex items-center gap-1 text-xs font-semibold text-sky-600 hover:underline">
                            <i class="fa-solid fa-book-open"></i> Open Agent Ledger
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</main>
<?php $this->endSection() ?>