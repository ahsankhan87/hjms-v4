<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors) || !empty($filterErrors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach (array_merge((array) ($errors ?? []), (array) ($filterErrors ?? [])) as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Expenses</h3>
                <p class="text-xs text-slate-500">Track payments and operational outflows for the active season.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?= site_url('/expense-categories') ?>" class="btn btn-md btn-secondary"><i class="fa-solid fa-tags"></i><span>Manage Categories</span></a>
                <a href="<?= site_url('/expenses/add') ?>" class="btn btn-md btn-primary"><i class="fa-solid fa-plus"></i><span>Add Expense</span></a>
            </div>
        </div>
    </article>

    <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="get" action="<?= site_url('/expenses') ?>" class="grid gap-3 md:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">From Date</label>
                <input type="date" name="from_date" value="<?= esc((string) ($filters['from_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">To Date</label>
                <input type="date" name="to_date" value="<?= esc((string) ($filters['to_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Category</label>
                <select name="expense_category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <?php foreach (($categories ?? []) as $category): ?>
                        <option value="<?= esc((string) $category['id']) ?>" <?= (string) ($filters['expense_category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= esc((string) $category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Payment Method</label>
                <select name="payment_method" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <?php foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'card' => 'Card', 'online' => 'Online', 'other' => 'Other'] as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= (string) ($filters['payment_method'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Status</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="posted" <?= (string) ($filters['status'] ?? '') === 'posted' ? 'selected' : '' ?>>Posted</option>
                    <option value="pending" <?= (string) ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="voided" <?= (string) ($filters['status'] ?? '') === 'voided' ? 'selected' : '' ?>>Voided</option>
                </select>
            </div>
            <div class="md:col-span-5 flex flex-wrap gap-2">
                <button type="submit" class="btn btn-md btn-primary"><i class="fa-solid fa-filter"></i><span>Apply Filters</span></button>
                <a href="<?= site_url('/expenses') ?>" class="btn btn-md btn-secondary"><i class="fa-solid fa-rotate-right"></i><span>Reset</span></a>
                <div class="ml-auto rounded-lg bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">Total: PKR <?= esc(number_format((float) ($totalAmount ?? 0), 2)) ?></div>
            </div>
        </form>
    </section>

    <?php
    $postedTotal = 0.0;
    $pendingTotal = 0.0;
    $voidedTotal = 0.0;
    $postedCount = 0;
    $pendingCount = 0;
    $voidedCount = 0;

    foreach ((array) ($rows ?? []) as $expenseRow) {
        $amount = (float) ($expenseRow['amount'] ?? 0);
        $status = strtolower((string) ($expenseRow['status'] ?? 'posted'));
        if ($status === 'pending') {
            $pendingTotal += $amount;
            $pendingCount++;
        } elseif ($status === 'voided') {
            $voidedTotal += $amount;
            $voidedCount++;
        } else {
            $postedTotal += $amount;
            $postedCount++;
        }
    }
    ?>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Grand Total</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">PKR <?= esc(number_format((float) ($totalAmount ?? 0), 2)) ?></p>
            <p class="mt-1 text-xs text-slate-500">Entries: <?= esc((string) count((array) ($rows ?? []))) ?></p>
        </article>
        <article class="rounded-xl border border-emerald-200 bg-emerald-50/40 px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-emerald-700">Posted Total</p>
            <p class="mt-1 text-lg font-semibold text-emerald-700">PKR <?= esc(number_format($postedTotal, 2)) ?></p>
            <p class="mt-1 text-xs text-emerald-700/80">Entries: <?= esc((string) $postedCount) ?></p>
        </article>
        <article class="rounded-xl border border-amber-200 bg-amber-50/50 px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-amber-700">Pending Total</p>
            <p class="mt-1 text-lg font-semibold text-amber-700">PKR <?= esc(number_format($pendingTotal, 2)) ?></p>
            <p class="mt-1 text-xs text-amber-700/80">Entries: <?= esc((string) $pendingCount) ?></p>
        </article>
        <article class="rounded-xl border border-rose-200 bg-rose-50/40 px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-rose-700">Voided Total</p>
            <p class="mt-1 text-lg font-semibold text-rose-700">PKR <?= esc(number_format($voidedTotal, 2)) ?></p>
            <p class="mt-1 text-xs text-rose-700/80">Entries: <?= esc((string) $voidedCount) ?></p>
        </article>
    </section>

    <div class="list-card overflow-auto">
        <table class="list-table">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2 text-left">Date</th>
                    <th class="px-3 py-2 text-left">Category</th>
                    <th class="px-3 py-2 text-left">Paid To</th>
                    <th class="px-3 py-2 text-left">Method</th>
                    <th class="px-3 py-2 text-left">Note</th>
                    <th class="px-3 py-2 text-left">Amount</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="9" class="px-3 py-6 text-center text-slate-500">No expenses found.</td>
                    </tr>
                    <?php else: foreach ($rows as $row): ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2">#<?= esc((string) $row['id']) ?></td>
                            <td class="px-3 py-2 text-slate-700"><?= esc((string) ($row['expense_date'] ?? '-')) ?></td>
                            <td class="px-3 py-2 font-medium text-slate-800"><?= esc((string) ($row['category_name'] ?? '-')) ?></td>
                            <td class="px-3 py-2 text-slate-600"><?= esc((string) ($row['paid_to'] ?? '-')) ?></td>
                            <td class="px-3 py-2 text-slate-600"><?= esc(str_replace('_', ' ', ucfirst((string) ($row['payment_method'] ?? '-')))) ?></td>
                            <td class="px-3 py-2 text-slate-600"><?= esc((string) ($row['note'] ?? '-')) ?></td>
                            <td class="px-3 py-2 font-semibold text-slate-800"><?= esc(number_format((float) ($row['amount'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2">
                                <?php $status = (string) ($row['status'] ?? 'posted'); ?>
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?= $status === 'posted' ? 'bg-emerald-100 text-emerald-700' : ($status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') ?>"><?= esc(ucfirst($status)) ?></span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <a href="<?= site_url('/expenses/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="Edit Expense"><i class="fa-solid fa-pen"></i></a>
                                    <form method="post" action="<?= site_url('/expenses/delete') ?>" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="expense_id" value="<?= esc((string) $row['id']) ?>">
                                        <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this expense?')" title="Delete Expense"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-200 bg-slate-50">
                    <td colspan="6" class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Filtered Total</td>
                    <td class="px-3 py-2 text-sm font-semibold text-slate-900">PKR <?= esc(number_format((float) ($totalAmount ?? 0), 2)) ?></td>
                    <td colspan="2" class="px-3 py-2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</main>
<?php $this->endSection() ?>