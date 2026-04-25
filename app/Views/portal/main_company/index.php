<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-base font-semibold text-slate-800">Main Company Profile</h1>
                <p class="mt-1 text-xs text-slate-500">Manage branding and voucher details used across receipts, vouchers, and print templates.</p>
            </div>
            <a href="<?= site_url('/main-company/settings') ?>" class="btn btn-md btn-outline inline-flex items-center gap-2">
                <i class="fa-solid fa-sliders"></i>
                <span>Voucher & Contact Settings</span>
            </a>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-4 xl:col-span-2">
            <form method="post" action="<?= site_url('/main-company/update') ?>" enctype="multipart/form-data" class="space-y-4">
                <?= csrf_field() ?>

                <div class="rounded-xl border border-slate-200 p-3">
                    <h3 class="text-sm font-semibold text-slate-800">Company Basics</h3>
                    <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Company Name <span class="text-rose-600">*</span></label>
                            <input name="name" value="<?= esc(old('name', (string) ($row['name'] ?? 'KARWAN-E-TAIF PVT LTD'))) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Enter your registered company name">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Tagline</label>
                            <input name="tagline" value="<?= esc(old('tagline', (string) ($row['tagline'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Optional marketing tagline">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Address</label>
                            <textarea name="address" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Street, city, and country"><?= esc(old('address', (string) ($row['address'] ?? ''))) ?></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Phone</label>
                            <input name="phone" value="<?= esc(old('phone', (string) ($row['phone'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Primary company phone">
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Email</label>
                            <input type="email" name="email" value="<?= esc(old('email', (string) ($row['email'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="contact@company.com">
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Website</label>
                            <input name="website" value="<?= esc(old('website', (string) ($row['website'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="https://example.com">
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Logo Image</label>
                            <input type="file" name="logo_file" accept=".jpg,.jpeg,.png,.webp" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php if (!empty($row['logo_url'])): ?><p class="mt-1 text-xs text-slate-500">A logo is currently configured.</p><?php endif; ?>
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">NTN</label>
                            <input name="ntn" value="<?= esc(old('ntn', (string) ($row['ntn'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="National Tax Number">
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">STRN</label>
                            <input name="strn" value="<?= esc(old('strn', (string) ($row['strn'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Sales Tax Registration Number">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-200 pt-3">
                    <button type="submit" class="btn btn-md btn-primary inline-flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Main Company</span>
                    </button>
                </div>
            </form>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <h3 class="text-sm font-semibold text-slate-800">Profile Preview</h3>
            <?php if (!empty($row['logo_url'])): ?><p class="mt-3"><img src="<?= esc((string) $row['logo_url']) ?>" alt="Main Company Logo" style="max-height:80px; max-width:100%; object-fit:contain;"></p><?php endif; ?>
            <p class="mt-3 text-sm font-semibold text-slate-800"><?= esc((string) ($row['name'] ?? 'KARWAN-E-TAIF PVT LTD')) ?></p>
            <?php if (!empty($row['tagline'])): ?><p class="mt-1 text-xs text-slate-600"><?= esc((string) $row['tagline']) ?></p><?php endif; ?>
            <?php if (!empty($row['address'])): ?><p class="mt-2 text-xs text-slate-600"><?= esc((string) $row['address']) ?></p><?php endif; ?>
            <?php if (!empty($row['phone']) || !empty($row['email'])): ?><p class="mt-2 text-xs text-slate-600"><?= esc(trim((string) ($row['phone'] ?? '') . (!empty($row['phone']) && !empty($row['email']) ? ' | ' : '') . (string) ($row['email'] ?? ''))) ?></p><?php endif; ?>
            <?php if (!empty($row['ntn']) || !empty($row['strn'])): ?><p class="mt-2 text-xs text-slate-600"><?= esc(trim((!empty($row['ntn']) ? 'NTN: ' . (string) $row['ntn'] : '') . (!empty($row['ntn']) && !empty($row['strn']) ? ' | ' : '') . (!empty($row['strn']) ? 'STRN: ' . (string) $row['strn'] : ''))) ?></p><?php endif; ?>
        </article>
    </section>
</main>
<?php $this->endSection() ?>