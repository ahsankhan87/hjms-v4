<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($filterErrors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($filterErrors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white p-5">
        <form method="get" action="/app/reports" class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="text-sm font-medium">From Date</label>
                <input type="date" name="from_date" value="<?= esc($filters['from_date'] ?? '') ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">To Date</label>
                <input type="date" name="to_date" value="<?= esc($filters['to_date'] ?? '') ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-md btn-primary btn-block">Apply Filter</button>
            </div>
        </form>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <article class="rounded-xl bg-white p-4 ring-1 ring-slate-200">
            <p class="text-xs text-slate-500">Pilgrims</p>
            <p class="mt-1 text-2xl font-semibold"><?= esc($dashboard['total_pilgrims'] ?? 0) ?></p>
        </article>
        <article class="rounded-xl bg-white p-4 ring-1 ring-slate-200">
            <p class="text-xs text-slate-500">Bookings</p>
            <p class="mt-1 text-2xl font-semibold"><?= esc($dashboard['total_bookings'] ?? 0) ?></p>
        </article>
        <article class="rounded-xl bg-white p-4 ring-1 ring-slate-200">
            <p class="text-xs text-slate-500">Confirmed</p>
            <p class="mt-1 text-2xl font-semibold"><?= esc($dashboard['confirmed_bookings'] ?? 0) ?></p>
        </article>
        <article class="rounded-xl bg-white p-4 ring-1 ring-slate-200">
            <p class="text-xs text-slate-500">Pending Visas</p>
            <p class="mt-1 text-2xl font-semibold"><?= esc($dashboard['pending_visas'] ?? 0) ?></p>
        </article>
        <article class="rounded-xl bg-white p-4 ring-1 ring-slate-200">
            <p class="text-xs text-slate-500">Overdue Installments</p>
            <p class="mt-1 text-2xl font-semibold"><?= esc($dashboard['overdue_installments'] ?? 0) ?></p>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-200 bg-white p-5">
            <h3 class="text-lg font-semibold">Financial Summary</h3>
            <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-lg bg-slate-50 p-3">Gross: <strong>PKR <?= esc(number_format((float) ($financialSummary['gross_collections'] ?? 0), 2)) ?></strong></div>
                <div class="rounded-lg bg-slate-50 p-3">Refunds: <strong>PKR <?= esc(number_format((float) ($financialSummary['refunds'] ?? 0), 2)) ?></strong></div>
                <div class="rounded-lg bg-slate-50 p-3">Net: <strong>PKR <?= esc(number_format((float) ($financialSummary['net_collections'] ?? 0), 2)) ?></strong></div>
                <div class="rounded-lg bg-slate-50 p-3">Expenses: <strong>PKR <?= esc(number_format((float) ($financialSummary['total_expenses'] ?? 0), 2)) ?></strong></div>
                <div class="rounded-lg bg-slate-50 p-3 col-span-2">Surplus: <strong>PKR <?= esc(number_format((float) ($financialSummary['cash_surplus'] ?? 0), 2)) ?></strong></div>
            </div>
        </article>

        <article class="list-card overflow-auto">
            <h3 class="text-lg font-semibold mb-3">Collections by Channel</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Channel</th>
                        <th class="px-3 py-2 text-left">Count</th>
                        <th class="px-3 py-2 text-left">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($collectionByChannel)): ?><tr>
                            <td colspan="3" class="px-3 py-4 text-slate-500">No records.</td>
                        </tr><?php endif; ?>
                    <?php foreach ($collectionByChannel as $row): ?><tr class="border-t border-slate-100">
                            <td class="px-3 py-2"><?= esc($row['channel']) ?></td>
                            <td class="px-3 py-2"><?= esc($row['payment_count']) ?></td>
                            <td class="px-3 py-2">PKR <?= esc(number_format((float) $row['total_amount'], 2)) ?></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="list-card overflow-auto">
            <h3 class="text-lg font-semibold mb-3">Booking Status</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Bookings</th>
                        <th class="px-3 py-2 text-left">Pilgrims</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookingStatus)): ?><tr>
                            <td colspan="3" class="px-3 py-4 text-slate-500">No records.</td>
                        </tr><?php endif; ?>
                    <?php foreach ($bookingStatus as $row): ?><tr class="border-t border-slate-100">
                            <td class="px-3 py-2"><?= esc($row['status']) ?></td>
                            <td class="px-3 py-2"><?= esc($row['booking_count']) ?></td>
                            <td class="px-3 py-2"><?= esc($row['pilgrim_count']) ?></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
        </article>

        <article class="list-card overflow-auto">
            <h3 class="text-lg font-semibold mb-3">Visa Status</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Visas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($visaStatus)): ?><tr>
                            <td colspan="2" class="px-3 py-4 text-slate-500">No records.</td>
                        </tr><?php endif; ?>
                    <?php foreach ($visaStatus as $row): ?><tr class="border-t border-slate-100">
                            <td class="px-3 py-2"><?= esc($row['status']) ?></td>
                            <td class="px-3 py-2"><?= esc($row['visa_count']) ?></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
        </article>
    </section>
</main>
<?php $this->endSection() ?>