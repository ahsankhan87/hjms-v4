<?php
/**
 * Per-room-type rates (per person, per night) for a hotel.
 *
 * @var array<string, mixed> $rates [tier => amount]
 */
$rates = is_array($rates ?? null) ? $rates : [];
$tierLabels = \App\Services\HotelRateService::TIER_LABELS;
$tiers = \App\Services\HotelRateService::TIERS;
$inputClass = 'mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm';
?>
<div>
    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Room Rates</p>
    <p class="mt-1 text-xs text-slate-500">Set the rate in <strong>SAR</strong> per person per night for each room type. Packages load these rates automatically when this hotel is selected (multiplied by the stay nights) and can still override them per package.</p>
    <div class="mt-2 grid gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <?php foreach ($tiers as $tier): ?>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600"><?= esc($tierLabels[$tier]) ?> (SAR)</label>
                <input type="number" step="0.01" min="0" name="rates[<?= esc($tier) ?>]"
                    value="<?= esc((string) ($rates[$tier] ?? '')) ?>"
                    placeholder="0.00" class="<?= $inputClass ?>">
            </div>
        <?php endforeach; ?>
    </div>
</div>
