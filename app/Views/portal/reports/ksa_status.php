<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($errors)): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
            <?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="bg-gradient-to-r from-sky-700 to-sky-600 px-4 py-4 text-white sm:px-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.16em] text-sky-100">Reports Module</p>
                    <h1 class="text-lg font-semibold">KSA Status Snapshot</h1>
                    <p class="text-xs text-sky-100">Date-wise movement and in-country occupancy estimate.</p>
                </div>
                <a href="<?= site_url('/reports') ?>" class="inline-flex items-center gap-2 rounded-lg border border-sky-200/40 bg-white/10 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-white/20">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    Back to Reports
                </a>
            </div>
        </div>

        <div class="border-t border-slate-200 px-4 py-3 sm:px-5">
            <form method="get" action="<?= site_url('/reports/ksa-status') ?>" class="flex flex-wrap items-end gap-2">
                <div>
                    <label for="status_date" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">KSA Status On</label>
                    <div class="flex items-center gap-2 rounded-lg border border-slate-300 px-2.5 py-1.5">
                        <i class="fa-solid fa-calendar-days text-slate-500"></i>
                        <input id="status_date" type="date" name="status_date" value="<?= esc((string) $statusDate) ?>" class="border-0 bg-transparent p-0 text-sm text-slate-800 focus:ring-0">
                    </div>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    Update
                </button>
            </form>
            <p class="mt-2 text-[11px] text-slate-500">Note: city split is an itinerary estimate based on arrival-return duration.</p>
        </div>

        <div class="border-t border-slate-200 px-4 py-3 sm:px-5">
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p class="text-xs text-slate-500">Arrival</p>
                    <p class="text-lg font-semibold text-sky-700"><?= esc((string) ($metrics['arrival'] ?? 0)) ?></p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p class="text-xs text-slate-500">Departure</p>
                    <p class="text-lg font-semibold text-sky-700"><?= esc((string) ($metrics['departure'] ?? 0)) ?></p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p class="text-xs text-slate-500">Inside KSA</p>
                    <p class="text-lg font-semibold text-emerald-700"><?= esc((string) ($metrics['inside_ksa'] ?? 0)) ?></p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p class="text-xs text-slate-500">Makkah Check-in</p>
                    <p class="text-lg font-semibold text-slate-800"><?= esc((string) ($metrics['makkah_checkin'] ?? 0)) ?></p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p class="text-xs text-slate-500">Makkah Checkout</p>
                    <p class="text-lg font-semibold text-slate-800"><?= esc((string) ($metrics['makkah_checkout'] ?? 0)) ?></p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p class="text-xs text-slate-500">Madinah Check-in</p>
                    <p class="text-lg font-semibold text-slate-800"><?= esc((string) ($metrics['madinah_checkin'] ?? 0)) ?></p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p class="text-xs text-slate-500">Madinah Checkout</p>
                    <p class="text-lg font-semibold text-slate-800"><?= esc((string) ($metrics['madinah_checkout'] ?? 0)) ?></p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p class="text-xs text-slate-500">In Makkah</p>
                    <p class="text-lg font-semibold text-slate-800"><?= esc((string) ($metrics['in_makkah'] ?? 0)) ?></p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p class="text-xs text-slate-500">In Madinah</p>
                    <p class="text-lg font-semibold text-slate-800"><?= esc((string) ($metrics['in_madinah'] ?? 0)) ?></p>
                </div>
            </div>
        </div>
    </section>
</main>
<?php $this->endSection() ?>