<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-3">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 lg:col-span-1">
            <h3 class="text-lg font-semibold">Edit Transport</h3>
            <form method="post" action="<?= site_url('/app/transports/update') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="transport_id" value="<?= esc($row['id']) ?>">
                <div>
                    <label class="text-sm font-medium">Transport Name</label>
                    <input name="transport_name" value="<?= esc(old('transport_name', (string) ($row['transport_name'] ?? ''))) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Provider Name</label>
                    <input name="provider_name" value="<?= esc(old('provider_name', $row['provider_name'])) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Type</label>
                    <?php $selectedVehicleType = strtolower((string) old('vehicle_type', (string) ($row['vehicle_type'] ?? ''))); ?>
                    <select name="vehicle_type" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select Type</option>
                        <?php foreach (($vehicleTypeOptions ?? []) as $typeKey => $typeLabel): ?>
                            <option value="<?= esc($typeKey) ?>" <?= $selectedVehicleType === $typeKey ? 'selected' : '' ?>><?= esc($typeLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Driver Name</label>
                    <input name="driver_name" value="<?= esc(old('driver_name', (string) ($row['driver_name'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Driver Phone</label>
                    <input name="driver_phone" value="<?= esc(old('driver_phone', (string) ($row['driver_phone'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Seat Capacity</label>
                    <input type="number" name="seat_capacity" min="0" value="<?= esc(old('seat_capacity', (string) ($row['seat_capacity'] ?? '0'))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="btn btn-md btn-primary btn-block">Update Transport</button>
            </form>

            <hr class="my-5 border-slate-200">

            <h3 class="text-lg font-semibold">Delete Transport</h3>
            <form method="post" action="<?= site_url('/app/transports/delete') ?>" class="mt-4">
                <?= csrf_field() ?>
                <input type="hidden" name="transport_id" value="<?= esc($row['id']) ?>">
                <button type="submit" class="btn btn-md btn-danger btn-block" onclick="return confirm('Delete this transport provider?')">Delete Transport</button>
            </form>
        </article>

        <div class="space-y-6 lg:col-span-2">
            <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 overflow-auto">
                <h3 class="text-lg font-semibold mb-4">Transport Details</h3>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><strong>ID:</strong> #<?= esc($row['id']) ?></div>
                    <div><strong>Name:</strong> <?= esc((string) ($row['transport_name'] ?? '-')) ?></div>
                    <div><strong>Provider:</strong> <?= esc($row['provider_name']) ?></div>
                    <div><strong>Type:</strong> <?= esc(ucfirst((string) ($row['vehicle_type'] ?? '-'))) ?></div>
                    <div><strong>Driver:</strong> <?= esc($row['driver_name'] ?: '-') ?></div>
                    <div><strong>Driver Phone:</strong> <?= esc($row['driver_phone'] ?: '-') ?></div>
                    <div><strong>Seat Capacity:</strong> <?= esc((int) ($row['seat_capacity'] ?? 0)) ?></div>
                </dl>
            </article>

            <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 overflow-auto">
                <h3 class="text-lg font-semibold mb-2">Transport Itinerary Legs</h3>
                <p class="text-xs text-slate-500 mb-3">Create route legs here. Packages only link this transport.</p>
                <p class="text-sm font-medium text-slate-700 mb-4">Compact Route: <?= esc((string) (($compactRoute ?? '') !== '' ? $compactRoute : '-')) ?></p>

                <form method="post" action="<?= site_url('/app/transports/legs/create') ?>" class="grid gap-3 md:grid-cols-5">
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
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left">
                                <th class="py-2 pr-3">Seq</th>
                                <th class="py-2 pr-3">From</th>
                                <th class="py-2 pr-3">To</th>
                                <th class="py-2 pr-3">Ziarat</th>
                                <th class="py-2 pr-3">Site</th>
                                <th class="py-2 pr-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($legRows)): foreach ($legRows as $leg): ?>
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 pr-3"><?= esc((string) ($leg['seq_no'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($leg['from_code'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($leg['to_code'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= (int) ($leg['is_ziarat'] ?? 0) === 1 ? 'Yes' : 'No' ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($leg['ziarat_site'] ?? '')) ?></td>
                                        <td class="py-2 pr-3">
                                            <div class="flex items-center gap-1">
                                                <form method="post" action="<?= site_url('/app/transports/legs/move') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="transport_id" value="<?= esc((string) $row['id']) ?>">
                                                    <input type="hidden" name="transport_leg_id" value="<?= esc((string) $leg['id']) ?>">
                                                    <input type="hidden" name="direction" value="up">
                                                    <button type="submit" class="btn btn-xs btn-secondary">↑</button>
                                                </form>
                                                <form method="post" action="<?= site_url('/app/transports/legs/move') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="transport_id" value="<?= esc((string) $row['id']) ?>">
                                                    <input type="hidden" name="transport_leg_id" value="<?= esc((string) $leg['id']) ?>">
                                                    <input type="hidden" name="direction" value="down">
                                                    <button type="submit" class="btn btn-xs btn-secondary">↓</button>
                                                </form>
                                                <form method="post" action="<?= site_url('/app/transports/legs/delete') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="transport_id" value="<?= esc((string) $row['id']) ?>">
                                                    <input type="hidden" name="transport_leg_id" value="<?= esc((string) $leg['id']) ?>">
                                                    <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="6" class="py-3 text-slate-500">No itinerary legs added.</td>
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