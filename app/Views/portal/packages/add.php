<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <h1 class="text-base font-semibold text-slate-800">Create Package</h1>
        <p class="mt-1 text-xs text-slate-500">Define the core package details first, then open edit to attach flights, hotels, transports, and price slabs.</p>
    </section>

    <section>
        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <form method="post" action="<?php echo site_url('/packages') ?>" enctype="multipart/form-data" class="space-y-4">
                <?= csrf_field() ?>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Code <span class="text-rose-600">*</span></label>
                        <input name="code" value="<?= esc(old('code')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="e.g. UMR-APR-2026">
                    </div>
                    <div>
                        <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Type</label>
                        <select name="package_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="hajj" <?= old('package_type') === 'hajj' ? 'selected' : '' ?>>Hajj</option>
                            <option value="umrah" <?= old('package_type') === 'umrah' ? 'selected' : '' ?>>Umrah</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Visa Cost (PKR)</label>
                        <input type="number" step="0.01" min="0" name="purchase_price_visa" value="<?= esc(old('purchase_price_visa')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="e.g. 120000">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Name <span class="text-rose-600">*</span></label>
                    <input name="name" value="<?= esc(old('name')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Enter package name">
                </div>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Duration (days) <span class="text-rose-600">*</span></label>
                        <input type="number" name="duration_days" value="<?= esc(old('duration_days')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="e.g. 14">
                    </div>
                    <div>
                        <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Departure Date &amp; Time <span class="text-rose-600">*</span></label>
                        <input type="datetime-local" name="departure_date" value="<?= esc(old('departure_date')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Arrival Date &amp; Time <span class="text-slate-400 normal-case">(auto)</span></label>
                    <input type="datetime-local" name="arrival_date" value="<?= esc(old('arrival_date')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="rounded-xl border border-slate-200 p-3">
                    <h3 class="text-sm font-semibold text-slate-800">Voucher Settings (Package-wise)</h3>
                    <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Default Shirka Company</label>
                            <select name="default_shirka_company_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Select shirka company…</option>
                                <?php foreach (($companies ?? []) as $item): ?>
                                    <option value="<?= esc($item['id']) ?>" <?= (string) old('default_shirka_company_id') === (string) ($item['id'] ?? '') ? 'selected' : '' ?>><?= esc((string) ($item['name'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <?php $predefined_instruction_ur = "ضروری ہدایات

1- ہوٹل سے داخلہ (چیک ان) کا وقت دوپہر 12 بجے ہے اور باہر (چیک آؤٹ) کا وقت دوپہر 02 بجے ہے۔

2- اگر کسی بھی عمرہ زائر کو برائے پیشہ ورانہ کاروبار کے مطابق طے شدہ عمرہ چیک کی مدت میں کوئی مسئلہ ہو جائے تو اسے فوری انتظامیہ سے رابطہ کرنا چاہیے، ورنہ وہ خود ذمہ دار ہو گا۔

3- پراپرٹی سعودی عرب میں کمپنی کے پاس ہے اور اسی کے ذریعے فراہم کی جاتی ہے۔

4- عمرہ زائرین کو کسی بھی شکایت کی صورت میں سعودی عرب میں ہمارے دفتر سے براہ راست رابطہ کرنا چاہیے، کسی غیر سے رابطہ نہ کریں۔

5- فریبوں سے بچنے کے لیے 24 گھنٹے پہلے ہمارے اسٹاف سے رابطہ کریں۔ فریب میں رہنے کی صورت میں حاصل ہونے والی کوئی بھی ذمہ داری خود اٹھانی ہو گی۔

6- ہوٹل میں قیام کا وقت دوپہر میں مقرر کیا گیا ہے، برائے مہربانی اس کا خیال رکھیں۔

7- تمام زائرین اپنے سامان کی حفاظت کے خود ذمہ دار ہوں گے۔

8- زائرین کا ملک سے روانگی اور ہوٹل سے باہر جانے سے 3 دن پہلے کمپنی کے نمائندے سے شرائط و ضوابط کے لیے رابطہ کرنا لازم ہے اور تصدیق کرانا ضروری ہے۔" ?>

                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Voucher Instructions (Urdu)</label>
                            <textarea name="voucher_instructions_ur" rows="4" dir="rtl" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="واؤچر ہدایات"><?= esc(old('voucher_instructions_ur') ?? $predefined_instruction_ur) ?></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Voucher Instructions (English)</label>
                            <textarea name="voucher_instructions_en" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Optional English instructions"><?= esc(old('voucher_instructions_en')) ?></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Makkah Office</label>
                            <?php $predefined_makkah_contact = "Niamat kiyani Phone No.+966 53 908 1421"; ?>
                            <input name="makkah_contact" value="<?= esc(old('makkah_contact') ?? $predefined_makkah_contact) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="مکہ دفتر رابطہ">
                        </div>

                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Madina Office</label>
                            <?php $predefined_madina_contact = "M Irfan Phone No.00966568553058"; ?>
                            <input name="madina_contact" value="<?= esc(old('madina_contact') ?? $predefined_madina_contact) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="مدینہ دفتر رابطہ">
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Transport Contact</label>
                            <?php $predefined_transport_contact = "Sajid Nawaz Phone No.00966591195335"; ?>
                            <input name="transport_contact" value="<?= esc(old('transport_contact') ?? $predefined_transport_contact) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="ٹرانسپورٹ رابطہ">
                        </div>

                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium uppercase tracking-wide text-slate-600">Notes</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Optional notes for internal team"><?= esc(old('notes')) ?></textarea>
                </div>
                <p class="text-xs text-slate-500">After creating the package, open edit page to link Flights, Hotels, Transports, and Sharing/Quad/Triple/Double costs from their modules.</p>
                <div class="flex justify-end border-t border-slate-200 pt-3">
                    <button type="submit" class="btn btn-md btn-primary inline-flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>Create Package</span>
                    </button>
                </div>
            </form>
        </article>
    </section>
</main>
<script>
    (function() {
        const form = document.querySelector('form[action*="/packages"]');
        if (!form) {
            return;
        }

        const durationInput = form.querySelector('input[name="duration_days"]');
        const departureInput = form.querySelector('input[name="departure_date"]');
        const arrivalInput = form.querySelector('input[name="arrival_date"]');

        if (!durationInput || !departureInput || !arrivalInput) {
            return;
        }

        const formatDate = function(dateObj) {
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dateObj.getDate()).padStart(2, '0');
            return dateObj.getFullYear() + '-' + month + '-' + day;
        };

        const updateArrivalDate = function() {
            if (!departureInput.value) {
                return;
            }

            const duration = parseInt(durationInput.value || '0', 10);
            if (!duration || duration < 1) {
                return;
            }

            // Extract date part from datetime-local value for day arithmetic
            const departureDate = new Date(departureInput.value.substring(0, 10) + 'T00:00:00');
            if (Number.isNaN(departureDate.getTime())) {
                return;
            }

            departureDate.setDate(departureDate.getDate() + duration);
            // Set arrival in datetime-local format (time defaults to 00:00)
            arrivalInput.value = formatDate(departureDate) + 'T00:00';
        };

        durationInput.addEventListener('input', updateArrivalDate);
        departureInput.addEventListener('change', updateArrivalDate);
        updateArrivalDate();
    })();
</script>
<?php $this->endSection() ?>