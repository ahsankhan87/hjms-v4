<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Pilgrims Directory</h3>
                    <p class="text-xs text-slate-500">Manage pilgrim records, visa status, and booking linkage.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= site_url('/pilgrims/add') ?>" class="btn btn-md btn-primary">
                        <i class="fa-solid fa-user-plus"></i><span>Add Pilgrim</span>
                    </a>
                    <a href="<?= site_url('/pilgrims/import') ?>" class="btn btn-md btn-secondary">
                        <i class="fa-solid fa-upload"></i><span>Import CSV</span>
                    </a>
                </div>
            </div>
        </article>

        <article class="list-card">
            <table class="list-table">
                <thead>
                    <tr>
                        <th>Pilgrim</th>
                        <th>DOB</th>
                        <th>Category</th>
                        <th>Passport</th>
                        <th>CNIC</th>
                        <th>Visa Type</th>
                        <th>Visa Status</th>
                        <th>Booking</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pilgrims)): ?>
                        <tr>
                            <td colspan="10" class="py-6 text-center text-slate-500">No pilgrims found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pilgrims as $pilgrim): ?>
                            <?php
                            $category = 'Other';
                            $ageYears = null;
                            $dob = trim((string) ($pilgrim['date_of_birth'] ?? ''));
                            if ($dob !== '') {
                                try {
                                    $dobDate = new DateTimeImmutable($dob);
                                    $ageYears = $dobDate->diff(new DateTimeImmutable('today'))->y;
                                    $category = $ageYears >= 18 ? 'Adult' : 'Child';
                                } catch (Throwable $e) {
                                    $category = 'Other';
                                    $ageYears = null;
                                }
                            }

                            $categoryLabel = $ageYears !== null ? ($category . ' (' . $ageYears . ')') : $category;
                            $categoryBadgeClass = 'bg-slate-100 text-slate-700';
                            if ($category === 'Adult') {
                                $categoryBadgeClass = 'bg-emerald-100 text-emerald-700';
                            } elseif ($category === 'Child') {
                                $categoryBadgeClass = 'bg-amber-100 text-amber-700';
                            }

                            $dobDisplay = '-';
                            if ($dob !== '') {
                                try {
                                    $dobDisplay = (new DateTimeImmutable($dob))->format('d/m/Y');
                                } catch (Throwable $e) {
                                    $dobDisplay = $dob;
                                }
                            }

                            $visaStatusLabel = (string) ($pilgrim['latest_visa_status'] ?? '-');
                            $visaStatusKey = strtolower(trim($visaStatusLabel));
                            $visaStatusBadgeClass = 'bg-slate-100 text-slate-700';
                            if ($visaStatusKey === 'approved') {
                                $visaStatusBadgeClass = 'bg-emerald-100 text-emerald-700';
                            } elseif ($visaStatusKey === 'submitted') {
                                $visaStatusBadgeClass = 'bg-sky-100 text-sky-700';
                            } elseif ($visaStatusKey === 'rejected') {
                                $visaStatusBadgeClass = 'bg-rose-100 text-rose-700';
                            } elseif ($visaStatusKey === 'draft') {
                                $visaStatusBadgeClass = 'bg-amber-100 text-amber-700';
                            }
                            ?>
                            <tr>
                                <td>
                                    <p class="font-semibold text-slate-800"><?= esc((string) ($pilgrim['full_name'] ?? '-')) ?></p>
                                </td>
                                <td><?= esc($dobDisplay) ?></td>
                                <td>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium <?= esc($categoryBadgeClass) ?>">
                                        <?= esc($categoryLabel) ?>
                                    </span>
                                </td>
                                <td><?= esc($pilgrim['passport_no']) ?></td>
                                <td><?= esc((string) ($pilgrim['cnic'] ?? '-')) ?></td>
                                <td><?= esc((string) ($pilgrim['latest_visa_type'] ?? '-')) ?></td>
                                <td>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium <?= esc($visaStatusBadgeClass) ?>">
                                        <?= esc($visaStatusLabel) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php $bookingStatus = strtolower(trim((string) ($pilgrim['booking_status'] ?? ''))); ?>
                                    <?php if ($bookingStatus === 'confirmed'): ?>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-emerald-100 text-emerald-700">
                                            Confirmed <?= !empty($pilgrim['booking_no']) ? '(' . esc((string) $pilgrim['booking_no']) . ')' : '' ?>
                                        </span>
                                    <?php elseif ($bookingStatus === 'draft'): ?>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-amber-100 text-amber-700">
                                            Draft <?= !empty($pilgrim['booking_no']) ? '(' . esc((string) $pilgrim['booking_no']) . ')' : '' ?>
                                        </span>
                                    <?php elseif ($bookingStatus === 'cancelled'): ?>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-rose-100 text-rose-700">Cancelled</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-slate-100 text-slate-700">Unbooked</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($pilgrim['phone'] ?? '-') ?></td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <a href="<?= site_url('/pilgrims/' . (int) $pilgrim['id'] . '/edit') ?>" class="icon-btn" title="View / Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="post" action="<?= site_url('/pilgrims/delete') ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="pilgrim_id" value="<?= esc($pilgrim['id']) ?>">
                                            <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this pilgrim?')" title="Delete">
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
        </article>
    </section>
</main>
<?php $this->endSection() ?>