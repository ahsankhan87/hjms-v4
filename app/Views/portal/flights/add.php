<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="w-full space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Create Flight</h3>
            <p class="text-xs text-slate-500">Add flight details and upload ticket files.</p>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <form method="post" action="<?= site_url('/flights') ?>" enctype="multipart/form-data" class="space-y-3">
                <?= csrf_field() ?>
                <?php $airlineValue = (string) old('airline'); ?>
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
                        <input name="flight_no" value="<?= esc(old('flight_no')) ?>" required placeholder="e.g. PK-301" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">PNR</label>
                        <input name="pnr" value="<?= esc(old('pnr')) ?>" placeholder="Booking reference" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <?php $departureAirportValue = (string) old('departure_airport'); ?>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Departure Airport</label>
                        <select name="departure_airport" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select departure airport</option>
                            <?php foreach (['LHE', 'ISB', 'KHI', 'MUX', 'PEW', 'JED', 'MED', 'RUH', 'DMM', 'DXB', 'DOH', 'AUH'] as $airport): ?>
                                <option value="<?= esc($airport) ?>" <?= $departureAirportValue === $airport ? 'selected' : '' ?>><?= esc($airport) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php $arrivalAirportValue = (string) old('arrival_airport'); ?>
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
                        <input type="datetime-local" name="departure_at" value="<?= esc(old('departure_at')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Arrival Date &amp; Time</label>
                        <input type="datetime-local" name="arrival_at" value="<?= esc(old('arrival_at')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Ticket Upload (PDF/JPG/PNG, max 5MB)</label>
                    <input type="file" name="ticket_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/flights') ?>" class="btn btn-md btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-md btn-primary">
                        <i class="fa-solid fa-check"></i><span>Create Flight</span>
                    </button>
                </div>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>