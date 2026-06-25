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
                <h1 class="text-base font-semibold text-slate-900">Profit &amp; Loss Report</h1>
                <p class="text-xs text-slate-500">Formula: (Sales + Package Amounts) - Expenses</p>
            </div>
            <a href="<?= site_url('/reports') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                Back to Reports
            </a>
        </div>

        <form method="get" action="<?= site_url('/reports/profit-loss') ?>" class="mt-3 grid gap-2 md:grid-cols-4">
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
                <a href="<?= site_url('/reports/profit-loss') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Sales Amount</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">PKR <?= esc(number_format((float) ($salesAmount ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Package Amount</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">PKR <?= esc(number_format((float) ($packageAmount ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Total Income</p>
            <p class="mt-1 text-lg font-semibold text-emerald-700">PKR <?= esc(number_format((float) ($totalIncome ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Expenses</p>
            <p class="mt-1 text-lg font-semibold text-rose-700">PKR <?= esc(number_format((float) ($expenseAmount ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Net Profit/Loss</p>
            <p class="mt-1 text-lg font-semibold <?= ((float) ($netProfit ?? 0)) >= 0 ? 'text-emerald-700' : 'text-rose-700' ?>">PKR <?= esc(number_format((float) ($netProfit ?? 0), 2)) ?></p>
        </article>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5">
        <h3 class="text-sm font-semibold text-slate-900">Formula Breakdown</h3>
        <div class="mt-3 overflow-auto">
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Component</th>
                        <th class="px-3 py-2 text-left">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2">Sales Income</td>
                        <td class="px-3 py-2">PKR <?= esc(number_format((float) ($salesAmount ?? 0), 2)) ?></td>
                    </tr>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2">Package Income</td>
                        <td class="px-3 py-2">PKR <?= esc(number_format((float) ($packageAmount ?? 0), 2)) ?></td>
                    </tr>
                    <tr class="border-t border-slate-100 bg-emerald-50/40">
                        <td class="px-3 py-2 font-semibold">Total Income (Sales + Packages)</td>
                        <td class="px-3 py-2 font-semibold text-emerald-700">PKR <?= esc(number_format((float) ($totalIncome ?? 0), 2)) ?></td>
                    </tr>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2">Total Expenses</td>
                        <td class="px-3 py-2 text-rose-700">PKR <?= esc(number_format((float) ($expenseAmount ?? 0), 2)) ?></td>
                    </tr>
                    <tr class="border-t border-slate-100 bg-slate-50">
                        <td class="px-3 py-2 font-semibold">Net Profit/Loss = (Sales + Packages) - Expenses</td>
                        <td class="px-3 py-2 font-semibold <?= ((float) ($netProfit ?? 0)) >= 0 ? 'text-emerald-700' : 'text-rose-700' ?>">PKR <?= esc(number_format((float) ($netProfit ?? 0), 2)) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php $this->endSection() ?>