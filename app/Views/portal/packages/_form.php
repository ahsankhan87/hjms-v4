<?php
/**
 * Unified package form (create + edit).
 *
 * @var array  $row
 * @var bool   $isEdit
 * @var string $formAction
 * @var array  $companies
 * @var array  $hotelOptions
 * @var array  $flightTemplates
 * @var array  $transportOptions
 * @var array  $flight
 * @var array  $hotelStays
 * @var array  $transportRows
 * @var array|null $pricingSummary
 */
$success = $success ?? null;
$error = $error ?? null;
$errors = $errors ?? [];

$stayRows = old('hotel_stay');
if (! is_array($stayRows) || $stayRows === []) {
    $stayRows = $hotelStays;
}

$transportInputRows = old('transport_row');
if (! is_array($transportInputRows) || $transportInputRows === []) {
    $transportInputRows = $transportRows;
}

$incHotel = (int) old('include_hotel', (string) ($row['include_hotel'] ?? 1));
$incTicket = (int) old('include_ticket', (string) ($row['include_ticket'] ?? 1));
$incTransport = (int) old('include_transport', (string) ($row['include_transport'] ?? 1));

$inputClass = 'mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500';
$labelClass = 'block text-xs font-medium text-slate-600';
$cardClass = 'rounded-xl border border-slate-200 bg-white';

