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
                <h1 class="text-base font-semibold text-slate-900">Supplier / Accounts Payable Report</h1>
                <p class="text-xs text-slate-500">Supplier liabilities from opening and ledger movements.</p>
            </div>
            <a href="<?= site_url('/reports') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                Back to Reports
            </a>
        </div>

        <form method="get" action="<?= site_url('/reports/suppliers-payable') ?>" class="mt-3 grid gap-2 md:grid-cols-4">
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
                <a href="<?= site_url('/reports/suppliers-payable') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="grid gap-3 sm:grid-cols-5">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Opening</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">PKR <?= esc(number_format((float) ($totals['opening'] ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Debits</p>
            <p class="mt-1 text-lg font-semibold text-rose-700">PKR <?= esc(number_format((float) ($totals['debit'] ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Credits</p>
            <p class="mt-1 text-lg font-semibold text-emerald-700">PKR <?= esc(number_format((float) ($totals['credit'] ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Closing</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">PKR <?= esc(number_format((float) ($totals['closing'] ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Net Payable</p>
            <p class="mt-1 text-lg font-semibold text-amber-700">PKR <?= esc(number_format((float) ($totals['payable'] ?? 0), 2)) ?></p>
        </article>
    </section>

    <section class="list-card overflow-auto rounded-xl border border-slate-200 bg-white">
        <table class="list-table">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">Supplier</th>
                    <th class="px-3 py-2 text-left">Type</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-right">Opening</th>
                    <th class="px-3 py-2 text-right">Debit</th>
                    <th class="px-3 py-2 text-right">Credit</th>
                    <th class="px-3 py-2 text-right">Closing</th>
                    <th class="px-3 py-2 text-right">Payable</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="8" class="px-3 py-4 text-slate-500">No records.</td>
                    </tr>
                    <?php else: foreach ($rows as $row): ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2"><?= esc(trim((string) ($row['supplier_name'] ?? '') . ' ' . ((string) ($row['supplier_code'] ?? '') !== '' ? '(' . (string) $row['supplier_code'] . ')' : ''))) ?></td>
                            <td class="px-3 py-2 capitalize"><?= esc((string) ($row['supplier_type'] ?? '-')) ?></td>
                            <td class="px-3 py-2"><?= ((int) ($row['is_active'] ?? 0) === 1) ? 'Active' : 'Inactive' ?></td>
                            <td class="px-3 py-2 text-right">PKR <?= esc(number_format((float) ($row['opening_balance'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2 text-right text-rose-700">PKR <?= esc(number_format((float) ($row['total_debit'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2 text-right text-emerald-700">PKR <?= esc(number_format((float) ($row['total_credit'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2 text-right">PKR <?= esc(number_format((float) ($row['closing_balance'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2 text-right font-semibold text-amber-700">PKR <?= esc(number_format((float) ($row['payable_balance'] ?? 0), 2)) ?></td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </section>
</main>
<?php $this->endSection() ?>