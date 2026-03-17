<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
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
    <?php if (!empty($errors) || !empty($filterErrors)): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 space-y-1">
            <?php foreach (array_merge((array) ($errors ?? []), (array) ($filterErrors ?? [])) as $err): ?>
                <p class="text-xs text-amber-700"><?= esc($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Page header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Payment Desk</h1>
            <p class="text-sm text-slate-500">View, filter, and manage all payment transactions</p>
        </div>
        <a href="<?= site_url('/payments/create') ?>" class="btn btn-md btn-primary">
            <i class="fa-solid fa-plus"></i><span>Post New Payment</span>
        </a>
    </div>

    <!-- Filters -->
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="<?= site_url('/payments') ?>" class="grid gap-4 md:grid-cols-4 lg:grid-cols-7">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">From Date</label>
                <input type="date" name="from_date" value="<?= esc((string) ($filters['from_date'] ?? '')) ?>"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">To Date</label>
                <input type="date" name="to_date" value="<?= esc((string) ($filters['to_date'] ?? '')) ?>"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring focus:ring-emerald-100">
            </div>
            <!-- <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Channel</label>
                <select name="channel" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                    <option value="">All</option>
                    <option value="manual" <?= (string) ($filters['channel'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual</option>
                    <option value="bank" <?= (string) ($filters['channel'] ?? '') === 'bank'   ? 'selected' : '' ?>>Bank</option>
                    <option value="online" <?= (string) ($filters['channel'] ?? '') === 'online' ? 'selected' : '' ?>>Online</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Type</label>
                <select name="payment_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                    <option value="">All</option>
                    <option value="payment" <?= (string) ($filters['payment_type'] ?? '') === 'payment' ? 'selected' : '' ?>>Payment</option>
                    <option value="refund" <?= (string) ($filters['payment_type'] ?? '') === 'refund'  ? 'selected' : '' ?>>Refund</option>
                </select>
            </div> -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                    <option value="">All</option>
                    <option value="posted" <?= (string) ($filters['status'] ?? '') === 'posted'  ? 'selected' : '' ?>>Posted</option>
                    <option value="voided" <?= (string) ($filters['status'] ?? '') === 'voided'  ? 'selected' : '' ?>>Voided</option>
                    <option value="pending" <?= (string) ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="failed" <?= (string) ($filters['status'] ?? '') === 'failed'  ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Agent</label>
                <select name="agent_id" class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                    <option value="">All</option>
                    <?php foreach (($agents ?? []) as $agent): ?>
                        <option value="<?= esc((string) ($agent['id'] ?? '')) ?>" <?= (string) ($filters['agent_id'] ?? '') === (string) ($agent['id'] ?? '') ? 'selected' : '' ?>>
                            <?= esc((string) ($agent['name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Booking</label>
                <select name="booking_id" class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                    <option value="">All</option>
                    <?php foreach ($bookings as $item): ?>
                        <option value="<?= esc((string) $item['id']) ?>" <?= (string) ($filters['booking_id'] ?? '') === (string) $item['id'] ? 'selected' : '' ?>>
                            <?= esc((string) $item['booking_no']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-4 lg:col-span-7 flex flex-wrap gap-2">
                <button type="submit" class="btn btn-md btn-primary">
                    <i class="fa-solid fa-filter"></i><span>Apply Filters</span>
                </button>
                <a href="<?= site_url('/payments') ?>" class="btn btn-md btn-secondary">
                    <i class="fa-solid fa-rotate-right"></i><span>Reset</span>
                </a>
            </div>
        </form>
    </section>

    <!-- Payments table -->
    <section class="list-card rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h2 class="font-semibold text-slate-900">Payment Records</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="list-table w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3">Payment No</th>
                        <th class="px-4 py-3">Booking</th>
                        <th class="px-4 py-3">Agent</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3" data-orderable="false">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-400">
                                <i class="fa-solid fa-inbox mb-2 block text-4xl text-slate-200"></i>
                                No payments found for the selected filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $rowStatus = (string) ($row['status'] ?? '');
                        $rowIsRefund = (string) ($row['payment_type'] ?? '') === 'refund';
                        if ($rowStatus === 'posted') {
                            $rowStatusClass = 'bg-emerald-100 text-emerald-700';
                        } elseif ($rowStatus === 'voided') {
                            $rowStatusClass = 'bg-rose-100 text-rose-700';
                        } elseif ($rowStatus === 'pending') {
                            $rowStatusClass = 'bg-amber-100 text-amber-700';
                        } else {
                            $rowStatusClass = 'bg-slate-100 text-slate-600';
                        }
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3">
                                <a href="<?= site_url('/payments/' . (int) $row['id'] . '/view') ?>" class="font-semibold text-sky-700 hover:underline">
                                    <?= esc($row['payment_no']) ?>
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-700"><?= esc((string) ($row['booking_no'] ?? ('#' . $row['booking_id']))) ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= esc((string) ($row['agent_name'] ?? 'â€”')) ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold
                                    <?= $rowIsRefund ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                    <?= esc(ucfirst((string) ($row['payment_type'] ?? 'payment'))) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">
                                <?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?>
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold <?= $rowStatusClass ?>">
                                    <?= esc(ucfirst($rowStatus)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs"><?= esc($row['payment_date']) ?></td>
                            <td class="px-4 py-3">
                                <div class="js-action-wrap">
                                    <button type="button" class="btn btn-sm btn-secondary js-action-toggle inline-flex items-center gap-1">
                                        Actions <i class="fa-solid fa-chevron-down text-xs leading-none transition-transform duration-200 js-action-arrow"></i>
                                    </button>
                                    <div class="js-action-menu hidden w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                        <a href="<?= site_url('/payments/' . (int) $row['id'] . '/view') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <i class="fa-solid fa-eye text-slate-400"></i> View
                                        </a>
                                        <a href="<?= site_url('/payments/' . (int) $row['id'] . '/edit') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <i class="fa-solid fa-pen-to-square text-slate-400"></i> Edit
                                        </a>
                                        <a href="<?= site_url('/payments/' . (int) $row['id'] . '/receipt') ?>" target="_blank" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <i class="fa-solid fa-receipt text-slate-400"></i> Receipt
                                        </a>
                                        <?php if ((int) ($row['agent_id'] ?? 0) > 0): ?>
                                            <a href="<?= site_url('/agents/' . (int) $row['agent_id'] . '/ledger') ?>" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                                <i class="fa-solid fa-book-open text-slate-400"></i> Agent Ledger
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($rowStatus !== 'voided'): ?>
                                            <div class="my-1 border-t border-slate-100"></div>
                                            <button type="button"
                                                class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 js-void-btn"
                                                data-payment-id="<?= esc((string) $row['id']) ?>"
                                                data-payment-no="<?= esc((string) $row['payment_no']) ?>"
                                                data-amount="<?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?>">
                                                <i class="fa-solid fa-ban"></i> Void
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
</main>

<!-- Void Modal -->
<div id="void-modal" style="display:none" class="fixed inset-0 z-50 items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="mx-4 w-full max-w-md rounded-xl bg-white shadow-2xl">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-semibold text-slate-900">Void Payment</h3>
            <p class="mt-1 text-sm text-slate-500">This will permanently mark the payment as voided.</p>
        </div>
        <form method="post" action="<?= site_url('/payments/delete') ?>" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" id="void-modal-payment-id" name="payment_id" value="">
            <div class="flex gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0 text-lg text-amber-600"></i>
                <p class="text-sm text-amber-700">
                    Voiding payment <strong id="void-modal-payment-no"></strong>
                    of <strong id="void-modal-amount"></strong> cannot be undone.
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Reason <span class="font-normal text-slate-400">(optional)</span>
                </label>
                <input type="text" name="void_reason" id="void-modal-reason"
                    placeholder="Duplicate entry, incorrect amount, etc."
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-rose-400 focus:outline-none focus:ring focus:ring-rose-100">
            </div>
            <div class="flex gap-3 border-t border-slate-100 pt-4">
                <button type="submit" class="btn btn-md btn-danger flex-1">Confirm Void</button>
                <button type="button" id="void-modal-close" class="btn btn-md btn-secondary flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        // ── Dropdown toggles (fixed-position, escape overflow) ───────────
        var activeMenu = null;
        var activeBtn = null;

        function setArrow(btn, open) {
            var arrow = btn.querySelector('.js-action-arrow');
            if (arrow) arrow.style.transform = open ? 'rotate(180deg)' : '';
        }

        function closeActive() {
            if (activeMenu) {
                activeMenu.classList.add('hidden');
                setArrow(activeBtn, false);
                activeMenu = null;
                activeBtn = null;
            }
        }

        function positionMenu(btn, menu) {
            var rect = btn.getBoundingClientRect();
            var menuW = 176; // w-44 = 11rem = 176px (fixed, avoids offsetWidth=0 when hidden)
            var left = rect.right - menuW;
            if (left < 8) left = 8;
            menu.style.position = 'fixed';
            menu.style.zIndex = '9999';
            menu.style.top = (rect.bottom + 4) + 'px';
            menu.style.left = left + 'px';
        }

        document.querySelectorAll('.js-action-toggle').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var menu = btn.closest('.js-action-wrap').querySelector('.js-action-menu');
                if (activeMenu === menu) {
                    closeActive();
                    return;
                }
                closeActive();
                positionMenu(btn, menu); // position BEFORE showing to avoid flash
                menu.classList.remove('hidden');
                setArrow(btn, true);
                activeMenu = menu;
                activeBtn = btn;
            });
        });

        document.addEventListener('click', closeActive);

        // Reposition on scroll / resize so menu stays anchored to button
        window.addEventListener('scroll', function() {
            if (activeMenu && activeBtn) positionMenu(activeBtn, activeMenu);
        }, true);
        window.addEventListener('resize', function() {
            if (activeMenu && activeBtn) positionMenu(activeBtn, activeMenu);
        });

        // ── Void modal ───────────────────────────────────────────────────
        var modal = document.getElementById('void-modal');
        var pidInput = document.getElementById('void-modal-payment-id');
        var pnoSpan = document.getElementById('void-modal-payment-no');
        var amtSpan = document.getElementById('void-modal-amount');
        var reasonInput = document.getElementById('void-modal-reason');

        document.querySelectorAll('.js-void-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                pidInput.value = btn.dataset.paymentId || '';
                pnoSpan.textContent = btn.dataset.paymentNo || '';
                amtSpan.textContent = 'SAR ' + (btn.dataset.amount || '0.00');
                reasonInput.value = '';
                modal.style.display = 'flex';
            });
        });

        document.getElementById('void-modal-close').addEventListener('click', function() {
            modal.style.display = 'none';
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) modal.style.display = 'none';
        });
    })();
</script>
<?php $this->endSection() ?>