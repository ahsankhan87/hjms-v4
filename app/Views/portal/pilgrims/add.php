<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="w-full space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Create Pilgrim</h3>
            <p class="text-xs text-slate-500">Enter identity, passport, and contact details to register a new pilgrim.</p>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <form method="post" action="<?= site_url('/pilgrims') ?>" enctype="multipart/form-data" class="space-y-5">
                <?= csrf_field() ?>

                <!-- Personal Information -->
                <div class="space-y-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Personal Information</p>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">First Name <span class="text-rose-500">*</span></label>
                            <input name="first_name" value="<?= esc(old('first_name')) ?>" required placeholder="Given name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Last Name</label>
                            <input name="last_name" value="<?= esc(old('last_name')) ?>" placeholder="Family name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Father Name</label>
                            <input name="father_name" value="<?= esc(old('father_name')) ?>" placeholder="Father's name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Gender</label>
                            <select name="gender" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="male" <?= old('gender', 'male') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="<?= esc(old('date_of_birth')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Place of Birth</label>
                            <input name="city" value="<?= esc(old('city')) ?>" placeholder="City" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Document & Identity -->
                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Document &amp; Identity</p>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Passport No <span class="text-rose-500">*</span></label>
                            <input name="passport_no" value="<?= esc(old('passport_no')) ?>" required placeholder="e.g. AB1234567" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">CNIC No</label>
                            <input name="cnic" value="<?= esc(old('cnic')) ?>" placeholder="XXXXX-XXXXXXX-X" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Nationality</label>
                            <select name="country" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="Pakistan" <?= old('country', 'Pakistan') === 'Pakistan' ? 'selected' : '' ?>>Pakistan</option>
                                <option value="Others" <?= old('country') === 'Others' ? 'selected' : '' ?>>Others</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Mahram</label>
                            <select name="mehram" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <?php $mehramValue = old('mehram', ''); ?>
                                <?php $mehramOptions = ['' => 'Please Select', 'Grand Father' => 'Grand Father', 'Father' => 'Father', 'Son' => 'Son', 'Grand Son' => 'Grand Son', 'Brother' => 'Brother', 'Nephew' => 'Nephew', 'Uncle' => 'Uncle', 'Husband' => 'Husband', 'Father in law' => 'Father in law', 'Son-in-law' => 'Son-in-law', 'Stepfather (Mother\'s husband)' => 'Stepfather (Mother\'s husband)', 'Stepson (Husband\'s son)' => 'Stepson (Husband\'s son)', 'Self' => 'Self', 'Women Group' => 'Women Group']; ?>
                                <?php foreach ($mehramOptions as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= $mehramValue === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Passport Issue Date</label>
                            <input type="date" name="passport_issue_date" value="<?= esc(old('passport_issue_date')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Passport Expiry Date</label>
                            <input type="date" name="passport_expiry_date" value="<?= esc(old('passport_expiry_date')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Contact Information</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Mobile No</label>
                            <input name="mobile_no" value="<?= esc(old('mobile_no')) ?>" placeholder="e.g. 03001234567" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Phone</label>
                            <input name="phone" value="<?= esc(old('phone')) ?>" placeholder="Landline" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                            <input type="email" name="email" value="<?= esc(old('email')) ?>" placeholder="name@example.com" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Photos & Notes -->
                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Photos &amp; Notes</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Pilgrim Photo</label>
                            <input type="file" name="pilgrim_image" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Passport Copy</label>
                            <input type="file" name="passport_image" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Notes</label>
                        <textarea name="description" rows="3" placeholder="Additional notes or remarks…" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('description')) ?></textarea>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/pilgrims') ?>" class="btn btn-md btn-secondary">Cancel</a>
                    <button class="btn btn-md btn-primary" type="submit">
                        <i class="fa-solid fa-check"></i><span>Create Pilgrim</span>
                    </button>
                </div>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>