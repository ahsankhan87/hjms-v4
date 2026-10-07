<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<?php
$fullName = trim((string) ($row['full_name'] ?? ''));
if ($fullName === '') {
    $fullName = 'Pilgrim';
}

$isActive = (int) ($row['is_active'] ?? 1) === 1;

$dob = trim((string) ($row['date_of_birth'] ?? ''));
$ageYears = null;
if ($dob !== '') {
    try {
        $ageYears = (new DateTimeImmutable($dob))->diff(new DateTimeImmutable('today'))->y;
    } catch (Throwable $e) {
        $ageYears = null;
    }
}

$gender = strtolower(trim((string) ($row['gender'] ?? '')));
$genderLabel = $gender === 'male' ? 'Male' : ($gender === 'female' ? 'Female' : '—');

$initials = strtoupper(mb_substr((string) ($row['first_name'] ?? ''), 0, 1) . mb_substr((string) ($row['last_name'] ?? ''), 0, 1));
if (trim($initials) === '') {
    $initials = 'P';
}

$fmtDate = static function ($value): string {
    $value = trim((string) $value);
    if ($value === '') {
        return '—';
    }
    try {
        return (new DateTimeImmutable($value))->format('d M Y');
    } catch (Throwable $e) {
        return $value;
    }
};

$fmtDateTime = static function ($value): string {
    $value = trim((string) $value);
    if ($value === '') {
        return '—';
    }
    try {
        return (new DateTimeImmutable($value))->format('d M Y, h:i A');
    } catch (Throwable $e) {
        return $value;
    }
};

$attachmentUrl = static function (string $path, string $subdir = 'pilgrims'): string {
    return asset_upload_url($path, $subdir);
};

$latestBooking = $bookings[0] ?? null;

$visaStatus = strtolower(trim((string) ($visa['status'] ?? '')));
$visaStatusClasses = 'bg-slate-100 text-slate-600';
if ($visaStatus === 'approved') {
    $visaStatusClasses = 'bg-emerald-100 text-emerald-700';
} elseif ($visaStatus === 'submitted') {
    $visaStatusClasses = 'bg-sky-100 text-sky-700';
} elseif ($visaStatus === 'rejected') {
    $visaStatusClasses = 'bg-rose-100 text-rose-700';
} elseif ($visaStatus === 'draft') {
    $visaStatusClasses = 'bg-amber-100 text-amber-700';
}

