<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-3">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 lg:col-span-1">
            <h3 class="text-lg font-semibold">Agent Ledger</h3>
            <p class="mt-2 text-sm"><strong>Agent:</strong> <?= esc((string) ($agent['name'] ?? '')) ?></p>
            <p class="text-sm"><strong>Code:</strong> <?= esc((string) ($agent['code'] ?? '')) ?></p>
            <p class="text-sm"><strong>Closing Balance:</strong> <?= esc(number_format((float) ($closingBalance ?? 0), 2)) ?></p>

            <hr class="my-5 border-slate-200">

            <h4 class="text-base font-semibold">Post Manual Entry</h4>
            <form method="post" action="<?= site_url('/agents/ledger') ?>" class="mt-3 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="agent_id" value="<?= esc((string) $agent['id']) ?>">
                <input type="date" name="entry_date" value="<?= esc(old('entry_date', date('Y-m-d'))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <?php $entryType = old('entry_type', 'credit'); ?>
                <select name="entry_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="debit" <?= $entryType === 'debit' ? 'selected' : '' ?>>Debit (Increase Receivable)</option>
                    <option value="credit" <?= $entryType === 'credit' ? 'selected' : '' ?>>Credit (Payment Received)</option>
                    <option value="adjustment" <?= $entryType === 'adjustment' ? 'selected' : '' ?>>Adjustment</option>
                </select>
                <input name="amount" value="<?= esc(old('amount')) ?>" placeholder="Amount" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="description" value="<?= esc(old('description')) ?>" placeholder="Description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button type="submit" class="btn btn-md btn-primary btn-block">Post Entry</button>
            </form>
        </article>

        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 lg:col-span-2 overflow-auto">
            <h3 class="text-lg font-semibold mb-4">Ledger Entries</h3>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left">
                        <th class="py-2 pr-3">Date</th>
                        <th class="py-2 pr-3">Type</th>
                        <th class="py-2 pr-3">Description</th>
                        <th class="py-2 pr-3">Ref</th>
                        <th class="py-2 pr-3">Debit</th>
                        <th class="py-2 pr-3">Credit</th>
                        <th class="py-2">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)): foreach ($rows as $row): ?>
                            <tr class="border-b border-slate-100">
                                <td class="py-2 pr-3"><?= esc((string) ($row['entry_date'] ?? '')) ?></td>
                                <td class="py-2 pr-3"><?= esc(ucfirst((string) ($row['entry_type'] ?? ''))) ?></td>
                                <td class="py-2 pr-3"><?= esc((string) ($row['description'] ?? '')) ?></td>
                                <td class="py-2 pr-3">
                                    <?php if ((string) ($row['reference_type'] ?? '') === 'booking' && !empty($row['reference_id'])): ?>
                                        <a class="text-sky-700 hover:underline" href="<?= site_url('/bookings/' . (int) $row['reference_id'] . '/edit') ?>">Booking #<?= esc((string) $row['reference_id']) ?></a>
                                    <?php elseif ((string) ($row['reference_type'] ?? '') === 'payment' && !empty($row['reference_id'])): ?>
                                        <a class="text-sky-700 hover:underline" href="<?= site_url('/payments/' . (int) $row['reference_id'] . '/receipt') ?>" target="_blank">Payment #<?= esc((string) $row['reference_id']) ?></a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 pr-3"><?= esc(number_format((float) ($row['debit_amount'] ?? 0), 2)) ?></td>
                                <td class="py-2 pr-3"><?= esc(number_format((float) ($row['credit_amount'] ?? 0), 2)) ?></td>
                                <td class="py-2"><?= esc(number_format((float) ($row['running_balance'] ?? 0), 2)) ?></td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="7" class="py-3 text-slate-500">No ledger entries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </article>
    </section>
</main>
<?php $this->endSection() ?>