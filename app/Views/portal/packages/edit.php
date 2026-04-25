<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <h1 class="text-base font-semibold text-slate-800">Manage Package</h1>
        <p class="mt-1 text-xs text-slate-500">Update package details and attach pricing, hotels, flights, and transport records from one place.</p>
    </section>

    <section class="grid gap-4 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-1">
            <h3 class="text-sm font-semibold text-slate-800 inline-flex items-center gap-2"><i class="fa-solid fa-pen-to-square text-emerald-600"></i><span>Update Package</span></h3>
            <form method="post" action="<?php echo site_url('packages/update') ?>" class="mt-3 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                <label class="block text-xs font-medium text-slate-600">Code
                    <input name="code" value="<?= esc(old('code', $row['code'])) ?>" placeholder="Code" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>
                <label class="block text-xs font-medium text-slate-600">Name
                    <input name="name" value="<?= esc(old('name', $row['name'])) ?>" placeholder="Name" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-xs font-medium text-slate-600">Package Type
                        <select name="package_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Package Type</option>
                            <option value="hajj" <?= (old('package_type', $row['package_type']) === 'hajj') ? 'selected' : '' ?>>Hajj</option>
                            <option value="umrah" <?= (old('package_type', $row['package_type']) === 'umrah') ? 'selected' : '' ?>>Umrah</option>
                        </select>
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Duration (Days)
                        <input type="number" name="duration_days" min="1" value="<?= esc(old('duration_days', $row['duration_days'])) ?>" placeholder="Duration Days" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <?php
                    $depVal = old('departure_date', $row['departure_date'] ?? '');
                    $depVal = $depVal !== '' ? date('Y-m-d\TH:i', strtotime((string)$depVal)) : '';
                    $arrVal = old('arrival_date', $row['arrival_date'] ?? '');
                    $arrVal = $arrVal !== '' ? date('Y-m-d\TH:i', strtotime((string)$arrVal)) : '';
                    ?>
                    <label class="block text-xs font-medium text-slate-600">Departure Date &amp; Time
                        <input type="datetime-local" name="departure_date" value="<?= esc($depVal) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Arrival Date &amp; Time <span class="font-normal text-slate-400">(auto)</span>
                        <input type="datetime-local" name="arrival_date" value="<?= esc($arrVal) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                </div>
                <label class="block text-xs font-medium text-slate-600">Notes
                    <textarea name="notes" rows="2" placeholder="Notes (optional)" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('notes', $row['notes'])) ?></textarea>
                </label>
                <label class="block text-xs font-medium text-slate-600">Status
                    <select name="is_active" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Status (optional)</option>
                        <option value="1" <?= (string) old('is_active', (string) $row['is_active']) === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (string) old('is_active', (string) $row['is_active']) === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </label>
                <div class="border-t border-slate-100 pt-3">
                    <p class="text-xs font-medium text-slate-600 mb-1">Package Includes</p>
                    <p class="text-xs text-slate-400 mb-2">Uncheck a component if pilgrims arrange it themselves. The voucher will show &ldquo;Self-arranged&rdquo; for excluded components.</p>
                    <?php
                    $incHotel     = (int) old('include_hotel',     (string) ($row['include_hotel']     ?? 1));
                    $incTicket    = (int) old('include_ticket',    (string) ($row['include_ticket']    ?? 1));
                    $incTransport = (int) old('include_transport', (string) ($row['include_transport'] ?? 1));
                    ?>
                    <div class="space-y-1.5">
                        <input type="hidden" name="include_hotel" value="0">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" id="toggle_hotel" name="include_hotel" value="1" class="rounded border-slate-300" <?= $incHotel ? 'checked' : '' ?>>
                            <span>Hotel Accommodation</span>
                        </label>
                        <input type="hidden" name="include_ticket" value="0">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" id="toggle_ticket" name="include_ticket" value="1" class="rounded border-slate-300" <?= $incTicket ? 'checked' : '' ?>>
                            <span>Flight / Ticket</span>
                        </label>
                        <input type="hidden" name="include_transport" value="0">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" id="toggle_transport" name="include_transport" value="1" class="rounded border-slate-300" <?= $incTransport ? 'checked' : '' ?>>
                            <span>Transport</span>
                        </label>
                    </div>
                </div>
                <p class="text-xs text-slate-500">Flights, Hotels, Transports, and package price slabs are managed from the linked sections on the right.</p>
                <button type="submit" class="btn btn-md btn-primary btn-block inline-flex items-center justify-center gap-2"><i class="fa-solid fa-floppy-disk"></i><span>Update Package</span></button>
            </form>

            <hr class="my-4 border-slate-200">

            <h3 class="text-sm font-semibold text-slate-800 inline-flex items-center gap-2"><i class="fa-solid fa-trash-can text-rose-600"></i><span>Delete Package</span></h3>
            <form method="post" action="/packages/delete" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100" title="Delete Package" aria-label="Delete Package"><i class="fa-solid fa-trash-can"></i></button>
            </form>
        </article>

        <div class="space-y-4 lg:col-span-2">
            <article id="hotel-section" class="rounded-xl border border-slate-200 bg-white p-4 overflow-auto">
                <h3 class="mb-4 inline-flex items-center gap-2 text-lg font-semibold"><i class="fa-solid fa-hotel text-emerald-600"></i><span>Package Hotels</span></h3>
                <p class="text-xs text-slate-500 mb-3">Attach hotel pricing once, then reuse that pricing for as many stay segments as the itinerary needs.</p>
                <p class="text-xs text-slate-500 mb-3">Sequence rule: next hotel check-in starts from previous hotel check-out, and `8+8+5` is treated as Makkah, Madina, then Makkah again.</p>
                <form method="post" action="<?= site_url('packages/hotels/create') ?>" class="grid gap-3 md:grid-cols-6" data-package-end="<?= esc((string) ($packageStayEnd ?? '')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                    <label class="block text-xs font-medium text-slate-600 md:col-span-2">Hotel
                        <select name="hotel_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Select Hotel</option>
                            <?php foreach (($hotelOptions ?? []) as $hotelOption): ?>
                                <?php $label = (string) ($hotelOption['name'] ?? '-') . (!empty($hotelOption['city']) ? ' - ' . (string) $hotelOption['city'] : ''); ?>
                                <option value="<?= esc((string) $hotelOption['id']) ?>" data-city="<?= esc((string) ($hotelOption['city'] ?? '')) ?>"><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block text-xs font-medium text-slate-600 md:col-span-2">Stay Distribution / Duration
                        <input name="stay_distribution" value="<?= esc((string) ($stayDistributionValue ?? '')) ?>" placeholder="e.g. 8+8+5 or Makkah:8 Madina:8 Makkah:5" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Check-In Date
                        <input type="date" name="check_in_date" value="<?= esc((string) ($stayCheckIn ?? '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Check-Out Date
                        <input type="date" name="check_out_date" value="<?= esc((string) ($stayCheckOut ?? '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Sharing Cost (PKR)
                        <input name="sharing_cost" placeholder="e.g. 45000" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Quad Cost (PKR)
                        <input name="quad_cost" placeholder="e.g. 50000" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Triple Cost (PKR)
                        <input name="triple_cost" placeholder="e.g. 55000" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Double Cost (PKR)
                        <input name="double_cost" placeholder="e.g. 60000" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <div class="flex items-end">
                        <button type="submit" class="btn btn-primary h-9 w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 text-xs font-semibold"><i class="fa-solid fa-link"></i><span>Attach Hotel</span></button>
                    </div>
                </form>
                <div id="hotel-pricing-hint" class="mt-3 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-700">Select a hotel. If it is already linked in this package, its saved pricing will be reused automatically.</div>
                <div id="next-stay-hint" class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800">Next hotel leg will appear here.</div>
                <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                <th class="px-2 py-1.5">Hotel</th>
                                <th class="px-2 py-1.5">City</th>
                                <th class="px-2 py-1.5">Check-In</th>
                                <th class="px-2 py-1.5">Check-Out</th>
                                <th class="px-2 py-1.5">Sharing</th>
                                <th class="px-2 py-1.5">Quad</th>
                                <th class="px-2 py-1.5">Triple</th>
                                <th class="px-2 py-1.5">Double</th>
                                <th class="px-2 py-1.5">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php if (!empty($hotelRows)): foreach ($hotelRows as $hotelRow): ?>
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-2 py-1.5"><?= esc((string) ($hotelRow['hotel_name'] ?: ($hotelRow['hotel_master_name'] ?? ''))) ?></td>
                                        <td class="px-2 py-1.5"><?= esc((string) ($hotelRow['hotel_city'] ?? '')) ?></td>
                                        <td class="px-2 py-1.5"><?= esc((string) ($hotelRow['check_in_date'] ?? '')) ?></td>
                                        <td class="px-2 py-1.5"><?= esc((string) ($hotelRow['check_out_date'] ?? '')) ?></td>
                                        <td class="px-2 py-1.5 font-semibold">PKR <?= esc(number_format((float) ($hotelRow['sharing_cost'] ?? 0), 2)) ?></td>
                                        <td class="px-2 py-1.5 font-semibold">PKR <?= esc(number_format((float) ($hotelRow['quad_cost'] ?? 0), 2)) ?></td>
                                        <td class="px-2 py-1.5 font-semibold">PKR <?= esc(number_format((float) ($hotelRow['triple_cost'] ?? 0), 2)) ?></td>
                                        <td class="px-2 py-1.5 font-semibold">PKR <?= esc(number_format((float) ($hotelRow['double_cost'] ?? 0), 2)) ?></td>
                                        <td class="px-2 py-1.5">
                                            <form method="post" action="<?= site_url('packages/hotels/delete') ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                                                <input type="hidden" name="package_hotel_id" value="<?= esc($hotelRow['id']) ?>">
                                                <button type="submit" class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100" title="Delete Hotel Stay" aria-label="Delete Hotel Stay"><i class="fa-solid fa-trash-can text-[11px]"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="9" class="px-2 py-3 text-slate-500">No hotel stay segments attached.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($hotelPricingRows)): ?>
                    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                    <th class="px-2 py-1.5">Saved Hotel Pricing</th>
                                    <th class="px-2 py-1.5">City</th>
                                    <th class="px-2 py-1.5">Sharing</th>
                                    <th class="px-2 py-1.5">Quad</th>
                                    <th class="px-2 py-1.5">Triple</th>
                                    <th class="px-2 py-1.5">Double</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <?php foreach ($hotelPricingRows as $hotelPricingRow): ?>
                                    <tr>
                                        <td class="px-2 py-1.5"><?= esc((string) ($hotelPricingRow['hotel_name'] ?: ($hotelPricingRow['hotel_master_name'] ?? ''))) ?></td>
                                        <td class="px-2 py-1.5"><?= esc((string) ($hotelPricingRow['hotel_city'] ?? '')) ?></td>
                                        <td class="px-2 py-1.5">PKR <?= esc(number_format((float) ($hotelPricingRow['sharing_cost'] ?? 0), 2)) ?></td>
                                        <td class="px-2 py-1.5">PKR <?= esc(number_format((float) ($hotelPricingRow['quad_cost'] ?? 0), 2)) ?></td>
                                        <td class="px-2 py-1.5">PKR <?= esc(number_format((float) ($hotelPricingRow['triple_cost'] ?? 0), 2)) ?></td>
                                        <td class="px-2 py-1.5">PKR <?= esc(number_format((float) ($hotelPricingRow['double_cost'] ?? 0), 2)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </article>

            <article id="flight-section" class="rounded-xl border border-slate-200 bg-white p-4 overflow-auto">
                <h3 class="mb-4 inline-flex items-center gap-2 text-lg font-semibold"><i class="fa-solid fa-plane-departure text-emerald-600"></i><span>Package Flights</span></h3>
                <p class="mb-3 text-xs text-slate-500">Select one paired flight record. Outbound and return timings are taken from Flight Management automatically.</p>
                <form method="post" action="<?= site_url('packages/flights/create') ?>" class="grid gap-3 md:grid-cols-5">
                    <?= csrf_field() ?>
                    <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                    <label class="block text-xs font-medium text-slate-600 md:col-span-3">Flight
                        <select name="flight_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Select Flight</option>
                            <?php foreach ($flightOptions as $flight): ?>
                                <?php
                                $outboundLabel = trim((string) (($flight['airline'] ?? '-') . ' ' . ($flight['flight_no'] ?? '-')));
                                $outboundRoute = trim((string) (($flight['departure_airport'] ?? '') . ' -> ' . ($flight['arrival_airport'] ?? '')), ' ->');
                                $returnLabel = trim((string) (($flight['return_airline'] ?? '-') . ' ' . ($flight['return_flight_no'] ?? '-')));
                                $returnRoute = trim((string) (($flight['return_departure_airport'] ?? '') . ' -> ' . ($flight['return_arrival_airport'] ?? '')), ' ->');
                                $optionText = 'OUT: ' . $outboundLabel . ' | ' . ($outboundRoute !== '' ? $outboundRoute : '-') . ' | ' . (($flight['departure_at'] ?? '-') ?: '-')
                                    . ' || RET: ' . $returnLabel . ' | ' . ($returnRoute !== '' ? $returnRoute : '-') . ' | ' . (($flight['return_departure_at'] ?? '-') ?: '-');
                                ?>
                                <option value="<?= esc((string) $flight['id']) ?>"><?= esc($optionText) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Combined Cost (PKR)
                        <input name="cost_amount" placeholder="e.g. 240000" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </label>
                    <div class="flex items-end md:col-span-5">
                        <button type="submit" class="btn btn-md btn-primary w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold"><i class="fa-solid fa-link"></i><span>Attach Flight</span></button>
                    </div>
                </form>
                <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                <th class="px-2 py-1.5">Journey</th>
                                <th class="px-2 py-1.5">Flight</th>
                                <th class="px-2 py-1.5">Route</th>
                                <th class="px-2 py-1.5">Departure</th>
                                <th class="px-2 py-1.5">Arrival</th>
                                <th class="px-2 py-1.5">PNR</th>
                                <th class="px-2 py-1.5">Cost</th>
                                <th class="px-2 py-1.5">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php if (!empty($flightRows)): foreach ($flightRows as $idx => $flightRow): ?>
                                    <?php $journeyLabel = ((int) $idx % 2 === 0) ? 'OUTBOUND' : 'RETURN'; ?>
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-2 py-1.5"><?= esc($journeyLabel) ?></td>
                                        <td class="px-2 py-1.5"><?= esc(trim((string) (($flightRow['airline'] ?? '') . ' ' . ($flightRow['flight_no'] ?? '')))) ?></td>
                                        <td class="px-2 py-1.5"><?= esc(trim((string) (($flightRow['departure_airport'] ?? '') . ' -> ' . ($flightRow['arrival_airport'] ?? '')), ' ->')) ?: '-' ?></td>
                                        <td class="px-2 py-1.5"><?= esc((string) ($flightRow['departure_at'] ?? '')) ?></td>
                                        <td class="px-2 py-1.5"><?= esc((string) ($flightRow['arrival_at'] ?? '')) ?></td>
                                        <td class="px-2 py-1.5"><?= esc((string) (($flightRow['pnr'] ?? '') !== '' ? $flightRow['pnr'] : '-')) ?></td>
                                        <td class="px-2 py-1.5 font-semibold">PKR <?= esc(number_format((float) ($flightRow['cost_amount'] ?? 0), 2)) ?></td>
                                        <td class="px-2 py-1.5">
                                            <form method="post" action="<?= site_url('packages/flights/delete') ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                                                <input type="hidden" name="package_flight_id" value="<?= esc($flightRow['id']) ?>">
                                                <button type="submit" class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100" title="Delete Flight Link" aria-label="Delete Flight Link"><i class="fa-solid fa-trash-can text-[11px]"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="8" class="px-2 py-3 text-slate-500">No package flights attached.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article id="transport-section" class="rounded-xl border border-slate-200 bg-white p-4 overflow-auto">
                <h3 class="mb-4 inline-flex items-center gap-2 text-lg font-semibold"><i class="fa-solid fa-bus text-emerald-600"></i><span>Package Transports</span></h3>
                <form method="post" action="<?= site_url('packages/transports/create') ?>" class="grid gap-3 md:grid-cols-5">
                    <?= csrf_field() ?>
                    <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                    <label class="block text-xs font-medium text-slate-600 md:col-span-2">Transport
                        <select name="transport_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Select Transport</option>
                            <?php foreach ($transportOptions as $transport): ?>
                                <option value="<?= esc((string) $transport['id']) ?>"><?= esc(($transport['transport_name'] ?? '-') . ' | ' . ($transport['provider_name'] ?? '-') . ' | ' . ($transport['vehicle_type'] ?? '-') . ' | Seats: ' . ($transport['seat_capacity'] ?? '-')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Seat Capacity
                        <input name="seat_capacity" placeholder="Seat Capacity (optional)" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Transport Cost (PKR)
                        <input name="cost_amount" placeholder="e.g. 15000" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </label>
                    <div class="flex items-end">
                        <button type="submit" class="btn btn-primary h-9 w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 text-xs font-semibold"><i class="fa-solid fa-link"></i><span>Attach Transport</span></button>
                    </div>
                </form>
                <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                <th class="px-2 py-1.5">Name</th>
                                <th class="px-2 py-1.5">Provider</th>
                                <th class="px-2 py-1.5">Vehicle</th>
                                <th class="px-2 py-1.5">Seats</th>
                                <th class="px-2 py-1.5">Cost</th>
                                <th class="px-2 py-1.5">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php if (!empty($transportRows)): foreach ($transportRows as $transportRow): ?>
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-2 py-1.5"><?= esc((string) (($transportRow['transport_name'] ?? '') !== '' ? $transportRow['transport_name'] : ($transportRow['master_transport_name'] ?? '-'))) ?></td>
                                        <td class="px-2 py-1.5"><?= esc((string) ($transportRow['provider_name'] ?? '')) ?></td>
                                        <td class="px-2 py-1.5"><?= esc((string) ($transportRow['vehicle_type'] ?? '')) ?></td>
                                        <td class="px-2 py-1.5"><?= esc((string) ($transportRow['seat_capacity'] ?? '')) ?></td>
                                        <td class="px-2 py-1.5 font-semibold">PKR <?= esc(number_format((float) ($transportRow['cost_amount'] ?? 0), 2)) ?></td>
                                        <td class="px-2 py-1.5">
                                            <form method="post" action="<?= site_url('packages/transports/delete') ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                                                <input type="hidden" name="package_transport_id" value="<?= esc($transportRow['id']) ?>">
                                                <button type="submit" class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100" title="Delete Transport Link" aria-label="Delete Transport Link"><i class="fa-solid fa-trash-can text-[11px]"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="6" class="px-2 py-3 text-slate-500">No package transports attached.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <?php
            $summaryMode = (string) ($pricingSummary['mode'] ?? ((int) ($row['include_hotel'] ?? 1) === 1 ? 'tiered' : 'flat'));
            $summaryPriceMap = (array) ($pricingSummary['price_map'] ?? []);
            $summaryFlatPrice = $pricingSummary['flat_price'] ?? null;
            ?>
            <article class="rounded-xl border border-slate-200 bg-white p-4 overflow-auto">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="inline-flex items-center gap-2 text-lg font-semibold"><i class="fa-solid fa-calculator text-emerald-600"></i><span>Package Grand Total</span></h3>
                        <p class="mt-1 text-xs text-slate-500">Derived automatically from linked hotel, flight, and transport costs.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-700"><?= esc($summaryMode === 'flat' ? 'Flat package' : 'Tiered package') ?></span>
                </div>
                <div class="mt-4 grid gap-3 <?= $summaryMode === 'flat' ? 'md:grid-cols-1' : 'md:grid-cols-4' ?>">
                    <?php if ($summaryMode === 'flat'): ?>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Package Price</div>
                            <div class="mt-1 text-2xl font-bold text-slate-800"><?= $summaryFlatPrice !== null ? 'PKR ' . esc(number_format((float) $summaryFlatPrice, 2)) : '—' ?></div>
                            <div class="mt-1 text-xs text-slate-500">Used for flight-only, transport-only, or flat component packages.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach (['sharing', 'quad', 'triple', 'double'] as $tier): ?>
                            <?php $tierPrice = $summaryPriceMap[$tier] ?? null; ?>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?= esc($tier) ?></div>
                                <div class="mt-1 text-xl font-bold text-slate-800"><?= $tierPrice !== null ? 'PKR ' . esc(number_format((float) $tierPrice, 2)) : 'Not configured' ?></div>
                                <div class="mt-1 text-xs text-slate-500"><?= $tierPrice !== null ? 'Hotel tier cost plus linked ticket and transport costs.' : 'Attach hotel rows for this room type to complete pricing.' ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="mt-3 grid gap-2 text-xs text-slate-600 md:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">Hotel subtotal source: <?= $summaryMode === 'flat' ? 'Not used for flat packages' : esc((string) count($summaryPriceMap)) . ' configured tier(s)' ?></div>
                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">Flight subtotal: PKR <?= esc(number_format((float) ($pricingSummary['flight_total'] ?? 0), 2)) ?></div>
                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">Transport subtotal: PKR <?= esc(number_format((float) ($pricingSummary['transport_total'] ?? 0), 2)) ?></div>
                </div>
            </article>

        </div>
    </section>
</main>
<script>
    (function() {
        const packageForm = document.querySelector('form[action*="packages/update"]');
        if (!packageForm) {
            return;
        }

        const durationInput = packageForm.querySelector('input[name="duration_days"]');
        const departureInput = packageForm.querySelector('input[name="departure_date"]');
        const arrivalInput = packageForm.querySelector('input[name="arrival_date"]');
        const hotelForm = document.querySelector('form[action*="packages/hotels/create"]');
        const roomSelect = hotelForm ? hotelForm.querySelector('select[name="hotel_id"]') : null;
        const stayDistributionInput = hotelForm ? hotelForm.querySelector('input[name="stay_distribution"]') : null;
        const hotelCheckInInput = hotelForm ? hotelForm.querySelector('input[name="check_in_date"]') : null;
        const hotelCheckOutInput = hotelForm ? hotelForm.querySelector('input[name="check_out_date"]') : null;
        const hotelPricingHint = document.getElementById('hotel-pricing-hint');
        const nextStayHint = document.getElementById('next-stay-hint');
        const hotelCostInputs = hotelForm ? [
            hotelForm.querySelector('input[name="sharing_cost"]'),
            hotelForm.querySelector('input[name="quad_cost"]'),
            hotelForm.querySelector('input[name="triple_cost"]'),
            hotelForm.querySelector('input[name="double_cost"]')
        ] : [];
        const packageStayEnd = hotelForm ? (hotelForm.dataset.packageEnd || '') : '';
        const existingHotelPricing = <?= json_encode($existingHotelPricing ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const currentHotelStayCount = <?= (int) ($currentHotelStayCount ?? 0) ?>;

        const formatDate = function(dateObj) {
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dateObj.getDate()).padStart(2, '0');
            return dateObj.getFullYear() + '-' + month + '-' + day;
        };

        const addDays = function(baseDate, days) {
            const next = new Date(baseDate.getTime());
            next.setDate(next.getDate() + days);
            return next;
        };

        const normalizeCity = function(city) {
            const raw = (city || '').toLowerCase();
            if (raw.indexOf('madina') !== -1 || raw.indexOf('medina') !== -1 || raw.indexOf('madinah') !== -1) {
                return 'madina';
            }
            if (raw.indexOf('makkah') !== -1 || raw.indexOf('mecca') !== -1) {
                return 'makkah';
            }

            return raw;
        };

        const cityLabel = function(city) {
            return normalizeCity(city) === 'madina' ? 'Madina' : 'Makkah';
        };

        const buildSegments = function(value, fallbackDuration, selectedCity) {
            const text = (value || '').toLowerCase().trim();
            if (!text) {
                return fallbackDuration > 0 ? [{
                    city: normalizeCity(selectedCity),
                    days: fallbackDuration
                }] : [];
            }

            const namedMatches = Array.from(text.matchAll(/(makkah|madina|medina)\s*[:=\-]?\s*(\d+)/gi));
            if (namedMatches.length > 0) {
                return namedMatches.map(function(match) {
                    return {
                        city: normalizeCity(match[1] || ''),
                        days: Math.max(1, parseInt(match[2] || '0', 10))
                    };
                });
            }

            const numbers = (text.match(/\d+/g) || []).map(function(item) {
                return parseInt(item, 10);
            });

            if (numbers.length >= 3) {
                return [{
                        city: 'makkah',
                        days: Math.max(1, numbers[0])
                    },
                    {
                        city: 'madina',
                        days: Math.max(1, numbers[1])
                    },
                    {
                        city: 'makkah',
                        days: Math.max(1, numbers[2])
                    }
                ];
            }
            if (numbers.length === 2) {
                return [{
                        city: 'makkah',
                        days: Math.max(1, numbers[0])
                    },
                    {
                        city: 'madina',
                        days: Math.max(1, numbers[1])
                    }
                ];
            }
            if (numbers.length === 1) {
                return [{
                    city: 'makkah',
                    days: Math.max(1, numbers[0])
                }];
            }

            return fallbackDuration > 0 ? [{
                city: normalizeCity(selectedCity),
                days: fallbackDuration
            }] : [];
        };

        const syncHotelPricing = function() {
            if (!roomSelect) {
                return;
            }

            const hotelId = roomSelect.value || '';
            const profile = hotelId !== '' ? existingHotelPricing[hotelId] : null;

            hotelCostInputs.forEach(function(input) {
                if (!input) {
                    return;
                }

                const fieldName = input.getAttribute('name');
                if (profile && Object.prototype.hasOwnProperty.call(profile, fieldName)) {
                    input.value = profile[fieldName];
                    input.readOnly = true;
                    input.classList.add('bg-slate-100');
                } else {
                    input.readOnly = false;
                    input.classList.remove('bg-slate-100');
                    if (!input.dataset.userTouched) {
                        input.value = '';
                    }
                }
            });

            if (hotelPricingHint) {
                hotelPricingHint.textContent = profile ?
                    'Existing pricing found for this hotel. The new stay will reuse those saved costs automatically.' :
                    'No saved pricing exists for this hotel in this package yet. Enter the four room-tier costs once.';
            }
        };

        const nextSegmentMeta = function(selectedCity) {
            const duration = parseInt((durationInput && durationInput.value) || '0', 10);
            const segments = buildSegments(stayDistributionInput ? stayDistributionInput.value : '', duration, selectedCity || '');
            let segmentIndex = currentHotelStayCount;

            if (segments.length === 0) {
                return {
                    segment: null,
                    segmentIndex: segmentIndex,
                    totalSegments: 0,
                    isComplete: false,
                };
            }

            if (segmentIndex >= segments.length) {
                return {
                    segment: null,
                    segmentIndex: segmentIndex,
                    totalSegments: segments.length,
                    isComplete: true,
                };
            }

            if (segmentIndex < 0) {
                segmentIndex = 0;
            }

            return {
                segment: segments[segmentIndex],
                segmentIndex: segmentIndex,
                totalSegments: segments.length,
                isComplete: false,
            };
        };

        const syncNextStayHint = function() {
            if (!nextStayHint || !roomSelect || !hotelCheckInInput || !hotelCheckOutInput) {
                return;
            }

            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const selectedCity = selectedOption ? (selectedOption.dataset.city || '') : '';
            const meta = nextSegmentMeta(selectedCity);

            if (meta.totalSegments === 0) {
                nextStayHint.className = 'mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700';
                nextStayHint.textContent = 'Enter the stay distribution so the system can show the next hotel leg.';
                return;
            }

            if (meta.isComplete || !meta.segment) {
                nextStayHint.className = 'mt-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700';
                nextStayHint.textContent = 'All hotel legs in the stay distribution have already been added.';
                return;
            }

            const expectedCity = cityLabel(meta.segment.city || '');
            const startDate = hotelCheckInInput.value || '-';
            const endDate = hotelCheckOutInput.value || '-';
            const isMismatch = selectedCity !== '' && normalizeCity(selectedCity) !== normalizeCity(meta.segment.city || '');

            nextStayHint.className = isMismatch ?
                'mt-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700' :
                'mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800';

            nextStayHint.textContent = 'Next leg: ' + expectedCity + ' for ' + String(meta.segment.days || 1) + ' day(s), from ' + startDate + ' to ' + endDate + '.' + (isMismatch ? ' Please select a ' + expectedCity + ' hotel.' : '');
        };

        const updateArrivalDate = function() {
            if (!durationInput || !departureInput || !arrivalInput || !departureInput.value) {
                return;
            }

            const duration = parseInt(durationInput.value || '0', 10);
            if (!duration || duration < 1) {
                return;
            }

            // datetime-local value is "YYYY-MM-DDTHH:MM" — extract date part for day arithmetic
            const departureDate = new Date(departureInput.value.substring(0, 10) + 'T00:00:00');
            if (Number.isNaN(departureDate.getTime())) {
                return;
            }

            const arrivalDate = addDays(departureDate, duration);
            // Set arrival as datetime-local: keep time at 00:00 by default
            arrivalInput.value = formatDate(arrivalDate) + 'T00:00';
        };

        const updateHotelDates = function() {
            if (!roomSelect || !hotelCheckInInput || !hotelCheckOutInput) {
                return;
            }

            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            if (!selectedOption) {
                return;
            }

            const city = (selectedOption.dataset.city || '').toLowerCase();
            // hotel check-in input is type="date" (yyyy-MM-dd); departure is datetime-local — strip to date part
            const rawCheckIn = hotelCheckInInput.value || (departureInput ? departureInput.value : '');
            if (!rawCheckIn) {
                return;
            }
            const baseCheckInValue = rawCheckIn.substring(0, 10); // ensure yyyy-MM-dd

            const baseCheckInDate = new Date(baseCheckInValue + 'T00:00:00');
            if (Number.isNaN(baseCheckInDate.getTime())) {
                return;
            }

            const duration = parseInt((durationInput && durationInput.value) || '0', 10);
            const segments = buildSegments(stayDistributionInput ? stayDistributionInput.value : '', duration, city);
            let segmentIndex = currentHotelStayCount;
            if (segmentIndex >= segments.length) {
                segmentIndex = segments.length - 1;
            }
            if (segmentIndex < 0) {
                segmentIndex = 0;
            }
            const segment = segments[segmentIndex] || {
                city: normalizeCity(city),
                days: duration > 0 ? duration : 1
            };

            hotelCheckInInput.value = formatDate(baseCheckInDate);

            let checkOut = addDays(baseCheckInDate, Math.max(1, segment.days || 1));

            if (packageStayEnd) {
                // packageStayEnd may be a full datetime string — use date part only
                const endDate = new Date(packageStayEnd.substring(0, 10) + 'T00:00:00');
                if (!Number.isNaN(endDate.getTime()) && checkOut > endDate) {
                    checkOut = endDate;
                }
            }

            hotelCheckOutInput.value = formatDate(checkOut);
            syncNextStayHint();
        };

        if (durationInput) {
            durationInput.addEventListener('input', function() {
                updateArrivalDate();
                updateHotelDates();
                syncNextStayHint();
            });
        }
        if (departureInput) {
            departureInput.addEventListener('change', function() {
                updateArrivalDate();
                updateHotelDates();
                syncNextStayHint();
            });
        }
        if (stayDistributionInput) {
            stayDistributionInput.addEventListener('input', function() {
                updateHotelDates();
                syncNextStayHint();
            });
        }
        if (roomSelect) {
            roomSelect.addEventListener('change', function() {
                syncHotelPricing();
                updateHotelDates();
                syncNextStayHint();
            });
        }
        hotelCostInputs.forEach(function(input) {
            if (input) {
                input.addEventListener('input', function() {
                    input.dataset.userTouched = '1';
                });
            }
        });

        updateArrivalDate();
        syncHotelPricing();
        updateHotelDates();
        syncNextStayHint();
    })();

    // Component include toggles — show/hide the linking sections immediately on change
    (function() {
        var toggleMap = {
            toggle_hotel: document.getElementById('hotel-section'),
            toggle_ticket: document.getElementById('flight-section'),
            toggle_transport: document.getElementById('transport-section'),
        };

        function syncSections() {
            Object.keys(toggleMap).forEach(function(id) {
                var cb = document.getElementById(id);
                var section = toggleMap[id];
                if (cb && section) {
                    section.style.display = cb.checked ? '' : 'none';
                }
            });
        }

        Object.keys(toggleMap).forEach(function(id) {
            var cb = document.getElementById(id);
            if (cb) {
                cb.addEventListener('change', syncSections);
            }
        });

        syncSections(); // apply current state on page load
    })();

    // Sync flight override date fields from package departure / arrival dates
    // (function() {
    //     var packageForm = document.querySelector('form[action*="packages/update"]');
    //     if (!packageForm) return;

    //     var pkgDep = packageForm.querySelector('input[name="departure_date"]');
    //     var pkgArr = packageForm.querySelector('input[name="arrival_date"]');

    //     var flightForm = document.querySelector('form[action*="packages/flights/create"]');
    //     if (!flightForm) return;

    //     var outboundDep = flightForm.querySelector('input[name="outbound_departure_at"]');
    //     var outboundArr = flightForm.querySelector('input[name="outbound_arrival_at"]');
    //     var returnDep = flightForm.querySelector('input[name="return_departure_at"]');
    //     var returnArr = flightForm.querySelector('input[name="return_arrival_at"]');

    //     function applyPackageDates() {
    //         if (pkgDep && pkgDep.value) {
    //             if (outboundDep) outboundDep.value = pkgDep.value;
    //             if (outboundArr) outboundArr.value = pkgDep.value;
    //         }
    //         if (pkgArr && pkgArr.value) {
    //             if (returnDep) returnDep.value = pkgArr.value;
    //             if (returnArr) returnArr.value = pkgArr.value;
    //         }
    //     }

    //     if (pkgDep) pkgDep.addEventListener('change', applyPackageDates);
    //     if (pkgArr) pkgArr.addEventListener('change', applyPackageDates);

    //     applyPackageDates();
    // })();
</script>
<?php $this->endSection() ?>