<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-1">
            <h3 class="text-lg font-semibold">Add Company</h3>
            <form method="post" action="<?= site_url('/companies') ?>" enctype="multipart/form-data" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input name="name" value="<?= esc(old('name')) ?>" required placeholder="Company Name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="tagline" value="<?= esc(old('tagline')) ?>" placeholder="Tagline" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <textarea name="address" rows="3" placeholder="Address" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('address')) ?></textarea>
                <input name="phone" value="<?= esc(old('phone')) ?>" placeholder="Phone" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="email" name="email" value="<?= esc(old('email')) ?>" placeholder="Email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="website" value="<?= esc(old('website')) ?>" placeholder="Website" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <div>
                    <label class="text-sm font-medium">Logo Image</label>
                    <input type="file" name="logo_file" accept=".jpg,.jpeg,.png,.webp" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <input name="ntn" value="<?= esc(old('ntn')) ?>" placeholder="NTN" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="strn" value="<?= esc(old('strn')) ?>" placeholder="STRN" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="saudi_partner" value="<?= esc(old('saudi_partner')) ?>" placeholder="Saudi Partner" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button type="submit" class="btn btn-md btn-primary btn-block">Create Company</button>
            </form>

            <hr class="my-5 border-slate-200">

            <h3 class="text-lg font-semibold">Update Company</h3>
            <form method="post" action="<?= site_url('/companies/update') ?>" enctype="multipart/form-data" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="number" name="company_id" min="1" required placeholder="Company ID" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="name" placeholder="Name (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="tagline" placeholder="Tagline (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <textarea name="address" rows="2" placeholder="Address (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                <input name="phone" placeholder="Phone (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="email" name="email" placeholder="Email (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="website" placeholder="Website (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <div>
                    <label class="text-sm font-medium">Logo Image (optional)</label>
                    <input type="file" name="logo_file" accept=".jpg,.jpeg,.png,.webp" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <input name="ntn" placeholder="NTN (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="strn" placeholder="STRN (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="saudi_partner" placeholder="Saudi Partner (optional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="is_active" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Status (optional)</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button type="submit" class="btn btn-md btn-primary btn-block">Update Company</button>
            </form>

            <hr class="my-5 border-slate-200">

            <h3 class="text-lg font-semibold">Delete Company</h3>
            <form method="post" action="<?= site_url('/companies/delete') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="number" name="company_id" min="1" required placeholder="Company ID" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button type="submit" class="btn btn-md btn-danger btn-block">Delete Company</button>
            </form>
        </article>

        <article class="list-card lg:col-span-2 overflow-auto">
            <h3 class="text-lg font-semibold mb-4">Company List</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Phone</th>
                        <th class="px-3 py-2 text-left">Email</th>
                        <th class="px-3 py-2 text-left">NTN</th>
                        <th class="px-3 py-2 text-left">STRN</th>
                        <th class="px-3 py-2 text-left">Active</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-slate-500">No companies found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 font-medium">#<?= esc($row['id']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['name']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['phone'] ?? '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['email'] ?? '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['ntn'] ?? '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['strn'] ?? '-') ?></td>
                                <td class="px-3 py-2"><?= (int) ($row['is_active'] ?? 0) === 1 ? 'Yes' : 'No' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </article>
    </section>
</main>
<?php $this->endSection() ?>