<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<?php
$summaryMode = (string) ($pricingSummary['mode'] ?? ((int) ($row['include_hotel'] ?? 1) === 1 ? 'tiered' : 'flat'));
$summaryPriceMap = (array) ($pricingSummary['price_map'] ?? []);
$summaryFlatPrice = $pricingSummary['flat_price'] ?? null;
?>
<section class="rounded-xl border border-slate-200 bg-white px-4 py-3">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-base font-semibold text-slate-800">Edit Package — <?= esc((string) ($row['code'] ?? '')) ?></h1>
            <p class="mt-1 text-xs text-slate-500">Flights come first; hotel stay dates and the package duration follow the flight dates automatically.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= site_url('packages') ?>" class="inline-flex h-8 items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 text-[11px] font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-arrow-left"></i><span>All packages</span></a>
            <form method="post" action="<?= site_url('packages/delete') ?>" onsubmit="return confirm('Delete this package and all of its linked flights, hotels and transport?');">
                <?= csrf_field() ?>
                <input type="hidden" name="package_id" value="<?= esc((string) (int) ($row['id'] ?? 0)) ?>">
                <button type="submit" class="inline-flex h-8 items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2 text-[11px] font-semibold text-rose-600 hover:bg-rose-100" title="Delete package">
                    <i class="fa-solid fa-trash-can"></i><span>Delete package</span>
                </button>
            </form>
        </div>
    </div>

    <div class="mt-3 grid gap-2 md:grid-cols-4">
        <?php if ($summaryMode === 'flat'): ?>
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 md:col-span-4">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Package price</div>
                <div class="mt-0.5 text-lg font-bold text-slate-800"><?= $summaryFlatPrice !== null ? 'PKR ' . esc(number_format((float) $summaryFlatPrice, 2)) : '—' ?></div>
            </div>
        <?php else: ?>
            <?php foreach (\App\Services\HotelRateService::TIER_LABELS as $tierKey => $tierLabel): ?>
                <?php $tierPrice = $summaryPriceMap[$tierKey] ?? null; ?>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500"><?= esc($tierLabel) ?></div>
                    <div class="mt-0.5 text-base font-bold text-slate-800"><?= $tierPrice !== null ? 'PKR ' . esc(number_format((float) $tierPrice, 2)) : 'Not configured' ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
    $summaryRate = (float) ($pricingSummary['exchange_rate'] ?? $row['exchange_rate'] ?? 1);
    $summaryHotelTotals = (array) ($pricingSummary['hotel_totals'] ?? []);
    $summaryHotelSar = $summaryHotelTotals === [] ? 0.0 : min(array_map('floatval', $summaryHotelTotals));
    $summaryHotelSarMax = $summaryHotelTotals === [] ? 0.0 : max(array_map('floatval', $summaryHotelTotals));
    ?>
    <div class="mt-2 grid gap-2 text-[11px] text-slate-600 md:grid-cols-4 xl:grid-cols-7">
        <div class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5">Flight (PKR): <?= esc(number_format((float) ($pricingSummary['flight_total'] ?? 0), 2)) ?></div>
        <div class="rounded-lg border border-slate-200 bg-white px-3 py-1.5">
            Hotels (SAR): <?= esc(number_format($summaryHotelSar, 2)) ?><?= $summaryHotelSarMax > $summaryHotelSar ? ' – ' . esc(number_format($summaryHotelSarMax, 2)) : '' ?>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white px-3 py-1.5">Transport (SAR): <?= esc(number_format((float) ($pricingSummary['transport_total_sar'] ?? 0), 2)) ?></div>
        <div class="rounded-lg border border-slate-200 bg-white px-3 py-1.5">
            Ziarat (SAR): <?= (int) ($row['include_ziarat'] ?? 0) === 1 ? esc(number_format((float) ($pricingSummary['ziarat_total_sar'] ?? 0), 2)) : 'Not included' ?>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white px-3 py-1.5">Visa (SAR): <?= esc(number_format((float) ($pricingSummary['visa_total_sar'] ?? 0), 2)) ?></div>
        <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5">
            <?php $profitMode = (string) ($pricingSummary['profit_mode'] ?? 'amount'); ?>
            <?php $profitValue = (float) ($pricingSummary['profit_value'] ?? 0); ?>
            <?php if ($profitMode === 'percent'): ?>
                Profit (%): <?= esc(rtrim(rtrim(number_format($profitValue, 2, '.', ''), '0'), '.')) ?>%
            <?php else: ?>
                Profit (PKR): <?= esc(number_format($profitValue, 2)) ?>
            <?php endif; ?>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 font-semibold text-emerald-800">SAR → PKR: <?= esc(rtrim(rtrim(number_format($summaryRate, 4, '.', ''), '0'), '.')) ?></div>
    </div>
</section>

<?= $this->include('portal/packages/_form') ?>
<?php $this->endSection() ?>