<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <h3 class="text-sm font-semibold text-slate-800">Edit Expense</h3>
        <p class="text-xs text-slate-500">Update the expense details for the active season.</p>
    </article>

    <article class="rounded-xl border border-slate-200 bg-white p-4">
        <form method="post" action="<?= site_url('/expenses/update') ?>" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="expense_id" value="<?= esc((string) $row['id']) ?>">
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Expense Date</label>
                    <input type="date" name="expense_date" value="<?= esc(old('expense_date', (string) $row['expense_date'])) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Category</label>
                    <select name="expense_category_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select Category</option>
                        <?php foreach (($categories ?? []) as $category): ?>
                            <option value="<?= esc((string) $category['id']) ?>" <?= (string) old('expense_category_id', (string) $row['expense_category_id']) === (string) $category['id'] ? 'selected' : '' ?>><?= esc((string) $category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Amount</label>
                    <input name="amount" value="<?= esc(old('amount', (string) $row['amount'])) ?>" placeholder="0.00" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Paid To</label>
                    <input name="paid_to" value="<?= esc(old('paid_to', (string) ($row['paid_to'] ?? ''))) ?>" placeholder="Vendor / Payee" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Payment Method</label>
                    <?php $paymentMethod = (string) old('payment_method', (string) ($row['payment_method'] ?? 'cash')); ?>
                    <select name="payment_method" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'card' => 'Card', 'online' => 'Online', 'other' => 'Other'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= $paymentMethod === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Reference No</label>
                    <input name="reference_no" value="<?= esc(old('reference_no', (string) ($row['reference_no'] ?? ''))) ?>" placeholder="Invoice / Voucher / Receipt No" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                    <?php $status = (string) old('status', (string) ($row['status'] ?? 'posted')); ?>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="posted" <?= $status === 'posted' ? 'selected' : '' ?>>Posted</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="voided" <?= $status === 'voided' ? 'selected' : '' ?>>Voided</option>
                    </select>
                </div>
                <div></div>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Note</label>
                <textarea name="note" rows="3" placeholder="Optional internal note" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('note', (string) ($row['note'] ?? ''))) ?></textarea>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <a href="<?= site_url('/expenses') ?>" class="btn btn-md btn-secondary">Back</a>
                <button type="submit" class="btn btn-md btn-primary"><i class="fa-solid fa-check"></i><span>Update Expense</span></button>
            </div>
        </form>
    </article>
</main>
<?php $this->endSection() ?>