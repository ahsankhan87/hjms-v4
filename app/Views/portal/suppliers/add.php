<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700\"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700\"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700\"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="w-full space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Create Supplier</h3>
            <p class="text-xs text-slate-500">Add supplier profile and initial ledger settings.</p>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <form method="post" action="<?= site_url('/suppliers') ?>" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Supplier Name</label>
                    <input name="supplier_name" value="<?= esc(old('supplier_name')) ?>" placeholder="Supplier Name" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Supplier Type</label>
                    <select name="supplier_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Supplier Type</option>
                        <option value="visa">Visa</option>
                        <option value="transport">Transport</option>
                        <option value="hotel">Hotel</option>
                        <option value="ticket">Ticket</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Contact Person</label>
                        <input name="contact_person" value="<?= esc(old('contact_person')) ?>" placeholder="Contact Person" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Phone</label>
                        <input name="phone" value="<?= esc(old('phone')) ?>" placeholder="Phone" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                        <input name="email" value="<?= esc(old('email')) ?>" placeholder="Email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Opening Balance</label>
                        <input name="opening_balance" value="<?= esc(old('opening_balance', '0')) ?>" placeholder="Opening Balance" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Address</label>
                    <input name="address" value="<?= esc(old('address')) ?>" placeholder="Address" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                    <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="1" <?= old('is_active', '1') === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= old('is_active') === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/suppliers') ?>" class="btn btn-md btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-md btn-primary"><i class="fa-solid fa-check"></i><span>Create Supplier</span></button>
                </div>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>