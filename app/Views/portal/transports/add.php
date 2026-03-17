<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="w-full space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Create Transport Provider</h3>
            <p class="text-xs text-slate-500">Set vehicle type, provider profile, and capacity information.</p>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <form method="post" action="<?= site_url('/transports') ?>" class="space-y-3">
                <?= csrf_field() ?>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Transport Name <span class="text-rose-500">*</span></label>
                        <input name="transport_name" value="<?= esc(old('transport_name')) ?>" required placeholder="e.g. Al-Haramain Bus" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Provider Name <span class="text-rose-500">*</span></label>
                        <input name="provider_name" value="<?= esc(old('provider_name')) ?>" required placeholder="Company or operator" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Vehicle Type <span class="text-rose-500">*</span></label>
                        <select name="vehicle_type" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select Type</option>
                            <?php foreach (($vehicleTypeOptions ?? []) as $typeKey => $typeLabel): ?>
                                <option value="<?= esc($typeKey) ?>" <?= old('vehicle_type') === $typeKey ? 'selected' : '' ?>><?= esc($typeLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Seat Capacity</label>
                        <input type="number" name="seat_capacity" min="0" value="<?= esc(old('seat_capacity', '0')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Driver Name</label>
                        <input name="driver_name" value="<?= esc(old('driver_name')) ?>" placeholder="Optional" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Driver Phone</label>
                        <input name="driver_phone" value="<?= esc(old('driver_phone')) ?>" placeholder="Optional" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/transports') ?>" class="btn btn-md btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-md btn-primary">
                        <i class="fa-solid fa-check"></i><span>Create Transport</span>
                    </button>
                </div>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>