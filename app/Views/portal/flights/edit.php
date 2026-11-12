<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <?php
    $dateTimeParts = static function (string $base, string $fallback = ''): array {
        $oldDate = old($base . '_date');
        $oldTime = old($base . '_time');

        if ($oldDate !== null || $oldTime !== null) {
            return [(string) $oldDate, (string) $oldTime];
        }

        $raw = trim($fallback);
        if ($raw === '') {
            return ['', ''];
        }

        $timestamp = strtotime($raw);

        return $timestamp === false ? ['', ''] : [date('Y-m-d', $timestamp), date('H:i', $timestamp)];
    };
    ?>

    <section class="grid gap-3 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-1">
            <h3 class="text-sm font-semibold text-slate-800">Edit Flight</h3>
            <form method="post" action="<?= site_url('/flights/update') ?>" enctype="multipart/form-data" class="mt-4 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="flight_id" value="<?= esc($row['id']) ?>">
                <section class="rounded-lg border border-slate-200 p-3">
                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-700">Departure Flight (To KSA)</h4>
                    <?php $outboundAirlineValue = (string) old('outbound_airline', (string) ($row['airline'] ?? '')); ?>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Airline</label>
                        <select name="outbound_airline" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select airline</option>
                            <?php foreach (['PIA', 'Saudia', 'Airblue', 'AirSial', 'SereneAir', 'Flynas', 'Flyadeal', 'Emirates', 'Qatar Airways', 'Etihad', 'Turkish Airlines', 'Other'] as $airline): ?>
                                <option value="<?= esc($airline) ?>" <?= $outboundAirlineValue === $airline ? 'selected' : '' ?>><?= esc($airline) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Flight No <span class="text-rose-500">*</span></label>
                            <input name="outbound_flight_no" value="<?= esc(old('outbound_flight_no', (string) ($row['flight_no'] ?? ''))) ?>" required placeholder="e.g. PK-301" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">PNR</label>
                            <input name="outbound_pnr" value="<?= esc(old('outbound_pnr', (string) ($row['pnr'] ?? ''))) ?>" placeholder="Booking reference" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <?php $outboundDepartureAirportValue = (string) old('outbound_departure_airport', (string) ($row['departure_airport'] ?? '')); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Departure Airport</label>
                            <select name="outbound_departure_airport" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Select departure airport</option>
                                <?php foreach (['LHE', 'ISB', 'KHI', 'MUX', 'PEW', 'JED', 'MED', 'RUH', 'DMM', 'DXB', 'DOH', 'AUH'] as $airport): ?>
                                    <option value="<?= esc($airport) ?>" <?= $outboundDepartureAirportValue === $airport ? 'selected' : '' ?>><?= esc($airport) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php $outboundArrivalAirportValue = (string) old('outbound_arrival_airport', (string) ($row['arrival_airport'] ?? '')); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Arrival Airport</label>
                            <select name="outbound_arrival_airport" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Select arrival airport</option>
                                <?php foreach (['JED', 'MED', 'RUH', 'DMM', 'LHE', 'ISB', 'KHI', 'MUX', 'PEW', 'DXB', 'DOH', 'AUH'] as $airport): ?>
                                    <option value="<?= esc($airport) ?>" <?= $outboundArrivalAirportValue === $airport ? 'selected' : '' ?>><?= esc($airport) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-3">
                        <?php [$editDepartureDate, $editDepartureTime] = $dateTimeParts('outbound_departure_at', (string) ($row['departure_at'] ?? '')); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Departure Date &amp; Time (24-hour)</label>
                            <div class="flex flex-wrap items-center gap-2">
                                <input type="date" name="outbound_departure_at_date" value="<?= esc($editDepartureDate) ?>" class="min-w-0 flex-1 basis-36 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <input type="text" name="outbound_departure_at_time" value="<?= esc($editDepartureTime) ?>" placeholder="HH:MM" inputmode="numeric" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="24-hour time, e.g. 14:30" class="js-time-24 w-20 shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                        <?php [$editArrivalDate, $editArrivalTime] = $dateTimeParts('outbound_arrival_at', (string) ($row['arrival_at'] ?? '')); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Arrival Date &amp; Time (24-hour)</label>
                            <div class="flex flex-wrap items-center gap-2">
                                <input type="date" name="outbound_arrival_at_date" value="<?= esc($editArrivalDate) ?>" class="min-w-0 flex-1 basis-36 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <input type="text" name="outbound_arrival_at_time" value="<?= esc($editArrivalTime) ?>" placeholder="HH:MM" inputmode="numeric" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="24-hour time, e.g. 14:30" class="js-time-24 w-20 shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Ticket Fare per Pilgrim (PKR)</label>
                            <input type="number" step="0.01" min="0" name="outbound_fare" value="<?= esc(old('outbound_fare', (string) ((float) ($row['fare'] ?? 0) > 0 ? (float) $row['fare'] : ''))) ?>" placeholder="e.g. 240000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <p class="mt-1 text-[11px] text-slate-400">Round-trip ticket fare. Loaded automatically as the ticket cost when this flight is selected in a package.</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Departure Ticket Upload (replace)</label>
                        <input type="file" name="outbound_ticket_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php if (!empty($row['ticket_file_name'])): ?>
                            <p class="mt-1 text-xs text-slate-500">Current: <a href="<?= esc(asset_upload_url((string) ($row['ticket_file_path'] ?? ''), 'flights')) ?>" target="_blank" class="text-indigo-600 hover:underline"><?= esc($row['ticket_file_name']) ?></a></p>
                            <label class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-rose-600"><input type="checkbox" name="remove_ticket_file" value="1" class="rounded border-slate-300"> Remove</label>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 p-3">
                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-700">Arrival Flight (From KSA)</h4>
                    <?php $returnAirlineValue = (string) old('return_airline', (string) ($row['return_airline'] ?? '')); ?>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Airline</label>
                        <select name="return_airline" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select airline</option>
                            <?php foreach (['PIA', 'Saudia', 'Airblue', 'AirSial', 'SereneAir', 'Flynas', 'Flyadeal', 'Emirates', 'Qatar Airways', 'Etihad', 'Turkish Airlines', 'Other'] as $airline): ?>
                                <option value="<?= esc($airline) ?>" <?= $returnAirlineValue === $airline ? 'selected' : '' ?>><?= esc($airline) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Flight No <span class="text-rose-500">*</span></label>
                            <input name="return_flight_no" value="<?= esc(old('return_flight_no', (string) ($row['return_flight_no'] ?? ''))) ?>" required placeholder="e.g. PK-302" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">PNR</label>
                            <input name="return_pnr" value="<?= esc(old('return_pnr', (string) ($row['return_pnr'] ?? ''))) ?>" placeholder="Booking reference" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <?php $returnDepartureAirportValue = (string) old('return_departure_airport', (string) ($row['return_departure_airport'] ?? '')); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Departure Airport</label>
                            <select name="return_departure_airport" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Select departure airport</option>
                                <?php foreach (['JED', 'MED', 'RUH', 'DMM', 'LHE', 'ISB', 'KHI', 'MUX', 'PEW', 'DXB', 'DOH', 'AUH'] as $airport): ?>
                                    <option value="<?= esc($airport) ?>" <?= $returnDepartureAirportValue === $airport ? 'selected' : '' ?>><?= esc($airport) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php $returnArrivalAirportValue = (string) old('return_arrival_airport', (string) ($row['return_arrival_airport'] ?? '')); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Arrival Airport</label>
                            <select name="return_arrival_airport" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Select arrival airport</option>
                                <?php foreach (['LHE', 'ISB', 'KHI', 'MUX', 'PEW', 'JED', 'MED', 'RUH', 'DMM', 'DXB', 'DOH', 'AUH'] as $airport): ?>
                                    <option value="<?= esc($airport) ?>" <?= $returnArrivalAirportValue === $airport ? 'selected' : '' ?>><?= esc($airport) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-3">
                        <?php [$editReturnDepartureDate, $editReturnDepartureTime] = $dateTimeParts('return_departure_at', (string) ($row['return_departure_at'] ?? '')); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Departure Date &amp; Time (24-hour)</label>
                            <div class="flex flex-wrap items-center gap-2">
                                <input type="date" name="return_departure_at_date" value="<?= esc($editReturnDepartureDate) ?>" class="min-w-0 flex-1 basis-36 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <input type="text" name="return_departure_at_time" value="<?= esc($editReturnDepartureTime) ?>" placeholder="HH:MM" inputmode="numeric" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="24-hour time, e.g. 14:30" class="js-time-24 w-20 shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                        <?php [$editReturnArrivalDate, $editReturnArrivalTime] = $dateTimeParts('return_arrival_at', (string) ($row['return_arrival_at'] ?? '')); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Arrival Date &amp; Time (24-hour)</label>
                            <div class="flex flex-wrap items-center gap-2">
                                <input type="date" name="return_arrival_at_date" value="<?= esc($editReturnArrivalDate) ?>" class="min-w-0 flex-1 basis-36 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <input type="text" name="return_arrival_at_time" value="<?= esc($editReturnArrivalTime) ?>" placeholder="HH:MM" inputmode="numeric" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="24-hour time, e.g. 14:30" class="js-time-24 w-20 shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Arrival Ticket Upload (replace)</label>
                        <input type="file" name="return_ticket_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php if (!empty($row['return_ticket_file_name'])): ?>
                            <p class="mt-1 text-xs text-slate-500">Current: <a href="<?= esc(asset_upload_url((string) ($row['return_ticket_file_path'] ?? ''), 'flights')) ?>" target="_blank" class="text-indigo-600 hover:underline"><?= esc($row['return_ticket_file_name']) ?></a></p>
                            <label class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-rose-600"><input type="checkbox" name="remove_return_ticket_file" value="1" class="rounded border-slate-300"> Remove</label>
                        <?php endif; ?>
                    </div>
                </section>
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
                    <label class="mb-1 block text-xs font-medium text-slate-600">Departure At (24-hour)</label>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="date" name="departure_at_date" class="min-w-0 flex-1 basis-36 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <input type="text" name="departure_at_time" placeholder="HH:MM" inputmode="numeric" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="24-hour time, e.g. 14:30" class="js-time-24 w-20 shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Arrival At (24-hour)</label>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="date" name="arrival_at_date" class="min-w-0 flex-1 basis-36 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <input type="text" name="arrival_at_time" placeholder="HH:MM" inputmode="numeric" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="24-hour time, e.g. 14:30" class="js-time-24 w-20 shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    function normalize24Hour(raw) {
        let value = String(raw || '').trim().toLowerCase().replace(/\s+/g, '');
        if (value === '') {
            return '';
        }

        let meridiem = '';
        const meridiemMatch = value.match(/(am|pm|a\.m\.|p\.m\.)$/);
        if (meridiemMatch) {
            meridiem = meridiemMatch[1].charAt(0) === 'p' ? 'pm' : 'am';
            value = value.slice(0, meridiemMatch.index);
        }

        value = value.replace(/[^0-9:]/g, '');
        if (value === '') {
            return '';
        }

        let hoursPart;
        let minutesPart;
        if (value.includes(':')) {
            const parts = value.split(':');
            hoursPart = parts[0];
            minutesPart = parts[1] || '0';
        } else if (value.length <= 2) {
            hoursPart = value;
            minutesPart = '0';
        } else {
            hoursPart = value.slice(0, value.length - 2);
            minutesPart = value.slice(-2);
        }

        let hours = parseInt(hoursPart, 10);
        let minutes = parseInt(minutesPart, 10);
        if (Number.isNaN(hours) || Number.isNaN(minutes)) {
            return '';
        }

        if (meridiem === 'pm' && hours < 12) {
            hours += 12;
        }
        if (meridiem === 'am' && hours === 12) {
            hours = 0;
        }

        if (hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
            return '';
        }

        return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
    }

    document.querySelectorAll('.js-time-24').forEach((input) => {
        const normalize = () => {
            const value = normalize24Hour(input.value);
            if (value !== '') {
                input.value = value;
            }
        };
        input.addEventListener('blur', normalize);
        input.addEventListener('change', normalize);
    });

    const form = document.querySelector('form[action*="/flights/update"]');
    if (!form) {
        return;
    }

    const field = (name) => form.querySelector(`[name="${name}"]`);

    function syncDateTimes() {
        const departureDate = field('outbound_departure_at_date');
        const departureTime = field('outbound_departure_at_time');
        const dateValue = departureDate ? departureDate.value : '';
        const timeValue = departureTime ? normalize24Hour(departureTime.value) : '';

        if (dateValue === '' && timeValue === '') {
            return;
        }

        ['outbound_arrival_at', 'return_departure_at', 'return_arrival_at'].forEach((base) => {
            const date = field(base + '_date');
            const time = field(base + '_time');

            if (date && dateValue !== '') {
                date.value = dateValue;
            }
            if (time && timeValue !== '') {
                time.value = timeValue;
            }
        });
    }

    ['outbound_departure_at_date', 'outbound_departure_at_time'].forEach((name) => {
        const input = field(name);
        if (!input) {
            return;
        }

        input.addEventListener('change', syncDateTimes);
        input.addEventListener('input', syncDateTimes);
        input.addEventListener('blur', syncDateTimes);
    });
});
</script>
<?php $this->endSection() ?>