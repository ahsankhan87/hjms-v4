<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-3xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold">Add Supplier</h3>
            <form method="post" action="<?= site_url('/app/suppliers') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input name="supplier_name" value="<?= esc(old('supplier_name')) ?>" placeholder="Supplier Name" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="supplier_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Supplier Type</option>
                    <option value="visa">Visa</option>
                    <option value="transport">Transport</option>
                    <option value="hotel">Hotel</option>
                    <option value="ticket">Ticket</option>
                    <option value="other">Other</option>
                </select>
                <div class="grid grid-cols-2 gap-3">
                    <input name="contact_person" value="<?= esc(old('contact_person')) ?>" placeholder="Contact Person" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input name="phone" value="<?= esc(old('phone')) ?>" placeholder="Phone" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <input name="email" value="<?= esc(old('email')) ?>" placeholder="Email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input name="opening_balance" value="<?= esc(old('opening_balance', '0')) ?>" placeholder="Opening Balance" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <input name="address" value="<?= esc(old('address')) ?>" placeholder="Address" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="1" <?= old('is_active', '1') === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= old('is_active') === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <button type="submit" class="btn btn-md btn-primary btn-block">Create Supplier</button>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>