<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-3xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-sm font-semibold text-slate-900">Add Pilgrim</h2>
            <form method="post" action="<?= site_url('/app/pilgrims') ?>" enctype="multipart/form-data" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">First Name</label>
                        <input name="first_name" value="<?= esc(old('first_name')) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Last Name</label>
                        <input name="last_name" value="<?= esc(old('last_name')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Father Name</label>
                    <input name="father_name" value="<?= esc(old('father_name')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Passport No</label>
                        <input name="passport_no" value="<?= esc(old('passport_no')) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">CNIC No</label>
                        <input name="cnic" value="<?= esc(old('cnic')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Nationality</label>
                        <select name="country" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                            <option value="Pakistan" <?= old('country', 'Pakistan') === 'Pakistan' ? 'selected' : '' ?>>Pakistan</option>
                            <option value="Others" <?= old('country') === 'Others' ? 'selected' : '' ?>>Others</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Gender</label>
                        <select name="gender" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                            <option value="male" <?= old('gender', 'male') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?= esc(old('date_of_birth')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Passport Issue Date</label>
                        <input type="date" name="passport_issue_date" value="<?= esc(old('passport_issue_date')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Passport Expiry Date</label>
                        <input type="date" name="passport_expiry_date" value="<?= esc(old('passport_expiry_date')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Place of Birth</label>
                        <input name="city" value="<?= esc(old('city')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Mobile No</label>
                        <input name="mobile_no" value="<?= esc(old('mobile_no')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Mahram</label>
                    <select name="mehram" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                        <?php $mehramValue = old('mehram', ''); ?>
                        <?php $mehramOptions = ['' => 'Please Select', 'Grand Father' => 'Grand Father', 'Father' => 'Father', 'Son' => 'Son', 'Grand Son' => 'Grand Son', 'Brother' => 'Brother', 'Nephew' => 'Nephew', 'Uncle' => 'Uncle', 'Husband' => 'Husband', 'Father in law' => 'Father in law', 'Son-in-law' => 'Son-in-law', 'Stepfather (Mother\'s husband)' => 'Stepfather (Mother\'s husband)', 'Stepson (Husband\'s son)' => 'Stepson (Husband\'s son)', 'Self' => 'Self', 'Women Group' => 'Women Group']; ?>
                        <?php foreach ($mehramOptions as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= $mehramValue === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Pilgrim Image</label>
                        <input type="file" name="pilgrim_image" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Passport Image</label>
                        <input type="file" name="passport_image" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Phone</label>
                    <input name="phone" value="<?= esc(old('phone')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                    <input type="email" name="email" value="<?= esc(old('email')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Description</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none"><?= esc(old('description')) ?></textarea>
                </div>
                <button class="btn btn-md btn-primary btn-block">Create Pilgrim</button>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>