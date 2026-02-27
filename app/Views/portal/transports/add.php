<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-3xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold">Add Transport Provider</h3>
            <form method="post" action="<?= site_url('/app/transports') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="text-sm font-medium">Transport Name</label>
                    <input name="transport_name" value="<?= esc(old('transport_name')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Provider Name</label>
                    <input name="provider_name" value="<?= esc(old('provider_name')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Type</label>
                    <select name="vehicle_type" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select Type</option>
                        <?php foreach (($vehicleTypeOptions ?? []) as $typeKey => $typeLabel): ?>
                            <option value="<?= esc($typeKey) ?>" <?= old('vehicle_type') === $typeKey ? 'selected' : '' ?>><?= esc($typeLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Driver Name</label>
                    <input name="driver_name" value="<?= esc(old('driver_name')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Driver Phone</label>
                    <input name="driver_phone" value="<?= esc(old('driver_phone')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Seat Capacity</label>
                    <input type="number" name="seat_capacity" min="0" value="<?= esc(old('seat_capacity', '0')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="btn btn-md btn-primary btn-block">Create Transport</button>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>