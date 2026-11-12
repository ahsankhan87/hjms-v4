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
$incZiarat = (int) old('include_ziarat', (string) ($row['include_ziarat'] ?? 0));

$profitMode = (string) old('profit_mode', (string) ($row['profit_mode'] ?? 'amount'));
$profitMode = in_array($profitMode, ['amount', 'percent'], true) ? $profitMode : 'amount';
$profitValue = old('profit_value', (string) ((float) ($row['profit_value'] ?? 0) > 0 ? (float) $row['profit_value'] : ''));
$selectedFlightTemplateId = (int) old('flight_id', (string) (int) ($flight['flight_id'] ?? 0));

$inputClass = 'mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500';
$labelClass = 'block text-[10px] font-bold uppercase tracking-wide text-slate-500';
$cardClass = 'overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm';
$accordionHeadClass = 'flex w-full items-center gap-2.5 px-3 py-2.5 text-left transition hover:bg-slate-50';
$accordionIconClass = 'flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-[11px] text-emerald-600';

$tierKeys = \App\Services\HotelRateService::TIERS;
$tierLabels = is_array($tierLabels ?? null) && $tierLabels !== [] ? $tierLabels : \App\Services\HotelRateService::TIER_LABELS;
$hotelRates = is_array($hotelRates ?? null) ? $hotelRates : [];
$hotelRatesJson = json_encode($hotelRates, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

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

$predefinedMakkahContact = 'INAMULLAH JAN Phone No.00966556590014';
$predefinedMadinaContact = 'MIRZA AZGHAR Phone No.00923007128830';
$predefinedTransportContact = 'NABEEL Phone No.00966563899241';

$renderHotelRow = static function (array $stay, int|string $index) use ($hotelOptions, $inputClass, $labelClass, $tierLabels, $tierKeys): void {
    $currentHotelId = (int) ($stay['hotel_id'] ?? 0);
    $auto = (string) ($stay['auto'] ?? '');
    $nights = (string) ($stay['nights'] ?? '');
    $formatCost = static function ($value): string {
        return (float) $value > 0 ? (string) (float) $value : '';
    };
?>
    <div data-hotel-row class="rounded-lg border border-slate-200 bg-slate-50/60 p-2">
        <div class="flex items-center gap-2">
            <span data-stay-label class="inline-flex shrink-0 items-center rounded-md bg-emerald-600 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">Stay</span>
            <span data-stay-dates class="min-w-0 flex-1 truncate text-[11px] font-medium text-slate-500"></span>
            <button type="button" data-remove-hotel-row class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white text-[10px] text-rose-600 hover:border-rose-300 hover:bg-rose-50" title="Remove stay">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="mt-1.5 grid grid-cols-2 gap-1.5 sm:grid-cols-4">
            <div class="col-span-1">
                <label class="<?= $labelClass ?>">City</label>
                <select name="hotel_stay[<?= $index ?>][city]" data-hotel-city class="<?= $inputClass ?>">
                    <?php foreach (['Makkah', 'Madina'] as $cityOption): ?>
                        <option value="<?= esc($cityOption) ?>" <?= strcasecmp((string) ($stay['city'] ?? ''), $cityOption) === 0 ? 'selected' : '' ?>><?= esc($cityOption) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-span-1 sm:order-2">
                <label class="<?= $labelClass ?>">Nights</label>
                <input type="number" min="0" name="hotel_stay[<?= $index ?>][nights]" value="<?= esc($nights) ?>" data-stay-nights data-auto="<?= $auto !== '' ? '1' : '0' ?>" data-preferred="<?= esc($auto) ?>" class="<?= $inputClass ?>">
                <input type="hidden" name="hotel_stay[<?= $index ?>][auto]" value="<?= esc($auto) ?>" data-stay-auto>
            </div>
            <div class="col-span-2 sm:order-1">
                <label class="<?= $labelClass ?>">Hotel</label>
                <select name="hotel_stay[<?= $index ?>][hotel_id]" data-hotel-select class="<?= $inputClass ?>">
                    <option value="">+ Not listed / new hotel</option>
                    <?php foreach (($hotelOptions ?? []) as $hotelOption): ?>
                        <?php $hotelOptionId = (int) ($hotelOption['id'] ?? 0); ?>
                        <option value="<?= esc((string) $hotelOptionId) ?>" <?= $currentHotelId === $hotelOptionId ? 'selected' : '' ?>>
                            <?= esc((string) ($hotelOption['name'] ?? '')) ?><?= ($hotelOption['city'] ?? '') !== '' ? ' — ' . esc((string) $hotelOption['city']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div data-hotel-name-wrap class="col-span-2 <?= $currentHotelId > 0 ? 'hidden' : '' ?> sm:order-3 sm:col-span-4">
                <label class="<?= $labelClass ?>">Hotel name <span class="font-medium normal-case tracking-normal text-slate-400">— only when the hotel is not in the list</span></label>
                <input name="hotel_stay[<?= $index ?>][hotel_name]" value="<?= esc((string) ($stay['hotel_name'] ?? '')) ?>" placeholder="Type the hotel name" class="<?= $inputClass ?>">
            </div>
        </div>
        <div class="mt-1.5 flex items-center justify-between gap-2">
            <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Room rates (SAR, whole stay)</span>
            <button type="button" data-reload-rates class="inline-flex items-center gap-1 rounded border border-emerald-200 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700 hover:bg-emerald-50">
                <i class="fa-solid fa-rotate text-[9px]"></i><span>Use hotel rates</span>
            </button>
        </div>
        <div class="mt-1 grid grid-cols-3 gap-1.5 sm:grid-cols-5">
            <?php foreach ($tierKeys as $tierKey): ?>
                <div>
                    <span data-tier-label class="block text-[10px] font-semibold uppercase tracking-wide text-slate-400"><?= esc((string) ($tierLabels[$tierKey] ?? $tierKey)) ?></span>
                    <input type="number" step="0.01" min="0"
                        name="hotel_stay[<?= $index ?>][<?= esc($tierKey) ?>_cost]"
                        value="<?= esc($formatCost($stay[$tierKey . '_cost'] ?? 0)) ?>"
                        placeholder="0.00"
                        data-tier="<?= esc($tierKey) ?>"
                        data-hotel-cost class="mt-0.5 w-full rounded-md border border-slate-300 bg-white px-1.5 py-1 text-xs text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            <?php endforeach; ?>
        </div>
        <p data-rate-note class="mt-1 hidden text-[10px] font-medium leading-tight text-slate-500"></p>
        <p data-cost-note class="mt-1 hidden text-[10px] font-medium leading-tight text-sky-700"></p>
        <input type="hidden" name="hotel_stay[<?= $index ?>][stay_id]" value="<?= esc((string) (int) ($stay['stay_id'] ?? 0)) ?>">
    </div>
<?php
};
?>

<main class="space-y-3">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <form method="post" action="<?= esc($formAction) ?>" id="package-form" class="space-y-2.5">
        <?= csrf_field() ?>
        <input type="hidden" name="package_id" value="<?= esc((string) (int) ($row['id'] ?? 0)) ?>">
        <input type="hidden" name="flight_id" id="flight-id" value="<?= esc((string) (int) ($flight['flight_id'] ?? 0)) ?>">
        <input type="hidden" name="flight_source" id="flight-source" value="<?= (int) ($flight['flight_id'] ?? 0) > 0 ? 'existing' : '' ?>">

        <div class="flex items-center justify-between gap-2 px-0.5">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Package setup</p>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-emerald-700">
                <button type="button" data-acc-all="open" class="hover:underline">Expand all</button>
                <span class="text-slate-300">&middot;</span>
                <button type="button" data-acc-all="close" class="hover:underline">Collapse all</button>
            </div>
        </div>

        <!-- 1. flight & trip dates -->
        <section id="flight-section" data-acc="flight" class="<?= $cardClass ?>">
            <button type="button" data-acc-toggle class="<?= $accordionHeadClass ?>">
                <span class="<?= $accordionIconClass ?>"><i class="fa-solid fa-plane-departure"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-slate-800">Flight &amp; trip dates</span>
                    <span class="block truncate text-[11px] text-slate-500">Sets hotel check-in / check-out and the package duration</span>
                </span>
                <span id="chip-flight" class="hidden shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 sm:inline-flex">&mdash;</span>
                <i data-acc-chevron class="fa-solid fa-chevron-down shrink-0 text-[10px] text-slate-400 transition-transform"></i>
            </button>
            <div data-acc-body class="space-y-2 border-t border-slate-200 bg-slate-50/60 px-3 py-3">
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="<?= $labelClass ?>">Outbound departure <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="outbound_departure_at" id="trip-start" value="<?= esc(old('outbound_departure_at', (string) ($flight['departure_at'] ?? ''))) ?>" required class="<?= $inputClass ?>">
                        <p class="mt-0.5 text-[10px] leading-tight text-slate-400">Pakistan &rarr; Saudi Arabia &middot; first hotel check-in</p>
                    </div>
                    <div>
                        <label class="<?= $labelClass ?>">Return arrival <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="return_arrival_at" id="trip-end" value="<?= esc(old('return_arrival_at', (string) ($flight['return_arrival_at'] ?? ''))) ?>" required class="<?= $inputClass ?>">
                        <p class="mt-0.5 text-[10px] leading-tight text-slate-400">Saudi Arabia &rarr; Pakistan &middot; last hotel check-out</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="<?= $labelClass ?>">Duration</label>
                        <div id="trip-summary" class="mt-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800"><?= !empty($row['duration_days']) ? esc((string) (int) $row['duration_days']) . ' day(s) — calculated from the dates above' : 'Enter both dates above.' ?></div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 rounded-lg border border-slate-200 bg-white px-2.5 py-2">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-700">
                        <input type="hidden" name="include_ticket" value="0">
                        <input type="checkbox" name="include_ticket" value="1" id="toggle-ticket" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" <?= $incTicket ? 'checked' : '' ?>>
                        <span>Flight / ticket is part of this package</span>
                    </label>
                    <span id="ticket-off-note" class="text-[10px] font-medium text-slate-400">Flight details hidden &mdash; the dates above are still used for the itinerary.</span>
                </div>

                <div data-ticket-details class="rounded-lg border border-slate-200 bg-white p-2.5">
                    <div class="flex flex-wrap items-end gap-2">
                        <?php if (!empty($flightTemplates)): ?>
                            <div class="min-w-[12rem] flex-1">
                                <label class="<?= $labelClass ?>">Load from Flight Management <span class="font-medium normal-case tracking-normal text-slate-400">(optional)</span></label>
                                <select id="flight-template-select" class="<?= $inputClass ?>">
                                    <option value="">&mdash; enter flight details below &mdash;</option>
                                    <?php foreach ($flightTemplates as $template): ?>
                                        <?php
                                        $templateId = (int) ($template['id'] ?? 0);
                                        $templateOutbound = trim((string) (($template['airline'] ?? '') . ' ' . ($template['flight_no'] ?? '')));
                                        $templateReturn = trim((string) (($template['return_airline'] ?? '') . ' ' . ($template['return_flight_no'] ?? '')));
                                        $templateToLocal = static function ($value): string {
                                            return trim((string) ($value ?? '')) === '' ? '' : date('Y-m-d\TH:i', strtotime((string) $value));
                                        };
                                        $templateFormatDate = static function ($value): string {
                                            $value = trim((string) ($value ?? ''));
                                            if ($value === '') {
                                                return '';
                                            }
                                            $ts = strtotime($value);

                                            return $ts === false ? '' : date('d/m/Y H:i', $ts);
                                        };
                                        $templateOutboundDate = $templateFormatDate($template['departure_at'] ?? '');
                                        $templateReturnDate = $templateFormatDate($template['return_departure_at'] ?? '');
                                        $templateLabel = $templateOutbound !== '' ? $templateOutbound : 'Flight #' . (int) ($template['id'] ?? 0);
                                        if ($templateOutboundDate !== '') {
                                            $templateLabel .= ' · ' . $templateOutboundDate;
                                        }
                                        $templateReturnLabel = trim($templateReturn . ' ' . $templateReturnDate);
                                        if ($templateReturnLabel !== '') {
                                            $templateLabel .= ' · back: ' . $templateReturnLabel;
                                        }
                                        $templateNightsEnd = trim((string) ($template['return_arrival_at'] ?? ''));
                                        if ($templateNightsEnd === '') {
                                            $templateNightsEnd = trim((string) ($template['arrival_at'] ?? ''));
                                        }
                                        $templateNightsStart = trim((string) ($template['departure_at'] ?? ''));
                                        if ($templateNightsStart !== '' && $templateNightsEnd !== '') {
                                            $nightsStartTs = strtotime($templateNightsStart);
                                            $nightsEndTs = strtotime($templateNightsEnd);
                                            if ($nightsStartTs !== false && $nightsEndTs !== false && $nightsEndTs >= $nightsStartTs) {
                                                $templateNights = (int) floor((strtotime(date('Y-m-d', $nightsEndTs)) - strtotime(date('Y-m-d', $nightsStartTs))) / 86400);
                                                if ($templateNights > 0) {
                                                    $templateLabel .= ' · ' . $templateNights . ' night' . ($templateNights === 1 ? '' : 's');
                                                }
                                            }
                                        }
                                        ?>
                                        <option value="<?= esc((string) $templateId) ?>"
                                            <?= $selectedFlightTemplateId === $templateId ? 'selected' : '' ?>
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
                                            data-fare="<?= esc((string) (float) ($template['fare'] ?? 0)) ?>"><?= esc($templateLabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <div class="w-full sm:w-40">
                            <label class="<?= $labelClass ?>">Ticket cost (PKR)</label>
                            <input type="number" step="0.01" min="0" name="ticket_cost" id="ticket-cost" value="<?= esc(old('ticket_cost', (float) ($flight['cost_amount'] ?? 0) > 0 ? (string) (float) $flight['cost_amount'] : '')) ?>" placeholder="e.g. 240000" class="<?= $inputClass ?>">
                        </div>
                    </div>
                    <p class="mt-1 text-[10px] text-slate-400">Only the ticket stays in PKR &mdash; every other package cost is in SAR. <span id="ticket-fare-note" class="hidden font-semibold text-emerald-600"></span></p>

                    <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Departure flight</p>
                    <div class="mt-1 grid grid-cols-2 gap-1.5 sm:grid-cols-3 xl:grid-cols-6">
                        <div>
                            <label class="<?= $labelClass ?>">Airline</label>
                            <input name="airline" value="<?= esc(old('airline', (string) ($flight['airline'] ?? ''))) ?>" placeholder="PIA" class="<?= $inputClass ?>">
                        </div>
                        <div>
                            <label class="<?= $labelClass ?>">Flight No</label>
                            <input name="flight_no" value="<?= esc(old('flight_no', (string) ($flight['flight_no'] ?? ''))) ?>" placeholder="PK-741" class="<?= $inputClass ?>">
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
                        <div class="col-span-2 sm:col-span-1">
                            <label class="<?= $labelClass ?>">Arrival</label>
                            <input type="datetime-local" name="outbound_arrival_at" value="<?= esc(old('outbound_arrival_at', (string) ($flight['arrival_at'] ?? ''))) ?>" class="<?= $inputClass ?>">
                        </div>
                    </div>

                    <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Return flight</p>
                    <div class="mt-1 grid grid-cols-2 gap-1.5 sm:grid-cols-3 xl:grid-cols-6">
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
                        <div class="col-span-2 sm:col-span-1">
                            <label class="<?= $labelClass ?>">Departure</label>
                            <input type="datetime-local" name="return_departure_at" value="<?= esc(old('return_departure_at', (string) ($flight['return_departure_at'] ?? ''))) ?>" class="<?= $inputClass ?>">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. hotel stays -->
        <section id="hotel-section" data-acc="hotels" class="<?= $cardClass ?>">
            <button type="button" data-acc-toggle class="<?= $accordionHeadClass ?>">
                <span class="<?= $accordionIconClass ?>"><i class="fa-solid fa-hotel"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-slate-800">Hotel stays</span>
                    <span class="block truncate text-[11px] text-slate-500">Makkah &rarr; Madina &rarr; Makkah, dates chained from the flight</span>
                </span>
                <span id="chip-hotels" class="hidden shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 sm:inline-flex">&mdash;</span>
                <i data-acc-chevron class="fa-solid fa-chevron-down shrink-0 text-[10px] text-slate-400 transition-transform"></i>
            </button>
            <div data-acc-body class="space-y-2 border-t border-slate-200 bg-slate-50/60 px-3 py-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-700">
                        <input type="hidden" name="include_hotel" value="0">
                        <input type="checkbox" name="include_hotel" value="1" id="toggle-hotel" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" <?= $incHotel ? 'checked' : '' ?>>
                        <span>Hotel is part of this package</span>
                    </label>
                    <button type="button" id="add-hotel-row" class="inline-flex h-7 items-center gap-1 rounded-lg border border-emerald-200 bg-white px-2 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-50">
                        <i class="fa-solid fa-plus text-[10px]"></i><span>Add stay</span>
                    </button>
                </div>
                <div data-hotel-body class="space-y-2">
                    <div id="hotel-stay-rows" class="space-y-2">
                        <?php foreach ($stayRows as $index => $stay): ?>
                            <?php $renderHotelRow((array) $stay, (int) $index); ?>
                        <?php endforeach; ?>
                    </div>
                    <div id="stay-balance" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] font-medium text-slate-600">Stay dates are calculated once the flight dates are entered.</div>
                </div>
            </div>
        </section>

        <!-- 3. transport + ziarat -->
        <section id="transport-section" data-acc="transport" class="<?= $cardClass ?>">
            <button type="button" data-acc-toggle class="<?= $accordionHeadClass ?>">
                <span class="<?= $accordionIconClass ?>"><i class="fa-solid fa-bus"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-slate-800">Transport &amp; ziarat</span>
                    <span class="block truncate text-[11px] text-slate-500">Pick from the master list or type a new one</span>
                </span>
                <span id="chip-transport" class="hidden shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 sm:inline-flex">&mdash;</span>
                <i data-acc-chevron class="fa-solid fa-chevron-down shrink-0 text-[10px] text-slate-400 transition-transform"></i>
            </button>
            <div data-acc-body class="space-y-2 border-t border-slate-200 bg-slate-50/60 px-3 py-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-700">
                        <input type="hidden" name="include_transport" value="0">
                        <input type="checkbox" name="include_transport" value="1" id="toggle-transport" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" <?= $incTransport ? 'checked' : '' ?>>
                        <span>Transport is part of this package</span>
                    </label>
                    <button type="button" id="add-transport-row" class="inline-flex h-7 items-center gap-1 rounded-lg border border-emerald-200 bg-white px-2 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-50">
                        <i class="fa-solid fa-plus text-[10px]"></i><span>Add transport</span>
                    </button>
                </div>

                <div id="transport-rows" class="space-y-1.5">
                    <?php foreach ($transportInputRows as $index => $transportRow): ?>
                        <?php $transportSelected = (int) ($transportRow['transport_id'] ?? 0) > 0; ?>
                        <div data-transport-row class="grid grid-cols-4 items-end gap-1.5 rounded-lg border border-slate-200 bg-slate-50/60 p-2 sm:flex sm:flex-wrap">
                            <div class="col-span-4 sm:min-w-[9rem] sm:flex-1">
                                <label class="<?= $labelClass ?>">Transport</label>
                                <select name="transport_row[<?= (int) $index ?>][transport_id]" data-transport-select class="<?= $inputClass ?>">
                                    <option value="">+ Not listed / new transport</option>
                                    <?php foreach (($transportOptions ?? []) as $transportOption): ?>
                                        <?php $transportOptionId = (int) ($transportOption['id'] ?? 0); ?>
                                        <option value="<?= esc((string) $transportOptionId) ?>" <?= (int) ($transportRow['transport_id'] ?? 0) === $transportOptionId ? 'selected' : '' ?>>
                                            <?= esc((string) ($transportOption['transport_name'] ?? '')) ?> — <?= esc((string) ($transportOption['provider_name'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div data-transport-name-wrap class="col-span-4 sm:min-w-[8rem] sm:flex-1 <?= $transportSelected ? 'hidden' : '' ?>">
                                <label class="<?= $labelClass ?>">New name</label>
                                <input name="transport_row[<?= (int) $index ?>][transport_name]" value="<?= esc((string) ($transportRow['transport_name'] ?? '')) ?>" placeholder="If not listed" class="<?= $inputClass ?>">
                            </div>
                            <div class="col-span-1 sm:w-[4.5rem]">
                                <label class="<?= $labelClass ?>">Seats</label>
                                <input type="number" min="0" name="transport_row[<?= (int) $index ?>][seat_capacity]" value="<?= esc((string) ($transportRow['seat_capacity'] ?? '')) ?>" class="<?= $inputClass ?>">
                            </div>
                            <div class="col-span-2 sm:w-[5.5rem]">
                                <label class="<?= $labelClass ?>">Cost SAR</label>
                                <input type="number" step="0.01" min="0" name="transport_row[<?= (int) $index ?>][cost_amount]" value="<?= esc((string) ($transportRow['cost_amount'] ?? '')) ?>" class="<?= $inputClass ?>">
                            </div>
                            <button type="button" data-remove-transport-row class="col-span-1 inline-flex h-8 w-full items-center justify-center sm:w-8 sm:shrink-0 rounded-md border border-slate-200 bg-white text-[10px] text-rose-600 hover:border-rose-300 hover:bg-rose-50" title="Remove transport">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="rounded-lg border border-amber-200 bg-amber-50/70 px-2.5 py-2">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                        <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-amber-100 text-[10px] text-amber-700"><i class="fa-solid fa-kaaba"></i></span>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-amber-900">
                            <input type="hidden" name="include_ziarat" value="0">
                            <input type="checkbox" name="include_ziarat" value="1" id="toggle-ziarat" class="h-4 w-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500" <?= $incZiarat ? 'checked' : '' ?>>
                            <span>Ziarat included</span>
                        </label>
                        <span class="text-[10px] text-amber-700">Visit of the holy sites &mdash; added to the package price.</span>
                        <div id="ziarat-fields" class="flex items-center gap-1.5">
                            <input type="number" step="0.01" min="0" name="ziarat_cost" value="<?= esc(old('ziarat_cost', (string) ((float) ($row['ziarat_cost'] ?? 0) > 0 ? (float) $row['ziarat_cost'] : ''))) ?>" placeholder="150" class="w-24 rounded-lg border border-amber-300 bg-white px-2 py-1 text-sm text-slate-800 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            <span class="text-[10px] font-bold uppercase tracking-wide text-amber-700">SAR</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. package details -->
        <section id="details-section" data-acc="details" class="<?= $cardClass ?>">
            <button type="button" data-acc-toggle class="<?= $accordionHeadClass ?>">
                <span class="<?= $accordionIconClass ?>"><i class="fa-solid fa-tags"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-slate-800">Package details</span>
                    <span class="block truncate text-[11px] text-slate-500">Code, seats, visa, exchange rate, profit and notes</span>
                </span>
                <span id="chip-details" class="hidden shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 sm:inline-flex">&mdash;</span>
                <i data-acc-chevron class="fa-solid fa-chevron-down shrink-0 text-[10px] text-slate-400 transition-transform"></i>
            </button>
            <div data-acc-body class="space-y-2 border-t border-slate-200 bg-slate-50/60 px-3 py-3">
                <div class="grid grid-cols-2 gap-1.5 lg:grid-cols-6">
                    <div>
                        <label class="<?= $labelClass ?>">Code <span class="text-rose-500">*</span></label>
                        <input name="code" value="<?= esc(old('code', (string) ($row['code'] ?? ''))) ?>" required placeholder="UMR-APR-2026" pattern="^\S+$" title="Package code cannot contain spaces." class="<?= $inputClass ?>">
                        <p class="mt-0.5 text-[10px] text-slate-400">No spaces allowed. Use dash or underscore.</p>
                    </div>
                    <div class="col-span-1 lg:col-span-3">
                        <label class="<?= $labelClass ?>">Name <span class="text-rose-500">*</span></label>
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
                        <label class="<?= $labelClass ?>">Status</label>
                        <select name="is_active" class="<?= $inputClass ?>">
                            <?php $isActive = (string) old('is_active', (string) ($row['is_active'] ?? 1)); ?>
                            <option value="1" <?= $isActive === '1' ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= $isActive === '0' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-1.5 lg:grid-cols-6">
                    <div>
                        <label class="<?= $labelClass ?>">Seats <span class="text-rose-500">*</span></label>
                        <input type="number" min="1" name="total_seats" id="package-total-seats" value="<?= esc(old('total_seats', (string) (int) ($row['total_seats'] ?? 0) > 0 ? (string) (int) $row['total_seats'] : '')) ?>" required placeholder="45" class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label class="<?= $labelClass ?>">Visa cost (SAR)</label>
                        <input type="number" step="0.01" min="0" name="purchase_price_visa" value="<?= esc(old('purchase_price_visa', (string) ($row['purchase_price_visa'] ?? ''))) ?>" placeholder="575" class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label class="<?= $labelClass ?>">SAR &rarr; PKR <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.0001" min="0.0001" name="exchange_rate" id="exchange-rate" value="<?= esc(old('exchange_rate', (string) ($exchangeRate ?? 1))) ?>" required placeholder="74.5" class="<?= $inputClass ?>">
                    </div>
                    <div class="col-span-2 lg:col-span-3">
                        <label class="<?= $labelClass ?>">Internal notes</label>
                        <input name="notes" value="<?= esc(old('notes', (string) ($row['notes'] ?? ''))) ?>" class="<?= $inputClass ?>">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-1.5 lg:grid-cols-6">
                    <div>
                        <label class="<?= $labelClass ?>">Profit type</label>
                        <select name="profit_mode" id="profit-mode" class="<?= $inputClass ?>">
                            <option value="amount" <?= $profitMode === 'amount' ? 'selected' : '' ?>>Fixed amount (PKR)</option>
                            <option value="percent" <?= $profitMode === 'percent' ? 'selected' : '' ?>>Percentage (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="<?= $labelClass ?>">Company profit <span data-profit-unit class="font-medium normal-case tracking-normal text-slate-400"><?= $profitMode === 'percent' ? '(%)' : '(PKR)' ?></span></label>
                        <input type="number" step="0.01" min="0" name="profit_value" id="profit-value" value="<?= esc((string) $profitValue) ?>" placeholder="<?= $profitMode === 'percent' ? '10' : '25000' ?>" class="<?= $inputClass ?>">
                    </div>
                    <div class="col-span-2 lg:col-span-4">
                        <p class="mt-4 text-[10px] text-slate-400">Profit per pilgrim, added to the package price like visa, transport or ziarat &mdash; either a fixed PKR amount or a percentage of the total cost.</p>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400">Seats are sold once per package and used up by confirmed bookings. Hotel, transport, visa and ziarat are SAR and converted with the rate above; the ticket and the company profit stay in PKR.</p>
            </div>
        </section>

        <!-- 5. voucher -->
        <section id="voucher-section" data-acc="voucher" class="<?= $cardClass ?>">
            <button type="button" data-acc-toggle class="<?= $accordionHeadClass ?>">
                <span class="<?= $accordionIconClass ?>"><i class="fa-solid fa-file-lines"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-slate-800">Voucher &amp; instructions</span>
                    <span class="block truncate text-[11px] text-slate-500">Optional &mdash; shirka, contacts and printed instructions</span>
                </span>
                <span id="chip-voucher" class="hidden shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 sm:inline-flex">Optional</span>
                <i data-acc-chevron class="fa-solid fa-chevron-down shrink-0 text-[10px] text-slate-400 transition-transform"></i>
            </button>
            <div data-acc-body class="space-y-2 border-t border-slate-200 bg-slate-50/60 px-3 py-3">
                <div class="grid gap-1.5 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="sm:col-span-2 lg:col-span-1">
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
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="<?= $labelClass ?>">Voucher instructions (Urdu)</label>
                        <textarea name="voucher_instructions_ur" rows="3" dir="rtl" class="<?= $inputClass ?>"><?= esc(old('voucher_instructions_ur', (string) ($row['voucher_instructions_ur'] ?? '') !== '' ? $row['voucher_instructions_ur'] : $predefinedInstructionUr)) ?></textarea>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="<?= $labelClass ?>">Voucher instructions (English)</label>
                        <textarea name="voucher_instructions_en" rows="2" class="<?= $inputClass ?>"><?= esc(old('voucher_instructions_en', (string) ($row['voucher_instructions_en'] ?? ''))) ?></textarea>
                    </div>
                </div>
            </div>
        </section>

        <div class="sticky bottom-0 z-20 -mx-3 border-t border-slate-200 bg-white/95 px-3 py-2 shadow-[0_-6px_16px_-8px_rgba(15,23,42,0.25)] backdrop-blur md:-mx-6 md:px-6">
            <div class="flex items-center gap-2">
                <p class="hidden min-w-0 flex-1 truncate text-[11px] text-slate-500 lg:block">Flight dates drive the hotel dates, nights and duration automatically.</p>
                <div class="flex flex-1 items-center justify-end gap-2 lg:flex-none">
                    <a href="<?= site_url('packages') ?>" class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-arrow-left"></i><span>Cancel</span></a>
                    <button type="submit" class="inline-flex h-9 flex-1 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 sm:flex-none">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span><?= $isEdit ? 'Save changes' : 'Create package' ?></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</main>

<template id="hotel-row-template">
    <?php
    $templateStay = [
        'stay_id' => 0,
        'city' => 'Makkah',
        'hotel_id' => 0,
        'hotel_name' => '',
        'nights' => '',
        'auto' => 'rest',
    ];
    foreach ($tierKeys as $tierKey) {
        $templateStay[$tierKey . '_cost'] = '';
    }
    $renderHotelRow($templateStay, '__INDEX__');
    ?>
</template>

<template id="transport-row-template">
    <div data-transport-row class="grid grid-cols-4 items-end gap-1.5 rounded-lg border border-slate-200 bg-slate-50/60 p-2 sm:flex sm:flex-wrap">
        <div class="col-span-4 sm:min-w-[9rem] sm:flex-1">
            <label class="<?= $labelClass ?>">Transport</label>
            <select name="transport_row[__INDEX__][transport_id]" data-transport-select class="<?= $inputClass ?>">
                <option value="">+ Not listed / new transport</option>
                <?php foreach (($transportOptions ?? []) as $transportOption): ?>
                    <option value="<?= esc((string) (int) ($transportOption['id'] ?? 0)) ?>"><?= esc((string) ($transportOption['transport_name'] ?? '')) ?> — <?= esc((string) ($transportOption['provider_name'] ?? '')) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div data-transport-name-wrap class="col-span-4 sm:min-w-[8rem] sm:flex-1">
            <label class="<?= $labelClass ?>">New name</label>
            <input name="transport_row[__INDEX__][transport_name]" placeholder="If not listed" class="<?= $inputClass ?>">
        </div>
        <div class="col-span-1 sm:w-[4.5rem]">
            <label class="<?= $labelClass ?>">Seats</label>
            <input type="number" min="0" name="transport_row[__INDEX__][seat_capacity]" class="<?= $inputClass ?>">
        </div>
        <div class="col-span-2 sm:w-[5.5rem]">
            <label class="<?= $labelClass ?>">Cost SAR</label>
            <input type="number" step="0.01" min="0" name="transport_row[__INDEX__][cost_amount]" class="<?= $inputClass ?>">
        </div>
        <button type="button" data-remove-transport-row class="col-span-1 inline-flex h-8 w-full items-center justify-center sm:w-8 sm:shrink-0 rounded-md border border-slate-200 bg-white text-[10px] text-rose-600 hover:border-rose-300 hover:bg-rose-50" title="Remove transport">
            <i class="fa-solid fa-xmark"></i>
        </button>
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
        var hotelRates = <?= $hotelRatesJson ?>;
        var tierKeys = <?= json_encode($tierKeys) ?>;
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

        function formatDisplayDate(date) {
            return pad(date.getDate()) + '-' + pad(date.getMonth() + 1) + '-' + date.getFullYear();
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
            return {
                start: start,
                end: end,
                days: diffDays(start, end)
            };
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
            placeholder.textContent = '+ Not listed / new hotel';
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

        function rowNights(row) {
            var nightsInput = rowField(row, 'nights');
            var nights = nightsInput ? Math.max(0, parseInt(nightsInput.value || '0', 10)) : 0;

            if (nights > 0) {
                return nights;
            }

            var window = tripWindow();
            var index = rows().indexOf(row);
            if (window && index === 0) {
                return window.days;
            }

            return 0;
        }

        function effectiveStayNights(row) {
            var plan = stayPlan(tripWindow());
            var entry = plan[rows().indexOf(row)];

            return entry ? entry.nights : rowNights(row);
        }

        /**
         * Nights a hotel rate is multiplied by. The stay's own Nights field wins —
         * the chained plan extends the last filled stay to the trip end, which would
         * inflate a whole-stay cost while the other stays are still being filled in.
         */
        function rateNights(row) {
            var nightsInput = rowField(row, 'nights');
            var nights = nightsInput ? Math.max(0, parseInt(nightsInput.value || '0', 10)) : 0;

            return nights > 0 ? nights : effectiveStayNights(row);
        }

        /**
         * Loads the hotel-module rate for a stay row and converts it to the
         * whole-stay cost that the package stores (rate per person per night × nights).
         */
        function applyHotelRates(row, force) {
            var select = rowField(row, 'hotel_id');
            var note = row.querySelector('[data-rate-note]');
            var hotelId = select ? String(select.value || '') : '';
            var rates = hotelId !== '' ? (hotelRates[hotelId] || null) : null;
            var nights = rateNights(row);

            if (!rates) {
                if (note && hotelId !== '') {
                    note.textContent = 'No room rates are set for this hotel in the Hotel module yet — entering one here will not update the hotel master rates.';
                    note.classList.remove('hidden');
                } else if (note) {
                    note.classList.add('hidden');
                    note.textContent = '';
                }
                return;
            }

            var hasAnyRate = tierKeys.some(function(tier) {
                return Number(rates[tier] || 0) > 0;
            });

            if (!hasAnyRate) {
                if (note) {
                    note.textContent = 'This hotel has no rates saved in the Hotel module yet — enter the rates for this stay manually.';
                    note.classList.remove('hidden');
                }
                return;
            }

            var applied = [];
            row.querySelectorAll('[data-hotel-cost]').forEach(function(input) {
                var tier = input.dataset.tier || '';
                var rate = Number(rates[tier] || 0);

                if (!force && String(input.value || '').trim() !== '') {
                    return;
                }

                if (rate <= 0) {
                    return;
                }

                var total = nights > 0 ? rate * nights : rate;
                input.value = String(Math.round(total * 100) / 100);

                var labelEl = input.parentElement ? input.parentElement.querySelector('[data-tier-label]') : null;
                var labelText = labelEl && labelEl.textContent.trim() !== '' ? labelEl.textContent.trim() : tier;
                applied.push(labelText + ' ' + Number(rate).toLocaleString() + (nights > 0 ? ' × ' + nights + 'n' : ''));
            });

            if (note) {
                if (applied.length > 0) {
                    note.textContent = 'Rates loaded from the hotel module (SAR, per person / night): ' + applied.join(', ') + '. You can override any value.';
                    note.classList.remove('hidden');
                } else {
                    note.classList.add('hidden');
                    note.textContent = '';
                }
            }

            if (applied.length > 0) {
                row.dataset.ratesAuto = '1';
                row.dataset.costManual = '';
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

        function stayNightsValue(row) {
            var nightsInput = rowField(row, 'nights');

            return nightsInput ? Math.max(0, parseInt(nightsInput.value || '0', 10)) : 0;
        }

        function syncCostSharing() {
            var seen = {};

            rows().forEach(function(row) {
                var key = hotelKey(row);
                if (key === '') {
                    setCostLock(row, false, '');
                    return;
                }

                // Hotel-module rates and manual overrides already follow this stay's own
                // nights, so copying another stay's totals would make them wrong.
                if (row.dataset.ratesAuto === '1' || row.dataset.costManual === '1') {
                    setCostLock(row, false, '');
                    if (!Object.prototype.hasOwnProperty.call(seen, key)) {
                        seen[key] = row;
                    }
                    return;
                }

                if (Object.prototype.hasOwnProperty.call(seen, key)) {
                    var sourceRow = seen[key];
                    var sourceNights = stayNightsValue(sourceRow);
                    var targetNights = stayNightsValue(row);

                    row.querySelectorAll('[data-hotel-cost]').forEach(function(input) {
                        var name = input.getAttribute('name') || '';
                        var suffix = name.substring(name.lastIndexOf('['));
                        var sourceInput = sourceRow.querySelector('[data-hotel-cost][name$="' + suffix + '"]');
                        if (!sourceInput) {
                            return;
                        }

                        var sourceValue = parseFloat(String(sourceInput.value || '').replace(/,/g, ''));

                        // Same hotel: carry the per-night rate over, then scale it to this stay.
                        if (!Number.isFinite(sourceValue) || sourceNights <= 0 || targetNights <= 0 || sourceNights === targetNights) {
                            input.value = sourceInput.value;
                            return;
                        }

                        input.value = String(Math.round(sourceValue / sourceNights * targetNights * 100) / 100);
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

        /**
         * The last stay absorbs whatever nights are left over. Packages saved with
         * fixed nights have no remainder row, so promote the last row when the user
         * edits an earlier stay and nothing is left to auto-fill.
         */
        function ensureRemainderRow(editedRow) {
            var all = rows();
            if (all.length < 2) {
                return;
            }

            var lastRow = all[all.length - 1];
            if (lastRow === editedRow) {
                return;
            }

            var hasAutoRow = all.some(function(row) {
                var nightsInput = rowField(row, 'nights');

                return nightsInput && nightsInput.dataset.auto === '1';
            });

            if (hasAutoRow) {
                return;
            }

            var lastNights = rowField(lastRow, 'nights');
            var lastAuto = lastRow.querySelector('[data-stay-auto]');

            if (lastNights) {
                lastNights.dataset.auto = '1';
                lastNights.dataset.preferred = 'rest';
            }
            if (lastAuto) {
                lastAuto.value = 'rest';
            }
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

            // Over-allocated while the user is still typing: keep their numbers and
            // let the balance message explain the overrun.
            if (remaining < 0) {
                return;
            }

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

        /**
         * Mirrors the server-side planner: rows with a hotel and nights are chained
         * from the trip start, trimmed to the trip end, and the last valid stay
         * absorbs any remaining nights.
         *
         * @returns {Object<string, {checkIn: Date, checkOut: Date, nights: number, extended: boolean}>}
         */
        function stayPlan(window) {
            var plan = {};

            if (!window) {
                return plan;
            }

            var valid = rows().filter(function(row) {
                var nightsInput = rowField(row, 'nights');
                var nights = nightsInput ? Math.max(0, parseInt(nightsInput.value || '0', 10)) : 0;
                var hotel = rowField(row, 'hotel_id');
                var nameInput = rowField(row, 'hotel_name');
                var hasHotel = (hotel && String(hotel.value || '') !== '') || (nameInput && String(nameInput.value || '').trim() !== '');

                return nights > 0 && hasHotel;
            });

            if (valid.length === 0) {
                return plan;
            }

            var cursor = new Date(window.start.getTime());
            var lastKey = '';

            valid.forEach(function(row) {
                var nightsInput = rowField(row, 'nights');
                var nights = Math.max(0, parseInt(nightsInput.value || '0', 10));
                var checkOut = addDays(cursor, nights);

                if (checkOut.getTime() > window.end.getTime()) {
                    checkOut = new Date(window.end.getTime());
                }

                if (checkOut.getTime() <= cursor.getTime()) {
                    return;
                }

                var index = rows().indexOf(row);
                plan[index] = {
                    checkIn: new Date(cursor.getTime()),
                    checkOut: checkOut,
                    nights: diffDays(cursor, checkOut),
                    extended: false
                };

                lastKey = String(index);
                cursor = checkOut;
            });

            if (lastKey !== '' && cursor.getTime() < window.end.getTime()) {
                plan[lastKey].checkOut = new Date(window.end.getTime());
                plan[lastKey].nights = diffDays(plan[lastKey].checkIn, plan[lastKey].checkOut);
                plan[lastKey].extended = true;
            }

            return plan;
        }

        function paintStays() {
            var window = tripWindow();
            var plan = stayPlan(window);
            var rawNights = 0;

            rows().forEach(function(row, index) {
                row.dataset.stayNumber = String(index + 1);
                var label = row.querySelector('[data-stay-label]');
                if (label) {
                    label.textContent = 'Stay ' + (index + 1);
                }

                var nightsInput = rowField(row, 'nights');
                rawNights += nightsInput ? Math.max(0, parseInt(nightsInput.value || '0', 10)) : 0;

                var dates = row.querySelector('[data-stay-dates]');
                if (!dates) {
                    return;
                }

                var entry = plan[index];
                if (!entry) {
                    dates.textContent = '';
                    return;
                }

                dates.textContent = formatDisplayDate(entry.checkIn) + ' → ' + formatDisplayDate(entry.checkOut) +
                    ' (' + entry.nights + ' night' + (entry.nights === 1 ? '' : 's') +
                    (entry.extended ? ', extended to trip end' : '') + ')';
            });

            if (!stayBalance) {
                return;
            }

            if (!window) {
                stayBalance.textContent = 'Stay dates will be calculated once the outbound departure and return arrival dates are entered.';
                stayBalance.className = 'rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] font-medium text-slate-600';
                return;
            }

            if (rawNights > window.days) {
                stayBalance.textContent = 'Stays add up to ' + rawNights + ' night(s) but the trip is only ' + window.days + ' night(s) (' + formatDate(window.start) + ' → ' + formatDate(window.end) + '). Extra nights are trimmed to the trip window on save.';
                stayBalance.className = 'rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-[11px] font-medium text-rose-700';
                return;
            }

            if (rawNights < window.days) {
                stayBalance.textContent = (window.days - rawNights) + ' night(s) of the ' + window.days + '-night trip are not allocated yet. The last stay will be extended to ' + formatDate(window.end) + '.';
                stayBalance.className = 'rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-medium text-amber-700';
                return;
            }

            stayBalance.textContent = 'All ' + window.days + ' night(s) allocated: ' + formatDate(window.start) + ' → ' + formatDate(window.end) + '.';
            stayBalance.className = 'rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] font-medium text-emerald-700';
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

            rows().forEach(function(row) {
                if (row.dataset.ratesAuto === '1') {
                    applyHotelRates(row, true);
                }
            });

            syncCostSharing();
            paintChips();
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
                    syncHotelNameWrap(row);
                    applyHotelRates(row, true);
                    refresh(false);
                });
            }

            var reloadRatesButton = row.querySelector('[data-reload-rates]');
            if (reloadRatesButton) {
                reloadRatesButton.addEventListener('click', function() {
                    applyHotelRates(row, true);
                    syncCostSharing();
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

                    // Editing an earlier stay re-fills the last stay with whatever nights are left.
                    var allRows = rows();
                    var isLastRow = allRows[allRows.length - 1] === row;
                    if (!isLastRow) {
                        ensureRemainderRow(row);
                    }

                    refresh(!isLastRow);
                });
            }

            row.querySelectorAll('[data-hotel-cost]').forEach(function(input) {
                input.addEventListener('input', function() {
                    row.dataset.ratesAuto = '';
                    row.dataset.costManual = '1';
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
                    paintChips();
                });
            }

            var select = row.querySelector('[data-transport-select]');
            if (select) {
                select.addEventListener('change', function() {
                    syncTransportNameWrap(row);
                });
            }

            var costInput = row.querySelector('[name$="[cost_amount]"]');
            if (costInput) {
                costInput.addEventListener('input', paintChips);
            }

            syncTransportNameWrap(row);
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
            paintChips();
        }

        rows().forEach(function(row) {
            fillHotelSelect(row);
            bindHotelRow(row);
            applyHotelRates(row, false);
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

        /* ---------- show / hide ---------- */
        function setHidden(el, hide) {
            if (el) {
                el.classList.toggle('hidden', !!hide);
            }
        }

        function syncHotelNameWrap(row) {
            var select = rowField(row, 'hotel_id');
            var wrap = row.querySelector('[data-hotel-name-wrap]');
            var nameInput = rowField(row, 'hotel_name');
            var hasHotel = select ? String(select.value || '') !== '' : false;

            if (hasHotel && nameInput) {
                nameInput.value = '';
            }

            setHidden(wrap, hasHotel);
        }

        function syncTransportNameWrap(row) {
            var select = row.querySelector('[data-transport-select]');
            setHidden(row.querySelector('[data-transport-name-wrap]'), select ? String(select.value || '') !== '' : false);
        }

        /* ---------- collapsible sections ---------- */
        var accSections = Array.prototype.slice.call(form.querySelectorAll('[data-acc]'));
        var accStorageKey = 'hjms:package-form:sections';
        var accDefaults = {
            flight: true,
            hotels: true,
            transport: false,
            details: true,
            voucher: false
        };
        var forceOpenSections = <?= (! empty($errors) || ! empty($error)) ? 'true' : 'false' ?>;
        var accStored = {};

        try {
            accStored = JSON.parse(window.localStorage.getItem(accStorageKey) || '{}') || {};
        } catch (e) {
            accStored = {};
        }

        function accSet(section, open, persist) {
            setHidden(section.querySelector('[data-acc-body]'), !open);

            var chevron = section.querySelector('[data-acc-chevron]');
            if (chevron) {
                chevron.classList.toggle('rotate-180', open);
            }

            section.dataset.accOpen = open ? '1' : '0';

            if (persist) {
                accStored[section.dataset.acc] = open;
                try {
                    window.localStorage.setItem(accStorageKey, JSON.stringify(accStored));
                } catch (e) {
                    /* storage unavailable */
                }
            }
        }

        accSections.forEach(function(section) {
            var name = section.dataset.acc || '';
            var open = forceOpenSections || (Object.prototype.hasOwnProperty.call(accStored, name) ? !!accStored[name] : accDefaults[name] !== false);
            accSet(section, open, false);

            var toggle = section.querySelector('[data-acc-toggle]');
            if (toggle) {
                toggle.addEventListener('click', function() {
                    accSet(section, section.dataset.accOpen !== '1', true);
                });
            }
        });

        Array.prototype.slice.call(form.querySelectorAll('[data-acc-all]')).forEach(function(button) {
            button.addEventListener('click', function() {
                var open = button.dataset.accAll === 'open';
                accSections.forEach(function(section) {
                    accSet(section, open, true);
                });
            });
        });

        var ticketToggle = document.getElementById('toggle-ticket');
        var ticketDetails = form.querySelector('[data-ticket-details]');
        var ticketOffNote = document.getElementById('ticket-off-note');
        var hotelToggle = document.getElementById('toggle-hotel');
        var hotelBody = form.querySelector('[data-hotel-body]');
        var transportToggle = document.getElementById('toggle-transport');
        var transportBody = form.querySelector('[data-transport-body]');
        var ziaratToggle = document.getElementById('toggle-ziarat');
        var ziaratFields = document.getElementById('ziarat-fields');

        function applyIncludeToggles() {
            setHidden(ticketDetails, ticketToggle ? !ticketToggle.checked : false);
            setHidden(ticketOffNote, ticketToggle ? ticketToggle.checked : true);
            setHidden(hotelBody, hotelToggle ? !hotelToggle.checked : false);
            setHidden(transportBody, transportToggle ? !transportToggle.checked : false);
            setHidden(ziaratFields, ziaratToggle ? !ziaratToggle.checked : false);
        }

        [ticketToggle, hotelToggle, transportToggle, ziaratToggle].forEach(function(toggle) {
            if (!toggle) {
                return;
            }
            toggle.addEventListener('change', function() {
                applyIncludeToggles();
                paintChips();
            });
        });

        applyIncludeToggles();

        /* ---------- profit type (fixed amount vs percentage) ---------- */
        var profitModeSelect = document.getElementById('profit-mode');
        var profitValueInput = document.getElementById('profit-value');
        var profitUnit = document.querySelector('[data-profit-unit]');

        function applyProfitMode() {
            var isPercent = profitModeSelect && profitModeSelect.value === 'percent';

            if (profitUnit) {
                profitUnit.textContent = isPercent ? '(%)' : '(PKR)';
            }
            if (profitValueInput) {
                profitValueInput.placeholder = isPercent ? '10' : '25000';
            }
        }

        if (profitModeSelect) {
            profitModeSelect.addEventListener('change', applyProfitMode);
            applyProfitMode();
        }

        /* ---------- live section chips ---------- */
        function setChip(id, text, tone) {
            var chip = document.getElementById(id);
            if (!chip) {
                return;
            }

            var tones = {
                slate: 'bg-slate-100 text-slate-600',
                emerald: 'bg-emerald-100 text-emerald-700',
                amber: 'bg-amber-100 text-amber-700',
                rose: 'bg-rose-100 text-rose-700'
            };

            chip.textContent = text;
            chip.className = 'hidden shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold sm:inline-flex ' + (tones[tone] || tones.slate);
        }

        function numberOrZero(value) {
            var parsed = parseFloat(String(value || '').replace(/,/g, ''));

            return Number.isFinite(parsed) ? parsed : 0;
        }

        function paintChips() {
            var trip = tripWindow();
            setChip('chip-flight', trip ? trip.days + ' days' : 'Dates missing', trip ? 'emerald' : 'rose');

            var stayCount = 0;
            var stayNights = 0;

            rows().forEach(function(row) {
                var nightsInput = rowField(row, 'nights');
                var hotel = rowField(row, 'hotel_id');
                var nameInput = rowField(row, 'hotel_name');
                var nights = nightsInput ? Math.max(0, parseInt(nightsInput.value || '0', 10)) : 0;
                var hasHotel = (hotel && String(hotel.value || '') !== '') || (nameInput && String(nameInput.value || '').trim() !== '');

                if (nights > 0 && hasHotel) {
                    stayCount += 1;
                    stayNights += nights;
                }
            });

            if (hotelToggle && !hotelToggle.checked) {
                setChip('chip-hotels', 'Not included', 'slate');
            } else if (stayCount > 0) {
                setChip('chip-hotels', stayCount + ' stay' + (stayCount === 1 ? '' : 's') + ' · ' + stayNights + ' nights', 'emerald');
            } else {
                setChip('chip-hotels', 'No stays yet', 'amber');
            }

            var transportTotal = 0;
            Array.prototype.slice.call(form.querySelectorAll('[data-transport-row] [name$="[cost_amount]"]')).forEach(function(input) {
                transportTotal += numberOrZero(input.value);
            });

            if (transportToggle && !transportToggle.checked) {
                setChip('chip-transport', 'Not included', 'slate');
            } else if (transportTotal > 0) {
                setChip('chip-transport', 'SAR ' + transportTotal.toLocaleString(), 'emerald');
            } else {
                setChip('chip-transport', 'No transport', 'amber');
            }

            var codeInput = form.querySelector('[name="code"]');
            var seatsInput = document.getElementById('package-total-seats');
            var codeValue = codeInput ? String(codeInput.value || '').trim() : '';
            var seatsValue = seatsInput ? String(seatsInput.value || '').trim() : '';

            if (codeValue === '' || seatsValue === '') {
                setChip('chip-details', 'Required fields', 'rose');
            } else {
                setChip('chip-details', seatsValue + ' seats', 'emerald');
            }

            var shirkaSelect = form.querySelector('[name="default_shirka_company_id"]');
            var shirkaText = shirkaSelect && shirkaSelect.selectedIndex >= 0 ? String(shirkaSelect.options[shirkaSelect.selectedIndex].textContent || '').trim() : '';
            var hasShirka = shirkaText !== '' && shirkaText.indexOf('Select') === -1;
            setChip('chip-voucher', hasShirka ? shirkaText : 'Optional', hasShirka ? 'emerald' : 'slate');
        }

        ['code', 'total_seats', 'default_shirka_company_id'].forEach(function(name) {
            var input = form.querySelector('[name="' + name + '"]');
            if (!input) {
                return;
            }
            input.addEventListener('input', paintChips);
            input.addEventListener('change', paintChips);
        });

        var flightTemplateSelect = document.getElementById('flight-template-select');
        var flightIdInput = document.getElementById('flight-id');
        var flightSourceInput = document.getElementById('flight-source');
        var ticketCostInput = document.getElementById('ticket-cost');
        var fareNote = document.getElementById('ticket-fare-note');
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
                    if (fareNote) {
                        fareNote.textContent = '';
                        fareNote.classList.add('hidden');
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

                var fare = numberOrZero(option.dataset.fare);

                if (ticketCostInput && fare > 0) {
                    ticketCostInput.value = String(fare);
                    ticketCostInput.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                }

                if (fareNote) {
                    fareNote.textContent = fare > 0 ? 'Ticket cost ' + fare.toLocaleString() + ' PKR loaded from the flight.' : '';
                    fareNote.classList.toggle('hidden', fare <= 0);
                }

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