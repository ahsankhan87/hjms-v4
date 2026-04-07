<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($filterErrors)): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
            <?php foreach ($filterErrors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h1 class="text-base font-semibold text-slate-900">Operations Reports</h1>
                <p class="text-xs text-slate-500">Booking pipeline, visa flow, and pilgrim composition.</p>
            </div>
            <a href="<?= site_url('/reports') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                Back to Reports
            </a>
        </div>

        <form method="get" action="<?= site_url('/reports/operations') ?>" class="mt-3 grid gap-2 md:grid-cols-4">
            <div>
                <label for="from_date" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">From Date</label>
                <input id="from_date" type="date" name="from_date" value="<?= esc((string) ($filters['from_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <div>
                <label for="to_date" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">To Date</label>
                <input id="to_date" type="date" name="to_date" value="<?= esc((string) ($filters['to_date'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-800 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    Apply Filter
                </button>
                <a href="<?= site_url('/reports/operations') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="grid gap-3 sm:grid-cols-4">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Bookings</p>
            <p class="mt-1 text-lg font-semibold text-slate-900"><?= esc((string) ($totals['total_bookings'] ?? 0)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Pilgrims</p>
            <p class="mt-1 text-lg font-semibold text-slate-900"><?= esc((string) ($totals['total_pilgrims'] ?? 0)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Pending Visas</p>
            <p class="mt-1 text-lg font-semibold text-amber-700"><?= esc((string) ($totals['pending_visas'] ?? 0)) ?></p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Approved Visas</p>
            <p class="mt-1 text-lg font-semibold text-emerald-700"><?= esc((string) ($totals['visa_approved'] ?? 0)) ?></p>
        </article>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <article class="list-card overflow-auto rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-slate-900">Booking Pipeline Status</h3>
            </div>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Bookings</th>
                        <th class="px-3 py-2 text-left">Pilgrims</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookingStatusRows)): ?>
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-slate-500">No records.</td>
                        </tr>
                        <?php else: foreach ($bookingStatusRows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2"><?= esc((string) ($row['status'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['booking_count'] ?? 0)) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['pilgrim_count'] ?? 0)) ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </article>

        <article class="list-card overflow-auto rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-slate-900">Visa Processing Status</h3>
            </div>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($visaStatusRows)): ?>
                        <tr>
                            <td colspan="2" class="px-3 py-4 text-slate-500">No records.</td>
                        </tr>
                        <?php else: foreach ($visaStatusRows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2"><?= esc((string) ($row['status'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['visa_count'] ?? 0)) ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </article>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <article class="list-card overflow-auto rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-slate-900">Booking Mix by Pricing Tier</h3>
            </div>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Tier</th>
                        <th class="px-3 py-2 text-left">Bookings</th>
                        <th class="px-3 py-2 text-left">Pilgrims</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tierMixRows)): ?>
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-slate-500">No records.</td>
                        </tr>
                        <?php else: foreach ($tierMixRows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 capitalize"><?= esc((string) ($row['pricing_tier'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['booking_count'] ?? 0)) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['pilgrim_count'] ?? 0)) ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </article>

        <article class="list-card overflow-auto rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-slate-900">Pilgrim Gender Distribution</h3>
            </div>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Gender</th>
                        <th class="px-3 py-2 text-left">Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($genderRows)): ?>
                        <tr>
                            <td colspan="2" class="px-3 py-4 text-slate-500">No records.</td>
                        </tr>
                        <?php else: foreach ($genderRows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 capitalize"><?= esc((string) ($row['gender_key'] ?? 'unknown')) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['pilgrim_count'] ?? 0)) ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </article>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <article class="list-card overflow-auto rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-slate-900">Recent Bookings</h3>
            </div>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Booking No</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Agent</th>
                        <th class="px-3 py-2 text-left">Pilgrims</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentBookings)): ?>
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-slate-500">No records.</td>
                        </tr>
                        <?php else: foreach ($recentBookings as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2"><?= esc((string) ($row['booking_no'] ?? '-')) ?></td>
                                <td class="px-3 py-2 capitalize"><?= esc((string) ($row['status'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['agent_name'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['total_pilgrims'] ?? 0)) ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </article>

        <article class="list-card overflow-auto rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-slate-900">Pending Visa Queue</h3>
            </div>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Visa No</th>
                        <th class="px-3 py-2 text-left">Pilgrim</th>
                        <th class="px-3 py-2 text-left">Booking</th>
                        <th class="px-3 py-2 text-left">Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendingVisaRows)): ?>
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-slate-500">No records.</td>
                        </tr>
                        <?php else: foreach ($pendingVisaRows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2"><?= esc((string) ($row['visa_no'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc(trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''))) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['booking_no'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($row['submission_date'] ?? '-')) ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </article>
    </section>
</main>
<?php $this->endSection() ?>