<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <h3 class="text-sm font-semibold text-slate-800">Create Expense</h3>
        <p class="text-xs text-slate-500">Post an operational expense against a category.</p>
    </article>

    <article class="rounded-xl border border-slate-200 bg-white p-4">
        <?php if (empty($categories)): ?><div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">Create an expense category first before posting expenses.</div><?php endif; ?>
        <form method="post" action="<?= site_url('/expenses') ?>" class="space-y-3">
            <?= csrf_field() ?>
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Expense Date</label>
                    <input type="date" name="expense_date" value="<?= esc(old('expense_date', date('Y-m-d'))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Category</label>
                    <select name="expense_category_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select Category</option>
                        <?php foreach (($categories ?? []) as $category): ?>
                            <option value="<?= esc((string) $category['id']) ?>" <?= old('expense_category_id') === (string) $category['id'] ? 'selected' : '' ?>><?= esc((string) $category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Amount</label>
                    <input name="amount" value="<?= esc(old('amount')) ?>" placeholder="0.00" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Paid To</label>
                    <input name="paid_to" value="<?= esc(old('paid_to')) ?>" placeholder="Vendor / Payee" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Payment Method</label>
                    <select name="payment_method" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'card' => 'Card', 'online' => 'Online', 'other' => 'Other'] as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= old('payment_method', 'cash') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Reference No</label>
                    <input name="reference_no" value="<?= esc(old('reference_no')) ?>" placeholder="Invoice / Voucher / Receipt No" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="posted" <?= old('status', 'posted') === 'posted' ? 'selected' : '' ?>>Posted</option>
                        <option value="pending" <?= old('status') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="voided" <?= old('status') === 'voided' ? 'selected' : '' ?>>Voided</option>
                    </select>
                </div>
                <div></div>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Note</label>
                <textarea name="note" rows="3" placeholder="Optional internal note" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('note')) ?></textarea>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <a href="<?= site_url('/expenses') ?>" class="btn btn-md btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-md btn-primary" <?= empty($categories) ? 'disabled' : '' ?>><i class="fa-solid fa-check"></i><span>Create Expense</span></button>
            </div>
        </form>
    </article>
</main>
<?php $this->endSection() ?>