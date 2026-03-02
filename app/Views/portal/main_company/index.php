<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-2">
            <h3 class="text-lg font-semibold">Main Company</h3>
            <p class="mt-1 text-sm text-slate-500">This is your own company profile used in receipts and system branding.</p>
            <form method="post" action="<?= site_url('/main-company/update') ?>" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <?= csrf_field() ?>
                <div class="md:col-span-2"><label class="text-sm font-medium">Company Name</label><input name="name" value="<?= esc(old('name', (string) ($row['name'] ?? 'KARWAN-E-TAIF PVT LTD'))) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div class="md:col-span-2"><label class="text-sm font-medium">Tagline</label><input name="tagline" value="<?= esc(old('tagline', (string) ($row['tagline'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div class="md:col-span-2"><label class="text-sm font-medium">Address</label><textarea name="address" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('address', (string) ($row['address'] ?? ''))) ?></textarea></div>
                <div><label class="text-sm font-medium">Phone</label><input name="phone" value="<?= esc(old('phone', (string) ($row['phone'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label class="text-sm font-medium">Email</label><input type="email" name="email" value="<?= esc(old('email', (string) ($row['email'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label class="text-sm font-medium">Website</label><input name="website" value="<?= esc(old('website', (string) ($row['website'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div>
                    <label class="text-sm font-medium">Logo Image</label>
                    <input type="file" name="logo_file" accept=".jpg,.jpeg,.png,.webp" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <?php if (!empty($row['logo_url'])): ?><p class="mt-1 text-xs text-slate-500">Current logo is already set.</p><?php endif; ?>
                </div>
                <div><label class="text-sm font-medium">NTN</label><input name="ntn" value="<?= esc(old('ntn', (string) ($row['ntn'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label class="text-sm font-medium">STRN</label><input name="strn" value="<?= esc(old('strn', (string) ($row['strn'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div class="md:col-span-2">
                    <h4 class="text-base font-semibold text-slate-700">Voucher Content (Urdu - Primary)</h4>
                </div>
                <div class="md:col-span-2"><label class="text-sm font-medium">Voucher Instructions (Urdu)</label><textarea name="voucher_instructions_ur" rows="6" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" dir="rtl"><?= esc(old('voucher_instructions_ur', (string) ($row['voucher_instructions_ur'] ?? ''))) ?></textarea></div>

                <div class="md:col-span-2">
                    <h4 class="text-base font-semibold text-slate-700">Voucher Content (English - Optional)</h4>
                </div>
                <div class="md:col-span-2"><label class="text-sm font-medium">Voucher Instructions (English)</label><textarea name="voucher_instructions_en" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('voucher_instructions_en', (string) ($row['voucher_instructions_en'] ?? ''))) ?></textarea></div>

                <div class="md:col-span-2">
                    <h4 class="text-base font-semibold text-slate-700">Contact Details (English)</h4>
                </div>
                <div><label class="text-sm font-medium">Makkah Office Contact (English)</label><input name="makkah_contact_en" value="<?= esc(old('makkah_contact_en', (string) ($row['makkah_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label class="text-sm font-medium">Madina Office Contact (English)</label><input name="madina_contact_en" value="<?= esc(old('madina_contact_en', (string) ($row['madina_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div class="md:col-span-2"><label class="text-sm font-medium">Transport Contact (English)</label><input name="transport_contact_en" value="<?= esc(old('transport_contact_en', (string) ($row['transport_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>

                <div><label class="text-sm font-medium">Makkah Office Contact</label><input name="makkah_contact" value="<?= esc(old('makkah_contact', (string) ($row['makkah_contact'] ?? $row['makkah_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label class="text-sm font-medium">Madina Office Contact</label><input name="madina_contact" value="<?= esc(old('madina_contact', (string) ($row['madina_contact'] ?? $row['madina_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div class="md:col-span-2"><label class="text-sm font-medium">Transport Contact</label><input name="transport_contact" value="<?= esc(old('transport_contact', (string) ($row['transport_contact'] ?? $row['transport_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                <div class="md:col-span-2"><button type="submit" class="btn btn-md btn-primary">Save Main Company</button></div>
            </form>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-1">
            <h3 class="text-lg font-semibold">Preview</h3>
            <?php if (!empty($row['logo_url'])): ?><p class="mt-3"><img src="<?= esc((string) $row['logo_url']) ?>" alt="Main Company Logo" style="max-height:80px; max-width:100%; object-fit:contain;"></p><?php endif; ?>
            <p class="mt-3 font-semibold text-slate-800"><?= esc((string) ($row['name'] ?? 'KARWAN-E-TAIF PVT LTD')) ?></p>
            <?php if (!empty($row['tagline'])): ?><p class="text-sm text-slate-600"><?= esc((string) $row['tagline']) ?></p><?php endif; ?>
            <?php if (!empty($row['address'])): ?><p class="mt-2 text-sm text-slate-600"><?= esc((string) $row['address']) ?></p><?php endif; ?>
            <?php if (!empty($row['phone']) || !empty($row['email'])): ?><p class="mt-2 text-sm text-slate-600"><?= esc(trim((string) ($row['phone'] ?? '') . (!empty($row['phone']) && !empty($row['email']) ? ' | ' : '') . (string) ($row['email'] ?? ''))) ?></p><?php endif; ?>
            <?php if (!empty($row['ntn']) || !empty($row['strn'])): ?><p class="mt-2 text-sm text-slate-600"><?= esc(trim((!empty($row['ntn']) ? 'NTN: ' . (string) $row['ntn'] : '') . (!empty($row['ntn']) && !empty($row['strn']) ? ' | ' : '') . (!empty($row['strn']) ? 'STRN: ' . (string) $row['strn'] : ''))) ?></p><?php endif; ?>
        </article>
    </section>
</main>
<?php $this->endSection() ?>