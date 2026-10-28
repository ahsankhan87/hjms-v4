<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<?php
$componentIcons = [
    'visa' => 'fa-passport',
    'ticket' => 'fa-plane-departure',
    'hotel' => 'fa-hotel',
    'transport' => 'fa-bus',
    'package' => 'fa-box-open',
];
?>
<main class="space-y-4">
    <?php if (!empty($filterErrors)): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
            <?php foreach ($filterErrors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h1 class="text-base font-semibold text-slate-900">Sales Components Report</h1>
                <p class="text-xs text-slate-500">How many visas, tickets, hotel stays, transport legs and packages were issued.</p>
            </div>
            <a href="<?= site_url('/reports') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                Back to Reports
            </a>
        </div>

        <form method="get" action="<?= site_url('/reports/sale-components') ?>" class="mt-3 grid gap-2 md:grid-cols-4">
            <div>
                <label for="from_date" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">From Date</label>
                <input id="from_date" type="date" name="from_date" value="<?= esc((string) ($filters['from_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <div>
                <label for="to_date" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">To Date</label>
                <input id="to_date" type="date" name="to_date" value="<?= esc((string) ($filters['to_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <div>
                <label for="component" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Component</label>
                <select id="component" name="component" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <option value="">All</option>
                    <?php foreach (($componentTypes ?? []) as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= (string) ($filters['component'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    Apply Filter
                </button>
                <a href="<?= site_url('/reports/sale-components') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="grid gap-3 sm:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Components Issued</p>
            <p class="mt-1 text-lg font-semibold text-slate-900"><?= esc((string) ($totalComponents ?? 0)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Total Value (PKR)</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">PKR <?= esc(number_format((float) ($totalPkr ?? 0), 2)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Component Types</p>
            <p class="mt-1 text-lg font-semibold text-slate-900"><?= count(array_filter($summary ?? [], static function (array $row): bool {
                return (int) ($row['count'] ?? 0) > 0;
            })) ?></p>
        </article>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <h2 class="mb-3 text-sm font-semibold text-slate-800">Issued by Component</h2>
        <div class="overflow-auto">
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Component</th>
                        <th class="px-3 py-2 text-right">Issued</th>
                        <th class="px-3 py-2 text-right">Total (PKR)</th>
                        <th class="px-3 py-2 text-left">Currency Mix</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($summary ?? []) as $type => $row): ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2 font-medium text-slate-800">
                                <i class="fa-solid <?= esc($componentIcons[$type] ?? 'fa-circle') ?> mr-1 text-[11px] text-emerald-600"></i>
                                <?= esc((string) ($row['label'] ?? ucfirst((string) $type))) ?>
                            </td>
                            <td class="px-3 py-2 text-right font-semibold text-slate-800"><?= esc((string) ($row['count'] ?? 0)) ?></td>
                            <td class="px-3 py-2 text-right text-slate-700"><?= esc(number_format((float) ($row['total_pkr'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2 text-slate-600">
                                <?php $currencyMix = (array) ($row['currencies'] ?? []); ?>
                                <?php if ($currencyMix === []): ?>
                                    <span class="text-slate-400">-</span>
                                <?php else: ?>
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($currencyMix as $currency => $count): ?>
                                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                                <?= esc((string) $currency) ?>: <?= esc((string) $count) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <h2 class="mb-3 text-sm font-semibold text-slate-800">Component Detail</h2>
        <div class="list-card overflow-auto">
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Date</th>
                        <th class="px-3 py-2 text-left">Invoice #</th>
                        <th class="px-3 py-2 text-left">Component</th>
                        <th class="px-3 py-2 text-left">Detail</th>
                        <th class="px-3 py-2 text-left">Pax</th>
                        <th class="px-3 py-2 text-left">Currency</th>
                        <th class="px-3 py-2 text-right">Amount</th>
                        <th class="px-3 py-2 text-right">PKR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($detailRows)): ?>
                        <tr>
                            <td colspan="8" class="px-3 py-6 text-center text-slate-500">No components found for the selected filters.</td>
                        </tr>
                    <?php else: foreach ($detailRows as $detail): ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2 text-slate-700"><?= esc((string) ($detail['sale_date'] ?? '-')) ?></td>
                            <td class="px-3 py-2">
                                <a href="<?= site_url('/sales/' . (int) ($detail['sale_id'] ?? 0)) ?>" class="font-medium text-emerald-700 hover:underline">
                                    <?= esc((string) (($detail['invoice_no'] ?? '') !== '' ? $detail['invoice_no'] : ('#' . ($detail['sale_id'] ?? '')))) ?>
                                </a>
                            </td>
                            <td class="px-3 py-2 text-slate-700"><?= esc((string) ($componentTypes[$detail['item_type']] ?? ucfirst((string) $detail['item_type']))) ?></td>
                            <td class="px-3 py-2 text-slate-600"><?= esc((string) ($detail['detail'] ?? '-')) ?></td>
                            <td class="px-3 py-2 text-slate-600"><?= esc((string) (($detail['pax_name'] ?? '') !== '' ? $detail['pax_name'] : '-')) ?></td>
                            <td class="px-3 py-2 text-slate-600"><?= esc((string) ($detail['currency'] ?? 'PKR')) ?></td>
                            <td class="px-3 py-2 text-right text-slate-700"><?= esc(number_format((float) ($detail['amount'] ?? 0), 2)) ?></td>
                            <td class="px-3 py-2 text-right font-semibold text-slate-800"><?= esc(number_format((float) ($detail['amount_pkr'] ?? 0), 2)) ?></td>
                        </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php $this->endSection() ?>
