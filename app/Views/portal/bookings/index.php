<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<style>
    .bookings-table-wrap,
    .bookings-table-wrap .dataTables_wrapper,
    .bookings-table-wrap .dataTables_scroll,
    .bookings-table-wrap .dataTables_scrollBody {
        overflow-x: auto !important;
    }

    .bookings-table-wrap .list-table {
        min-width: 1300px;
    }

    .bookings-table-wrap .list-table th,
    .bookings-table-wrap .list-table td {
        white-space: nowrap;
    }
</style>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="list-card bookings-table-wrap">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-lg font-semibold">Booking List</h3>
            <a href="<?= site_url('/bookings/add') ?>" class="btn btn-md btn-primary"><i class="fa-solid fa-plus"></i> Add Booking</a>
        </div>
        <table class="list-table">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2 text-left">Booking No</th>
                    <th class="px-3 py-2 text-left">Package</th>
                    <th class="px-3 py-2 text-left">KSA Arrival</th>
                    <th class="px-3 py-2 text-left">KSA Return</th>
                    <th class="px-3 py-2 text-left">Room Type</th>
                    <th class="px-3 py-2 text-left">Tier</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Pilgrims</th>
                    <th class="px-3 py-2 text-left">Total</th>
                    <th class="px-3 py-2 text-left">Paid</th>
                    <th class="px-3 py-2 text-left">Outstanding</th>
                    <th class="px-3 py-2 text-left">Created</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2"><?= esc((string) $row['id']) ?></td>
                        <td class="px-3 py-2 font-medium"><?= esc($row['booking_no']) ?></td>
                        <td class="px-3 py-2"><?= esc((string) ($row['package_name'] ?? ('#' . $row['package_id']))) ?></td>
                        <td class="px-3 py-2"><?= esc((string) ($row['ksa_arrival_date'] ?? '')) ?></td>
                        <td class="px-3 py-2"><?= esc((string) ($row['ksa_return_date'] ?? '')) ?></td>
                        <td class="px-3 py-2"><?= esc((string) ($row['room_types'] ?? '')) ?></td>
                        <td class="px-3 py-2"><?= esc((string) ($row['pricing_tier'] ?? '-')) ?></td>
                        <td class="px-3 py-2"><?= esc($row['status']) ?></td>
                        <td class="px-3 py-2"><?= esc($row['total_pilgrims']) ?></td>
                        <td class="px-3 py-2"><?= esc(number_format((float) ($row['total_amount'] ?? 0), 2)) ?></td>
                        <td class="px-3 py-2"><?= esc(number_format((float) ($row['paid_amount'] ?? 0), 2)) ?></td>
                        <td class="px-3 py-2"><?= esc(number_format((float) ($row['outstanding_amount'] ?? 0), 2)) ?></td>
                        <td class="px-3 py-2"><?= esc($row['created_at']) ?></td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <a href="<?= site_url('/bookings/' . (int) $row['id'] . '/edit') ?>" class="btn btn-sm btn-secondary" title="Edit" aria-label="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="<?= site_url('/bookings/' . (int) $row['id'] . '/voucher') ?>" target="_blank" class="btn btn-sm btn-secondary" title="Voucher" aria-label="Voucher">
                                    <i class="fa-solid fa-file-lines"></i>
                                </a>
                                <form method="post" action="<?= site_url('/bookings/delete') ?>" onsubmit="return confirm('Delete this booking?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="booking_id" value="<?= esc((string) $row['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" aria-label="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<?php $this->endSection() ?>