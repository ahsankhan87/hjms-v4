<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($filterErrors)): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
            <?php foreach ($filterErrors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h1 class="text-base font-semibold text-slate-900">Finance Reports</h1>
                <p class="text-xs text-slate-500">Collections, refunds, channels, and top receivable agents.</p>
            </div>
            <a href="<?= site_url('/reports') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                Back to Reports
            </a>
        </div>

        <form method="get" action="<?= site_url('/reports/finance') ?>" class="mt-3 grid gap-2 md:grid-cols-4">
            <div>
                <label for="from_date" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">From Date</label>
                <input id="from_date" type="date" name="from_date" value="<?= esc((string) ($filters['from_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <div>
                <label for="to_date" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">To Date</label>
                <input id="to_date" type="date" name="to_date" value="<?= esc((string) ($filters['to_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    Apply Filter
                </button>
                <a href="<?= site_url('/reports/finance') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="grid gap-3 sm:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Gross Collections</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">PKR <?= esc(number_format((float) ($grossCollections ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Refunds</p>
            <p class="mt-1 text-lg font-semibold text-rose-700">PKR <?= esc(number_format((float) ($refunds ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Net Collections</p>
            <p class="mt-1 text-lg font-semibold text-emerald-700">PKR <?= esc(number_format((float) ($netCollections ?? 0), 2)) ?></p>
        </article>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <article class="list-card overflow-auto rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-slate-900">Collections by Channel</h3>
            </div>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Channel</th>
                        <th class="px-3 py-2 text-left">Count</th>
                        <th class="px-3 py-2 text-left">Net Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($channelSummary)): ?>
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-slate-500">No records.</td>
                        </tr>
                        <?php else: foreach ($channelSummary as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2"><?= esc((string) ($row['channel'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['payment_count'] ?? 0)) ?></td>
                                <td class="px-3 py-2">PKR <?= esc(number_format((float) ($row['net_amount'] ?? 0), 2)) ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </article>

        <article class="list-card overflow-auto rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-slate-900">Top Agent Outstanding</h3>
            </div>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Agent</th>
                        <th class="px-3 py-2 text-left">Receivable</th>
                        <th class="px-3 py-2 text-left">Collected</th>
                        <th class="px-3 py-2 text-left">Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agentSummary)): ?>
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-slate-500">No records.</td>
                        </tr>
                        <?php else: foreach ($agentSummary as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2"><?= esc((string) ($row['agent_name'] ?? '-')) ?></td>
                                <td class="px-3 py-2">PKR <?= esc(number_format((float) ($row['receivable_amount'] ?? 0), 2)) ?></td>
                                <td class="px-3 py-2">PKR <?= esc(number_format((float) ($row['collected_amount'] ?? 0), 2)) ?></td>
                                <td class="px-3 py-2 font-semibold text-rose-700">PKR <?= esc(number_format((float) ($row['outstanding_amount'] ?? 0), 2)) ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </article>
    </section>

    <section class="list-card overflow-auto rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-900">Payment Transactions</h3>
        </div>
        <table class="list-table">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">Payment No</th>
                    <th class="px-3 py-2 text-left">Date</th>
                    <th class="px-3 py-2 text-left">Booking</th>
                    <th class="px-3 py-2 text-left">Agent</th>
                    <th class="px-3 py-2 text-left">Channel</th>
                    <th class="px-3 py-2 text-left">Type</th>
                    <th class="px-3 py-2 text-left">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paymentRows)): ?>
                    <tr>
                        <td colspan="7" class="px-3 py-4 text-slate-500">No records.</td>
                    </tr>
                    <?php else: foreach ($paymentRows as $row): ?>
                        <?php $isRefund = (string) ($row['payment_type'] ?? 'payment') === 'refund'; ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2"><?= esc((string) ($row['payment_no'] ?? '-')) ?></td>
                            <td class="px-3 py-2"><?= esc((string) ($row['payment_date'] ?? '-')) ?></td>
                            <td class="px-3 py-2"><?= esc((string) ($row['booking_no'] ?? '-')) ?></td>
                            <td class="px-3 py-2"><?= esc((string) ($row['agent_name'] ?? '-')) ?></td>
                            <td class="px-3 py-2"><?= esc((string) ($row['channel'] ?? '-')) ?></td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold <?= $isRefund ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                    <?= esc((string) ($row['payment_type'] ?? 'payment')) ?>
                                </span>
                            </td>
                            <td class="px-3 py-2 font-semibold <?= $isRefund ? 'text-rose-700' : 'text-slate-900' ?>">
                                <?= $isRefund ? '-' : '' ?>PKR <?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </section>
</main>
<?php $this->endSection() ?>