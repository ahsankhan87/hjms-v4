<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<?php
$status    = (string) ($row['status'] ?? 'draft');
$statusMap = [
    'draft'     => ['bg' => 'bg-slate-100',   'text' => 'text-slate-600',   'ring' => 'ring-slate-300'],
    'confirmed' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-300'],
    'cancelled' => ['bg' => 'bg-rose-100',    'text' => 'text-rose-700',    'ring' => 'ring-rose-300'],
];
$stc = $statusMap[$status] ?? $statusMap['draft'];
$tierMap = [
    'sharing' => ['bg' => 'bg-violet-100', 'text' => 'text-violet-700'],
    'quad'    => ['bg' => 'bg-sky-100',    'text' => 'text-sky-700'],
    'triple'  => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700'],
    'double'  => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
];
$tier    = strtolower((string) ($row['pricing_tier'] ?? ''));
$tc      = $tierMap[$tier] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600'];

$total       = (float) ($row['total_amount']      ?? 0);
$paid        = (float) ($row['paid_amount']        ?? 0);
$outstanding = (float) ($row['outstanding_amount'] ?? 0);
$unitPrice   = (float) ($row['unit_price']         ?? 0);
$pilgrimCount = (int)  ($row['total_pilgrims']     ?? 0);
$paidPct     = $total > 0 ? min(100, max(0, ($paid / $total) * 100)) : 0;
$isOverpaid  = $outstanding < 0;
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

    <!-- ── Page header ─────────────────────────────────────────── -->
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="<?= site_url('/bookings') ?>"
                class="mt-0.5 inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm hover:bg-slate-50">
                <i class="ri-arrow-left-line text-sm"></i>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 font-mono"><?= esc($row['booking_no'] ?? 'Booking') ?></h1>
                    <span class="rounded-full <?= $stc['bg'] ?> <?= $stc['text'] ?> px-2.5 py-0.5 text-xs font-semibold capitalize ring-1 <?= $stc['ring'] ?>">
                        <?= esc($status) ?>
                    </span>
                    <?php if ($tier !== ''): ?>
                        <span class="rounded-full <?= $tc['bg'] ?> <?= $tc['text'] ?> px-2.5 py-0.5 text-xs font-semibold capitalize">
                            <?= esc($tier) ?> room
                        </span>
                    <?php endif; ?>
                </div>
                <p class="mt-0.5 text-xs text-slate-500">
                    Created <?= esc((string) ($row['created_at'] ?? '')) ?>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="<?= site_url('/bookings/' . (int) ($row['id'] ?? 0) . '/edit') ?>" class="btn btn-sm btn-secondary">
                <i class="ri-edit-line mr-1"></i>Edit
            </a>
            <a href="<?= site_url('/bookings/' . (int) ($row['id'] ?? 0) . '/voucher') ?>" target="_blank" class="btn btn-sm btn-secondary">
                <i class="ri-file-text-line mr-1"></i>Voucher
            </a>
            <form method="post" action="<?= site_url('/bookings/delete') ?>" onsubmit="return confirm('Delete this booking? This cannot be undone.');" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="booking_id" value="<?= esc((string) ($row['id'] ?? 0)) ?>">
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="ri-delete-bin-line mr-1"></i>Delete
                </button>
            </form>
        </div>
    </div>

    <!-- ── Two-column layout ────────────────────────────────────── -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- LEFT column (2/3) -->
        <div class="space-y-6 lg:col-span-2">

            <!-- Booking & Package details -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Booking &amp; Package Details</h2>
                </div>
                <dl class="divide-y divide-slate-100">
                    <?php if (!empty($row['package_name'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">Package</dt>
                            <dd class="text-sm font-semibold text-slate-900">
                                <?= esc($row['package_name']) ?>
                                <?php if (!empty($row['package_code'])): ?>
                                    <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-500"><?= esc($row['package_code']) ?></span>
                                <?php endif; ?>
                            </dd>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                        <dt class="text-sm text-slate-500">Pricing Tier</dt>
                        <dd class="text-sm">
                            <?php if ($tier !== ''): ?>
                                <span class="inline-flex items-center rounded-full <?= $tc['bg'] ?> <?= $tc['text'] ?> px-2.5 py-0.5 text-xs font-semibold capitalize">
                                    <?= esc($tier) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-slate-400">—</span>
                            <?php endif; ?>
                        </dd>
                    </div>

                    <?php if (!empty($row['ksa_arrival_date'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">KSA Arrival</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= esc($row['ksa_arrival_date']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($row['ksa_return_date'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">KSA Return</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= esc($row['ksa_return_date']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($row['room_types'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">Room Type(s)</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= esc($row['room_types']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($row['company_name'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">Shirka Company</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= esc($row['company_name']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($row['agent_name'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">Agent</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= esc($row['agent_name']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($row['branch_name'])): ?>
                        <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                            <dt class="text-sm text-slate-500">Branch</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= esc($row['branch_name']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-2 gap-2 px-6 py-3.5">
                        <dt class="text-sm text-slate-500">Total Pilgrims</dt>
                        <dd class="text-sm font-bold text-slate-900"><?= esc($pilgrimCount) ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Pilgrims list -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Pilgrims</h2>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600"><?= count($pilgrims) ?></span>
                </div>
                <?php if (empty($pilgrims)): ?>
                    <p class="px-6 py-8 text-center text-sm text-slate-400">No pilgrims linked to this booking.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="px-4 py-3 text-left">#</th>
                                    <th class="px-4 py-3 text-left">Name</th>
                                    <th class="px-4 py-3 text-left">Passport No</th>
                                    <th class="px-4 py-3 text-left">CNIC</th>
                                    <th class="px-4 py-3 text-left">Gender</th>
                                    <th class="px-4 py-3 text-left">Mobile</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($pilgrims as $i => $pl): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 text-slate-400"><?= $i + 1 ?></td>
                                        <td class="px-4 py-3 font-medium text-slate-900">
                                            <?= esc(trim(($pl['first_name'] ?? '') . ' ' . ($pl['last_name'] ?? ''))) ?>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs text-slate-700"><?= esc($pl['passport_no'] ?? '—') ?></td>
                                        <td class="px-4 py-3 font-mono text-xs text-slate-700"><?= esc($pl['cnic'] ?? '—') ?></td>
                                        <td class="px-4 py-3 capitalize text-slate-600"><?= esc($pl['gender'] ?? '—') ?></td>
                                        <td class="px-4 py-3 text-slate-600"><?= esc($pl['mobile_no'] ?? '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Payment history -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Payment History</h2>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600"><?= count($payments) ?></span>
                </div>
                <?php if (empty($payments)): ?>
                    <p class="px-6 py-8 text-center text-sm text-slate-400">No payments recorded yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="px-4 py-3 text-left">Payment No</th>
                                    <th class="px-4 py-3 text-left">Date</th>
                                    <th class="px-4 py-3 text-left">Type</th>
                                    <th class="px-4 py-3 text-left">Channel</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-right">Amount (PKR)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($payments as $pm): ?>
                                    <?php
                                    $pmStatus = (string) ($pm['status'] ?? 'posted');
                                    $pmType   = (string) ($pm['payment_type'] ?? 'payment');
                                    $pmStc = match ($pmStatus) {
                                        'posted'  => 'bg-emerald-100 text-emerald-700',
                                        'voided'  => 'bg-rose-100 text-rose-700',
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        default   => 'bg-slate-100 text-slate-600',
                                    };
                                    $pmTc = $pmType === 'refund'
                                        ? 'bg-rose-100 text-rose-700'
                                        : 'bg-sky-100 text-sky-700';
                                    ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3">
                                            <a href="<?= site_url('/payments/' . (int) ($pm['id'] ?? 0) . '/view') ?>"
                                                class="font-mono text-xs font-semibold text-sky-600 hover:underline">
                                                <?= esc($pm['payment_no'] ?? ('PMT-' . ($pm['id'] ?? '?'))) ?>
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600"><?= esc((string) ($pm['payment_date'] ?? '—')) ?></td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full <?= $pmTc ?> px-2 py-0.5 text-xs font-semibold capitalize">
                                                <?= esc($pmType) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 capitalize text-slate-600"><?= esc((string) ($pm['channel'] ?? '—')) ?></td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full <?= $pmStc ?> px-2 py-0.5 text-xs font-semibold capitalize">
                                                <?= esc($pmStatus) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold <?= $pmType === 'refund' ? 'text-rose-600' : 'text-slate-900' ?>">
                                            <?= $pmType === 'refund' ? '−' : '' ?><?= esc(number_format((float) ($pm['amount'] ?? 0), 2)) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Remarks -->
            <?php if (!empty($row['remarks'])): ?>
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-sm font-semibold text-slate-900">Remarks</h2>
                    </div>
                    <p class="whitespace-pre-line px-6 py-4 text-sm text-slate-700"><?= esc($row['remarks']) ?></p>
                </div>
            <?php endif; ?>

        </div><!-- /LEFT -->

        <!-- RIGHT column (1/3) -->
        <div class="space-y-6">

            <!-- Financial summary gradient card -->
            <div class="overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                <div class="bg-gradient-to-br from-sky-600 to-indigo-700 px-6 py-6">
                    <p class="text-xs font-semibold uppercase tracking-widest text-white/70">Total Amount</p>
                    <p class="mt-1 text-4xl font-extrabold tracking-tight text-white">
                        <?= esc(number_format($total, 2)) ?>
                    </p>
                    <p class="mt-1 text-xs text-white/60">PKR</p>
                </div>

                <!-- Breakdown rows -->
                <div class="divide-y divide-slate-100 bg-white">
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-sm text-slate-500">Unit Price</span>
                        <span class="text-sm font-semibold text-slate-800"><?= esc(number_format($unitPrice, 2)) ?></span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-sm text-slate-500">× Pilgrims</span>
                        <span class="text-sm font-semibold text-slate-800"><?= esc($pilgrimCount) ?></span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-sm text-slate-500">Paid</span>
                        <span class="text-sm font-bold text-emerald-600"><?= esc(number_format($paid, 2)) ?></span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-sm text-slate-500">Outstanding</span>
                        <span class="text-sm font-bold <?= $isOverpaid ? 'text-emerald-600' : ($outstanding > 0 ? 'text-rose-600' : 'text-emerald-600') ?>">
                            <?php if ($isOverpaid): ?>
                                +<?= esc(number_format(abs($outstanding), 2)) ?> (Overpaid)
                            <?php elseif ($outstanding > 0): ?>
                                <?= esc(number_format($outstanding, 2)) ?>
                            <?php else: ?>
                                Fully Paid
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <!-- Progress bar -->
                <div class="border-t border-slate-100 bg-white px-5 pb-5 pt-4">
                    <div class="mb-1.5 flex items-center justify-between text-xs text-slate-500">
                        <span>Payment progress</span>
                        <span class="font-semibold text-slate-700"><?= number_format($paidPct, 1) ?>%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-emerald-500 transition-all" style="width: <?= number_format($paidPct, 2) ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Status & meta card -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Details</h2>
                </div>
                <dl class="divide-y divide-slate-100">
                    <div class="flex items-center justify-between px-5 py-3">
                        <dt class="text-xs text-slate-500">Status</dt>
                        <dd>
                            <span class="inline-flex items-center rounded-full <?= $stc['bg'] ?> <?= $stc['text'] ?> px-2.5 py-0.5 text-xs font-semibold capitalize ring-1 <?= $stc['ring'] ?>">
                                <?= esc($status) ?>
                            </span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <dt class="text-xs text-slate-500">Pricing Source</dt>
                        <dd class="text-xs font-semibold text-slate-700 capitalize"><?= esc(str_replace('_', ' ', (string) ($row['pricing_source'] ?? '—'))) ?></dd>
                    </div>
                    <?php if (!empty($row['price_locked_at'])): ?>
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-xs text-slate-500">Price Locked</dt>
                            <dd class="text-xs font-semibold text-slate-700"><?= esc((string) $row['price_locked_at']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <div class="flex items-center justify-between px-5 py-3">
                        <dt class="text-xs text-slate-500">Booking ID</dt>
                        <dd class="font-mono text-xs font-bold text-slate-700">#<?= esc((string) ($row['id'] ?? '?')) ?></dd>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <dt class="text-xs text-slate-500">Created</dt>
                        <dd class="text-xs text-slate-600"><?= esc((string) ($row['created_at'] ?? '—')) ?></dd>
                    </div>
                    <?php if (!empty($row['updated_at']) && $row['updated_at'] !== $row['created_at']): ?>
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-xs text-slate-500">Updated</dt>
                            <dd class="text-xs text-slate-600"><?= esc((string) $row['updated_at']) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>

            <!-- Quick links -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Quick Actions</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    <a href="<?= site_url('/payments/create?booking_id=' . (int) ($row['id'] ?? 0)) ?>"
                        class="flex items-center gap-3 px-5 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        <i class="ri-add-circle-line text-emerald-500"></i>
                        Add Payment
                    </a>
                    <a href="<?= site_url('/bookings/' . (int) ($row['id'] ?? 0) . '/edit') ?>"
                        class="flex items-center gap-3 px-5 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        <i class="ri-edit-line text-sky-500"></i>
                        Edit Booking
                    </a>
                    <a href="<?= site_url('/bookings/' . (int) ($row['id'] ?? 0) . '/voucher') ?>" target="_blank"
                        class="flex items-center gap-3 px-5 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        <i class="ri-file-text-line text-slate-500"></i>
                        Print Voucher
                    </a>
                </div>
            </div>

        </div><!-- /RIGHT -->
    </div>
</main>
<?php $this->endSection() ?>