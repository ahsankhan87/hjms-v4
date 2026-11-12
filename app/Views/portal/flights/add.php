<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <?php
    $dateTimeParts = static function (string $base): array {
        $oldDate = old($base . '_date');
        $oldTime = old($base . '_time');

        if ($oldDate !== null || $oldTime !== null) {
            return [(string) $oldDate, (string) $oldTime];
        }

        return ['', ''];
    };
    ?>

    <section class="w-full space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Create Flight</h3>
            <p class="text-xs text-slate-500">Add flight details and upload ticket files.</p>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <form method="post" action="<?= site_url('/admin/flights') ?>" enctype="multipart/form-data" class="space-y-4">
                <?= csrf_field() ?>
                <section class="rounded-lg border border-sky-200 bg-sky-50 p-3">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-sky-800">Quick paste supplier short code</h4>
                    <p class="mb-2 text-xs text-sky-700">Paste 1 line for one-way (outbound only) or 2 lines for round-trip (outbound + return). PKR/SAR amount in the line will auto-fill fare.</p>
                    <textarea id="supplierShortCodes" rows="3" placeholder="SV-705 24 Nov KHI-JED 0305 0530 ... PKR 125000&#10;SV-704 14 Dec JED-KHI 1935 0120 ... SAR 1800" class="w-full rounded-lg border border-sky-300 bg-white px-3 py-2 text-sm"></textarea>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <button type="button" id="applySupplierShortCodes" class="btn btn-sm btn-secondary">Apply Short Codes</button>
                        <p id="supplierShortCodeStatus" class="text-xs text-slate-500"></p>
                    </div>
                </section>
                <section class="rounded-lg border border-slate-200 p-3">
                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-700">Departure Flight (To KSA)</h4>
                    <?php $outboundAirlineValue = (string) old('outbound_airline'); ?>
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
                            <input name="outbound_flight_no" value="<?= esc(old('outbound_flight_no')) ?>" required placeholder="e.g. PK-301" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">PNR</label>
                            <input name="outbound_pnr" value="<?= esc(old('outbound_pnr')) ?>" placeholder="Booking reference" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <?php $outboundDepartureAirportValue = (string) old('outbound_departure_airport'); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Departure Airport</label>
                            <select name="outbound_departure_airport" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Select departure airport</option>
                                <?php foreach (['LHE', 'ISB', 'KHI', 'MUX', 'PEW', 'JED', 'MED', 'RUH', 'DMM', 'DXB', 'DOH', 'AUH'] as $airport): ?>
                                    <option value="<?= esc($airport) ?>" <?= $outboundDepartureAirportValue === $airport ? 'selected' : '' ?>><?= esc($airport) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php $outboundArrivalAirportValue = (string) old('outbound_arrival_airport'); ?>
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
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <?php [$departureDateValue, $departureTimeValue] = $dateTimeParts('outbound_departure_at'); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Departure Date &amp; Time (24-hour)</label>
                            <div class="flex gap-2">
                                <input type="date" name="outbound_departure_at_date" value="<?= esc($departureDateValue) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <input type="text" name="outbound_departure_at_time" value="<?= esc($departureTimeValue) ?>" placeholder="HH:MM" inputmode="numeric" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="24-hour time, e.g. 14:30" class="js-time-24 w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                        <?php [$arrivalDateValue, $arrivalTimeValue] = $dateTimeParts('outbound_arrival_at'); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Arrival Date &amp; Time (24-hour)</label>
                            <div class="flex gap-2">
                                <input type="date" name="outbound_arrival_at_date" value="<?= esc($arrivalDateValue) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <input type="text" name="outbound_arrival_at_time" value="<?= esc($arrivalTimeValue) ?>" placeholder="HH:MM" inputmode="numeric" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="24-hour time, e.g. 14:30" class="js-time-24 w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Ticket Fare per Pilgrim (PKR)</label>
                            <input type="number" step="0.01" min="0" name="outbound_fare" value="<?= esc(old('outbound_fare')) ?>" placeholder="e.g. 240000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <p class="mt-1 text-[11px] text-slate-400">Round-trip ticket fare. Loaded automatically as the ticket cost when this flight is selected in a package.</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Departure Ticket Upload (PDF/JPG/PNG, max 5MB)</label>
                        <input type="file" name="outbound_ticket_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 p-3">
                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-700">Arrival Flight (From KSA)</h4>
                    <?php $returnAirlineValue = (string) old('return_airline'); ?>
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
                            <input name="return_flight_no" value="<?= esc(old('return_flight_no')) ?>" required placeholder="e.g. PK-302" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">PNR</label>
                            <input name="return_pnr" value="<?= esc(old('return_pnr')) ?>" placeholder="Booking reference" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <?php $returnDepartureAirportValue = (string) old('return_departure_airport'); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Departure Airport</label>
                            <select name="return_departure_airport" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Select departure airport</option>
                                <?php foreach (['JED', 'MED', 'RUH', 'DMM', 'LHE', 'ISB', 'KHI', 'MUX', 'PEW', 'DXB', 'DOH', 'AUH'] as $airport): ?>
                                    <option value="<?= esc($airport) ?>" <?= $returnDepartureAirportValue === $airport ? 'selected' : '' ?>><?= esc($airport) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php $returnArrivalAirportValue = (string) old('return_arrival_airport'); ?>
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
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <?php [$returnDepartureDateValue, $returnDepartureTimeValue] = $dateTimeParts('return_departure_at'); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Departure Date &amp; Time (24-hour)</label>
                            <div class="flex gap-2">
                                <input type="date" name="return_departure_at_date" value="<?= esc($returnDepartureDateValue) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <input type="text" name="return_departure_at_time" value="<?= esc($returnDepartureTimeValue) ?>" placeholder="HH:MM" inputmode="numeric" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="24-hour time, e.g. 14:30" class="js-time-24 w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                        <?php [$returnArrivalDateValue, $returnArrivalTimeValue] = $dateTimeParts('return_arrival_at'); ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Arrival Date &amp; Time (24-hour)</label>
                            <div class="flex gap-2">
                                <input type="date" name="return_arrival_at_date" value="<?= esc($returnArrivalDateValue) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <input type="text" name="return_arrival_at_time" value="<?= esc($returnArrivalTimeValue) ?>" placeholder="HH:MM" inputmode="numeric" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="24-hour time, e.g. 14:30" class="js-time-24 w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Arrival Ticket Upload (PDF/JPG/PNG, max 5MB)</label>
                        <input type="file" name="return_ticket_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </section>
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/admin/flights') ?>" class="btn btn-md btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-md btn-primary">
                        <i class="fa-solid fa-check"></i><span>Create Flight</span>
                    </button>
                </div>
            </form>
        </article>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action*="/flights"]');
    if (!form) {
        return;
    }

    const field = (name) => form.querySelector(`[name="${name}"]`);
    const shortCodeInput = document.getElementById('supplierShortCodes');
    const shortCodeApplyButton = document.getElementById('applySupplierShortCodes');
    const shortCodeStatus = document.getElementById('supplierShortCodeStatus');
    let isApplyingShortCode = false;

    // Start every date/time field at the browser's current date & time (blank fields only,
    // so re-submitted values survive a validation error).
    (function prefillCurrentDateTime() {
        const now = new Date();
        const pad = (value) => String(value).padStart(2, '0');
        const today = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
        const currentTime = `${pad(now.getHours())}:${pad(now.getMinutes())}`;

        form.querySelectorAll('input[type="date"]').forEach((input) => {
            if (input.value === '') {
                input.value = today;
            }
        });

        form.querySelectorAll('.js-time-24').forEach((input) => {
            if (input.value.trim() === '') {
                input.value = currentTime;
            }
        });
    })();

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

    form.querySelectorAll('.js-time-24').forEach((input) => {
        const normalize = () => {
            const value = normalize24Hour(input.value);
            if (value !== '') {
                input.value = value;
            }
        };
        input.addEventListener('blur', normalize);
        input.addEventListener('change', normalize);
    });

    const airline = field('outbound_airline');
    const returnAirline = field('return_airline');
    if (airline && returnAirline) {
        airline.addEventListener('change', () => {
            if (isApplyingShortCode) {
                return;
            }
            returnAirline.value = airline.value;
        });
    }

    ['outbound_departure_airport', 'outbound_arrival_airport'].forEach((name) => {
        const input = field(name);
        if (!input) {
            return;
        }

        input.addEventListener('change', () => {
            if (isApplyingShortCode) {
                return;
            }
            const from = field('outbound_departure_airport');
            const to = field('outbound_arrival_airport');
            const returnFrom = field('return_departure_airport');
            const returnTo = field('return_arrival_airport');
            if (!from || !to || !returnFrom || !returnTo || !from.value || !to.value) {
                return;
            }

            returnFrom.value = to.value;
            returnTo.value = from.value;
        });
    });

    function syncDateTimes() {
        if (isApplyingShortCode) {
            return;
        }

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

    function setStatus(message, type) {
        if (!shortCodeStatus) {
            return;
        }
        shortCodeStatus.textContent = message;
        shortCodeStatus.classList.remove('text-slate-500', 'text-emerald-700', 'text-rose-600');
        shortCodeStatus.classList.add(type === 'ok' ? 'text-emerald-700' : type === 'error' ? 'text-rose-600' : 'text-slate-500');
    }

    function monthToIndex(token) {
        const months = {
            jan: 0, feb: 1, mar: 2, apr: 3, may: 4, jun: 5,
            jul: 6, aug: 7, sep: 8, oct: 9, nov: 10, dec: 11
        };
        const key = String(token || '').trim().toLowerCase().slice(0, 3);
        return Object.prototype.hasOwnProperty.call(months, key) ? months[key] : -1;
    }

    function normalizeTimeToken(token) {
        const raw = String(token || '').replace(/\D/g, '');
        if (raw.length < 3 || raw.length > 4) {
            return '';
        }
        const padded = raw.padStart(4, '0');
        const hours = parseInt(padded.slice(0, 2), 10);
        const minutes = parseInt(padded.slice(2), 10);
        if (Number.isNaN(hours) || Number.isNaN(minutes) || hours > 23 || minutes > 59) {
            return '';
        }
        return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
    }

    function toDateInput(dateObj) {
        const pad = (value) => String(value).padStart(2, '0');
        return `${dateObj.getFullYear()}-${pad(dateObj.getMonth() + 1)}-${pad(dateObj.getDate())}`;
    }

    function inferDepartureDate(day, monthIndex, referenceDate) {
        const year = referenceDate.getFullYear();
        const candidate = new Date(year, monthIndex, day);
        const threshold = new Date(referenceDate);
        threshold.setDate(threshold.getDate() - 2);
        if (candidate < threshold) {
            candidate.setFullYear(year + 1);
        }
        return candidate;
    }

    function parseSupplierLine(line, referenceDate) {
        const cleanLine = String(line || '').replace(/\s+/g, ' ').trim();
        if (cleanLine === '') {
            return null;
        }

        const parts = cleanLine.split(' ');
        if (parts.length < 6) {
            return null;
        }

        const flightNo = parts[0];
        const day = parseInt(parts[1], 10);
        const monthIndex = monthToIndex(parts[2]);
        const route = parts[3] || '';
        const depTime = normalizeTimeToken(parts[4]);
        const arrTime = normalizeTimeToken(parts[5]);

        const routeMatch = route.match(/^([A-Za-z]{3})-([A-Za-z]{3})$/);
        if (!flightNo || Number.isNaN(day) || day < 1 || day > 31 || monthIndex < 0 || !routeMatch || depTime === '' || arrTime === '') {
            return null;
        }

        const departureDate = inferDepartureDate(day, monthIndex, referenceDate);
        const arrivalDate = new Date(departureDate.getTime());
        if (arrTime <= depTime) {
            arrivalDate.setDate(arrivalDate.getDate() + 1);
        }

        return {
            airlineCode: (flightNo.split('-')[0] || '').toUpperCase(),
            flightNo: flightNo.toUpperCase(),
            departureAirport: routeMatch[1].toUpperCase(),
            arrivalAirport: routeMatch[2].toUpperCase(),
            departureDate: toDateInput(departureDate),
            arrivalDate: toDateInput(arrivalDate),
            departureTime: depTime,
            arrivalTime: arrTime,
        };
    }

    function mapAirlineCodeToName(code) {
        const map = {
            SV: 'Saudia',
            PK: 'PIA',
            PA: 'Airblue',
            PF: 'AirSial',
            ER: 'SereneAir',
            XY: 'Flynas',
            F3: 'Flyadeal',
            EK: 'Emirates',
            QR: 'Qatar Airways',
            EY: 'Etihad',
            TK: 'Turkish Airlines',
        };
        return map[code] || 'Other';
    }

    function setFieldValue(name, value) {
        const input = field(name);
        if (!input) {
            return;
        }
        input.value = value;
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function applyParsedFlight(parsed, prefix) {
        const airlineName = mapAirlineCodeToName(parsed.airlineCode);
        const airlineFieldName = prefix + 'airline';
        const airlineInput = field(airlineFieldName);
        if (airlineInput && airlineInput.tagName === 'SELECT') {
            const hasAirline = Array.from(airlineInput.options).some((opt) => opt.value === airlineName);
            setFieldValue(airlineFieldName, hasAirline ? airlineName : 'Other');
        } else {
            setFieldValue(airlineFieldName, airlineName);
        }

        setFieldValue(prefix + 'flight_no', parsed.flightNo);
        setFieldValue(prefix + 'departure_airport', parsed.departureAirport);
        setFieldValue(prefix + 'arrival_airport', parsed.arrivalAirport);
        setFieldValue(prefix + 'departure_at_date', parsed.departureDate);
        setFieldValue(prefix + 'departure_at_time', parsed.departureTime);
        setFieldValue(prefix + 'arrival_at_date', parsed.arrivalDate);
        setFieldValue(prefix + 'arrival_at_time', parsed.arrivalTime);
    }

    function extractFareAmountFromText(text) {
        const value = String(text || '');
        const currencyBefore = value.match(/\b(?:pkr|sar)\b\s*([0-9][0-9,]*(?:\.[0-9]+)?)/i);
        if (currencyBefore && currencyBefore[1]) {
            return parseFloat(currencyBefore[1].replace(/,/g, ''));
        }

        const currencyAfter = value.match(/([0-9][0-9,]*(?:\.[0-9]+)?)\s*\b(?:pkr|sar)\b/i);
        if (currencyAfter && currencyAfter[1]) {
            return parseFloat(currencyAfter[1].replace(/,/g, ''));
        }

        return NaN;
    }

    function applyFareFromLines(lines) {
        const fareInput = field('outbound_fare');
        if (!fareInput) {
            return false;
        }

        for (let index = 0; index < lines.length; index += 1) {
            const amount = extractFareAmountFromText(lines[index]);
            if (!Number.isNaN(amount) && amount >= 0) {
                fareInput.value = String(amount);
                fareInput.dispatchEvent(new Event('change', { bubbles: true }));
                fareInput.dispatchEvent(new Event('input', { bubbles: true }));
                return true;
            }
        }

        return false;
    }

    function applySupplierShortCodes() {
        if (!shortCodeInput) {
            return;
        }

        const lines = String(shortCodeInput.value || '')
            .split(/\r?\n/)
            .map((line) => line.trim())
            .filter((line) => line !== '');

        if (lines.length < 1) {
            setStatus('Please paste at least one short-code line.', 'error');
            return;
        }

        const now = new Date();
        const outbound = parseSupplierLine(lines[0], now);
        if (!outbound) {
            setStatus('Could not parse outbound line. Check format like: SV-705 24 Nov KHI-JED 0305 0530', 'error');
            return;
        }

        isApplyingShortCode = true;
        try {
            applyParsedFlight(outbound, 'outbound_');
            const fareApplied = applyFareFromLines(lines);

            if (lines.length >= 2) {
                const outboundReference = new Date(outbound.departureDate + 'T00:00:00');
                const inbound = parseSupplierLine(lines[1], outboundReference);
                if (!inbound) {
                    setStatus('Outbound filled, but return line could not be parsed. Format example: SV-704 14 Dec JED-KHI 1935 0120', 'error');
                    return;
                }

                applyParsedFlight(inbound, 'return_');
                setStatus(fareApplied ? 'Outbound + return + fare auto-filled from short codes.' : 'Outbound + return auto-filled from short codes.', 'ok');
                return;
            }

            setStatus(fareApplied ? 'Outbound + fare auto-filled from short code.' : 'Outbound flight auto-filled from short code.', 'ok');
        } finally {
            isApplyingShortCode = false;
        }
    }

    if (shortCodeApplyButton) {
        shortCodeApplyButton.addEventListener('click', applySupplierShortCodes);
    }
    if (shortCodeInput) {
        shortCodeInput.addEventListener('paste', () => {
            setTimeout(() => {
                if ((shortCodeInput.value || '').trim() !== '') {
                    applySupplierShortCodes();
                }
            }, 0);
        });
    }
});
</script>
<?php $this->endSection() ?>