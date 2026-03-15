<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<?php
$status   = (string) ($payment['status'] ?? 'pending');
$statusMap = [
    'posted'  => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
    'voided'  => ['bg' => 'bg-rose-100',    'text' => 'text-rose-700'],
    'pending' => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700'],
    'failed'  => ['bg' => 'bg-slate-100',   'text' => 'text-slate-600'],
];
$stc      = $statusMap[$status] ?? $statusMap['pending'];
$isVoided = $status === 'voided';
$isRefund = (string) ($payment['payment_type'] ?? '') === 'refund';

$bookingTotal  = (float) ($financials['total_amount'] ?? 0);
$paidAmount    = (float) ($financials['paid_amount']  ?? 0);
$outstanding   = max(0, $bookingTotal - $paidAmount);
$paidPct       = $bookingTotal > 0 ? min(100, ($paidAmount / $bookingTotal) * 100) : 0;
?>
<main class="space-y-6">

    <?php if (!empty($success)): ?>
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
            <i class="ri-checkbox-circle-line text-emerald-600"></i>
            <p class="text-sm text-emerald-700"><?= esc($success) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
            <i class="ri-error-warning-line text-rose-500"></i>
            <p class="text-sm text-rose-700"><?= esc($error) ?></p>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="<?= site_url('/payments') ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900"><?= esc($payment['payment_no'] ?? 'Payment') ?></h1>
                    <span class="rounded-full <?= $stc['bg'] ?> <?= $stc['text'] ?> px-2.5 py-0.5 text-xs font-semibold">
                        <?= esc(ucfirst($status)) ?>
                    </span>
                    <span class="rounded-full <?= $isRefund ? 'bg-rose-100 text-rose-700' : 'bg-sky-100 text-sky-700' ?> px-2.5 py-0.5 text-xs font-semibold">
                        <?= $isRefund ? 'Refund' : 'Payment' ?>
                    </span>
                </div>
                <p class="mt-0.5 text-sm text-slate-500">Payment record details and booking financials</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <?php if (!$isVoided): ?>
                <a href="<?= site_url('/payments/' . (int) ($payment['id'] ?? 0) . '/edit') ?>" class="btn btn-sm btn-secondary">
                    <i class="ri-edit-line mr-1"></i>Edit
                </a>
            <?php endif; ?>
            <a href="<?= site_url('/payments/' . (int) ($payment['id'] ?? 0) . '/receipt') ?>" target="_blank" class="btn btn-sm btn-secondary">
                <i class="ri-receipt-line mr-1"></i>Receipt
            </a>
            <?php if (!$isVoided): ?>
                <button type="button" id="open-void-modal" class="btn btn-sm btn-danger">
                    <i class="ri-forbid-line mr-1"></i>Void
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main content grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Left: Amount hero + Details -->
        <div class="lg:col-span-2 space-y-5">

            <!-- Amount hero card -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="<?= $isRefund ? 'bg-gradient-to-r from-rose-600 to-rose-500' : 'bg-gradient-to-r from-emerald-600 to-emerald-500' ?> px-6 py-6">
                    <p class="text-xs font-semibold uppercase tracking-widest text-white/70">
                        <?= $isRefund ? 'Refund Amount' : 'Payment Amount' ?>
                    </p>
                    <p class="mt-1 text-5xl font-extrabold tracking-tight text-white">
                        SAR <?= esc(number_format((float) ($payment['amount'] ?? 0), 2)) ?>
                    </p>
                </div>
                <div class="grid grid-cols-3 divide-x divide-slate-100">
                    <div class="px-5 py-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Channel</p>
                        <p class="mt-1 text-sm font-semibold capitalize text-slate-800"><?= esc((string) ($payment['channel'] ?? '—')) ?></p>
                    </div>
                    <div class="px-5 py-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Status</p>
                        <p class="mt-1 text-sm font-semibold capitalize <?= $stc['text'] ?>"><?= esc($status) ?></p>
                    </div>
                    <div class="px-5 py-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Payment Date</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?= esc((string) ($payment['payment_date'] ?? '—')) ?></p>
                    </div>
                </div>
            </div>

            <!-- Details card -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-900">Payment Details</h2>
                </div>
                <dl class="divide-y divide-slate-100">
                    <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                        <dt class="text-sm text-slate-500">Payment Number</dt>
                        <dd class="text-sm font-semibold text-slate-900"><?= esc($payment['payment_no'] ?? '—') ?></dd>
                    </div>

                    <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                        <dt class="text-sm text-slate-500">Booking</dt>
                        <dd class="text-sm font-semibold">
                            <a href="<?= site_url('/bookings/' . (int) ($payment['booking_id'] ?? 0) . '/edit') ?>"
                                class="text-sky-600 hover:underline">
                                <?= esc($payment['booking_no'] ?? ('Booking #' . ($payment['booking_id'] ?? '?'))) ?>
                            </a>
                        </dd>
                    </div>

                    <?php if (!empty($payment['agent_name'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">Agent</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= esc($payment['agent_name']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($payment['package_name'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">Package</dt>
                            <dd class="text-sm font-semibold text-slate-900">
                                <?= esc($payment['package_name']) ?>
                                <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-500"><?= esc($payment['package_code'] ?? '') ?></span>
                            </dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($payment['gateway_reference'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">Gateway Ref</dt>
                            <dd class="font-mono text-sm text-slate-900"><?= esc($payment['gateway_reference']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($payment['installment_id'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">Installment ID</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= esc((string) $payment['installment_id']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($payment['note'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">Notes</dt>
                            <dd class="text-sm text-slate-700 whitespace-pre-line"><?= esc($payment['note']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($payment['created_at'])): ?>
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 px-6 py-3.5">
                            <dt class="text-xs text-slate-400">Posted At</dt>
                            <dd class="text-xs text-slate-400"><?= esc($payment['created_at']) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Right sidebar -->
        <div class="space-y-4">

            <!-- Booking Financials -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="font-semibold text-slate-900">Booking Financials</h3>
                </div>
                <div class="p-5 space-y-4">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Booking</p>
                            <p class="mt-1 text-sm font-bold text-sky-700"><?= esc($payment['booking_no'] ?? '—') ?></p>
                        </div>
                        <?php if (!empty($payment['agent_name'])): ?>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Agent</p>
                                <p class="mt-1 text-sm font-semibold text-slate-800"><?= esc($payment['agent_name']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Progress bar -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Payment Progress</span>
                            <span class="font-semibold text-slate-700"><?= number_format($paidPct, 0) ?>%</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-emerald-500 transition-all"
                                style="width: <?= $paidPct ?>%"></div>
                        </div>
                    </div>

                    <!-- Financial breakdown -->
                    <div class="rounded-lg bg-slate-50 p-4 space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-xs text-slate-500">Booking Total</span>
                            <span class="text-sm font-semibold text-slate-800">SAR <?= number_format($bookingTotal, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-slate-500">Total Paid</span>
                            <span class="text-sm font-semibold text-emerald-600">SAR <?= number_format($paidAmount, 2) ?></span>
                        </div>
                        <div class="border-t border-slate-200 pt-2 flex justify-between">
                            <span class="text-xs font-semibold text-slate-600">Outstanding</span>
                            <span class="text-sm font-bold <?= $outstanding > 0 ? 'text-rose-600' : 'text-emerald-600' ?>">
                                SAR <?= number_format($outstanding, 2) ?>
                            </span>
                        </div>
                    </div>

                    <?php if ((int) ($payment['agent_id'] ?? 0) > 0): ?>
                        <a href="<?= site_url('/agents/' . (int) $payment['agent_id'] . '/ledger') ?>" class="inline-flex items-center gap-1 text-xs font-semibold text-sky-600 hover:underline">
                            <i class="ri-book-2-line"></i> View Agent Ledger
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="font-semibold text-slate-900">Quick Actions</h3>
                </div>
                <div class="p-3 space-y-0.5">
                    <?php if (!$isVoided): ?>
                        <a href="<?= site_url('/payments/' . (int) ($payment['id'] ?? 0) . '/edit') ?>" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                            <i class="ri-edit-line w-5 text-center text-slate-400"></i>Edit This Payment
                        </a>
                    <?php endif; ?>
                    <a href="<?= site_url('/payments/' . (int) ($payment['id'] ?? 0) . '/receipt') ?>" target="_blank" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                        <i class="ri-file-text-line w-5 text-center text-slate-400"></i>Print Receipt
                    </a>
                    <a href="<?= site_url('/bookings/' . (int) ($payment['booking_id'] ?? 0) . '/edit') ?>" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                        <i class="ri-calendar-check-line w-5 text-center text-slate-400"></i>View Booking
                    </a>
                    <a href="<?= site_url('/bookings/' . (int) ($payment['booking_id'] ?? 0) . '/voucher') ?>" target="_blank" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                        <i class="ri-coupon-line w-5 text-center text-slate-400"></i>Print Voucher
                    </a>
                    <?php if ((int) ($payment['agent_id'] ?? 0) > 0): ?>
                        <a href="<?= site_url('/agents/' . (int) $payment['agent_id'] . '/ledger') ?>" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                            <i class="ri-book-2-line w-5 text-center text-slate-400"></i>Agent Ledger
                        </a>
                    <?php endif; ?>
                    <a href="<?= site_url('/payments/create') ?>" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                        <i class="ri-add-circle-line w-5 text-center text-slate-400"></i>Post New Payment
                    </a>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- Void Modal -->
<?php if (!$isVoided): ?>
    <div id="void-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm" style="display:none">
        <div class="mx-4 w-full max-w-md rounded-xl bg-white shadow-2xl">
            <div class="border-b border-slate-100 px-6 py-5">
                <h3 class="text-lg font-semibold text-slate-900">Void Payment</h3>
                <p class="mt-1 text-sm text-slate-500">This will permanently mark the payment as voided.</p>
            </div>
            <form method="post" action="<?= site_url('/payments/delete') ?>" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="payment_id" value="<?= esc((string) ($payment['id'] ?? '')) ?>">

                <div class="flex gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                    <i class="ri-alert-line mt-0.5 shrink-0 text-lg text-amber-600"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Confirm Void</p>
                        <p class="mt-0.5 text-xs text-amber-700">
                            Payment <strong><?= esc($payment['payment_no'] ?? '') ?></strong>
                            of <strong>SAR <?= number_format((float) ($payment['amount'] ?? 0), 2) ?></strong> will be voided.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Reason <span class="font-normal text-slate-400">(optional)</span>
                    </label>
                    <input type="text" name="void_reason"
                        placeholder="Duplicate entry, incorrect amount, etc."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-rose-400 focus:outline-none focus:ring focus:ring-rose-100">
                </div>

                <div class="flex gap-3 border-t border-slate-100 pt-4">
                    <button type="submit" class="btn btn-md btn-danger flex-1">Confirm Void</button>
                    <button type="button" id="close-void-modal" class="btn btn-md btn-secondary flex-1">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        var voidModal = document.getElementById('void-modal');
        document.getElementById('open-void-modal').addEventListener('click', function() {
            voidModal.style.display = 'flex';
        });
        document.getElementById('close-void-modal').addEventListener('click', function() {
            voidModal.style.display = 'none';
        });
        voidModal.addEventListener('click', function(e) {
            if (e.target === voidModal) voidModal.style.display = 'none';
        });
    </script>
<?php endif; ?>
<?php $this->endSection() ?>