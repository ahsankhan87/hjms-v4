<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-3">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 lg:col-span-1">
            <h3 class="text-lg font-semibold">Update Package</h3>
            <form method="post" action="<?php echo site_url('packages/update') ?>" class="mt-4 space-y-3">
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
                <button type="submit" class="btn btn-md btn-primary btn-block">Update Package</button>
            </form>

            <hr class="my-5 border-slate-200">

            <h3 class="text-lg font-semibold">Delete Package</h3>
            <form method="post" action="/packages/delete" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                <button type="submit" class="btn btn-md btn-danger btn-block">Delete Package</button>
            </form>
        </article>

        <div class="space-y-6 lg:col-span-2">
            <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 overflow-auto">
                <h3 class="text-lg font-semibold mb-4">Package Costs</h3>
                <form method="post" action="<?= site_url('packages/costs/create') ?>" class="grid gap-3 md:grid-cols-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                    <label class="block text-xs font-medium text-slate-600 md:col-span-2">Cost Type
                        <select name="cost_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Select Cost Type</option>
                            <option value="sharing">Sharing</option>
                            <option value="quad">Quad</option>
                            <option value="triple">Triple</option>
                            <option value="double">Double</option>
                        </select>
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Amount
                        <input name="cost_amount" placeholder="e.g. 5000" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Seats Limit
                        <input type="number" name="seats_limit" placeholder="e.g. 40" min="1" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Supplier ID
                        <input name="supplier_id" placeholder="Supplier ID" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <div class="flex items-end">
                        <button type="submit" class="btn btn-md btn-primary w-full">Add Cost</button>
                    </div>
                    <label class="block text-xs font-medium text-slate-600 md:col-span-6">Description
                        <input name="description" placeholder="Description (optional)" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                </form>
                <div class="mt-4 overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left">
                                <th class="py-2 pr-3">Type</th>
                                <th class="py-2 pr-3">Amount</th>
                                <th class="py-2 pr-3">Seats Limit</th>
                                <th class="py-2 pr-3">Supplier</th>
                                <th class="py-2 pr-3">Description</th>
                                <th class="py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($costRows)): foreach ($costRows as $cost): ?>
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 pr-3 capitalize"><?= esc($cost['cost_type']) ?></td>
                                        <td class="py-2 pr-3 font-semibold"><?= esc(number_format((float)$cost['cost_amount'], 2)) ?></td>
                                        <td class="py-2 pr-3"><?= $cost['seats_limit'] !== null ? esc((string)$cost['seats_limit']) : '<span class="text-slate-400">—</span>' ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($cost['supplier_id'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($cost['description'] ?? '')) ?></td>
                                        <td class="py-2">
                                            <form method="post" action="<?= site_url('packages/costs/delete') ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                                                <input type="hidden" name="package_cost_id" value="<?= esc($cost['id']) ?>">
                                                <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="6" class="py-3 text-slate-500">No package costs added.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article id="hotel-section" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 overflow-auto">
                <h3 class="text-lg font-semibold mb-4">Package Hotels</h3>
                <p class="text-xs text-slate-500 mb-3">Hotels are linked directly from Hotel Management records.</p>
                <p class="text-xs text-slate-500 mb-3">Sequence rule: next hotel check-in starts from previous hotel check-out, and total stay cannot exceed package end date (<?= esc((string) ($packageStayEnd ?? '-')) ?>).</p>
                <form method="post" action="<?= site_url('packages/hotels/create') ?>" class="grid gap-3 md:grid-cols-4" data-package-end="<?= esc((string) ($packageStayEnd ?? '')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                    <label class="block text-xs font-medium text-slate-600 md:col-span-2">Hotel Room Type
                        <select name="hotel_room_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Select Hotel + Room Type</option>
                            <?php foreach (($hotelRoomOptions ?? []) as $roomOption): ?>
                                <?php
                                $availableRooms = (int) ($roomOption['available_rooms'] ?? 0);
                                $totalRooms = (int) ($roomOption['total_rooms'] ?? 0);
                                $label = (string) ($roomOption['hotel_name'] ?? '-')
                                    . (!empty($roomOption['hotel_city']) ? ' - ' . (string) $roomOption['hotel_city'] : '')
                                    . ' | ' . (string) ($roomOption['room_type'] ?? '-')
                                    . ' | Available: ' . $availableRooms . '/' . $totalRooms;
                                ?>
                                <option value="<?= esc((string) $roomOption['id']) ?>" data-city="<?= esc((string) ($roomOption['hotel_city'] ?? '')) ?>" <?= $availableRooms < 1 ? 'disabled' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block text-xs font-medium text-slate-600 md:col-span-2">Stay Distribution / Duration
                        <input name="stay_distribution" placeholder="e.g. 2+4+5 or Makkah:7 Madina:4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Check-In Date
                        <input type="date" name="check_in_date" value="<?= esc((string) ($stayCheckIn ?? '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Check-Out Date
                        <input type="date" name="check_out_date" value="<?= esc((string) ($stayCheckOut ?? '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </label>
                    <button type="submit" class="btn btn-md btn-primary">Attach Hotel</button>
                </form>
                <div class="mt-4 overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left">
                                <th class="py-2 pr-3">Hotel</th>
                                <th class="py-2 pr-3">City</th>
                                <th class="py-2 pr-3">Check-In</th>
                                <th class="py-2 pr-3">Check-Out</th>
                                <th class="py-2 pr-3">Room</th>
                                <th class="py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($hotelRows)): foreach ($hotelRows as $hotelRow): ?>
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 pr-3"><?= esc((string) ($hotelRow['hotel_name'] ?: ($hotelRow['hotel_master_name'] ?? ''))) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($hotelRow['hotel_city'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($hotelRow['check_in_date'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($hotelRow['check_out_date'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) (($hotelRow['room_type'] ?? '') !== '' ? $hotelRow['room_type'] : ($hotelRow['hotel_room_type'] ?? ''))) ?></td>
                                        <td class="py-2">
                                            <form method="post" action="<?= site_url('packages/hotels/delete') ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                                                <input type="hidden" name="package_hotel_id" value="<?= esc($hotelRow['id']) ?>">
                                                <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="6" class="py-3 text-slate-500">No package hotels attached.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article id="flight-section" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 overflow-auto">
                <h3 class="text-lg font-semibold mb-4">Package Flights</h3>
                <form method="post" action="<?= site_url('packages/flights/create') ?>" class="grid gap-3 md:grid-cols-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                    <label class="block text-xs font-medium text-slate-600 md:col-span-2">Outbound Flight (To KSA)
                        <select name="outbound_flight_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Select Outbound Flight</option>
                            <?php foreach ($flightOptions as $flight): ?>
                                <option value="<?= esc((string) $flight['id']) ?>"><?= esc(($flight['flight_no'] ?? '-') . ' | ' . ($flight['airline'] ?? '-') . ' | ' . ($flight['departure_at'] ?? '-')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Outbound Departure (optional override)
                        <input type="datetime-local" name="outbound_departure_at" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Outbound Arrival (optional override)
                        <input type="datetime-local" name="outbound_arrival_at" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-medium text-slate-600 md:col-span-2">Return Flight (From KSA)
                        <select name="return_flight_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Select Return Flight</option>
                            <?php foreach ($flightOptions as $flight): ?>
                                <option value="<?= esc((string) $flight['id']) ?>"><?= esc(($flight['flight_no'] ?? '-') . ' | ' . ($flight['airline'] ?? '-') . ' | ' . ($flight['departure_at'] ?? '-')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Return Departure (optional override)
                        <input type="datetime-local" name="return_departure_at" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-medium text-slate-600">Return Arrival (optional override)
                        <input type="datetime-local" name="return_arrival_at" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </label>
                    <button type="submit" class="btn btn-md btn-primary">Attach Flight</button>
                </form>
                <div class="mt-4 overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left">
                                <th class="py-2 pr-3">Journey</th>
                                <th class="py-2 pr-3">Flight No</th>
                                <th class="py-2 pr-3">Airline</th>
                                <th class="py-2 pr-3">Departure</th>
                                <th class="py-2 pr-3">Arrival</th>
                                <th class="py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($flightRows)): foreach ($flightRows as $idx => $flightRow): ?>
                                    <?php $journeyLabel = ((int) $idx % 2 === 0) ? 'OUTBOUND' : 'RETURN'; ?>
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 pr-3"><?= esc($journeyLabel) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($flightRow['flight_no'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($flightRow['airline'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($flightRow['departure_at'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($flightRow['arrival_at'] ?? '')) ?></td>
                                        <td class="py-2">
                                            <form method="post" action="<?= site_url('packages/flights/delete') ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                                                <input type="hidden" name="package_flight_id" value="<?= esc($flightRow['id']) ?>">
                                                <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="6" class="py-3 text-slate-500">No package flights attached.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article id="transport-section" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 overflow-auto">
                <h3 class="text-lg font-semibold mb-4">Package Transports</h3>
                <form method="post" action="<?= site_url('packages/transports/create') ?>" class="grid gap-3 md:grid-cols-4">
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
                    <button type="submit" class="btn btn-md btn-primary">Attach Transport</button>
                </form>
                <div class="mt-4 overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left">
                                <th class="py-2 pr-3">Name</th>
                                <th class="py-2 pr-3">Provider</th>
                                <th class="py-2 pr-3">Vehicle</th>
                                <th class="py-2 pr-3">Seats</th>
                                <th class="py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transportRows)): foreach ($transportRows as $transportRow): ?>
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 pr-3"><?= esc((string) (($transportRow['transport_name'] ?? '') !== '' ? $transportRow['transport_name'] : ($transportRow['master_transport_name'] ?? '-'))) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($transportRow['provider_name'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($transportRow['vehicle_type'] ?? '')) ?></td>
                                        <td class="py-2 pr-3"><?= esc((string) ($transportRow['seat_capacity'] ?? '')) ?></td>
                                        <td class="py-2">
                                            <form method="post" action="<?= site_url('packages/transports/delete') ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="package_id" value="<?= esc($row['id']) ?>">
                                                <input type="hidden" name="package_transport_id" value="<?= esc($transportRow['id']) ?>">
                                                <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="py-3 text-slate-500">No package transports attached.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
        const roomSelect = hotelForm ? hotelForm.querySelector('select[name="hotel_room_id"]') : null;
        const stayDistributionInput = hotelForm ? hotelForm.querySelector('input[name="stay_distribution"]') : null;
        const hotelCheckInInput = hotelForm ? hotelForm.querySelector('input[name="check_in_date"]') : null;
        const hotelCheckOutInput = hotelForm ? hotelForm.querySelector('input[name="check_out_date"]') : null;
        const packageStayEnd = hotelForm ? (hotelForm.dataset.packageEnd || '') : '';

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

        const parseDistribution = function(value) {
            const text = (value || '').toLowerCase().trim();
            if (!text) {
                return {
                    makkahDays: 0,
                    madinaDays: 0
                };
            }

            let makkahDays = 0;
            let madinaDays = 0;

            const makkahMatch = text.match(/makkah\s*[:=\-]?\s*(\d+)/i);
            const madinaMatch = text.match(/(?:madina|medina)\s*[:=\-]?\s*(\d+)/i);

            if (makkahMatch) {
                makkahDays = parseInt(makkahMatch[1] || '0', 10);
            }
            if (madinaMatch) {
                madinaDays = parseInt(madinaMatch[1] || '0', 10);
            }

            if (makkahDays > 0 || madinaDays > 0) {
                return {
                    makkahDays,
                    madinaDays
                };
            }

            const numbers = (text.match(/\d+/g) || []).map(function(item) {
                return parseInt(item, 10);
            });

            if (numbers.length >= 3) {
                return {
                    makkahDays: Math.max(0, numbers[0]) + Math.max(0, numbers[2]),
                    madinaDays: Math.max(0, numbers[1])
                };
            }
            if (numbers.length === 2) {
                return {
                    makkahDays: Math.max(0, numbers[0]),
                    madinaDays: Math.max(0, numbers[1])
                };
            }
            if (numbers.length === 1) {
                return {
                    makkahDays: Math.max(0, numbers[0]),
                    madinaDays: 0
                };
            }

            return {
                makkahDays: 0,
                madinaDays: 0
            };
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

            const split = parseDistribution(stayDistributionInput ? stayDistributionInput.value : '');
            let makkahDays = split.makkahDays;
            let madinaDays = split.madinaDays;

            if (makkahDays === 0 && madinaDays === 0) {
                const duration = parseInt((durationInput && durationInput.value) || '0', 10);
                makkahDays = duration > 0 ? duration : 1;
            }

            hotelCheckInInput.value = formatDate(baseCheckInDate);

            let checkOut = city.includes('madina') || city.includes('medina') ?
                addDays(baseCheckInDate, Math.max(1, madinaDays)) :
                addDays(baseCheckInDate, Math.max(1, makkahDays));

            if (packageStayEnd) {
                // packageStayEnd may be a full datetime string — use date part only
                const endDate = new Date(packageStayEnd.substring(0, 10) + 'T00:00:00');
                if (!Number.isNaN(endDate.getTime()) && checkOut > endDate) {
                    checkOut = endDate;
                }
            }

            hotelCheckOutInput.value = formatDate(checkOut);
        };

        if (durationInput) {
            durationInput.addEventListener('input', function() {
                updateArrivalDate();
                updateHotelDates();
            });
        }
        if (departureInput) {
            departureInput.addEventListener('change', function() {
                updateArrivalDate();
                updateHotelDates();
            });
        }
        if (stayDistributionInput) {
            stayDistributionInput.addEventListener('input', updateHotelDates);
        }
        if (roomSelect) {
            roomSelect.addEventListener('change', updateHotelDates);
        }

        updateArrivalDate();
        updateHotelDates();
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