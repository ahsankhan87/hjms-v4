<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-base font-semibold text-slate-800">Voucher & Contact Settings</h1>
                <p class="mt-1 text-xs text-slate-500">Manage voucher instructions and operational contact blocks used across voucher and print templates.</p>
            </div>
            <a href="<?= site_url('/main-company') ?>" class="btn btn-md btn-outline inline-flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back To Profile</span>
            </a>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-4 xl:col-span-2">
            <form method="post" action="<?= site_url('/main-company/update') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_to" value="settings">
                <input type="hidden" name="name" value="<?= esc(old('name', (string) ($row['name'] ?? 'KARWAN-E-TAIF PVT LTD'))) ?>">
                <input type="hidden" name="tagline" value="<?= esc(old('tagline', (string) ($row['tagline'] ?? ''))) ?>">
                <input type="hidden" name="address" value="<?= esc(old('address', (string) ($row['address'] ?? ''))) ?>">
                <input type="hidden" name="phone" value="<?= esc(old('phone', (string) ($row['phone'] ?? ''))) ?>">
                <input type="hidden" name="email" value="<?= esc(old('email', (string) ($row['email'] ?? ''))) ?>">
                <input type="hidden" name="website" value="<?= esc(old('website', (string) ($row['website'] ?? ''))) ?>">
                <input type="hidden" name="ntn" value="<?= esc(old('ntn', (string) ($row['ntn'] ?? ''))) ?>">
                <input type="hidden" name="strn" value="<?= esc(old('strn', (string) ($row['strn'] ?? ''))) ?>">

                <div class="rounded-xl border border-slate-200 p-3">
                    <h3 class="text-sm font-semibold text-slate-800">Voucher Content</h3>
                    <div class="mt-3 grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Voucher Instructions (Urdu)</label>
                            <textarea name="voucher_instructions_ur" rows="6" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" dir="rtl" placeholder="واؤچر ہدایات"><?= esc(old('voucher_instructions_ur', (string) ($row['voucher_instructions_ur'] ?? ''))) ?></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Voucher Instructions (English)</label>
                            <textarea name="voucher_instructions_en" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Optional English instructions for vouchers"><?= esc(old('voucher_instructions_en', (string) ($row['voucher_instructions_en'] ?? ''))) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 p-3">
                    <h3 class="text-sm font-semibold text-slate-800">Contact Blocks</h3>
                    <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Makkah Office (English)</label>
                            <input name="makkah_contact_en" value="<?= esc(old('makkah_contact_en', (string) ($row['makkah_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Makkah office contact details">
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Madina Office (English)</label>
                            <input name="madina_contact_en" value="<?= esc(old('madina_contact_en', (string) ($row['madina_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Madina office contact details">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Transport Contact (English)</label>
                            <input name="transport_contact_en" value="<?= esc(old('transport_contact_en', (string) ($row['transport_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Transport contact details in English">
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Makkah Office</label>
                            <input name="makkah_contact" value="<?= esc(old('makkah_contact', (string) ($row['makkah_contact'] ?? $row['makkah_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="مکہ دفتر رابطہ">
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Madina Office</label>
                            <input name="madina_contact" value="<?= esc(old('madina_contact', (string) ($row['madina_contact'] ?? $row['madina_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="مدینہ دفتر رابطہ">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Transport Contact</label>
                            <input name="transport_contact" value="<?= esc(old('transport_contact', (string) ($row['transport_contact'] ?? $row['transport_contact_en'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="ٹرانسپورٹ رابطہ">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-200 pt-3">
                    <button type="submit" class="btn btn-md btn-primary inline-flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Voucher Settings</span>
                    </button>
                </div>
            </form>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <h3 class="text-sm font-semibold text-slate-800">Usage Note</h3>
            <p class="mt-3 text-sm text-slate-600">These values are used in voucher layouts, printable documents, and other customer-facing exports where operational instructions and city contacts are shown.</p>
            <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
                <p class="font-semibold text-slate-700">Current Company</p>
                <p class="mt-1"><?= esc((string) ($row['name'] ?? 'KARWAN-E-TAIF PVT LTD')) ?></p>
                <?php if (!empty($row['phone'])): ?><p class="mt-2">Phone: <?= esc((string) $row['phone']) ?></p><?php endif; ?>
                <?php if (!empty($row['email'])): ?><p class="mt-1">Email: <?= esc((string) $row['email']) ?></p><?php endif; ?>
            </div>
        </article>
    </section>
</main>
<?php $this->endSection() ?>