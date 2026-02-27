<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-3xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold">Edit Supplier</h3>
            <form method="post" action="<?= site_url('/app/suppliers/update') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="supplier_id" value="<?= esc((string) $row['id']) ?>">
                <input name="supplier_name" value="<?= esc(old('supplier_name', (string) $row['supplier_name'])) ?>" placeholder="Supplier Name" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <?php $type = old('supplier_type', (string) $row['supplier_type']); ?>
                <select name="supplier_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="visa" <?= $type === 'visa' ? 'selected' : '' ?>>Visa</option>
                    <option value="transport" <?= $type === 'transport' ? 'selected' : '' ?>>Transport</option>
                    <option value="hotel" <?= $type === 'hotel' ? 'selected' : '' ?>>Hotel</option>
                    <option value="ticket" <?= $type === 'ticket' ? 'selected' : '' ?>>Ticket</option>
                    <option value="other" <?= $type === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
                <div class="grid grid-cols-2 gap-3">
                    <input name="contact_person" value="<?= esc(old('contact_person', (string) ($row['contact_person'] ?? ''))) ?>" placeholder="Contact Person" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input name="phone" value="<?= esc(old('phone', (string) ($row['phone'] ?? ''))) ?>" placeholder="Phone" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <input name="email" value="<?= esc(old('email', (string) ($row['email'] ?? ''))) ?>" placeholder="Email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input name="opening_balance" value="<?= esc(old('opening_balance', (string) ($row['opening_balance'] ?? '0'))) ?>" placeholder="Opening Balance" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <input name="address" value="<?= esc(old('address', (string) ($row['address'] ?? ''))) ?>" placeholder="Address" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <?php $active = (string) old('is_active', (string) ($row['is_active'] ?? '1')); ?>
                <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="1" <?= $active === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $active === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <button type="submit" class="btn btn-md btn-primary btn-block">Update Supplier</button>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>