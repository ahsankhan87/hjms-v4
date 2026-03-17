<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-3 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-1">
            <h3 class="text-sm font-semibold text-slate-800">Edit Flight</h3>
            <form method="post" action="<?= site_url('/flights/update') ?>" enctype="multipart/form-data" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="flight_id" value="<?= esc($row['id']) ?>">
                <?php $airlineValue = (string) old('airline', (string) ($row['airline'] ?? '')); ?>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Airline</label>
                    <select name="airline" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select airline</option>
                        <?php foreach (['PIA', 'Saudia', 'Airblue', 'AirSial', 'SereneAir', 'Flynas', 'Flyadeal', 'Emirates', 'Qatar Airways', 'Etihad', 'Turkish Airlines', 'Other'] as $airline): ?>
                            <option value="<?= esc($airline) ?>" <?= $airlineValue === $airline ? 'selected' : '' ?>><?= esc($airline) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Flight No <span class="text-rose-500">*</span></label>
                        <input name="flight_no" value="<?= esc(old('flight_no', $row['flight_no'])) ?>" required placeholder="e.g. PK-301" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">PNR</label>
                        <input name="pnr" value="<?= esc(old('pnr', (string) ($row['pnr'] ?? ''))) ?>" placeholder="Booking reference" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <?php $departureAirportValue = (string) old('departure_airport', (string) ($row['departure_airport'] ?? '')); ?>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Departure Airport</label>
                        <select name="departure_airport" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select departure airport</option>
                            <?php foreach (['LHE', 'ISB', 'KHI', 'MUX', 'PEW', 'JED', 'MED', 'RUH', 'DMM', 'DXB', 'DOH', 'AUH'] as $airport): ?>
                                <option value="<?= esc($airport) ?>" <?= $departureAirportValue === $airport ? 'selected' : '' ?>><?= esc($airport) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php $arrivalAirportValue = (string) old('arrival_airport', (string) ($row['arrival_airport'] ?? '')); ?>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Arrival Airport</label>
                        <select name="arrival_airport" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select arrival airport</option>
                            <?php foreach (['JED', 'MED', 'RUH', 'DMM', 'LHE', 'ISB', 'KHI', 'MUX', 'PEW', 'DXB', 'DOH', 'AUH'] as $airport): ?>
                                <option value="<?= esc($airport) ?>" <?= $arrivalAirportValue === $airport ? 'selected' : '' ?>><?= esc($airport) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Departure Date &amp; Time</label>
                        <input type="datetime-local" name="departure_at" value="<?= esc(old('departure_at', (string) str_replace(' ', 'T', (string) ($row['departure_at'] ?? '')))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Arrival Date &amp; Time</label>
                        <input type="datetime-local" name="arrival_at" value="<?= esc(old('arrival_at', (string) str_replace(' ', 'T', (string) ($row['arrival_at'] ?? '')))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Ticket Upload (replace)</label>
                    <input type="file" name="ticket_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <?php if (!empty($row['ticket_file_name'])): ?>
                        <p class="mt-1 text-xs text-slate-500">Current: <?= esc($row['ticket_file_name']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/flights') ?>" class="btn btn-md btn-secondary">Back</a>
                    <button type="submit" class="btn btn-md btn-primary">
                        <i class="fa-solid fa-check"></i><span>Update Flight</span>
                    </button>
                </div>
            </form>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-2 overflow-auto">
            <h3 class="text-sm font-semibold text-slate-800">Package Assignment (Departure Batch)</h3>
            <form method="post" action="<?= site_url('/flights/packages') ?>" class="mt-4 grid gap-3 md:grid-cols-4">
                <?= csrf_field() ?>
                <input type="hidden" name="flight_id" value="<?= esc($row['id']) ?>">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Package</label>
                    <select name="package_id" required class="js-select2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select package</option>
                        <?php foreach ($packages as $pkg): ?>
                            <option value="<?= esc($pkg['id']) ?>"><?= esc(($pkg['code'] ?? '') . ' - ' . ($pkg['name'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Departure At</label>
                    <input type="datetime-local" name="departure_at" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Arrival At</label>
                    <input type="datetime-local" name="arrival_at" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn btn-md btn-primary w-full">Assign Package</button>
                </div>
            </form>

            <div class="mt-5 overflow-auto">
                <table class="list-table">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Package</th>
                            <th class="px-3 py-2 text-left">Flight</th>
                            <th class="px-3 py-2 text-left">Departure</th>
                            <th class="px-3 py-2 text-left">Arrival</th>
                            <th class="px-3 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($packageLinks)): ?>
                            <tr>
                                <td colspan="5" class="px-3 py-5 text-center text-slate-500">No package linked.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($packageLinks as $link): ?>
                                <tr class="border-t border-slate-100">
                                    <td class="px-3 py-2"><?= esc(($link['package_code'] ?? '-') . ' - ' . ($link['package_name'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc(($link['airline'] ?? '-') . ' ' . ($link['flight_no'] ?? '-')) ?></td>
                                    <td class="px-3 py-2"><?= esc($link['departure_at'] ?? '-') ?></td>
                                    <td class="px-3 py-2"><?= esc($link['arrival_at'] ?? '-') ?></td>
                                    <td class="px-3 py-2">
                                        <form method="post" action="<?= site_url('/flights/packages/delete') ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="flight_id" value="<?= esc($row['id']) ?>">
                                            <input type="hidden" name="link_id" value="<?= esc($link['id']) ?>">
                                            <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this package assignment?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</main>
<?php $this->endSection() ?>