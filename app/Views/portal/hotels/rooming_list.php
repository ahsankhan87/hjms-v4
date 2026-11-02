<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<?php
$package = $rooming['package'] ?? null;
$stays = $rooming['stays'] ?? [];
$totals = $rooming['totals'] ?? ['pilgrims' => 0, 'rooms' => 0];
$fmtDate = static function ($value): string {
    $value = trim((string) ($value ?? ''));

    return $value === '' ? '-' : date('d M Y', strtotime($value));
};
?>
<style>
    @media print {
        aside, header, footer, .no-print { display: none !important; }
        main { padding: 0 !important; }
        .print-card { border: 1px solid #cbd5e1 !important; box-shadow: none !important; break-inside: avoid; }
        body { background: #fff !important; }
    }
</style>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-base font-semibold text-slate-800">Rooming List</h1>
                <p class="mt-1 text-xs text-slate-500">Rooms assigned to pilgrims per hotel and stay. Share or print this sheet with the Saudi ground team.</p>
            </div>
            <form method="get" action="<?= site_url('hotels/rooming-list') ?>" class="flex flex-wrap items-end gap-2 no-print">
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">Package</label>
                    <select name="package_id" class="w-72 rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                        <?php if (empty($packages)): ?>
                            <option value="">No packages in this season</option>
                        <?php endif; ?>
                        <?php foreach (($packages ?? []) as $pkg): ?>
                            <?php $pkgId = (string) (int) ($pkg['id'] ?? 0); ?>
                            <option value="<?= esc($pkgId) ?>" <?= (string) $packageId === $pkgId ? 'selected' : '' ?>>
                                <?= esc((string) ($pkg['code'] ?? '')) ?> — <?= esc((string) ($pkg['name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-600">City</label>
                    <select name="city" class="w-40 rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                        <?php foreach (['' => 'All cities', 'Makkah' => 'Makkah', 'Madina' => 'Madina'] as $cityKey => $cityLabel): ?>
                            <option value="<?= esc($cityKey) ?>" <?= $city === $cityKey ? 'selected' : '' ?>><?= esc($cityLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <a href="<?= site_url('hotels/rooming-list/print?package_id=' . (int) $packageId . '&city=' . urlencode($city)) ?>" target="_blank" rel="noopener" class="btn btn-md btn-primary inline-flex items-center gap-2">
                    <i class="fa-solid fa-print"></i><span>Print Room List</span>
                </a>
                <a href="<?= site_url('hotels') ?>" class="btn btn-md btn-secondary inline-flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i><span>Back</span>
                </a>
            </form>
        </div>

        <?php if (!empty($package)): ?>
            <div class="mt-3 grid gap-2 text-xs text-slate-700 md:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><span class="text-slate-500">Package:</span> <span class="font-semibold"><?= esc((string) ($package['code'] ?? '')) ?></span> <?= esc((string) ($package['name'] ?? '')) ?></div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><span class="text-slate-500">Departure:</span> <?= esc($fmtDate($package['departure_date'] ?? '')) ?></div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><span class="text-slate-500">Return:</span> <?= esc($fmtDate($package['arrival_date'] ?? '')) ?></div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 font-semibold text-emerald-800"><?= esc((string) (int) ($totals['pilgrims'] ?? 0)) ?> pilgrims · <?= esc((string) (int) ($totals['rooms'] ?? 0)) ?> rooms</div>
            </div>
        <?php endif; ?>
    </section>

    <?php if (empty($stays)): ?>
        <section class="rounded-xl border border-slate-200 bg-white px-4 py-10 text-center text-sm text-slate-500">
            No confirmed pilgrims with hotel stays for this package yet.
        </section>
    <?php else: ?>
        <?php foreach ($stays as $stay): ?>
            <section class="print-card rounded-xl border border-slate-200 bg-white">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">
                            <i class="fa-solid fa-hotel mr-1 text-emerald-600"></i><?= esc((string) ($stay['hotel_name'] ?? '-')) ?>
                            <?php if (($stay['city'] ?? '') !== ''): ?><span class="text-slate-400">· <?= esc((string) $stay['city']) ?></span><?php endif; ?>
                            <?php if ((int) ($stay['star_rating'] ?? 0) > 0): ?><span class="text-amber-600">· <?= esc((string) (int) $stay['star_rating']) ?>★</span><?php endif; ?>
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            <?= esc($fmtDate($stay['check_in'] ?? '')) ?> → <?= esc($fmtDate($stay['check_out'] ?? '')) ?>
                            (<?= esc((string) (int) ($stay['nights'] ?? 0)) ?> nights)
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="rounded-full bg-slate-100 px-2 py-1 font-semibold text-slate-700"><?= esc((string) (int) ($stay['rooms'] ?? 0)) ?> rooms</span>
                        <span class="rounded-full bg-emerald-100 px-2 py-1 font-semibold text-emerald-700"><?= esc((string) (int) ($stay['pilgrims'] ?? 0)) ?> pilgrims</span>
                    </div>
                </div>

                <?php if (empty($stay['groups'])): ?>
                    <div class="px-4 py-4 text-xs text-slate-500">No pilgrims allocated for this stay.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-xs">
                            <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                <tr>
                                    <th class="px-3 py-2">Room</th>
                                    <th class="px-3 py-2">Occupancy</th>
                                    <th class="px-3 py-2">Pilgrim</th>
                                    <th class="px-3 py-2">Passport</th>
                                    <th class="px-3 py-2">Gender</th>
                                    <th class="px-3 py-2">Contact</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <?php foreach (($stay['groups'] ?? []) as $group): ?>
                                    <?php $pilgrims = $group['pilgrims'] ?? []; $rowspan = max(1, count($pilgrims)); $first = true; ?>
                                    <?php foreach ($pilgrims as $pilgrim): ?>
                                        <tr class="hover:bg-slate-50/70 align-top">
                                            <?php if ($first): ?>
                                                <td class="whitespace-nowrap px-3 py-2 font-semibold text-slate-800" rowspan="<?= esc((string) $rowspan) ?>">
                                                    <?= esc((string) ($group['tier_label'] ?? '')) ?> #<?= esc((string) (int) ($group['room_no'] ?? 0)) ?>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-2 text-slate-600" rowspan="<?= esc((string) $rowspan) ?>">
                                                    <?= esc((string) count($pilgrims)) ?>/<?= esc((string) (int) ($group['beds'] ?? 0)) ?>
                                                </td>
                                            <?php endif; ?>
                                            <td class="px-3 py-2 font-medium text-slate-800"><?= esc((string) ($pilgrim['name'] ?? '-')) ?></td>
                                            <td class="whitespace-nowrap px-3 py-2 text-slate-600"><?= esc((string) (($pilgrim['passport_no'] ?? '') !== '' ? $pilgrim['passport_no'] : '-')) ?></td>
                                            <td class="px-3 py-2 text-slate-600"><?= esc((string) (($pilgrim['gender'] ?? '') !== '' ? $pilgrim['gender'] : '-')) ?></td>
                                            <td class="whitespace-nowrap px-3 py-2 text-slate-600"><?= esc((string) (($pilgrim['contact'] ?? '') !== '' ? $pilgrim['contact'] : '-')) ?></td>
                                        </tr>
                                        <?php $first = false; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
<?php $this->endSection() ?>
