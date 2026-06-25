<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700\"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700\"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700\"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Supplier Ledger</h3>
                    <p class="text-xs text-slate-500">Track supplier bills, payments, and running balances.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="<?= site_url('/suppliers/' . (int) ($supplier['id'] ?? 0) . '/ledger/print?from=' . urlencode((string) ($filterFrom ?? '')) . '&to=' . urlencode((string) ($filterTo ?? ''))) ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="fa-solid fa-print"></i><span>Print</span></a>
                    <a href="<?= site_url('/suppliers/' . (int) ($supplier['id'] ?? 0) . '/ledger/print?autoprint=1&from=' . urlencode((string) ($filterFrom ?? '')) . '&to=' . urlencode((string) ($filterTo ?? ''))) ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="fa-solid fa-file-pdf"></i><span>Export PDF</span></a>
                </div>
            </div>
            <form method="get" action="<?= site_url('/suppliers/' . (int) ($supplier['id'] ?? 0) . '/ledger') ?>" class="mt-3 grid gap-2 md:grid-cols-6">
                <div>
                    <label for="from" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">From Date</label>
                    <input id="from" type="date" name="from" value="<?= esc((string) ($filterFrom ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>
                <div>
                    <label for="to" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">To Date</label>
                    <input id="to" type="date" name="to" value="<?= esc((string) ($filterTo ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>
                <div class="md:col-span-4 flex items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-filter"></i><span>Apply Filter</span></button>
                    <a href="<?= site_url('/suppliers/' . (int) ($supplier['id'] ?? 0) . '/ledger') ?>" class="btn btn-sm btn-secondary"><i class="fa-solid fa-rotate-left"></i><span>Reset</span></a>
                </div>
            </form>
        </article>

        <div class="grid gap-3 lg:grid-cols-3">
            <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-1">
                <h3 class="text-sm font-semibold text-slate-800">Supplier Ledger</h3>
                <p class="mt-2 text-sm"><strong>Supplier:</strong> <?= esc((string) ($supplier['supplier_name'] ?? '')) ?></p>
                <p class="text-sm"><strong>Type:</strong> <?= esc(ucfirst((string) ($supplier['supplier_type'] ?? ''))) ?></p>
                <p class="text-sm"><strong>Opening Balance:</strong> <?= esc(number_format((float) ($supplier['opening_balance'] ?? 0), 2)) ?></p>
                <p class="text-sm"><strong>Closing Balance:</strong> <?= esc(number_format((float) ($closingBalance ?? 0), 2)) ?></p>

                <hr class="my-5 border-slate-200">

                <h4 class="text-sm font-semibold text-slate-800">Post Ledger Entry</h4>
                <form method="post" action="<?= site_url('/suppliers/ledger') ?>" class="mt-3 space-y-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="supplier_id" value="<?= esc((string) $supplier['id']) ?>">
                    <input type="date" name="entry_date" value="<?= esc(old('entry_date', date('Y-m-d'))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <?php $entryType = old('entry_type', 'bill'); ?>
                    <select name="entry_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="bill" <?= $entryType === 'bill' ? 'selected' : '' ?>>Bill (Cr)</option>
                        <option value="payment" <?= $entryType === 'payment' ? 'selected' : '' ?>>Payment (Dr)</option>
                        <option value="adjustment" <?= $entryType === 'adjustment' ? 'selected' : '' ?>>Adjustment</option>
                    </select>
                    <input name="amount" value="<?= esc(old('amount')) ?>" placeholder="Amount" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input name="description" value="<?= esc(old('description')) ?>" placeholder="Description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button type="submit" class="btn btn-md btn-primary btn-block"><i class="fa-solid fa-check"></i><span>Post Entry</span></button>
                </form>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-2 overflow-auto">
                <h3 class="mb-3 text-sm font-semibold text-slate-800">Ledger Entries</h3>
                <?php
                $totalDebit = (float) ($totalDebit ?? 0);
                $totalCredit = (float) ($totalCredit ?? 0);
                ?>
                <table class="list-table">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Date</th>
                            <th class="px-3 py-2 text-left">Type</th>
                            <th class="px-3 py-2 text-left">Description</th>
                            <th class="px-3 py-2 text-left">Debit</th>
                            <th class="px-3 py-2 text-left">Credit</th>
                            <th class="px-3 py-2 text-left">Balance</th>
                            <th class="px-3 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rows)): foreach ($rows as $row): ?>
                                <tr class="border-t border-slate-100">
                                    <td class="px-3 py-2"><?= esc((string) ($row['entry_date'] ?? '')) ?></td>
                                    <td class="px-3 py-2"><?= esc(ucfirst((string) ($row['entry_type'] ?? ''))) ?></td>
                                    <td class="px-3 py-2"><?= esc((string) ($row['description'] ?? '')) ?></td>
                                    <td class="px-3 py-2"><?= esc(number_format((float) ($row['debit_amount'] ?? 0), 2)) ?></td>
                                    <td class="px-3 py-2"><?= esc(number_format((float) ($row['credit_amount'] ?? 0), 2)) ?></td>
                                    <td class="px-3 py-2"><?= esc(number_format((float) ($row['running_balance'] ?? 0), 2)) ?></td>
                                    <td class="px-3 py-2">
                                        <form method="post" action="<?= site_url('/suppliers/ledger/delete') ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="supplier_id" value="<?= esc((string) $supplier['id']) ?>">
                                            <input type="hidden" name="entry_id" value="<?= esc((string) ($row['id'] ?? '0')) ?>">
                                            <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this ledger entry?')" title="Delete Entry"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="7" class="px-3 py-5 text-center text-slate-500">No ledger entries found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-300 bg-slate-100 text-sm font-semibold text-slate-800">
                            <td colspan="3" class="px-3 py-2 text-right">Totals</td>
                            <td class="px-3 py-2"><?= esc(number_format($totalDebit, 2)) ?></td>
                            <td class="px-3 py-2"><?= esc(number_format($totalCredit, 2)) ?></td>
                            <td class="px-3 py-2"><?= esc(number_format((float) ($closingBalance ?? 0), 2)) ?></td>
                            <td class="px-3 py-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </article>
        </div>
    </section>
</main>
<?php $this->endSection() ?>