$bookingStatusClasses = static function (string $status): string {
    $status = strtolower(trim($status));
    if ($status === 'confirmed') {
        return 'bg-emerald-100 text-emerald-700';
    }
    if ($status === 'draft') {
        return 'bg-amber-100 text-amber-700';
    }
    if ($status === 'cancelled') {
        return 'bg-rose-100 text-rose-700';
    }

    return 'bg-slate-100 text-slate-600';
};
?>
<main class="space-y-4">

    <?php if (!empty($success)): ?>
        <div class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <p class="text-xs text-emerald-700"><?= esc($success) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2">
            <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
            <p class="text-xs text-rose-700"><?= esc($error) ?></p>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="<?= site_url('/pilgrims') ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50" title="Back to Pilgrims">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900"><?= esc($fullName) ?></h1>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold <?= $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' ?>">
                        <?= $isActive ? 'Active' : 'Inactive' ?>
                    </span>
                    <?php if (!empty($row['blood_group'])): ?>
                        <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                            <i class="fa-solid fa-droplet mr-1"></i><?= esc((string) $row['blood_group']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <p class="mt-0.5 text-sm text-slate-500">Pilgrim profile, documents, and booking history</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="<?= site_url('/pilgrims/' . (int) ($row['id'] ?? 0) . '/edit') ?>" class="btn btn-sm btn-secondary">
                <i class="fa-solid fa-pen"></i><span>Edit</span>
            </a>
            <form method="post" action="<?= site_url('/pilgrims/delete') ?>" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="pilgrim_id" value="<?= esc((string) ($row['id'] ?? '')) ?>">
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this pilgrim?');">
                    <i class="fa-solid fa-trash"></i><span>Delete</span>
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Left: main details -->
        <div class="space-y-5 lg:col-span-2">

            <!-- Profile hero -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-gradient-to-r from-sky-600 via-sky-500 to-cyan-500 px-6 py-6">
                    <div class="flex flex-wrap items-center gap-5">
                        <?php if (!empty($row['pilgrim_image_path'])): ?>
                            <img src="<?= esc($attachmentUrl((string) $row['pilgrim_image_path'])) ?>" alt="<?= esc($fullName) ?>" class="h-24 w-24 rounded-2xl object-cover ring-4 ring-white/30">
                        <?php else: ?>
                            <div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-white/20 text-3xl font-bold text-white ring-4 ring-white/30">
                                <?= esc($initials) ?>
                            </div>
                        <?php endif; ?>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-widest text-white/70">Pilgrim</p>
                            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-white"><?= esc($fullName) ?></h2>
                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-white/90">
                                <span><i class="fa-solid fa-passport mr-1.5"></i><?= esc((string) ($row['passport_no'] ?? '—')) ?></span>
                                <span><i class="fa-solid fa-flag mr-1.5"></i><?= esc((string) ($row['country'] ?? '—') ?: '—') ?></span>
                                <?php if ($ageYears !== null): ?>
                                    <span><i class="fa-solid fa-cake-candles mr-1.5"></i><?= esc((string) $ageYears) ?> yrs</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 divide-x divide-slate-100 sm:grid-cols-4">
                    <div class="px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Reference</p>
                        <p class="mt-0.5 truncate text-sm font-semibold text-slate-800"><?= esc((string) ($row['reference'] ?? '') ?: '—') ?></p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Blood Group</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-800"><?= esc((string) ($row['blood_group'] ?? '') ?: '—') ?></p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Gender</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-800"><?= esc($genderLabel) ?></p>
                    </div>
                    <div class="px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Season</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-800">#<?= esc((string) ($row['season_id'] ?? '—')) ?></p>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 px-6 py-4">
                    <i class="fa-solid fa-user text-slate-400"></i>
                    <h2 class="font-semibold text-slate-900">Personal Information</h2>
                </div>
                <dl class="divide-y divide-slate-100">
                    <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm text-slate-500">Full Name</dt>
                        <dd class="text-sm font-semibold text-slate-900 sm:col-span-2"><?= esc($fullName) ?></dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm text-slate-500">Father Name</dt>
                        <dd class="text-sm font-semibold text-slate-900 sm:col-span-2"><?= esc((string) ($row['father_name'] ?? '') ?: '—') ?></dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm text-slate-500">Gender</dt>
                        <dd class="text-sm font-semibold text-slate-900 sm:col-span-2"><?= esc($genderLabel) ?></dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm text-slate-500">Date of Birth</dt>
                        <dd class="text-sm font-semibold text-slate-900 sm:col-span-2">
                            <?= esc($fmtDate($row['date_of_birth'] ?? '')) ?>
                            <?php if ($ageYears !== null): ?>
                                <span class="ml-1 text-xs font-normal text-slate-400">(<?= esc((string) $ageYears) ?> years)</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm text-slate-500">Place of Birth</dt>
                        <dd class="text-sm font-semibold text-slate-900 sm:col-span-2"><?= esc((string) ($row['city'] ?? '') ?: '—') ?></dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm text-slate-500">Nationality</dt>
                        <dd class="text-sm font-semibold text-slate-900 sm:col-span-2"><?= esc((string) ($row['country'] ?? '') ?: '—') ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Document & Identity -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 px-6 py-4">
                    <i class="fa-solid fa-id-card text-slate-400"></i>
                    <h2 class="font-semibold text-slate-900">Document &amp; Identity</h2>
                </div>
                <dl class="divide-y divide-slate-100">
                    <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm text-slate-500">Passport No</dt>
                        <dd class="font-mono text-sm font-semibold text-slate-900 sm:col-span-2"><?= esc((string) ($row['passport_no'] ?? '') ?: '—') ?></dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm text-slate-500">CNIC No</dt>
                        <dd class="font-mono text-sm font-semibold text-slate-900 sm:col-span-2"><?= esc((string) ($row['cnic'] ?? '') ?: '—') ?></dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm text-slate-500">Passport Issue Date</dt>
                        <dd class="text-sm font-semibold text-slate-900 sm:col-span-2"><?= esc($fmtDate($row['passport_issue_date'] ?? '')) ?></dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm text-slate-500">Passport Expiry Date</dt>
                        <dd class="text-sm font-semibold text-slate-900 sm:col-span-2">
                            <?= esc($fmtDate($row['passport_expiry_date'] ?? '')) ?>
                            <?php
                            $expiryRaw = trim((string) ($row['passport_expiry_date'] ?? ''));
                            if ($expiryRaw !== '') {
                                try {
                                    $expiry = new DateTimeImmutable($expiryRaw);
                                    $today = new DateTimeImmutable('today');
                                    if ($expiry < $today) {
                                        echo '<span class="ml-2 rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700">Expired</span>';
                                    } elseif ($expiry < $today->modify('+6 months')) {
                                        echo '<span class="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Expiring soon</span>';
                                    }
                                } catch (Throwable $e) {
                                    // ignore
                                }
                            }
                            ?>
                        </dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                        <dt class="text-sm text-slate-500">Mahram</dt>
                        <dd class="text-sm font-semibold text-slate-900 sm:col-span-2"><?= esc((string) ($row['mehram'] ?? '') ?: '—') ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Contact Information -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 px-6 py-4">
                    <i class="fa-solid fa-address-book text-slate-400"></i>
                    <h2 class="font-semibold text-slate-900">Contact Information</h2>
                </div>
                <div class="grid grid-cols-1 gap-3 p-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"><i class="fa-solid fa-mobile-screen mr-1"></i>Mobile</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?= esc((string) ($row['mobile_no'] ?? '') ?: '—') ?></p>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"><i class="fa-solid fa-phone mr-1"></i>Phone</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800"><?= esc((string) ($row['phone'] ?? '') ?: '—') ?></p>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"><i class="fa-solid fa-envelope mr-1"></i>Email</p>
                        <p class="mt-1 truncate text-sm font-semibold text-slate-800"><?= esc((string) ($row['email'] ?? '') ?: '—') ?></p>
                    </div>
                </div>
            </div>

            <!-- Medical & Notes -->
            <?php if (!empty($row['blood_group']) || !empty($row['medical_notes']) || !empty($row['description'])): ?>
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-2 border-b border-slate-100 px-6 py-4">
                        <i class="fa-solid fa-notes-medical text-slate-400"></i>
                        <h2 class="font-semibold text-slate-900">Medical &amp; Notes</h2>
                    </div>
                    <dl class="divide-y divide-slate-100">
                        <?php if (!empty($row['blood_group'])): ?>
                            <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                                <dt class="text-sm text-slate-500">Blood Group</dt>
                                <dd class="text-sm font-semibold text-rose-700 sm:col-span-2"><?= esc((string) $row['blood_group']) ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($row['medical_notes'])): ?>
                            <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                                <dt class="text-sm text-slate-500">Medical Notes</dt>
                                <dd class="whitespace-pre-line text-sm text-slate-700 sm:col-span-2"><?= esc((string) $row['medical_notes']) ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($row['description'])): ?>
                            <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-3">
                                <dt class="text-sm text-slate-500">Notes</dt>
                                <dd class="whitespace-pre-line text-sm text-slate-700 sm:col-span-2"><?= esc((string) $row['description']) ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>
            <?php endif; ?>

            <!-- Travel History -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-route text-slate-400"></i>
                        <h2 class="font-semibold text-slate-900">Travel History</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600"><?= (int) ($historySummary['trips'] ?? 0) ?> trip(s)</span>
                </div>

                <?php if (!empty($travelHistory)): ?>
                    <div class="grid grid-cols-2 gap-3 border-b border-slate-100 px-6 py-4 sm:grid-cols-4">
                        <div class="rounded-lg bg-slate-50 px-3 py-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total Trips</p>
                            <p class="mt-0.5 text-lg font-bold text-slate-800"><?= (int) ($historySummary['trips'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 px-3 py-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600">Completed</p>
                            <p class="mt-0.5 text-lg font-bold text-emerald-700"><?= (int) ($historySummary['completed'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-lg bg-sky-50 px-3 py-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-sky-600">Total Billed</p>
                            <p class="mt-0.5 text-lg font-bold text-sky-700"><?= esc(number_format((float) ($historySummary['billed'] ?? 0), 2)) ?></p>
                        </div>
                        <div class="rounded-lg bg-amber-50 px-3 py-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-600">Outstanding</p>
                            <p class="mt-0.5 text-lg font-bold text-amber-700"><?= esc(number_format((float) ($historySummary['outstanding'] ?? 0), 2)) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($travelHistory)): ?>
                    <p class="px-6 py-8 text-center text-sm text-slate-400">
                        No travel history found<?= !empty($row['passport_no']) ? ' (matched by passport ' . esc((string) $row['passport_no']) . ')' : '' ?>.
                    </p>
                <?php else: ?>
                    <ol class="divide-y divide-slate-100">
                        <?php foreach ($travelHistory as $index => $trip): ?>
                            <?php
                            $tripStatus = strtolower(trim((string) ($trip['status'] ?? '')));
                            $tripBadgeClass = 'bg-slate-100 text-slate-600';
                            if ($tripStatus === 'confirmed') {
                                $tripBadgeClass = 'bg-emerald-100 text-emerald-700';
                            } elseif ($tripStatus === 'draft') {
                                $tripBadgeClass = 'bg-amber-100 text-amber-700';
                            } elseif ($tripStatus === 'cancelled') {
                                $tripBadgeClass = 'bg-rose-100 text-rose-700';
                            }

                            $packageLabel = trim((string) ($trip['package_name'] ?? ''));
                            if ($packageLabel === '') {
                                $packageLabel = 'Package';
                            }

                            $seasonName = trim((string) ($trip['season_name'] ?? ''));
                            $seasonYears = trim((string) ($trip['year_start'] ?? '') . '-' . (string) ($trip['year_end'] ?? ''), '-');
                            $seasonLabel = $seasonName !== '' ? $seasonName : ($seasonYears !== '' ? ('Season ' . $seasonYears) : '');
                            $tripVisa = $trip['visa'] ?? null;
                            ?>
                            <li class="px-6 py-4">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sky-100 text-xs font-bold text-sky-700"><?= (int) $index + 1 ?></span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900"><?= esc($packageLabel) ?></p>
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                <?php if ($seasonLabel !== ''): ?><span class="font-medium text-slate-600"><?= esc($seasonLabel) ?></span> &middot; <?php endif; ?>
                                                Booking
                                                <a href="<?= site_url('/bookings/' . (int) ($trip['id'] ?? 0) . '/edit') ?>" class="font-semibold text-sky-600 hover:underline"><?= esc((string) ($trip['booking_no'] ?? ('#' . ($trip['id'] ?? '')))) ?></a>
                                                <?php if (!empty($trip['package_code'])): ?> &middot; <span class="font-mono"><?= esc((string) $trip['package_code']) ?></span><?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold <?= $tripBadgeClass ?>"><?= esc(ucfirst($tripStatus !== '' ? $tripStatus : 'unknown')) ?></span>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Departure</p>
                                        <p class="mt-0.5 text-xs font-semibold text-slate-700"><?= esc($fmtDate($trip['departure_date'] ?? '')) ?></p>
                                    </div>
                                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Return</p>
                                        <p class="mt-0.5 text-xs font-semibold text-slate-700"><?= esc($fmtDate($trip['arrival_date'] ?? '')) ?></p>
                                    </div>
                                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Billed</p>
                                        <p class="mt-0.5 text-xs font-semibold text-slate-700"><?= esc(number_format((float) ($trip['total_amount'] ?? 0), 2)) ?></p>
                                    </div>
                                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Paid / Due</p>
                                        <p class="mt-0.5 text-xs font-semibold text-slate-700">
                                            <?= esc(number_format((float) ($trip['paid_amount'] ?? 0), 2)) ?> /
                                            <span class="<?= (float) ($trip['outstanding_amount'] ?? 0) > 0 ? 'text-rose-600' : 'text-emerald-600' ?>"><?= esc(number_format((float) ($trip['outstanding_amount'] ?? 0), 2)) ?></span>
                                        </p>
                                    </div>
                                </div>

                                <?php if (!empty($trip['package_type']) || !empty($tripVisa)): ?>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                                        <?php if (!empty($trip['package_type'])): ?>
                                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 font-medium text-indigo-700"><?= esc(ucfirst((string) $trip['package_type'])) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($tripVisa['visa_type'])): ?>
                                            <span class="rounded-full bg-violet-50 px-2 py-0.5 font-medium text-violet-700">Visa: <?= esc((string) $tripVisa['visa_type']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($tripVisa['status'])): ?>
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 font-medium text-slate-600">Visa <?= esc((string) $tripVisa['status']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($tripVisa['visa_no'])): ?>
                                            <span class="font-mono text-slate-500"><?= esc((string) $tripVisa['visa_no']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right sidebar -->
        <div class="space-y-4">

            <!-- Availability / Re-avail -->
            <div class="rounded-xl border <?= !empty($isAvailed) ? 'border-amber-200' : 'border-emerald-200' ?> bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-plane-departure text-slate-400"></i>
                        <h2 class="font-semibold text-slate-900">Trip Availability</h2>
                    </div>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold <?= !empty($isAvailed) ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?>">
                        <?= !empty($isAvailed) ? 'Availed' : 'Available' ?>
                    </span>
                </div>
                <div class="space-y-3 px-5 py-4">
                    <?php if (!empty($isAvailed)): ?>
                        <p class="text-xs text-slate-600">
                            This pilgrim is availed<?= !empty($availedSource['label']) ? ' (<span class="font-semibold">' . esc((string) $availedSource['label']) . '</span>)' : '' ?>
                            in the active season, so they are hidden from booking &amp; sales lists.
                        </p>
                        <form method="post" action="<?= site_url('/pilgrims/release') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="pilgrim_id" value="<?= esc((string) ($row['id'] ?? '')) ?>">
                            <button type="submit" class="btn btn-sm btn-primary w-full" onclick="return confirm('Allow this pilgrim to be sold/booked again for a new trip?');">
                                <i class="fa-solid fa-rotate-right"></i><span>Release for New Trip</span>
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-xs text-slate-600">
                            This pilgrim is available for a new booking or sale in the active season.
                        </p>
                    <?php endif; ?>

                    <div class="rounded-lg bg-slate-50 px-3 py-2 text-[11px] text-slate-500">
                        <div class="flex items-center justify-between">
                            <span>Total trips</span>
                            <span class="font-semibold text-slate-700"><?= (int) ($historySummary['trips'] ?? 0) ?></span>
                        </div>
                        <div class="mt-1 flex items-center justify-between">
                            <span>Last released</span>
                            <span class="font-semibold text-slate-700"><?= !empty($row['sale_released_at']) ? esc($fmtDateTime($row['sale_released_at'])) : '—' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status / meta -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">Record</h2>
                </div>
                <dl class="divide-y divide-slate-100">
                    <div class="flex items-center justify-between px-5 py-3">
                        <dt class="text-sm text-slate-500">Status</dt>
                        <dd>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold <?= $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' ?>">
                                <?= $isActive ? 'Active' : 'Inactive' ?>
                            </span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <dt class="text-sm text-slate-500">Agent</dt>
                        <dd class="text-sm font-semibold text-slate-800"><?= esc($agentName !== '' ? $agentName : '—') ?></dd>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <dt class="text-sm text-slate-500">Created</dt>
                        <dd class="text-xs text-slate-600"><?= esc($fmtDateTime($row['created_at'] ?? '')) ?></dd>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <dt class="text-sm text-slate-500">Updated</dt>
                        <dd class="text-xs text-slate-600"><?= esc($fmtDateTime($row['updated_at'] ?? '')) ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Visa -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-passport text-slate-400"></i>
                        <h2 class="font-semibold text-slate-900">Latest Visa</h2>
                    </div>
                    <?php if (!empty($visa['status'])): ?>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold <?= esc($visaStatusClasses) ?>"><?= esc(ucfirst($visaStatus)) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (empty($visa)): ?>
                    <p class="px-5 py-6 text-center text-sm text-slate-400">No visa record for this pilgrim.</p>
                <?php else: ?>
                    <dl class="divide-y divide-slate-100">
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-sm text-slate-500">Visa No</dt>
                            <dd class="font-mono text-sm font-semibold text-slate-800"><?= esc((string) ($visa['visa_no'] ?? '') ?: '—') ?></dd>
                        </div>
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-sm text-slate-500">Type</dt>
                            <dd class="text-sm font-semibold text-slate-800"><?= esc((string) ($visa['visa_type'] ?? '') ?: '—') ?></dd>
                        </div>
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-sm text-slate-500">Submitted</dt>
                            <dd class="text-xs text-slate-600"><?= esc($fmtDate($visa['submission_date'] ?? '')) ?></dd>
                        </div>
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-sm text-slate-500">Approved</dt>
                            <dd class="text-xs text-slate-600"><?= esc($fmtDate($visa['approval_date'] ?? '')) ?></dd>
                        </div>
                        <?php if (!empty($visa['rejection_reason'])): ?>
                            <div class="px-5 py-3">
                                <dt class="text-sm text-slate-500">Rejection Reason</dt>
                                <dd class="mt-1 whitespace-pre-line text-sm text-rose-700"><?= esc((string) $visa['rejection_reason']) ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($visa['visa_file_path'])): ?>
                            <div class="px-5 py-3">
                                <a href="<?= esc($attachmentUrl((string) $visa['visa_file_path'], 'visas')) ?>" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-50">
                                    <i class="fa-solid fa-file-arrow-down"></i><?= esc((string) ($visa['visa_file_name'] ?? 'Visa Document')) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </dl>
                <?php endif; ?>
            </div>

            <!-- Attachments -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                    <i class="fa-solid fa-paperclip text-slate-400"></i>
                    <h2 class="font-semibold text-slate-900">Attachments</h2>
                </div>
                <div class="space-y-2 p-5">
                    <?php if (!empty($row['pilgrim_image_path'])): ?>
                        <a href="<?= esc($attachmentUrl((string) $row['pilgrim_image_path'])) ?>" target="_blank" class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-100">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100 text-sky-600"><i class="fa-solid fa-image"></i></span>
                            <span class="min-w-0 flex-1 truncate"><?= esc((string) ($row['pilgrim_image_name'] ?? 'Pilgrim Photo')) ?></span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs text-slate-400"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($row['passport_image_path'])): ?>
                        <a href="<?= esc($attachmentUrl((string) $row['passport_image_path'])) ?>" target="_blank" class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-100">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 text-violet-600"><i class="fa-solid fa-id-card"></i></span>
                            <span class="min-w-0 flex-1 truncate"><?= esc((string) ($row['passport_image_name'] ?? 'Passport Copy')) ?></span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs text-slate-400"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($row['attachment_1'])): ?>
                        <a href="<?= esc($attachmentUrl((string) $row['attachment_1'])) ?>" target="_blank" class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-100">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600"><i class="fa-solid fa-file-lines"></i></span>
                            <span class="min-w-0 flex-1"><span class="block text-xs font-semibold text-slate-800">Attachment 1</span><span class="block truncate text-xs text-slate-500"><?= esc(basename((string) $row['attachment_1'])) ?></span></span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs text-slate-400"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($row['attachment_2'])): ?>
                        <a href="<?= esc($attachmentUrl((string) $row['attachment_2'])) ?>" target="_blank" class="flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-100">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600"><i class="fa-solid fa-file-lines"></i></span>
                            <span class="min-w-0 flex-1"><span class="block text-xs font-semibold text-slate-800">Attachment 2</span><span class="block truncate text-xs text-slate-500"><?= esc(basename((string) $row['attachment_2'])) ?></span></span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs text-slate-400"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (empty($row['pilgrim_image_path']) && empty($row['passport_image_path']) && empty($row['attachment_1']) && empty($row['attachment_2'])): ?>
                        <p class="py-4 text-center text-sm text-slate-400">No attachments uploaded.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bookings -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-slate-400"></i>
                        <h2 class="font-semibold text-slate-900">Bookings</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600"><?= count($bookings) ?></span>
                </div>
                <?php if (empty($bookings)): ?>
                    <p class="px-5 py-6 text-center text-sm text-slate-400">No bookings linked to this pilgrim.</p>
                <?php else: ?>
                    <ul class="divide-y divide-slate-100">
                        <?php foreach ($bookings as $booking): ?>
                            <li class="px-5 py-3">
                                <div class="flex items-center justify-between gap-2">
                                    <a href="<?= site_url('/bookings/' . (int) ($booking['id'] ?? 0) . '/edit') ?>" class="text-sm font-semibold text-sky-700 hover:underline">
                                        <?= esc((string) ($booking['booking_no'] ?? ('Booking #' . ($booking['id'] ?? '')))) ?>
                                    </a>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold <?= esc($bookingStatusClasses((string) ($booking['status'] ?? ''))) ?>">
                                        <?= esc(ucfirst((string) ($booking['status'] ?? 'unknown'))) ?>
                                    </span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
                                    <span><?= esc($fmtDate($booking['booking_date'] ?? '')) ?></span>
                                    <span class="font-semibold text-slate-700"><?= esc(number_format((float) ($booking['total_amount'] ?? 0), 2)) ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php $this->endSection() ?>