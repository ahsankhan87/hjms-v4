<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-3 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-1">
            <h3 class="text-sm font-semibold text-slate-800">Edit Transport</h3>
            <form method="post" action="<?= site_url('/transports/update') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="transport_id" value="<?= esc($row['id']) ?>">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Transport Name <span class="text-rose-500">*</span></label>
                        <input name="transport_name" value="<?= esc(old('transport_name', (string) ($row['transport_name'] ?? ''))) ?>" required placeholder="e.g. Al-Haramain Bus" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Provider Name <span class="text-rose-500">*</span></label>
                        <input name="provider_name" value="<?= esc(old('provider_name', $row['provider_name'])) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Vehicle Type <span class="text-rose-500">*</span></label>
                        <?php $selectedVehicleType = strtolower((string) old('vehicle_type', (string) ($row['vehicle_type'] ?? ''))); ?>
                        <select name="vehicle_type" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select Type</option>
                            <?php foreach (($vehicleTypeOptions ?? []) as $typeKey => $typeLabel): ?>
                                <option value="<?= esc($typeKey) ?>" <?= $selectedVehicleType === $typeKey ? 'selected' : '' ?>><?= esc($typeLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Seat Capacity</label>
                        <input type="number" name="seat_capacity" min="0" value="<?= esc(old('seat_capacity', (string) ($row['seat_capacity'] ?? '0'))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Driver Name</label>
                        <input name="driver_name" value="<?= esc(old('driver_name', (string) ($row['driver_name'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Driver Phone</label>
                        <input name="driver_phone" value="<?= esc(old('driver_phone', (string) ($row['driver_phone'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/transports') ?>" class="btn btn-md btn-secondary">Back</a>
                    <button type="submit" class="btn btn-md btn-primary">
                        <i class="fa-solid fa-check"></i><span>Update Transport</span>
                    </button>
                </div>
            </form>

            <hr class="my-5 border-slate-200">

            <h3 class="text-sm font-semibold text-slate-800">Delete Transport</h3>
            <form method="post" action="<?= site_url('/transports/delete') ?>" class="mt-4">
                <?= csrf_field() ?>
                <input type="hidden" name="transport_id" value="<?= esc($row['id']) ?>">
                <button type="submit" class="btn btn-md btn-danger btn-block" onclick="return confirm('Delete this transport provider?')">
                    <i class="fa-solid fa-trash"></i><span>Delete Transport</span>
                </button>
            </form>
        </article>

        <div class="space-y-6 lg:col-span-2">
            <article class="rounded-xl border border-slate-200 bg-white p-4 overflow-auto">
                <h3 class="mb-3 text-sm font-semibold text-slate-800">Transport Details</h3>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-2">
                    <div>
                        <dt class="text-xs text-slate-500">ID</dt>
                        <dd class="text-sm font-medium text-slate-800">#<?= esc($row['id']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Name</dt>
                        <dd class="text-sm font-medium text-slate-800"><?= esc((string) ($row['transport_name'] ?? '-')) ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Provider</dt>
                        <dd class="text-sm font-medium text-slate-800"><?= esc($row['provider_name']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Type</dt>
                        <dd class="text-sm font-medium text-slate-800"><?= esc(ucfirst((string) ($row['vehicle_type'] ?? '-'))) ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Driver</dt>
                        <dd class="text-sm font-medium text-slate-800"><?= esc($row['driver_name'] ?: '-') ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Driver Phone</dt>
                        <dd class="text-sm font-medium text-slate-800"><?= esc($row['driver_phone'] ?: '-') ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Seat Capacity</dt>
                        <dd class="text-sm font-medium text-slate-800"><?= esc((int) ($row['seat_capacity'] ?? 0)) ?></dd>
                    </div>
                </dl>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-4 overflow-auto">
                <h3 class="mb-2 text-sm font-semibold text-slate-800">Transport Itinerary Legs</h3>
                <p class="text-xs text-slate-500 mb-3">Create route legs here. Packages only link this transport.</p>
                <p class="text-sm font-medium text-slate-700 mb-4">Compact Route: <?= esc((string) (($compactRoute ?? '') !== '' ? $compactRoute : '-')) ?></p>

                <form method="post" action="<?= site_url('/transports/legs/create') ?>" class="grid gap-3 md:grid-cols-5">
                    <?= csrf_field() ?>
                    <input type="hidden" name="transport_id" value="<?= esc((string) $row['id']) ?>">
                    <input name="from_code" placeholder="From (e.g. JED)" maxlength="20" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input name="to_code" placeholder="To (e.g. MAK)" maxlength="20" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input name="ziarat_site" placeholder="Ziarat site (optional)" maxlength="180" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_ziarat" value="1" class="rounded border-slate-300">
                        Ziarat
                    </label>
                    <button type="submit" class="btn btn-md btn-primary">Add Leg</button>
                    <input name="notes" placeholder="Notes (optional)" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm md:col-span-5">
                </form>

                <div class="mt-4 overflow-auto">
                    <table class="list-table">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-3 py-2 text-left">Seq</th>
                                <th class="px-3 py-2 text-left">From</th>
                                <th class="px-3 py-2 text-left">To</th>
                                <th class="px-3 py-2 text-left">Ziarat</th>
                                <th class="px-3 py-2 text-left">Site</th>
                                <th class="px-3 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($legRows)): foreach ($legRows as $leg): ?>
                                    <tr class="border-t border-slate-100">
                                        <td class="px-3 py-2"><?= esc((string) ($leg['seq_no'] ?? '')) ?></td>
                                        <td class="px-3 py-2"><?= esc((string) ($leg['from_code'] ?? '')) ?></td>
                                        <td class="px-3 py-2"><?= esc((string) ($leg['to_code'] ?? '')) ?></td>
                                        <td class="px-3 py-2"><?= (int) ($leg['is_ziarat'] ?? 0) === 1 ? 'Yes' : 'No' ?></td>
                                        <td class="px-3 py-2"><?= esc((string) ($leg['ziarat_site'] ?? '')) ?></td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-1">
                                                <form method="post" action="<?= site_url('/transports/legs/move') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="transport_id" value="<?= esc((string) $row['id']) ?>">
                                                    <input type="hidden" name="transport_leg_id" value="<?= esc((string) $leg['id']) ?>">
                                                    <input type="hidden" name="direction" value="up">
                                                    <button type="submit" class="btn btn-sm btn-secondary">↑</button>
                                                </form>
                                                <form method="post" action="<?= site_url('/transports/legs/move') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="transport_id" value="<?= esc((string) $row['id']) ?>">
                                                    <input type="hidden" name="transport_leg_id" value="<?= esc((string) $leg['id']) ?>">
                                                    <input type="hidden" name="direction" value="down">
                                                    <button type="submit" class="btn btn-sm btn-secondary">↓</button>
                                                </form>
                                                <form method="post" action="<?= site_url('/transports/legs/delete') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="transport_id" value="<?= esc((string) $row['id']) ?>">
                                                    <input type="hidden" name="transport_leg_id" value="<?= esc((string) $leg['id']) ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="6" class="px-3 py-5 text-center text-slate-500">No itinerary legs added.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </section>
</main>
<?php $this->endSection() ?>