$hotelMasterJson = json_encode(array_map(static function (array $hotel): array {
    return [
        'id'   => (int) ($hotel['id'] ?? 0),
        'name' => (string) ($hotel['name'] ?? ''),
        'city' => (string) ($hotel['city'] ?? ''),
    ];
}, $hotelOptions ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

$predefinedInstructionUr = "ضروری ہدایات

1- ہوٹل سے داخلہ (چیک ان) کا وقت دوپہر 12 بجے ہے اور باہر (چیک آؤٹ) کا وقت دوپہر 02 بجے ہے۔

2- اگر کسی بھی عمرہ زائر کو برائے پیشہ ورانہ کاروبار کے مطابق طے شدہ عمرہ چیک کی مدت میں کوئی مسئلہ ہو جائے تو اسے فوری انتظامیہ سے رابطہ کرنا چاہیے، ورنہ وہ خود ذمہ دار ہو گا۔

3- پراپرٹی سعودی عرب میں کمپنی کے پاس ہے اور اسی کے ذریعے فراہم کی جاتی ہے۔

4- عمرہ زائرین کو کسی بھی شکایت کی صورت میں سعودی عرب میں ہمارے دفتر سے براہ راست رابطہ کرنا چاہیے، کسی غیر سے رابطہ نہ کریں۔

5- فریبوں سے بچنے کے لیے 24 گھنٹے پہلے ہمارے اسٹاف سے رابطہ کریں۔ فریب میں رہنے کی صورت میں حاصل ہونے والی کوئی بھی ذمہ داری خود اٹھانی ہو گی۔

6- ہوٹل میں قیام کا وقت دوپہر میں مقرر کیا گیا ہے، برائے مہربانی اس کا خیال رکھیں۔

7- تمام زائرین اپنے سامان کی حفاظت کے خود ذمہ دار ہوں گے۔

8- زائرین کا ملک سے روانگی اور ہوٹل سے باہر جانے سے 3 دن پہلے کمپنی کے نمائندے سے شرائط و ضوابط کے لیے رابطہ کرنا لازم ہے اور تصدیق کرانا ضروری ہے۔";

$predefinedMakkahContact = 'Niamat kiyani Phone No.+966 53 908 1421';
$predefinedMadinaContact = 'M Irfan Phone No.00966568553058';
$predefinedTransportContact = 'Sajid Nawaz Phone No.00966591195335';

$renderHotelRow = static function (array $stay, int|string $index) use ($hotelOptions, $inputClass, $labelClass): void {
    $currentHotelId = (int) ($stay['hotel_id'] ?? 0);
    $auto = (string) ($stay['auto'] ?? '');
    $nights = (string) ($stay['nights'] ?? '');
    $formatCost = static function ($value): string {
        return (float) $value > 0 ? (string) (float) $value : '';
    };
    ?>
    <div data-hotel-row class="rounded-lg border border-slate-200 bg-slate-50/70 p-3">
        <div class="flex items-center justify-between gap-2">
            <span data-stay-label class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Stay</span>
            <span data-stay-dates class="text-xs font-semibold text-slate-600"></span>
            <button type="button" data-remove-hotel-row class="inline-flex h-7 items-center gap-1 rounded-md border border-rose-200 bg-white px-2 text-[11px] font-semibold text-rose-600 hover:bg-rose-50">
                <i class="fa-solid fa-xmark"></i><span>Remove</span>
            </button>
        </div>
        <div class="mt-2 grid grid-cols-2 gap-2 md:grid-cols-4 xl:grid-cols-8">
            <div>
                <label class="<?= $labelClass ?>">City</label>
                <select name="hotel_stay[<?= $index ?>][city]" data-hotel-city class="<?= $inputClass ?>">
                    <?php foreach (['Makkah', 'Madina'] as $cityOption): ?>
                        <option value="<?= esc($cityOption) ?>" <?= strcasecmp((string) ($stay['city'] ?? ''), $cityOption) === 0 ? 'selected' : '' ?>><?= esc($cityOption) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-span-1 md:col-span-2">
                <label class="<?= $labelClass ?>">Hotel</label>
                <select name="hotel_stay[<?= $index ?>][hotel_id]" data-hotel-select class="<?= $inputClass ?>">
                    <option value="">Select hotel…</option>
                    <?php foreach (($hotelOptions ?? []) as $hotelOption): ?>
                        <?php $hotelOptionId = (int) ($hotelOption['id'] ?? 0); ?>
                        <option value="<?= esc((string) $hotelOptionId) ?>" <?= $currentHotelId === $hotelOptionId ? 'selected' : '' ?>>
                            <?= esc((string) ($hotelOption['name'] ?? '')) ?><?= ($hotelOption['city'] ?? '') !== '' ? ' — ' . esc((string) $hotelOption['city']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="<?= $labelClass ?>">New hotel name</label>
                <input name="hotel_stay[<?= $index ?>][hotel_name]" value="<?= esc((string) ($stay['hotel_name'] ?? '')) ?>" placeholder="If not in list" class="<?= $inputClass ?>">
            </div>
            <div>
                <label class="<?= $labelClass ?>">Nights</label>
                <input type="number" min="0" name="hotel_stay[<?= $index ?>][nights]" value="<?= esc($nights) ?>" data-stay-nights data-auto="<?= $auto !== '' ? '1' : '0' ?>" data-preferred="<?= esc($auto) ?>" class="<?= $inputClass ?>">
                <input type="hidden" name="hotel_stay[<?= $index ?>][auto]" value="<?= esc($auto) ?>" data-stay-auto>
            </div>
            <div class="col-span-2 md:col-span-4 xl:col-span-4">
                <label class="<?= $labelClass ?>">Room tier costs for this hotel (PKR, whole stay)</label>
                <div class="mt-1 grid grid-cols-2 gap-2 md:grid-cols-5">
                    <input name="hotel_stay[<?= $index ?>][sharing_cost]" value="<?= esc($formatCost($stay['sharing_cost'] ?? 0)) ?>" placeholder="Sharing" data-hotel-cost class="<?= $inputClass ?> !mt-0">
                    <input name="hotel_stay[<?= $index ?>][quad_cost]" value="<?= esc($formatCost($stay['quad_cost'] ?? 0)) ?>" placeholder="Quad" data-hotel-cost class="<?= $inputClass ?> !mt-0">
                    <input name="hotel_stay[<?= $index ?>][triple_cost]" value="<?= esc($formatCost($stay['triple_cost'] ?? 0)) ?>" placeholder="Triple" data-hotel-cost class="<?= $inputClass ?> !mt-0">
                    <input name="hotel_stay[<?= $index ?>][double_cost]" value="<?= esc($formatCost($stay['double_cost'] ?? 0)) ?>" placeholder="Double" data-hotel-cost class="<?= $inputClass ?> !mt-0">
                    <input type="number" min="0" name="hotel_stay[<?= $index ?>][total_seats]" value="<?= esc((string) ((int) ($stay['total_seats'] ?? 0) > 0 ? (int) $stay['total_seats'] : '')) ?>" placeholder="Seats" class="<?= $inputClass ?> !mt-0">
                </div>
                <p data-cost-note class="mt-1 hidden text-[11px] font-medium text-sky-700"></p>
            </div>
        </div>
        <input type="hidden" name="hotel_stay[<?= $index ?>][stay_id]" value="<?= esc((string) (int) ($stay['stay_id'] ?? 0)) ?>">
    </div>
    <?php
};
?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <form method="post" action="<?= esc($formAction) ?>" id="package-form" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="package_id" value="<?= esc((string) (int) ($row['id'] ?? 0)) ?>">
        <input type="hidden" name="flight_id" id="flight-id" value="<?= esc((string) (int) ($flight['flight_id'] ?? 0)) ?>">
        <input type="hidden" name="flight_source" id="flight-source" value="<?= (int) ($flight['flight_id'] ?? 0) > 0 ? 'existing' : '' ?>">

        <section class="<?= $cardClass ?> p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800"><i class="fa-solid fa-plane-departure text-emerald-600"></i><span>1. Flight &amp; Trip Dates</span></h2>
                    <p class="mt-1 text-xs text-slate-500">These dates drive everything else: outbound departure = first hotel check-in, return arrival = last hotel check-out. Duration is calculated automatically.</p>
                </div>
                <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">
                    <input type="hidden" name="include_ticket" value="0">
                    <input type="checkbox" name="include_ticket" value="1" id="toggle-ticket" class="rounded border-slate-300" <?= $incTicket ? 'checked' : '' ?>>
                    <span>Package includes flight / ticket</span>
                </label>
            </div>

            <div class="mt-3 grid gap-3 xl:grid-cols-2">
                <div class="rounded-lg border border-slate-200 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-700">Outbound — Pakistan to Saudi Arabia</h3>
                        <span class="text-[11px] text-slate-400">Defines first hotel check-in</span>
                    </div>
                    <?php if (!empty($flightTemplates)): ?>
                        <div class="mt-2">
                            <label class="<?= $labelClass ?>">Load from Flight Management <span class="font-normal text-slate-400">(optional)</span></label>
                            <select id="flight-template-select" class="<?= $inputClass ?>">
                                <option value="">— enter flight details below —</option>
                                <?php foreach ($flightTemplates as $template): ?>
                                    <?php
                                    $templateOutbound = trim((string) (($template['airline'] ?? '') . ' ' . ($template['flight_no'] ?? '')));
                                    $templateReturn = trim((string) (($template['return_airline'] ?? '') . ' ' . ($template['return_flight_no'] ?? '')));
                                    $templateToLocal = static function ($value): string {
                                        return trim((string) ($value ?? '')) === '' ? '' : date('Y-m-d\TH:i', strtotime((string) $value));
                                    };
                                    ?>
                                    <option value="<?= esc((string) (int) ($template['id'] ?? 0)) ?>"
                                        data-airline="<?= esc((string) ($template['airline'] ?? '')) ?>"
                                        data-flight-no="<?= esc((string) ($template['flight_no'] ?? '')) ?>"
                                        data-pnr="<?= esc((string) ($template['pnr'] ?? '')) ?>"
                                        data-departure-airport="<?= esc((string) ($template['departure_airport'] ?? '')) ?>"
                                        data-arrival-airport="<?= esc((string) ($template['arrival_airport'] ?? '')) ?>"
                                        data-outbound-departure-at="<?= esc($templateToLocal($template['departure_at'] ?? '')) ?>"
                                        data-outbound-arrival-at="<?= esc($templateToLocal($template['arrival_at'] ?? '')) ?>"
                                        data-return-airline="<?= esc((string) ($template['return_airline'] ?? '')) ?>"
                                        data-return-flight-no="<?= esc((string) ($template['return_flight_no'] ?? '')) ?>"
                                        data-return-pnr="<?= esc((string) ($template['return_pnr'] ?? '')) ?>"
                                        data-return-departure-airport="<?= esc((string) ($template['return_departure_airport'] ?? '')) ?>"
                                        data-return-arrival-airport="<?= esc((string) ($template['return_arrival_airport'] ?? '')) ?>"
                                        data-return-departure-at="<?= esc($templateToLocal($template['return_departure_at'] ?? '')) ?>"
                                        data-return-arrival-at="<?= esc($templateToLocal($template['return_arrival_at'] ?? '')) ?>"
                                    ><?= esc($templateOutbound !== '' ? $templateOutbound : 'Flight #' . (int) ($template['id'] ?? 0)) ?> · <?= esc((string) ($template['departure_at'] ?? '')) ?><?= $templateReturn !== '' ? ' · back: ' . esc($templateReturn) : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="mt-1 text-[11px] text-slate-400">Loading a saved flight keeps its master record; the trip dates below are still taken from the fields you fill in.</p>
                        </div>
                    <?php endif; ?>
                    <div class="mt-2 grid grid-cols-2 gap-2 md:grid-cols-3">
                        <div>
                            <label class="<?= $labelClass ?>">Airline</label>
                            <input name="airline" value="<?= esc(old('airline', (string) ($flight['airline'] ?? ''))) ?>" placeholder="e.g. PIA" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">Flight No</label>
                            <input name="flight_no" value="<?= esc(old('flight_no', (string) ($flight['flight_no'] ?? ''))) ?>" placeholder="e.g. PK-741" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">PNR</label>
                            <input name="pnr" value="<?= esc(old('pnr', (string) ($flight['pnr'] ?? ''))) ?>" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">From</label>
                            <input name="departure_airport" value="<?= esc(old('departure_airport', (string) ($flight['departure_airport'] ?? ''))) ?>" placeholder="Lahore (LHE)" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">To</label>
                            <input name="arrival_airport" value="<?= esc(old('arrival_airport', (string) ($flight['arrival_airport'] ?? ''))) ?>" placeholder="Jeddah (JED)" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">Departure <span class="text-rose-600">*</span></label>
                            <input type="datetime-local" name="outbound_departure_at" id="trip-start" value="<?= esc(old('outbound_departure_at', (string) ($flight['departure_at'] ?? ''))) ?>" required class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">Arrival</label>
                            <input type="datetime-local" name="outbound_arrival_at" value="<?= esc(old('outbound_arrival_at', (string) ($flight['arrival_at'] ?? ''))) ?>" class="<?= $inputClass ?>">
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-700">Return — Saudi Arabia to Pakistan</h3>
                        <span class="text-[11px] text-slate-400">Defines last hotel check-out</span>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-2 md:grid-cols-3">
                        <div>
                            <label class="<?= $labelClass ?>">Airline</label>
                            <input name="return_airline" value="<?= esc(old('return_airline', (string) ($flight['return_airline'] ?? ''))) ?>" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">Flight No</label>
                            <input name="return_flight_no" value="<?= esc(old('return_flight_no', (string) ($flight['return_flight_no'] ?? ''))) ?>" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">PNR</label>
                            <input name="return_pnr" value="<?= esc(old('return_pnr', (string) ($flight['return_pnr'] ?? ''))) ?>" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">From</label>
                            <input name="return_departure_airport" value="<?= esc(old('return_departure_airport', (string) ($flight['return_departure_airport'] ?? ''))) ?>" placeholder="Jeddah (JED)" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">To</label>
                            <input name="return_arrival_airport" value="<?= esc(old('return_arrival_airport', (string) ($flight['return_arrival_airport'] ?? ''))) ?>" placeholder="Lahore (LHE)" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">Departure</label>
                            <input type="datetime-local" name="return_departure_at" value="<?= esc(old('return_departure_at', (string) ($flight['return_departure_at'] ?? ''))) ?>" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">Arrival <span class="text-rose-600">*</span></label>
                            <input type="datetime-local" name="return_arrival_at" id="trip-end" value="<?= esc(old('return_arrival_at', (string) ($flight['return_arrival_at'] ?? ''))) ?>" required class="<?= $inputClass ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 grid gap-2 md:grid-cols-3">
                <div>
                    <label class="<?= $labelClass ?>">Ticket cost (PKR)</label>
                    <input type="number" step="0.01" min="0" name="ticket_cost" value="<?= esc(old('ticket_cost', (float) ($flight['cost_amount'] ?? 0) > 0 ? (string) (float) $flight['cost_amount'] : '')) ?>" placeholder="e.g. 240000" class="<?= $inputClass ?>">
                </div>
                <div class="md:col-span-2">
                    <label class="<?= $labelClass ?>">Trip duration</label>
                    <div id="trip-summary" class="mt-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-800">
                        <?= !empty($row['duration_days']) ? esc((string) (int) $row['duration_days']) . ' day(s) — calculated from the flight dates' : 'Enter the outbound departure and return arrival dates above.' ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="<?= $cardClass ?> p-4" id="hotel-section">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800"><i class="fa-solid fa-hotel text-emerald-600"></i><span>2. Hotel Stays</span></h2>
                    <p class="mt-1 text-xs text-slate-500">Default itinerary is Makkah → Madina → Makkah. Stay dates are chained automatically from the trip dates; add, remove or reorder stays as needed.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="add-hotel-row" class="btn btn-outline h-8 inline-flex items-center gap-1 px-2 text-[11px] font-semibold"><i class="fa-solid fa-plus"></i><span>Add stay</span></button>
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">
                        <input type="hidden" name="include_hotel" value="0">
                        <input type="checkbox" name="include_hotel" value="1" id="toggle-hotel" class="rounded border-slate-300" <?= $incHotel ? 'checked' : '' ?>>
                        <span>Hotel included</span>
                    </label>
                </div>
            </div>
            <div id="hotel-stay-rows" class="mt-3 space-y-3">
                <?php foreach ($stayRows as $index => $stay): ?>
                    <?php $renderHotelRow((array) $stay, (int) $index); ?>
                <?php endforeach; ?>
            </div>
            <div id="stay-balance" class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">Stay dates will be calculated once the flight dates are entered.</div>
        </section>

        <section class="<?= $cardClass ?> p-4" id="transport-section">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800"><i class="fa-solid fa-bus text-emerald-600"></i><span>3. Transport</span></h2>
                    <p class="mt-1 text-xs text-slate-500">Pick a transport from the master list or type a new one.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="add-transport-row" class="btn btn-outline h-8 inline-flex items-center gap-1 px-2 text-[11px] font-semibold"><i class="fa-solid fa-plus"></i><span>Add transport</span></button>
                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">
                        <input type="hidden" name="include_transport" value="0">
                        <input type="checkbox" name="include_transport" value="1" id="toggle-transport" class="rounded border-slate-300" <?= $incTransport ? 'checked' : '' ?>>
                        <span>Transport included</span>
                    </label>
                </div>
            </div>
            <div id="transport-rows" class="mt-3 space-y-2">
                <?php foreach ($transportInputRows as $index => $transportRow): ?>
                    <div data-transport-row class="grid grid-cols-2 gap-2 rounded-lg border border-slate-200 bg-slate-50/70 p-2 md:grid-cols-12">
                        <div class="md:col-span-3">
                            <label class="<?= $labelClass ?>">Transport</label>
                            <select name="transport_row[<?= (int) $index ?>][transport_id]" class="<?= $inputClass ?>">
                                <option value="">New / not listed</option>
                                <?php foreach (($transportOptions ?? []) as $transportOption): ?>
                                    <?php $transportOptionId = (int) ($transportOption['id'] ?? 0); ?>
                                    <option value="<?= esc((string) $transportOptionId) ?>" <?= (int) ($transportRow['transport_id'] ?? 0) === $transportOptionId ? 'selected' : '' ?>>
                                        <?= esc((string) ($transportOption['transport_name'] ?? '')) ?> — <?= esc((string) ($transportOption['provider_name'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="<?= $labelClass ?>">New transport name</label>
                            <input name="transport_row[<?= (int) $index ?>][transport_name]" value="<?= esc((string) ($transportRow['transport_name'] ?? '')) ?>" placeholder="If not in list" class="<?= $inputClass ?>">
                        </div>
                        <div class="md:col-span-2">
                            <label class="<?= $labelClass ?>">Seats</label>
                            <input type="number" min="0" name="transport_row[<?= (int) $index ?>][seat_capacity]" value="<?= esc((string) ($transportRow['seat_capacity'] ?? '')) ?>" class="<?= $inputClass ?>">
                        </div>
                        <div class="md:col-span-2">
                            <label class="<?= $labelClass ?>">Cost (PKR)</label>
                            <input type="number" step="0.01" min="0" name="transport_row[<?= (int) $index ?>][cost_amount]" value="<?= esc((string) ($transportRow['cost_amount'] ?? '')) ?>" class="<?= $inputClass ?>">
                        </div>
                        <div class="flex items-end md:col-span-2">
                            <button type="button" data-remove-transport-row class="inline-flex h-9 w-full items-center justify-center gap-1 rounded-md border border-rose-200 bg-white px-2 text-[11px] font-semibold text-rose-600 hover:bg-rose-50">
                                <i class="fa-solid fa-xmark"></i><span>Remove</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="<?= $cardClass ?> p-4">
            <h2 class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800"><i class="fa-solid fa-tags text-emerald-600"></i><span>4. Package Details</span></h2>
            <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label class="<?= $labelClass ?>">Code <span class="text-rose-600">*</span></label>
                    <input name="code" value="<?= esc(old('code', (string) ($row['code'] ?? ''))) ?>" required placeholder="e.g. UMR-APR-2026" class="<?= $inputClass ?>">
                </div>
                <div class="md:col-span-2">
                    <label class="<?= $labelClass ?>">Name <span class="text-rose-600">*</span></label>
                    <input name="name" value="<?= esc(old('name', (string) ($row['name'] ?? ''))) ?>" required placeholder="Package name" class="<?= $inputClass ?>">
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Type</label>
                    <select name="package_type" class="<?= $inputClass ?>">
                        <?php $packageType = old('package_type', (string) ($row['package_type'] ?? 'umrah')); ?>
                        <option value="umrah" <?= $packageType === 'umrah' ? 'selected' : '' ?>>Umrah</option>
                        <option value="hajj" <?= $packageType === 'hajj' ? 'selected' : '' ?>>Hajj</option>
                    </select>
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Visa cost (PKR)</label>
                    <input type="number" step="0.01" min="0" name="purchase_price_visa" value="<?= esc(old('purchase_price_visa', (string) ($row['purchase_price_visa'] ?? ''))) ?>" class="<?= $inputClass ?>">
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Status</label>
                    <select name="is_active" class="<?= $inputClass ?>">
                        <?php $isActive = (string) old('is_active', (string) ($row['is_active'] ?? 1)); ?>
                        <option value="1" <?= $isActive === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $isActive === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="<?= $labelClass ?>">Internal notes</label>
                    <input name="notes" value="<?= esc(old('notes', (string) ($row['notes'] ?? ''))) ?>" class="<?= $inputClass ?>">
                </div>
            </div>
        </section>

        <section class="<?= $cardClass ?> p-4">
            <h2 class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800"><i class="fa-solid fa-file-lines text-emerald-600"></i><span>5. Voucher Settings</span></h2>
            <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="<?= $labelClass ?>">Default Shirka company</label>
                    <?php
                    $selectedShirkaCompanyId = (string) old('default_shirka_company_id', (string) ($row['default_shirka_company_id'] ?? ''));
                    if ($selectedShirkaCompanyId === '' && ! empty($companies)) {
                        $selectedShirkaCompanyId = (string) ($companies[0]['id'] ?? '');
                    }
                    ?>
                    <select name="default_shirka_company_id" class="<?= $inputClass ?>">
                        <option value="">Select shirka company…</option>
                        <?php foreach (($companies ?? []) as $item): ?>
                            <?php $companyId = (string) ($item['id'] ?? ''); ?>
                            <option value="<?= esc($companyId) ?>" <?= $selectedShirkaCompanyId === $companyId ? 'selected' : '' ?>><?= esc((string) ($item['name'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="<?= $labelClass ?>">Voucher instructions (Urdu)</label>
                    <textarea name="voucher_instructions_ur" rows="4" dir="rtl" class="<?= $inputClass ?>"><?= esc(old('voucher_instructions_ur', (string) ($row['voucher_instructions_ur'] ?? '') !== '' ? $row['voucher_instructions_ur'] : $predefinedInstructionUr)) ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="<?= $labelClass ?>">Voucher instructions (English)</label>
                    <textarea name="voucher_instructions_en" rows="3" class="<?= $inputClass ?>"><?= esc(old('voucher_instructions_en', (string) ($row['voucher_instructions_en'] ?? ''))) ?></textarea>
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Makkah office</label>
                    <input name="makkah_contact" value="<?= esc(old('makkah_contact', (string) (($row['makkah_contact'] ?? '') !== '' ? $row['makkah_contact'] : $predefinedMakkahContact))) ?>" class="<?= $inputClass ?>">
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Madina office</label>
                    <input name="madina_contact" value="<?= esc(old('madina_contact', (string) (($row['madina_contact'] ?? '') !== '' ? $row['madina_contact'] : $predefinedMadinaContact))) ?>" class="<?= $inputClass ?>">
                </div>
                <div>
                    <label class="<?= $labelClass ?>">Transport contact</label>
                    <input name="transport_contact" value="<?= esc(old('transport_contact', (string) (($row['transport_contact'] ?? '') !== '' ? $row['transport_contact'] : $predefinedTransportContact))) ?>" class="<?= $inputClass ?>">
                </div>
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm mt-4">
            <p class="text-xs text-slate-500">Flight dates drive the hotel dates and package duration automatically.</p>
            <div class="flex items-center gap-2">
                <a href="<?= site_url('packages') ?>" class="btn btn-outline btn-md inline-flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i><span>Cancel</span></a>
                <button type="submit" class="btn btn-primary btn-md inline-flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span><?= $isEdit ? 'Save Changes' : 'Create Package' ?></span>
                </button>
            </div>
        </div>
    </form>
</main>

<template id="hotel-row-template">
    <?php $renderHotelRow([
        'stay_id' => 0,
        'city' => 'Makkah',
        'hotel_id' => 0,
        'hotel_name' => '',
        'nights' => '',
        'sharing_cost' => '',
        'quad_cost' => '',
        'triple_cost' => '',
        'double_cost' => '',
        'total_seats' => '',
        'auto' => 'rest',
    ], '__INDEX__'); ?>
</template>

<template id="transport-row-template">
    <div data-transport-row class="grid grid-cols-2 gap-2 rounded-lg border border-slate-200 bg-slate-50/70 p-2 md:grid-cols-12">
        <div class="md:col-span-3">
            <label class="<?= $labelClass ?>">Transport</label>
            <select name="transport_row[__INDEX__][transport_id]" class="<?= $inputClass ?>">
                <option value="">New / not listed</option>
                <?php foreach (($transportOptions ?? []) as $transportOption): ?>
                    <option value="<?= esc((string) (int) ($transportOption['id'] ?? 0)) ?>"><?= esc((string) ($transportOption['transport_name'] ?? '')) ?> — <?= esc((string) ($transportOption['provider_name'] ?? '')) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-3">
            <label class="<?= $labelClass ?>">New transport name</label>
            <input name="transport_row[__INDEX__][transport_name]" placeholder="If not in list" class="<?= $inputClass ?>">
        </div>
        <div class="md:col-span-2">
            <label class="<?= $labelClass ?>">Seats</label>
            <input type="number" min="0" name="transport_row[__INDEX__][seat_capacity]" class="<?= $inputClass ?>">
        </div>
        <div class="md:col-span-2">
            <label class="<?= $labelClass ?>">Cost (PKR)</label>
            <input type="number" step="0.01" min="0" name="transport_row[__INDEX__][cost_amount]" class="<?= $inputClass ?>">
        </div>
        <div class="flex items-end md:col-span-2">
            <button type="button" data-remove-transport-row class="inline-flex h-9 w-full items-center justify-center gap-1 rounded-md border border-rose-200 bg-white px-2 text-[11px] font-semibold text-rose-600 hover:bg-rose-50">
                <i class="fa-solid fa-xmark"></i><span>Remove</span>
            </button>
        </div>
    </div>
</template>

<script>
    (function() {
        'use strict';

        var form = document.getElementById('package-form');
        if (!form) {
            return;
        }

        var hotelMaster = <?= $hotelMasterJson ?>;
        var hotelRowsWrap = document.getElementById('hotel-stay-rows');
        var transportRowsWrap = document.getElementById('transport-rows');
        var hotelTemplate = document.getElementById('hotel-row-template');
        var transportTemplate = document.getElementById('transport-row-template');
        var tripStartInput = document.getElementById('trip-start');
        var tripEndInput = document.getElementById('trip-end');
        var tripSummary = document.getElementById('trip-summary');
        var stayBalance = document.getElementById('stay-balance');
        var nextHotelIndex = <?= (int) (is_array($stayRows) ? count($stayRows) : 0) ?>;
        var nextTransportIndex = <?= (int) (is_array($transportInputRows) ? count($transportInputRows) : 0) ?>;

        function pad(value) {
            return String(value).padStart(2, '0');
        }

        function formatDate(date) {
            return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
        }

        function parseDate(value) {
            if (!value) {
                return null;
            }

            var date = new Date(value.length > 10 ? value : value + 'T00:00:00');
            return Number.isNaN(date.getTime()) ? null : date;
        }

        function dateOnly(value) {
            return value ? value.substring(0, 10) : '';
        }

        function addDays(date, days) {
            var next = new Date(date.getTime());
            next.setDate(next.getDate() + days);
            return next;
        }

        function diffDays(from, to) {
            return Math.round((to.getTime() - from.getTime()) / 86400000);
        }

        function normalizeCity(value) {
            var text = String(value || '').toLowerCase();
            if (text.indexOf('makk') !== -1 || text.indexOf('mecc') !== -1) {
                return 'makkah';
            }
            if (text.indexOf('madi') !== -1 || text.indexOf('medi') !== -1) {
                return 'madina';
            }
            return text;
        }

        function tripWindow() {
            var start = parseDate(dateOnly(tripStartInput ? tripStartInput.value : ''));
            var end = parseDate(dateOnly(tripEndInput ? tripEndInput.value : ''));
            if (!start || !end || diffDays(start, end) < 1) {
                return null;
            }
            return { start: start, end: end, days: diffDays(start, end) };
        }

        function rows() {
            return Array.prototype.slice.call(hotelRowsWrap ? hotelRowsWrap.querySelectorAll('[data-hotel-row]') : []);
        }

        function rowField(row, suffix) {
            return row.querySelector('[name$="[' + suffix + ']"]');
        }

        function fillHotelSelect(row, keepValue) {
            var select = rowField(row, 'hotel_id');
            var city = normalizeCity(rowField(row, 'city') ? rowField(row, 'city').value : '');
            if (!select) {
                return;
            }

            var current = keepValue === undefined ? select.value : String(keepValue);
            select.innerHTML = '';

            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select hotel…';
            select.appendChild(placeholder);

            hotelMaster.forEach(function(hotel) {
                if (city !== '' && normalizeCity(hotel.city) !== '' && normalizeCity(hotel.city) !== city) {
                    return;
                }

                var option = document.createElement('option');
                option.value = String(hotel.id);
                option.textContent = hotel.name + (hotel.city ? ' — ' + hotel.city : '');
                select.appendChild(option);
            });

            if (current && select.querySelector('option[value="' + current + '"]')) {
                select.value = current;
            }
        }

        function hotelKey(row) {
            var select = rowField(row, 'hotel_id');
            var nameInput = rowField(row, 'hotel_name');
            var selectValue = select ? String(select.value || '') : '';
            if (selectValue !== '') {
                return 'id:' + selectValue;
            }
            var nameValue = nameInput ? String(nameInput.value || '').trim().toLowerCase() : '';
            return nameValue !== '' ? 'name:' + nameValue : '';
        }

        function setCostLock(row, locked, sourceLabel) {
            row.querySelectorAll('[data-hotel-cost]').forEach(function(input) {
                input.readOnly = locked;
                input.classList.toggle('bg-slate-100', locked);
            });

            var note = row.querySelector('[data-cost-note]');
            if (!note) {
                return;
            }

            if (locked && sourceLabel) {
                note.textContent = 'Pricing carried from ' + sourceLabel + ' (same hotel).';
                note.classList.remove('hidden');
            } else {
                note.textContent = '';
                note.classList.add('hidden');
            }
        }

        function syncCostSharing() {
            var seen = {};

            rows().forEach(function(row, index) {
                var key = hotelKey(row);
                if (key === '') {
                    setCostLock(row, false, '');
                    return;
                }

                if (Object.prototype.hasOwnProperty.call(seen, key)) {
                    var sourceRow = seen[key];
                    row.querySelectorAll('[data-hotel-cost]').forEach(function(input) {
                        var name = input.getAttribute('name') || '';
                        var suffix = name.substring(name.lastIndexOf('['));
                        var sourceInput = sourceRow.querySelector('[data-hotel-cost][name$="' + suffix + '"]');
                        if (sourceInput) {
                            input.value = sourceInput.value;
                        }
                    });
                    setCostLock(row, true, 'Stay ' + (sourceRow.dataset.stayNumber || ''));
                    return;
                }

                seen[key] = row;
                setCostLock(row, false, '');
            });
        }

        function syncAutoFlags() {
            rows().forEach(function(row) {
                var nightsInput = rowField(row, 'nights');
                var autoInput = row.querySelector('[data-stay-auto]');
                if (!nightsInput) {
                    return;
                }

                var auto = autoInput ? String(autoInput.value || '') : '';
                nightsInput.dataset.preferred = auto;
                nightsInput.dataset.auto = auto !== '' && auto !== '0' ? '1' : '0';
            });
        }

        function redistributeNightDefaults(window) {
            if (!window) {
                return;
            }

            syncAutoFlags();

            var all = rows();
            var allocated = all.reduce(function(total, row) {
                var nightsInput = rowField(row, 'nights');
                if (!nightsInput || nightsInput.dataset.auto === '1') {
                    return total;
                }
                return total + Math.max(0, parseInt(nightsInput.value || '0', 10));
            }, 0);

            var remaining = window.days - allocated;

            all.forEach(function(row) {
                var nightsInput = rowField(row, 'nights');
                if (!nightsInput || nightsInput.dataset.auto !== '1') {
                    return;
                }

                var preferred = nightsInput.dataset.preferred || '';
                var value;

                if (preferred === 'rest') {
                    value = Math.max(0, remaining);
                } else {
                    var preferredNumber = parseInt(preferred || '0', 10);
                    value = preferredNumber > 0 ? Math.min(preferredNumber, Math.max(0, remaining)) : 0;
                }

                nightsInput.value = value > 0 ? String(value) : '';
                remaining -= value;
            });
        }

        function paintStays() {
            var window = tripWindow();
            var all = rows();
            var cursor = window ? new Date(window.start.getTime()) : null;
            var rawNights = 0;

            all.forEach(function(row, index) {
                row.dataset.stayNumber = String(index + 1);
                var label = row.querySelector('[data-stay-label]');
                if (label) {
                    label.textContent = 'Stay ' + (index + 1);
                }

                var nightsInput = rowField(row, 'nights');
                var nights = nightsInput ? Math.max(0, parseInt(nightsInput.value || '0', 10)) : 0;
                var dates = row.querySelector('[data-stay-dates]');
                rawNights += nights;

                if (!window || !cursor || nights < 1) {
                    if (dates) {
                        dates.textContent = '';
                    }
                    return;
                }

                var trimmed = false;
                var checkOut = addDays(cursor, nights);
                if (checkOut.getTime() > window.end.getTime()) {
                    checkOut = new Date(window.end.getTime());
                    trimmed = true;
                }

                var nightsShown = diffDays(cursor, checkOut);

                if (dates) {
                    dates.textContent = nightsShown < 1 ?
                        'No nights left in the trip window' :
                        formatDate(cursor) + ' → ' + formatDate(checkOut) + ' (' + nightsShown + ' night' + (nightsShown === 1 ? '' : 's') + (trimmed ? ', trimmed to trip end' : '') + ')';
                }

                cursor = checkOut;
            });

            if (!stayBalance) {
                return;
            }

            if (!window) {
                stayBalance.textContent = 'Stay dates will be calculated once the outbound departure and return arrival dates are entered.';
                stayBalance.className = 'mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600';
                return;
            }

            if (rawNights > window.days) {
                stayBalance.textContent = 'Stays add up to ' + rawNights + ' night(s) but the trip is only ' + window.days + ' night(s) (' + formatDate(window.start) + ' → ' + formatDate(window.end) + '). Extra nights are trimmed to the trip window on save.';
                stayBalance.className = 'mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700';
                return;
            }

            if (rawNights < window.days) {
                stayBalance.textContent = (window.days - rawNights) + ' night(s) of the ' + window.days + '-night trip are not allocated yet. The last stay will be extended to ' + formatDate(window.end) + ' on save.';
                stayBalance.className = 'mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700';
                return;
            }

            stayBalance.textContent = 'All ' + window.days + ' night(s) allocated: ' + formatDate(window.start) + ' → ' + formatDate(window.end) + '.';
            stayBalance.className = 'mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700';
        }

        function refresh(redistribute) {
            var window = tripWindow();

            if (tripSummary) {
                if (window) {
                    tripSummary.textContent = window.days + ' day(s): ' + formatDate(window.start) + ' → ' + formatDate(window.end) + ' (calculated from the flight dates)';
                } else {
                    tripSummary.textContent = 'Enter the outbound departure and return arrival dates above.';
                }
            }

            if (redistribute) {
                redistributeNightDefaults(window);
            }

            paintStays();
            syncCostSharing();
        }

        function bindHotelRow(row) {
            var citySelect = rowField(row, 'city');
            var hotelSelect = rowField(row, 'hotel_id');
            var nightsInput = rowField(row, 'nights');

            if (citySelect) {
                citySelect.addEventListener('change', function() {
                    fillHotelSelect(row, '');
                    refresh(false);
                });
            }

            if (hotelSelect) {
                hotelSelect.addEventListener('change', function() {
                    var nameInput = rowField(row, 'hotel_name');
                    if (nameInput && hotelSelect.value !== '') {
                        nameInput.value = '';
                    }
                    refresh(false);
                });
            }

            var nameInput = rowField(row, 'hotel_name');
            if (nameInput) {
                nameInput.addEventListener('input', function() {
                    if (nameInput.value.trim() !== '' && hotelSelect) {
                        hotelSelect.value = '';
                    }
                    refresh(false);
                });
            }

            if (nightsInput) {
                nightsInput.addEventListener('input', function() {
                    nightsInput.dataset.auto = '0';
                    var autoInput = row.querySelector('[data-stay-auto]');
                    if (autoInput) {
                        autoInput.value = '0';
                    }
                    refresh(false);
                });
            }

            row.querySelectorAll('[data-hotel-cost]').forEach(function(input) {
                input.addEventListener('input', function() {
                    syncCostSharing();
                });
            });

            var removeButton = row.querySelector('[data-remove-hotel-row]');
            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    row.remove();
                    refresh(true);
                });
            }
        }

        function addHotelRow() {
            if (!hotelTemplate || !hotelRowsWrap) {
                return;
            }

            var html = hotelTemplate.innerHTML.replace(/__INDEX__/g, String(nextHotelIndex));
            nextHotelIndex += 1;

            var wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            var row = wrapper.firstElementChild;
            if (!row) {
                return;
            }

            hotelRowsWrap.appendChild(row);
            fillHotelSelect(row, '');
            bindHotelRow(row);
            refresh(true);
        }

        function bindTransportRow(row) {
            var removeButton = row.querySelector('[data-remove-transport-row]');
            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    row.remove();
                });
            }
        }

        function addTransportRow() {
            if (!transportTemplate || !transportRowsWrap) {
                return;
            }

            var html = transportTemplate.innerHTML.replace(/__INDEX__/g, String(nextTransportIndex));
            nextTransportIndex += 1;

            var wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            var row = wrapper.firstElementChild;
            if (!row) {
                return;
            }

            transportRowsWrap.appendChild(row);
            bindTransportRow(row);
        }

        rows().forEach(function(row) {
            fillHotelSelect(row);
            bindHotelRow(row);
        });

        Array.prototype.slice.call(transportRowsWrap ? transportRowsWrap.querySelectorAll('[data-transport-row]') : []).forEach(bindTransportRow);

        if (tripStartInput) {
            tripStartInput.addEventListener('change', function() {
                refresh(true);
            });
        }
        if (tripEndInput) {
            tripEndInput.addEventListener('change', function() {
                refresh(true);
            });
        }

        var addHotelButton = document.getElementById('add-hotel-row');
        if (addHotelButton) {
            addHotelButton.addEventListener('click', addHotelRow);
        }

        var addTransportButton = document.getElementById('add-transport-row');
        if (addTransportButton) {
            addTransportButton.addEventListener('click', addTransportRow);
        }

        var toggleMap = {
            'toggle-hotel': document.getElementById('hotel-section'),
            'toggle-transport': document.getElementById('transport-section')
        };

        Object.keys(toggleMap).forEach(function(id) {
            var checkbox = document.getElementById(id);
            var section = toggleMap[id];
            if (!checkbox || !section) {
                return;
            }

            var apply = function() {
                section.classList.toggle('opacity-60', !checkbox.checked);
            };

            checkbox.addEventListener('change', apply);
            apply();
        });

        var flightTemplateSelect = document.getElementById('flight-template-select');
        var flightIdInput = document.getElementById('flight-id');
        var flightSourceInput = document.getElementById('flight-source');
        var flightFieldNames = [
            'airline', 'flight_no', 'pnr', 'departure_airport', 'arrival_airport',
            'outbound_arrival_at', 'return_airline', 'return_flight_no', 'return_pnr',
            'return_departure_airport', 'return_arrival_airport', 'return_departure_at'
        ];

        if (flightTemplateSelect) {
            flightTemplateSelect.addEventListener('change', function() {
                var option = flightTemplateSelect.options[flightTemplateSelect.selectedIndex];
                if (!option || !option.value) {
                    if (flightIdInput) {
                        flightIdInput.value = '0';
                    }
                    if (flightSourceInput) {
                        flightSourceInput.value = '';
                    }
                    return;
                }

                if (flightIdInput) {
                    flightIdInput.value = option.value;
                }
                if (flightSourceInput) {
                    flightSourceInput.value = 'existing';
                }

                Object.keys(option.dataset).forEach(function(key) {
                    var input = form.querySelector('[name="' + key.replace(/[A-Z]/g, function(letter) {
                        return '_' + letter.toLowerCase();
                    }) + '"]');
                    if (input && option.dataset[key]) {
                        input.value = option.dataset[key];
                    }
                });

                refresh(true);
            });
        }

        flightFieldNames.forEach(function(name) {
            var input = form.querySelector('[name="' + name + '"]');
            if (!input) {
                return;
            }
            input.addEventListener('input', function() {
                if (flightSourceInput) {
                    flightSourceInput.value = 'custom';
                }
            });
        });

        refresh(true);
    })();
</script>
