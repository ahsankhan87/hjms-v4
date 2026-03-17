<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700\"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700\"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700\"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Supplier Ledger</h3>
            <p class="text-xs text-slate-500">Track supplier bills, payments, and running balances.</p>
        </article>

        <div class="grid gap-3 lg:grid-cols-3">
            <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-1">
                <h3 class="text-sm font-semibold text-slate-800">Supplier Ledger</h3>
                <p class="mt-2 text-sm"><strong>Supplier:</strong> <?= esc((string) ($supplier['supplier_name'] ?? '')) ?></p>
                <p class="text-sm"><strong>Type:</strong> <?= esc(ucfirst((string) ($supplier['supplier_type'] ?? ''))) ?></p>
                <p class="text-sm"><strong>Closing Balance:</strong> <?= esc(number_format((float) ($closingBalance ?? 0), 2)) ?></p>

                <hr class="my-5 border-slate-200">

                <h4 class="text-sm font-semibold text-slate-800">Post Ledger Entry</h4>
                <form method="post" action="<?= site_url('/suppliers/ledger') ?>" class="mt-3 space-y-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="supplier_id" value="<?= esc((string) $supplier['id']) ?>">
                    <input type="date" name="entry_date" value="<?= esc(old('entry_date', date('Y-m-d'))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <?php $entryType = old('entry_type', 'payment'); ?>
                    <select name="entry_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="bill" <?= $entryType === 'bill' ? 'selected' : '' ?>>Bill (Dr)</option>
                        <option value="payment" <?= $entryType === 'payment' ? 'selected' : '' ?>>Payment (Cr)</option>
                        <option value="adjustment" <?= $entryType === 'adjustment' ? 'selected' : '' ?>>Adjustment</option>
                    </select>
                    <input name="amount" value="<?= esc(old('amount')) ?>" placeholder="Amount" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input name="description" value="<?= esc(old('description')) ?>" placeholder="Description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button type="submit" class="btn btn-md btn-primary btn-block"><i class="fa-solid fa-check"></i><span>Post Entry</span></button>
                </form>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-2 overflow-auto">
                <h3 class="mb-3 text-sm font-semibold text-slate-800">Ledger Entries</h3>
                <table class="list-table">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Date</th>
                            <th class="px-3 py-2 text-left">Type</th>
                            <th class="px-3 py-2 text-left">Description</th>
                            <th class="px-3 py-2 text-left">Debit</th>
                            <th class="px-3 py-2 text-left">Credit</th>
                            <th class="px-3 py-2 text-left">Balance</th>
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
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="6" class="px-3 py-5 text-center text-slate-500">No ledger entries found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </article>
        </div>
    </section>
</main>
<?php $this->endSection() ?>