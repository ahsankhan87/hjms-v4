<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-1">
        <article class="lg:col-span-1">
            <div class="list-toolbar">
                <div class="flex space-x-3">
                    <a href="<?= site_url('/app/pilgrims/add') ?>" class="btn btn-md btn-primary">
                        <i class="ri-user-add-line mr-2"></i>Add Pilgrim
                    </a>
                    <a href="<?= site_url('/app/pilgrims/import') ?>" class="btn btn-md btn-secondary">
                        <i class="fa-solid fa-upload mr-2"></i>Import CSV
                    </a>
                </div>
            </div>
            <div class="list-card">
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
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pilgrims)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-slate-500">No pilgrims found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pilgrims as $pilgrim): ?>
                                <?php $initial = strtoupper(substr((string) ($pilgrim['full_name'] ?? 'P'), 0, 1)); ?>
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
                                        <div class="flex items-center space-x-3">
                                            <!-- <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                                <span class="text-emerald-600 font-semibold"><?= esc($initial) ?></span>
                                            </div> -->
                                            <div>
                                                <p class="font-semibold text-gray-800"><?= esc((string) ($pilgrim['full_name'] ?? '-')) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= esc($dobDisplay) ?></td>
                                    <td>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium <?= esc($categoryBadgeClass) ?>">
                                            <?= esc($categoryLabel) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($pilgrim['passport_no']) ?></td>
                                    <td><?= esc((string) ($pilgrim['cnic'] ?? '-')) ?></td>
                                    <td><?= esc((string) ($pilgrim['latest_visa_type'] ?? '-')) ?></td>
                                    <td>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium <?= esc($visaStatusBadgeClass) ?>">
                                            <?= esc($visaStatusLabel) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($pilgrim['phone'] ?? '-') ?></td>
                                    <td>
                                        <div class="flex space-x-2">
                                            <a href="<?= site_url('/app/pilgrims/' . (int) $pilgrim['id'] . '/edit') ?>" class="icon-btn" title="View / Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form method="post" action="<?= site_url('/app/pilgrims/delete') ?>" class="inline">
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
                <div class="list-footer">
                    <p>Showing 1-<?= esc(count($pilgrims)) ?> of <?= esc(count($pilgrims)) ?> pilgrims</p>
                    <div class="flex space-x-2">
                        <button type="button" class="btn btn-sm btn-secondary">Previous</button>
                        <button type="button" class="btn btn-sm btn-primary">1</button>
                        <button type="button" class="btn btn-sm btn-secondary">Next</button>
                    </div>
                </div>
            </div>
        </article>
    </section>
</main>
<?php $this->endSection() ?>