<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<?php
$labels = ['visa' => 'Visa', 'ticket' => 'Ticket', 'hotel' => 'Hotel', 'transport' => 'Transport', 'package' => 'Package', 'general' => 'General'];
$icons = ['visa' => 'fa-passport', 'ticket' => 'fa-plane-departure', 'hotel' => 'fa-hotel', 'transport' => 'fa-bus', 'package' => 'fa-box-open', 'general' => 'fa-file-invoice-dollar'];
$tones = [
    'visa' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'ring' => 'ring-sky-200', 'bar' => 'bg-sky-500'],
    'ticket' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-700', 'ring' => 'ring-violet-200', 'bar' => 'bg-violet-500'],
    'hotel' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-200', 'bar' => 'bg-amber-500'],
    'transport' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'ring' => 'ring-rose-200', 'bar' => 'bg-rose-500'],
    'package' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-200', 'bar' => 'bg-emerald-500'],
    'general' => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-700', 'ring' => 'ring-cyan-200', 'bar' => 'bg-cyan-500'],
];
$statusStyles = [
    'posted' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
    'pending' => 'bg-amber-100 text-amber-800 ring-amber-200',
    'voided' => 'bg-rose-100 text-rose-800 ring-rose-200',
];
$creditNotePaths = [
    'visa' => '/sales/credit-notes/add',
    'hotel' => '/hotel-sales/credit-notes/add',
    'ticket' => '/ticket-sales/credit-notes/add',
    'package' => '/package-sales/credit-notes/add',
];

$money = static function ($value): string {
    return number_format((float) $value, 2);
};
$date = static function ($value): string {
    $value = trim((string) $value);
    if ($value === '' || strpos($value, '0000-00-00') === 0) {
        return '';
    }
    $time = strtotime($value);

    return $time ? date('d M Y', $time) : $value;
};
$filled = static function ($value): bool {
    return trim((string) $value) !== '';
};

$status = (string) ($sale['status'] ?? 'posted');
$isAgent = (string) ($sale['customer_type'] ?? '') === 'agent';
$customerName = $isAgent
    ? (string) ($sale['agent_name'] ?? 'Agent')
    : ((string) ($sale['customer_name'] ?? '') !== '' ? (string) $sale['customer_name'] : 'Walk-in customer');
$saleType = (string) ($sale['sale_type'] ?? '');
$typeCounts = [];
foreach ($items as $item) {
    $t = (string) ($item['item_type'] ?? '');
    $typeCounts[$t] = ($typeCounts[$t] ?? 0) + 1;
}
$itemsTotalPkr = 0.0;
$orderedItems = [];
foreach (array_keys($labels) as $type) {
    foreach ($items as $item) {
        if ((string) ($item['item_type'] ?? '') === $type) {
            $orderedItems[] = $item;
        }
    }
}

