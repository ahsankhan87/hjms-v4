<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-5xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-sm font-semibold text-slate-900">Update Pilgrim</h2>
            <form method="post" action="<?= site_url('/app/pilgrims/update') ?>" enctype="multipart/form-data" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="pilgrim_id" value="<?= esc((string) ($row['id'] ?? '')) ?>">

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">First Name</label>
                        <input name="first_name" value="<?= esc(old('first_name', (string) ($row['first_name'] ?? ''))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Last Name</label>
                        <input name="last_name" value="<?= esc(old('last_name', (string) ($row['last_name'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Father Name</label>
                        <input name="father_name" value="<?= esc(old('father_name', (string) ($row['father_name'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">CNIC No</label>
                        <input name="cnic" value="<?= esc(old('cnic', (string) ($row['cnic'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Passport No</label>
                        <input name="passport_no" value="<?= esc(old('passport_no', (string) ($row['passport_no'] ?? ''))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
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
                        <label class="mb-1 block text-xs font-medium text-slate-600">Gender</label>
                        <?php $genderValue = old('gender', (string) ($row['gender'] ?? 'male')); ?>
                        <select name="gender" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="male" <?= $genderValue === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= $genderValue === 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?= esc(old('date_of_birth', (string) ($row['date_of_birth'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Passport Issue Date</label>
                        <input type="date" name="passport_issue_date" value="<?= esc(old('passport_issue_date', (string) ($row['passport_issue_date'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Passport Expiry Date</label>
                        <input type="date" name="passport_expiry_date" value="<?= esc(old('passport_expiry_date', (string) ($row['passport_expiry_date'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Place of Birth</label>
                        <input name="city" value="<?= esc(old('city', (string) ($row['city'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Mobile No</label>
                        <input name="mobile_no" value="<?= esc(old('mobile_no', (string) ($row['mobile_no'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
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
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Pilgrim Image</label>
                        <input type="file" name="pilgrim_image" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php if (!empty($row['pilgrim_image_path'])): ?><p class="mt-1 text-xs text-slate-500">Current: <?= esc((string) $row['pilgrim_image_name']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Passport Image</label>
                        <input type="file" name="passport_image" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <?php if (!empty($row['passport_image_path'])): ?><p class="mt-1 text-xs text-slate-500">Current: <?= esc((string) $row['passport_image_name']) ?></p><?php endif; ?>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Phone</label>
                        <input name="phone" value="<?= esc(old('phone', (string) ($row['phone'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                        <input type="email" name="email" value="<?= esc(old('email', (string) ($row['email'] ?? ''))) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Description</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('description', (string) ($row['description'] ?? ''))) ?></textarea>
                </div>
                <?php $statusValue = old('is_active', (string) ($row['is_active'] ?? '1')); ?>
                <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="1" <?= $statusValue === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $statusValue === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <button class="btn btn-md btn-primary btn-block">Update Pilgrim</button>
            </form>

            <hr class="my-5 border-slate-200">

            <h2 class="text-sm font-semibold text-slate-900">Delete Pilgrim</h2>
            <form method="post" action="<?= site_url('/app/pilgrims/delete') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="number" name="pilgrim_id" min="1" required placeholder="Pilgrim ID" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button class="btn btn-md btn-danger btn-block">Delete Pilgrim</button>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>