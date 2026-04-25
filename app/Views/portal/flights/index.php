<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-3">
        <?php
        $compactDateTime = static function ($value): string {
            $raw = trim((string) $value);
            if ($raw === '') {
                return '-';
            }

            $timestamp = strtotime($raw);
            if ($timestamp === false) {
                return $raw;
            }

            return date('d M Y H:i', $timestamp);
        };
        ?>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Flights</h3>
                    <p class="text-xs text-slate-500">Manage departures, tickets, and package links.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= site_url('/flights/add') ?>" class="btn btn-md btn-primary">
                        <i class="fa-solid fa-plus"></i><span>Add Flight</span>
                    </a>
                    <a href="<?= site_url('/flights/departure-batches') ?>" class="btn btn-md btn-secondary">
                        <i class="fa-solid fa-layer-group"></i><span>Departure Batch View</span>
                    </a>
                </div>
            </div>
        </article>

        <div class="list-card overflow-auto">
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Outbound</th>
                        <th class="px-3 py-2 text-left">Return</th>
                        <th class="px-3 py-2 text-left">Tickets</th>
                        <th class="px-3 py-2 text-left">Pkg Links</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-slate-500">No flights found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 font-medium">#<?= esc($row['id']) ?></td>
                                <td class="px-3 py-2">
                                    <div class="text-xs font-semibold text-slate-700"><?= esc(trim((string) (($row['airline'] ?? '') . ' ' . ($row['flight_no'] ?? '')))) ?></div>
                                    <div class="text-xs text-slate-500"><?= esc(trim((string) (($row['departure_airport'] ?? '') . ' -> ' . ($row['arrival_airport'] ?? '')), ' ->')) ?: '-' ?></div>
                                    <div class="text-xs text-slate-500">PNR: <?= esc((string) ($row['pnr'] ?? '-')) ?></div>
                                    <div class="text-xs text-slate-500"><?= esc($compactDateTime($row['departure_at'] ?? '')) ?> to <?= esc($compactDateTime($row['arrival_at'] ?? '')) ?></div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs font-semibold text-slate-700"><?= esc(trim((string) (($row['return_airline'] ?? '') . ' ' . ($row['return_flight_no'] ?? '')))) ?: '-' ?></div>
                                    <div class="text-xs text-slate-500"><?= esc(trim((string) (($row['return_departure_airport'] ?? '') . ' -> ' . ($row['return_arrival_airport'] ?? '')), ' ->')) ?: '-' ?></div>
                                    <div class="text-xs text-slate-500">PNR: <?= esc((string) (($row['return_pnr'] ?? '') !== '' ? $row['return_pnr'] : '-')) ?></div>
                                    <div class="text-xs text-slate-500"><?= esc($compactDateTime($row['return_departure_at'] ?? '')) ?> to <?= esc($compactDateTime($row['return_arrival_at'] ?? '')) ?></div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-xs text-slate-700">Outbound: <?= esc((string) (($row['ticket_file_name'] ?? '') !== '' ? $row['ticket_file_name'] : '-')) ?></div>
                                    <div class="text-xs text-slate-700">Return: <?= esc((string) (($row['return_ticket_file_name'] ?? '') !== '' ? $row['return_ticket_file_name'] : '-')) ?></div>
                                </td>
                                <td class="px-3 py-2"><?= esc((int) ($row['package_links'] ?? 0)) ?></td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= site_url('/flights/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="Edit Flight">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="post" action="<?= site_url('/flights/delete') ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="flight_id" value="<?= esc($row['id']) ?>">
                                            <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this flight?')" title="Delete Flight">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php $this->endSection() ?>