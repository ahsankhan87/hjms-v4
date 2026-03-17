<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="w-full space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Edit Pilgrim</h3>
            <p class="text-xs text-slate-500">Update pilgrim profile, documents, and account status.</p>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <form method="post" action="<?= site_url('/pilgrims/update') ?>" enctype="multipart/form-data" class="space-y-5">
                <?= csrf_field() ?>
                <input type="hidden" name="pilgrim_id" value="<?= esc((string) ($row['id'] ?? '')) ?>">

                <!-- Personal Information -->
                <div class="space-y-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Personal Information</p>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">First Name <span class="text-rose-500">*</span></label>
                            <input name="first_name" value="<?= esc(old('first_name', (string) ($row['first_name'] ?? ''))) ?>" required placeholder="Given name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Last Name</label>
                            <input name="last_name" value="<?= esc(old('last_name', (string) ($row['last_name'] ?? ''))) ?>" placeholder="Family name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Father Name</label>
                            <input name="father_name" value="<?= esc(old('father_name', (string) ($row['father_name'] ?? ''))) ?>" placeholder="Father's name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Gender</label>
                            <?php $genderValue = old('gender', (string) ($row['gender'] ?? 'male')); ?>
                            <select name="gender" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="male" <?= $genderValue === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $genderValue === 'female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="<?= esc(old('date_of_birth', (string) ($row['date_of_birth'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Place of Birth</label>
                            <input name="city" value="<?= esc(old('city', (string) ($row['city'] ?? ''))) ?>" placeholder="City" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Document & Identity -->
                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Document &amp; Identity</p>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Passport No <span class="text-rose-500">*</span></label>
                            <input name="passport_no" value="<?= esc(old('passport_no', (string) ($row['passport_no'] ?? ''))) ?>" required placeholder="e.g. AB1234567" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">CNIC No</label>
                            <input name="cnic" value="<?= esc(old('cnic', (string) ($row['cnic'] ?? ''))) ?>" placeholder="XXXXX-XXXXXXX-X" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Nationality</label>
                            <?php $countryValue = old('country', (string) ($row['country'] ?? 'Pakistan')); ?>
                            <select name="country" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="Pakistan" <?= $countryValue === 'Pakistan' ? 'selected' : '' ?>>Pakistan</option>
                                <option value="Others" <?= $countryValue === 'Others' ? 'selected' : '' ?>>Others</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Mahram</label>
                            <?php $mehramValue = old('mehram', (string) ($row['mehram'] ?? '')); ?>
                            <?php $mehramOptions = ['' => 'Please Select', 'Grand Father' => 'Grand Father', 'Father' => 'Father', 'Son' => 'Son', 'Grand Son' => 'Grand Son', 'Brother' => 'Brother', 'Nephew' => 'Nephew', 'Uncle' => 'Uncle', 'Husband' => 'Husband', 'Father in law' => 'Father in law', 'Son-in-law' => 'Son-in-law', 'Stepfather (Mother\'s husband)' => 'Stepfather (Mother\'s husband)', 'Stepson (Husband\'s son)' => 'Stepson (Husband\'s son)', 'Self' => 'Self', 'Women Group' => 'Women Group']; ?>
                            <select name="mehram" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <?php foreach ($mehramOptions as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= $mehramValue === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Passport Issue Date</label>
                            <input type="date" name="passport_issue_date" value="<?= esc(old('passport_issue_date', (string) ($row['passport_issue_date'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Passport Expiry Date</label>
                            <input type="date" name="passport_expiry_date" value="<?= esc(old('passport_expiry_date', (string) ($row['passport_expiry_date'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Contact Information</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Mobile No</label>
                            <input name="mobile_no" value="<?= esc(old('mobile_no', (string) ($row['mobile_no'] ?? ''))) ?>" placeholder="e.g. 03001234567" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Phone</label>
                            <input name="phone" value="<?= esc(old('phone', (string) ($row['phone'] ?? ''))) ?>" placeholder="Landline" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                            <input type="email" name="email" value="<?= esc(old('email', (string) ($row['email'] ?? ''))) ?>" placeholder="name@example.com" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Photos, Status & Notes -->
                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Photos, Status &amp; Notes</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Pilgrim Photo</label>
                            <input type="file" name="pilgrim_image" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php if (!empty($row['pilgrim_image_path'])): ?><p class="mt-1 text-xs text-slate-400">Current: <?= esc((string) $row['pilgrim_image_name']) ?></p><?php endif; ?>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Passport Copy</label>
                            <input type="file" name="passport_image" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php if (!empty($row['passport_image_path'])): ?><p class="mt-1 text-xs text-slate-400">Current: <?= esc((string) $row['passport_image_name']) ?></p><?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                        <?php $statusValue = old('is_active', (string) ($row['is_active'] ?? '1')); ?>
                        <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="1" <?= $statusValue === '1' ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= $statusValue === '0' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Notes</label>
                        <textarea name="description" rows="3" placeholder="Additional notes or remarks…" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('description', (string) ($row['description'] ?? ''))) ?></textarea>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
                    <button
                        class="btn btn-md btn-danger"
                        type="submit"
                        formaction="<?= site_url('/pilgrims/delete') ?>"
                        formmethod="post"
                        onclick="return confirm('Delete this pilgrim?');">
                        <i class="fa-solid fa-trash"></i><span>Delete</span>
                    </button>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="<?= site_url('/pilgrims') ?>" class="btn btn-md btn-secondary">Back</a>
                        <button class="btn btn-md btn-primary" type="submit">
                            <i class="fa-solid fa-check"></i><span>Update Pilgrim</span>
                        </button>
                    </div>
                </div>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>