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
                <h1 class="text-base font-semibold text-slate-900">Expenses by Category Report</h1>
                <p class="text-xs text-slate-500">Detailed breakdown of expenses across all categories.</p>
            </div>
            <a href="<?= site_url('/admin/reports') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                Back to Reports
            </a>
        </div>

        <form method="get" action="<?= site_url('/admin/reports/expenses-by-category') ?>" class="mt-3 grid gap-2 md:grid-cols-4">
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
                <a href="<?= site_url('/admin/reports/expenses-by-category') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                    Reset
                </a>
            </div>
        </form>
    </section>

    <!-- Summary Cards -->
    <section class="grid gap-3 sm:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Total Expenses</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">PKR <?= esc(number_format((float) ($totalExpense ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Categories</p>
            <p class="mt-1 text-lg font-semibold text-slate-900"><?= count($categoryData) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Total Transactions</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">
                <?= array_sum(array_column($categoryData, 'expense_count')) ?>
            </p>
        </article>
    </section>

    <!-- Category Breakdown Table -->
    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <h2 class="mb-3 text-sm font-semibold text-slate-800">Category Breakdown</h2>
        <div class="overflow-auto">
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Category</th>
                        <th class="px-3 py-2 text-right">Count</th>
                        <th class="px-3 py-2 text-right">Total Amount</th>
                        <th class="px-3 py-2 text-right">Percentage</th>
                        <th class="px-3 py-2 text-right">Avg Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categoryData)): ?>
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-slate-500">No expenses found.</td>
                        </tr>
                        <?php else: foreach ($categoryData as $cat): ?>
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <td class="px-3 py-2">
                                    <span class="inline-block rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                        <?= esc($cat['category']) ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right text-sm"><?= $cat['expense_count'] ?></td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-900">PKR <?= esc(number_format($cat['category_total'], 2)) ?></td>
                                <td class="px-3 py-2 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="h-6 rounded-full bg-gradient-to-r from-blue-500 to-blue-600" style="width: <?= $cat['percentage'] ?>%; min-width: 30px;"></div>
                                        <span class="text-xs font-medium text-slate-700"><?= $cat['percentage'] ?>%</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right text-sm text-slate-600">
                                    PKR <?= esc(number_format($cat['category_total'] / max(1, $cat['expense_count']), 2)) ?>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
                <?php if (!empty($categoryData)): ?>
                    <tfoot>
                        <tr class="border-t-2 border-slate-300 bg-slate-100 text-sm font-semibold text-slate-800">
                            <td class="px-3 py-2">TOTAL</td>
                            <td class="px-3 py-2 text-right"><?= array_sum(array_column($categoryData, 'expense_count')) ?></td>
                            <td class="px-3 py-2 text-right">PKR <?= esc(number_format((float) ($totalExpense ?? 0), 2)) ?></td>
                            <td class="px-3 py-2 text-right">100%</td>
                            <td class="px-3 py-2 text-right">
                                PKR <?= esc(number_format(
                                        (float) ($totalExpense ?? 0) / max(1, array_sum(array_column($categoryData, 'expense_count'))),
                                        2
                                    )) ?>
                            </td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </section>

    <!-- Detailed Expense Listing -->
    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <h2 class="mb-3 text-sm font-semibold text-slate-800">Recent Expenses</h2>
        <div class="overflow-auto">
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Date</th>
                        <th class="px-3 py-2 text-left">Category</th>
                        <th class="px-3 py-2 text-left">Paid To</th>
                        <th class="px-3 py-2 text-right">Amount</th>
                        <th class="px-3 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($detailedRows)): ?>
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-slate-500">No expense records found.</td>
                        </tr>
                        <?php else: foreach ($detailedRows as $row): ?>
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <td class="px-3 py-2 text-sm"><?= esc((string) ($row['expense_date'] ?? '')) ?></td>
                                <td class="px-3 py-2">
                                    <span class="inline-block rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                        <?= esc((string) ($row['category'] ?? 'Uncategorized')) ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-sm text-slate-700"><?= esc((string) ($row['paid_to'] ?? '')) ?></td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-900">PKR <?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?></td>
                                <td class="px-3 py-2">
                                    <?php
                                    $status = (string) ($row['status'] ?? 'posted');
                                    $statusClass = match ($status) {
                                        'posted' => 'bg-emerald-100 text-emerald-800',
                                        'draft' => 'bg-amber-100 text-amber-800',
                                        'voided' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-slate-100 text-slate-800',
                                    };
                                    ?>
                                    <span class="inline-block rounded-full px-2.5 py-1 text-xs font-medium <?= $statusClass ?>">
                                        <?= esc(ucfirst($status)) ?>
                                    </span>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php $this->endSection() ?>