$field = static function (string $label, string $value, string $extra = ''): string {
    if (trim($value) === '') {
        return '';
    }

    return '<div class="min-w-0"><dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">' . esc($label) . '</dt>'
        . '<dd class="mt-0.5 break-words text-sm font-semibold text-slate-800 ' . $extra . '">' . esc($value) . '</dd></div>';
};
?>
<style>
@media print {
    #sidebar, #sidebarBackdrop, body > div > div > header, .no-print { display: none !important; }
    body { background: #fff !important; }
    .print-card { box-shadow: none !important; break-inside: avoid; }
}
</style>
<main class="space-y-5">
    <?php if (!empty($success)): ?><div class="no-print rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="no-print rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>

    <nav class="no-print flex items-center gap-2 text-xs text-slate-500">
        <a href="<?= site_url('/admin/sales') ?>" class="hover:text-emerald-700"><i class="fa-solid fa-arrow-left mr-1"></i>Sales</a>
        <span>/</span>
        <span class="font-medium text-slate-700"><?= esc((string) ($sale['invoice_no'] ?? '')) ?></span>
    </nav>

    <section class="print-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="relative bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-600 px-5 py-5 text-white sm:px-7">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl ring-1 ring-white/25">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-widest text-emerald-100">Sale Invoice</p>
                        <h1 class="text-2xl font-bold leading-tight sm:text-3xl"><?= esc((string) ($sale['invoice_no'] ?? '')) ?></h1>
                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-emerald-50">
                            <span><i class="fa-regular fa-calendar mr-1"></i><?= esc($date($sale['sale_date'] ?? '') ?: '-') ?></span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize ring-1 <?= esc($statusStyles[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-200') ?>"><?= esc($status) ?></span>
                        </div>
                    </div>
                </div>
                <div class="no-print flex flex-wrap items-center gap-2">
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-white/15 px-3.5 py-2 text-sm font-medium text-white ring-1 ring-white/30 transition hover:bg-white/25"><i class="fa-solid fa-print"></i>Print</button>
                    <?php if (auth_can('sales.edit')): ?>
                        <a href="<?= site_url('/admin/sales/' . (int) $sale['id'] . '/edit') ?>" class="inline-flex items-center gap-2 rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50"><i class="fa-solid fa-pen"></i>Edit</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 divide-x divide-y divide-slate-100 border-t border-slate-100 sm:grid-cols-4 sm:divide-y-0">
            <div class="px-5 py-4">
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Total (PKR)</p>
                <p class="mt-1 text-xl font-bold text-emerald-700"><?= esc($money($totalPkr)) ?></p>
            </div>
            <div class="px-5 py-4">
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Components</p>
                <div class="mt-1.5 flex flex-wrap gap-1">
                    <?php foreach ($typeCounts as $type => $count): ?>
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 <?= esc(($tones[$type]['bg'] ?? 'bg-slate-50') . ' ' . ($tones[$type]['text'] ?? 'text-slate-700') . ' ' . ($tones[$type]['ring'] ?? 'ring-slate-200')) ?>">
                            <i class="fa-solid <?= esc($icons[$type] ?? 'fa-circle') ?> text-[10px]"></i><?= esc($labels[$type] ?? ucfirst((string) $type)) ?><?= $count > 1 ? ' ×' . (int) $count : '' ?>
                        </span>
                    <?php endforeach; ?>
                    <?php if ($typeCounts === []): ?><span class="text-sm text-slate-400">-</span><?php endif; ?>
                </div>
            </div>
            <div class="px-5 py-4">
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Customer</p>
                <p class="mt-1 truncate text-sm font-semibold text-slate-800" title="<?= esc($customerName) ?>"><?= esc($customerName) ?></p>
                <p class="text-xs text-slate-500"><?= $isAgent ? 'Agent' : 'Walk-in' ?></p>
            </div>
            <div class="px-5 py-4">
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Pilgrims</p>
                <p class="mt-1 text-xl font-bold text-slate-800"><?= count($pilgrims) ?></p>
            </div>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <?php if ($orderedItems === []): ?>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500">
                    <i class="fa-regular fa-folder-open mb-2 block text-2xl text-slate-300"></i>No components recorded for this sale.
                </div>
            <?php endif; ?>

            <?php foreach ($orderedItems as $item): ?>
                <?php
                $type = (string) ($item['item_type'] ?? '');
                $tone = $tones[$type] ?? $tones['package'];
                $currency = (string) ($item['currency'] ?? 'PKR');
                $rate = (float) ($item['exchange_rate'] ?? 1);
                $foreign = strtoupper($currency) !== 'PKR';
                $nights = (int) ($item['no_of_nights'] ?? 0);
                if ($nights < 1 && $filled($item['check_in_date'] ?? '') && $filled($item['check_out_date'] ?? '')) {
                    $nights = max(0, (int) round((strtotime((string) $item['check_out_date']) - strtotime((string) $item['check_in_date'])) / 86400));
                }
                ?>
                <article class="print-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex">
                        <div class="w-1.5 shrink-0 <?= esc($tone['bar']) ?>"></div>
                        <div class="min-w-0 flex-1">
                            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl text-lg ring-1 <?= esc($tone['bg'] . ' ' . $tone['text'] . ' ' . $tone['ring']) ?>"><i class="fa-solid <?= esc($icons[$type] ?? 'fa-circle') ?>"></i></span>
                                    <div>
                                        <h2 class="text-sm font-bold text-slate-800"><?= esc($labels[$type] ?? ucfirst($type)) ?></h2>
                                        <?php if ($type === 'transport' && (int) ($item['is_ziarat'] ?? 0) === 1): ?>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-semibold text-teal-700 ring-1 ring-teal-200"><i class="fa-solid fa-kaaba"></i>Ziarat</span>
                                        <?php elseif ($type === 'hotel' && $nights > 0): ?>
                                            <span class="text-xs text-slate-500"><?= $nights ?> night<?= $nights === 1 ? '' : 's' ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-base font-bold text-slate-900"><?= esc($currency) ?> <?= esc($money($item['amount'] ?? 0)) ?></p>
                                    <?php if ($foreign): ?>
                                        <p class="text-[11px] text-slate-500">@ <?= esc(rtrim(rtrim(number_format($rate, 4), '0'), '.')) ?> = <span class="font-semibold text-emerald-700">PKR <?= esc($money($item['amount_pkr'] ?? 0)) ?></span></p>
                                    <?php endif; ?>
                                </div>
                            </header>

                            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 px-5 py-4 sm:grid-cols-3">
                                <?php if ($type === 'visa'): ?>
                                    <?= $field('Visa Type', (string) ($item['visa_type'] ?? '')) ?>
                                    <?= $field('Visa No', (string) ($item['visa_no'] ?? '')) ?>
                                <?php elseif ($type === 'ticket'): ?>
                                    <?= $field('PNR', (string) ($item['pnr'] ?? ''), 'font-mono tracking-wider') ?>
                                    <?= $field('Sector', (string) ($item['sector'] ?? '')) ?>
                                    <?= $field('Ticket No', (string) ($item['ticket_no'] ?? ''), 'font-mono') ?>
                                <?php elseif ($type === 'hotel'): ?>
                                    <div class="col-span-2 sm:col-span-3"><?= $field('Hotel', (string) ($item['hotel_name'] ?? '')) ?></div>
                                    <?= $field('Check-in', $date($item['check_in_date'] ?? '')) ?>
                                    <?= $field('Check-out', $date($item['check_out_date'] ?? '')) ?>
                                    <?= $field('Nights', $nights > 0 ? (string) $nights : '') ?>
                                    <?= $field('Rooms', (int) ($item['room_qty'] ?? 0) > 0 ? (string) (int) $item['room_qty'] : '') ?>
                                    <?= $field('Room Type', (string) ($item['room_type'] ?? '')) ?>
                                <?php elseif ($type === 'transport'): ?>
                                    <?= $field('Transport Type', (string) ($item['transport_type'] ?? '')) ?>
                                    <div class="col-span-2"><?= $field('Route', (string) ($item['route'] ?? '')) ?></div>
                                <?php elseif ($type === 'package'): ?>
                                    <div class="col-span-2 sm:col-span-3"><?= $field('Package', (string) ($item['package_name'] ?? '')) ?></div>
                                    <?= $field('Departure', $date($item['departure_date'] ?? '')) ?>
                                    <?= $field('Arrival', $date($item['arrival_date'] ?? '')) ?>
                                <?php elseif ($type === 'general'): ?>
                                    <div class="col-span-2 sm:col-span-3"><?= $field('Particulars', (string) ($item['description'] ?? '')) ?></div>
                                <?php endif; ?>
                            </dl>

                            <?php if ($filled($item['description'] ?? '') || $filled($item['attachment'] ?? '')): ?>
                                <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-2.5">
                                    <p class="min-w-0 flex-1 text-xs text-slate-600"><?= $filled($item['description'] ?? '') ? nl2br(esc((string) $item['description'])) : '' ?></p>
                                    <?php if ($filled($item['attachment'] ?? '')): ?>
                                        <a href="<?= esc(asset_upload_url((string) $item['attachment'], 'sales')) ?>" target="_blank" class="no-print inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50"><i class="fa-solid fa-paperclip"></i>Attachment</a>
                                    <?php endif; ?>
                                </footer>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if ($orderedItems !== []): ?>
                <div class="print-card flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
                    <span class="text-sm font-semibold text-emerald-800">Grand Total (PKR)</span>
                    <span class="text-xl font-bold text-emerald-800"><?= esc($money($totalPkr)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <aside class="space-y-4">
            <section class="print-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-800"><i class="fa-solid fa-user-tie text-emerald-600"></i>Customer</h3>
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-base font-bold text-emerald-700"><?= esc(strtoupper(substr($customerName, 0, 1))) ?></span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-800"><?= esc($customerName) ?></p>
                        <p class="text-xs text-slate-500"><?= $isAgent ? 'Agent account' : 'Walk-in customer' ?></p>
                        <?php if ($isAgent && $filled($sale['agent_phone'] ?? '')): ?><p class="text-xs text-slate-500"><i class="fa-solid fa-phone mr-1"></i><?= esc((string) $sale['agent_phone']) ?></p><?php endif; ?>
                    </div>
                </div>
                <?php if ($isAgent && (int) ($sale['agent_id'] ?? 0) > 0 && auth_can('agents.view')): ?>
                    <a href="<?= site_url('/admin/agents/' . (int) $sale['agent_id'] . '/ledger') ?>" class="no-print mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 hover:underline"><i class="fa-solid fa-book"></i>View agent ledger</a>
                <?php endif; ?>
            </section>

            <section class="print-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 flex items-center justify-between text-sm font-bold text-slate-800">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-users text-emerald-600"></i>Pilgrims</span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600"><?= count($pilgrims) ?></span>
                </h3>
                <?php if ($pilgrims === [] && $filled($sale['pax_name'] ?? '')): ?>
                    <p class="text-sm text-slate-700"><?= esc((string) $sale['pax_name']) ?></p>
                <?php elseif ($pilgrims === []): ?>
                    <p class="text-sm text-slate-400">No pilgrims attached.</p>
                <?php else: ?>
                    <ul class="divide-y divide-slate-100">
                        <?php foreach ($pilgrims as $pilgrim): ?>
                            <?php $pName = trim((string) ($pilgrim['first_name'] ?? '') . ' ' . (string) ($pilgrim['last_name'] ?? '')); ?>
                            <li class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600"><?= esc(strtoupper(substr($pName !== '' ? $pName : 'P', 0, 1))) ?></span>
                                <div class="min-w-0 flex-1">
                                    <?php if (auth_can('pilgrims.view')): ?>
                                        <a href="<?= site_url('/admin/pilgrims/' . (int) $pilgrim['id']) ?>" class="block truncate text-sm font-semibold text-slate-800 hover:text-emerald-700"><?= esc($pName !== '' ? $pName : 'Pilgrim') ?></a>
                                    <?php else: ?>
                                        <span class="block truncate text-sm font-semibold text-slate-800"><?= esc($pName !== '' ? $pName : 'Pilgrim') ?></span>
                                    <?php endif; ?>
                                    <span class="block truncate text-xs text-slate-500">
                                        <?= $filled($pilgrim['passport_no'] ?? '') ? '<i class="fa-solid fa-passport mr-1"></i>' . esc((string) $pilgrim['passport_no']) : 'No passport' ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <?php if ($filled($sale['note'] ?? '')): ?>
                <section class="print-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-2 flex items-center gap-2 text-sm font-bold text-slate-800"><i class="fa-regular fa-note-sticky text-emerald-600"></i>Remarks</h3>
                    <p class="text-sm leading-relaxed text-slate-600"><?= nl2br(esc((string) $sale['note'])) ?></p>
                </section>
            <?php endif; ?>

            <?php if ($saleAttachments !== []): ?>
                <section class="no-print rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-800"><i class="fa-solid fa-paperclip text-emerald-600"></i>Attachments</h3>
                    <div class="space-y-2">
                        <?php foreach ($saleAttachments as $index => $attachment): ?>
                            <a href="<?= esc(asset_upload_url($attachment, 'sales')) ?>" target="_blank" class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50">
                                <i class="fa-solid fa-file text-slate-400"></i><span class="flex-1 truncate">Attachment <?= (int) $index + 1 ?></span><i class="fa-solid fa-arrow-up-right-from-square text-xs text-slate-400"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php
            $creditPath = $creditNotePaths[$saleType] ?? null;
            $canCredit = $creditPath !== null && $isAgent && (int) ($sale['agent_id'] ?? 0) > 0 && $status !== 'voided' && auth_can('sales.create');
            ?>
            <?php if ($canCredit): ?>
                <a href="<?= site_url($creditPath . '?sale_id=' . (int) $sale['id']) ?>" class="no-print flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-medium text-rose-700 transition hover:bg-rose-100"><i class="fa-solid fa-file-circle-plus"></i>Create Credit Note</a>
            <?php endif; ?>

            <p class="px-1 text-[11px] text-slate-400">
                Created <?= esc($date($sale['created_at'] ?? '') ?: '-') ?><?= $isLegacy ? ' · Recorded before the unified sale page' : '' ?>
            </p>
        </aside>
    </div>
</main>
<?php $this->endSection() ?>
