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
            <!-- Read Passport Data Section -->
            <div class="mb-6 space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-slate-800">Read Passport Data</h4>
                        <p class="text-xs text-slate-500">Extract data automatically from your passport image</p>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                        <p class="text-xs text-blue-700">
                            <strong>Notes:</strong>
                        </p>
                        <ul class="mt-1 space-y-1 text-xs text-blue-700">
                            <li>• Make sure the image quality is clear, so that the texts and the image of the pilgrim are clear.</li>
                            <li>• Make sure the text is clear about the specific dimensions of the image.</li>
                        </ul>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-medium text-slate-600">Passport Image <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="file" id="passportImageForOCR" name="passport_image" form="createPilgrimForm" accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <p class="mt-1 text-xs text-slate-500">JPG, PNG or WebP, max 5MB. This image is also saved as the pilgrim's passport copy.</p>
                    </div>
                    <div class="flex flex-col justify-end">
                        <button type="button" id="readPassportBtn" class="flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Read Passport</span>
                        </button>
                    </div>
                </div>
            </div>

            <form id="createPilgrimForm" method="post" action="<?= site_url('/admin/pilgrims') ?>" enctype="multipart/form-data" class="space-y-5">
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
                            <input type="date" name="date_of_birth" max="<?= date('Y-m-d') ?>" value="<?= esc(old('date_of_birth')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Place of Birth</label>
                            <input name="city" value="<?= esc(old('city')) ?>" placeholder="City" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Photos & Notes -->
                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Photos, Attachments &amp; Notes</p>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Pilgrim Photo</label>
                            <input type="file" name="pilgrim_image" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Attachment 1</label>
                            <input type="file" name="attachment_1" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Attachment 2</label>
                            <input type="file" name="attachment_2" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Notes</label>
                        <textarea name="description" rows="3" placeholder="Additional notes or remarks…" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('description')) ?></textarea>
                    </div>
                </div>

                <!-- Document & Identity -->
                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Document &amp; Identity</p>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Passport No <span class="text-rose-500">*</span></label>
                            <input name="passport_no" id="passportNoInput" value="<?= esc(old('passport_no')) ?>" required minlength="5" maxlength="20" pattern="[A-Za-z0-9 ]{5,20}" title="5-20 letters or numbers" autocomplete="off" placeholder="e.g. AB1234567" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <p id="passportNoError" class="mt-1 hidden text-xs font-medium text-rose-600"></p>
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

                <!-- Medical & Reference -->
                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Medical &amp; Reference</p>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Reference</label>
                            <input name="reference" value="<?= esc(old('reference')) ?>" placeholder="Reference / source" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Blood Group</label>
                            <?php $bloodGroupValue = old('blood_group', ''); ?>
                            <select name="blood_group" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Please Select</option>
                                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodGroup): ?>
                                    <option value="<?= esc($bloodGroup) ?>" <?= $bloodGroupValue === $bloodGroup ? 'selected' : '' ?>><?= esc($bloodGroup) ?></option>
                                <?php endforeach; ?>
                            </select>
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


                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/admin/pilgrims') ?>" class="btn btn-md btn-secondary">Cancel</a>
                    <button class="btn btn-md btn-primary" type="submit">
                        <i class="fa-solid fa-check"></i><span>Create Pilgrim</span>
                    </button>
                </div>
            </form>
        </article>
    </section>
</main>

<!-- Passport Scan Modal -->
<div id="passportScanModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
    <div class="w-full max-w-3xl rounded-lg bg-white shadow-lg">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-800">Passport Scan Result</h3>
            <button type="button" id="closePassportModal" class="text-slate-500 hover:text-slate-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="grid gap-3 px-6 py-4 sm:grid-cols-3">
            <div>
                <label class="text-xs font-medium text-slate-600">First Name</label>
                <p id="modalFirstName" class="mt-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">-</p>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600">Family Name</label>
                <p id="modalLastName" class="mt-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">-</p>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600">Father Name</label>
                <p id="modalFatherName" class="mt-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">-</p>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600">Date of Birth</label>
                <p id="modalDateOfBirth" class="mt-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">-</p>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600">Gender</label>
                <p id="modalGender" class="mt-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">-</p>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600">Nationality</label>
                <p id="modalNationality" class="mt-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">-</p>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600">Passport Number</label>
                <p id="modalPassportNo" class="mt-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">-</p>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600">Country of Issue</label>
                <p id="modalCountryIssued" class="mt-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">-</p>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600">Issue Date</label>
                <p id="modalIssueDate" class="mt-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">-</p>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600">Expiry Date</label>
                <p id="modalExpiryDate" class="mt-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">-</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-6 py-4">
            <button type="button" id="cancelPassportModal" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
            <button type="button" id="proceedPassportModal" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Proceed</button>
        </div>
    </div>
</div>

<!-- Passport OCR loader overlay -->
<div id="ocrOverlay" class="hidden fixed inset-0 flex items-center justify-center bg-slate-900/60 p-4" style="z-index:70">
    <div class="w-full max-w-sm rounded-xl bg-white p-6 text-center shadow-xl">
        <div class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-emerald-600"></div>
        <h4 class="mt-4 text-sm font-semibold text-slate-800">Reading passport...</h4>
        <p id="ocrStatusText" class="mt-1 text-xs text-slate-500">Starting...</p>
        <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-200">
            <div id="ocrProgressBar" class="h-full bg-emerald-600 transition-all duration-300" style="width: 0%"></div>
        </div>
        <p class="mt-3 text-[11px] text-slate-400">This can take up to a minute the first time. Please wait.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5.0.4/dist/tesseract.min.js"></script>
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const readBtn = $('readPassportBtn');
    const fileInput = $('passportImageForOCR');
    const modal = $('passportScanModal');
    const overlay = $('ocrOverlay');
    const statusText = $('ocrStatusText');
    const progressBar = $('ocrProgressBar');
    if (!readBtn || !fileInput || !modal) return;

    const BTN_HTML = readBtn.innerHTML;
    const BTN_BUSY_HTML = '<svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span>Reading...</span>';
    const MRZ_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789<';
    const MAX_FILE_SIZE = 5 * 1024 * 1024;
    let extractedData = {};

    function setBusy(busy) {
        readBtn.disabled = busy;
        readBtn.innerHTML = busy ? BTN_BUSY_HTML : BTN_HTML;
        overlay.classList.toggle('hidden', !busy);
        if (!busy) setProgress(0, 'Starting...');
    }

    function setProgress(pct, text) {
        progressBar.style.width = Math.max(0, Math.min(100, pct)) + '%';
        if (text) statusText.textContent = text;
    }

    function loadImage(file) {
        return new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => { URL.revokeObjectURL(url); resolve(img); };
            img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('Could not open the selected image.')); };
            img.src = url;
        });
    }

    // Crops from topFrac down, scales to a good OCR width, converts to grayscale and stretches contrast.
    function prepareCanvas(img, topFrac) {
        const sy = Math.floor(img.naturalHeight * topFrac);
        const sh = img.naturalHeight - sy;
        const scale = Math.min(3, 2000 / img.naturalWidth);
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(img.naturalWidth * scale);
        canvas.height = Math.round(sh * scale);
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, sy, img.naturalWidth, sh, 0, 0, canvas.width, canvas.height);

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const px = imageData.data;
        const hist = new Uint32Array(256);
        for (let i = 0; i < px.length; i += 4) {
            const g = (px[i] * 0.299 + px[i + 1] * 0.587 + px[i + 2] * 0.114) | 0;
            px[i] = px[i + 1] = px[i + 2] = g;
            hist[g]++;
        }
        const total = px.length / 4;
        let low = 0, high = 255, acc = 0;
        for (let v = 0; v < 256; v++) { acc += hist[v]; if (acc >= total * 0.02) { low = v; break; } }
        acc = 0;
        for (let v = 255; v >= 0; v--) { acc += hist[v]; if (acc >= total * 0.02) { high = v; break; } }
        const range = Math.max(1, high - low);
        for (let i = 0; i < px.length; i += 4) {
            const g = Math.max(0, Math.min(255, ((px[i] - low) * 255) / range));
            px[i] = px[i + 1] = px[i + 2] = g;
        }
        ctx.putImageData(imageData, 0, 0);
        return canvas;
    }

    // ---------- MRZ parsing ----------
    const DIGIT_FIX = { O: '0', Q: '0', D: '0', I: '1', L: '1', B: '8', S: '5', Z: '2', G: '6' };
    const toDigits = (s) => s.replace(/[A-Z]/g, (c) => DIGIT_FIX[c] || c);
    const cleanMrzLine = (l) => l.toUpperCase().replace(/[«‹]/g, '<').replace(/\s+/g, '').replace(/[^A-Z0-9<]/g, '');

    function titleCase(s) {
        return s.toLowerCase().replace(/\b[a-z]/g, (c) => c.toUpperCase());
    }

    function mrzDate(raw, kind) {
        const d = toDigits(raw);
        if (!/^\d{6}$/.test(d)) return '';
        const yy = parseInt(d.slice(0, 2), 10);
        const mm = parseInt(d.slice(2, 4), 10);
        const dd = parseInt(d.slice(4, 6), 10);
        if (mm < 1 || mm > 12 || dd < 1 || dd > 31) return '';
        let year = 2000 + yy;
        if (kind === 'dob' && year > new Date().getFullYear()) year -= 100;
        return year + '-' + String(mm).padStart(2, '0') + '-' + String(dd).padStart(2, '0');
    }

    function cleanGivenNames(raw) {
        const tokens = raw.replace(/\d/g, '').split('<').filter(Boolean);
        // '<' fillers are often misread as K/C/L/E/S at the end of the line
        while (tokens.length > 1 && (tokens[tokens.length - 1].length === 1 || /^[KCLES]{2,}$/.test(tokens[tokens.length - 1]))) tokens.pop();
        let name = tokens.join(' ');
        name = name.replace(/[KCLES]{4,}$/, '').trim();
        return name;
    }

    // Check digits (ICAO 9303): weights 7-3-1, digits as-is, letters A=10..Z=35, '<' = 0
    function mrzCheckDigit(str) {
        const w = [7, 3, 1];
        let sum = 0;
        for (let i = 0; i < str.length; i++) {
            const c = str[i];
            const v = c === '<' ? 0 : (c >= '0' && c <= '9' ? c.charCodeAt(0) - 48 : c.charCodeAt(0) - 55);
            sum += v * w[i % 3];
        }
        return sum % 10;
    }

    const LINE2_RE = /([A-Z0-9<]{9})([0-9A-Z<])([A-Z<]{3})([0-9OQDILBSZG]{6})([0-9OQDILBSZG<])([MF<])([0-9OQDILBSZG]{6})([0-9OQDILBSZG<])/;

    function parseMrzLine2(line) {
        const m = LINE2_RE.exec(line);
        if (!m) return null;
        let docNo = m[1];
        const nat = m[3].replace(/</g, '');
        if (nat === 'PAK' && /^[A-Z]{2}[0-9OQDILBSZG]{7}$/.test(docNo)) {
            docNo = docNo.slice(0, 2) + toDigits(docNo.slice(2));
        }
        const dobRaw = toDigits(m[4]);
        const expRaw = toDigits(m[7]);
        const dob = mrzDate(m[4], 'dob');
        const expiry = mrzDate(m[7], 'expiry');
        if (!dob || !expiry) return null;

        let score = 0;
        if (mrzCheckDigit(docNo) === parseInt(toDigits(m[2]), 10)) score++;
        if (mrzCheckDigit(dobRaw) === parseInt(toDigits(m[5]), 10)) score++;
        if (mrzCheckDigit(expRaw) === parseInt(toDigits(m[8]), 10)) score++;
        if (m[6] === 'M' || m[6] === 'F') score++;

        return {
            score: score,
            fields: {
                passportNo: docNo.replace(/</g, ''),
                nationalityCode: nat,
                dateOfBirth: dob,
                gender: m[6] === 'M' ? 'male' : (m[6] === 'F' ? 'female' : ''),
                expiryDate: expiry
            }
        };
    }

    // Returns every MRZ-looking line found in one OCR text; the best one is chosen later by voting.
    function parseMrz(text) {
        const lines = text.split(/\r?\n/).map(cleanMrzLine).filter((l) => l.length >= 20);
        const line2 = [];
        const line1 = [];
        lines.forEach((l) => {
            const p2 = parseMrzLine2(l);
            if (p2) {
                line2.push(p2);
            } else if (l.length >= 25 && (l.includes('<<') || l.startsWith('P<'))) {
                line1.push(l);
            }
        });
        return { line2: line2, line1: line1 };
    }

    // '<<' separates surname from given names, but OCR often reads it as '<K<', '<L<', etc.
    const NAME_SEP_RE = /<<(?=[A-Z])|<[KCLES]{1,2}<(?=[A-Z])/;

    function parseNames(line1, nat) {
        const code = nat || line1.slice(2, 5);
        const natPos = line1.indexOf(code);
        const start = (natPos >= 1 && natPos <= 4) ? natPos + 3 : 5;
        const rest = line1.slice(start);
        const sepMatch = NAME_SEP_RE.exec(rest);
        const sep = sepMatch ? sepMatch.index : -1;
        const surname = (sep >= 0 ? rest.slice(0, sep) : rest).replace(/\d/g, '').replace(/</g, ' ').trim();
        const given = sep >= 0 ? cleanGivenNames(rest.slice(sep + sepMatch[0].length)) : '';
        return { lastName: titleCase(surname), firstName: titleCase(given), sepFound: sep >= 0 };
    }

    function pickLine2(candidates) {
        const groups = {};
        candidates.forEach((c) => {
            const f = c.fields;
            const key = [f.passportNo, f.dateOfBirth, f.gender, f.expiryDate].join('|');
            if (!groups[key]) groups[key] = { fields: f, total: 0 };
            groups[key].total += c.score * 3 + 1;
        });
        return Object.values(groups).sort((a, b) => b.total - a.total)[0];
    }

    function pickNames(candidates, nat, printedText) {
        const printed = printedText
            .split(/\r?\n/)
            .filter((l) => !l.includes('<'))
            .join('\n')
            .toUpperCase();
        const printedHas = (word) => word.length >= 3 && new RegExp('\\b' + word.toUpperCase() + '\\b').test(printed);

        const groups = {};
        candidates.forEach((l) => {
            const names = parseNames(l, nat);
            let score = 0;
            if (!names.lastName) score -= 6;
            if (!names.firstName) score -= 6;
            if (/^P<[A-Z]{3}/.test(l)) score += 2;
            if (nat && l.slice(2, 5) === nat) score += 2;
            if (l.length === 44) score += 3; else if (Math.abs(l.length - 44) <= 2) score += 1;
            if (names.sepFound) score += 2;
            if (/<{3,}$/.test(l)) score += 2;
            if (/[KCLES]{3,}$/.test(l)) score -= 2;
            if (printedHas(names.lastName)) score += 3;
            names.firstName.split(' ').forEach((w) => { if (printedHas(w)) score += 3; });

            const key = names.lastName + '|' + names.firstName;
            if (!groups[key]) groups[key] = { names: names, total: 0 };
            groups[key].total += score + 2;
        });
        const best = Object.values(groups).sort((a, b) => b.total - a.total)[0];
        return best ? best.names : null;
    }

    // ---------- Biodata fallback (father name / issue date are not in the MRZ) ----------
    const MONTHS = { JAN: '01', FEB: '02', MAR: '03', APR: '04', MAY: '05', JUN: '06', JUL: '07', AUG: '08', SEP: '09', OCT: '10', NOV: '11', DEC: '12' };

    function parseTextDate(s) {
        const up = (s || '').toUpperCase();
        let m = up.match(/(\d{1,2})\s*([A-Z]{3})[A-Z]*\s*(\d{4})/);
        if (m && MONTHS[m[2]]) return m[3] + '-' + MONTHS[m[2]] + '-' + m[1].padStart(2, '0');
        m = up.match(/(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{4})/);
        if (m) return m[3] + '-' + m[2].padStart(2, '0') + '-' + m[1].padStart(2, '0');
        return '';
    }

    function parseBiodata(text) {
        const out = {};
        const lines = text.split(/\r?\n/).map((l) => l.trim()).filter(Boolean);
        for (let i = 0; i < lines.length; i++) {
            const up = lines[i].toUpperCase();
            if (!out.fatherName && /FATHER|HUSBAND/.test(up)) {
                const same = lines[i].match(/NAME[^A-Za-z]*([A-Za-z][A-Za-z ]{2,})$/i);
                const next = (lines[i + 1] || '').replace(/[^A-Za-z ]/g, '').trim();
                const cand = same ? same[1].trim() : (next.length >= 3 && !/NAME|DATE|PLACE/i.test(next) ? next : '');
                if (cand) out.fatherName = titleCase(cand);
            }
            if (!out.issueDate && /DATE OF ISSUE|ISSUE DATE/.test(up)) {
                out.issueDate = parseTextDate(lines[i]) || parseTextDate(lines[i + 1]);
            }
        }
        return out;
    }

    // ---------- Modal / form ----------
    function countryLabel(code) {
        return code === 'PAK' ? 'Pakistan' : (code || '');
    }

    function displayInModal(d) {
        const set = (id, v) => { $(id).textContent = v || '-'; };
        set('modalFirstName', d.firstName);
        set('modalLastName', d.lastName);
        set('modalFatherName', d.fatherName);
        set('modalDateOfBirth', d.dateOfBirth);
        set('modalGender', d.gender === 'male' ? 'Male' : (d.gender === 'female' ? 'Female' : ''));
        set('modalNationality', d.nationalityCode ? countryLabel(d.nationalityCode) + ' (' + d.nationalityCode + ')' : '');
        set('modalPassportNo', d.passportNo);
        set('modalCountryIssued', countryLabel(d.issuingCode));
        set('modalExpiryDate', d.expiryDate);
        set('modalIssueDate', d.issueDate);
    }

    function setField(selector, value) {
        const el = document.querySelector(selector);
        if (!el || !value) return;
        el.value = value;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function fillForm(d) {
        setField('input[name="first_name"]', d.firstName);
        setField('input[name="last_name"]', d.lastName);
        setField('input[name="father_name"]', d.fatherName);
        setField('input[name="date_of_birth"]', d.dateOfBirth);
        setField('select[name="gender"]', d.gender);
        if (d.nationalityCode) setField('select[name="country"]', d.nationalityCode === 'PAK' ? 'Pakistan' : 'Others');
        setField('input[name="passport_no"]', d.passportNo);
        setField('input[name="passport_issue_date"]', d.issueDate);
        setField('input[name="passport_expiry_date"]', d.expiryDate);
    }

    // ---------- OCR flow ----------
    async function runOcr(file) {
        setProgress(5, 'Preparing image...');
        const img = await loadImage(file);

        // Several independent reads of the MRZ are compared, so one bad read does not decide the result
        const passes = [
            { label: 'Reading machine readable zone...', top: 0.72, whitelist: MRZ_CHARS, psm: '6' },
            { label: 'Verifying machine readable zone...', top: 0.6, whitelist: MRZ_CHARS, psm: '6' },
            { label: 'Verifying machine readable zone...', top: 0.45, whitelist: MRZ_CHARS, psm: '6' },
            { label: 'Reading passport details...', top: 0, whitelist: '', psm: '3' }
        ];
        const span = 85 / passes.length;
        let passIndex = 0;

        setProgress(10, 'Loading OCR engine (first time may take a while)...');
        const worker = await Tesseract.createWorker('eng', 1, {
            logger: (m) => {
                if (m.status === 'recognizing text') {
                    setProgress(10 + passIndex * span + m.progress * span, passes[passIndex].label);
                } else if (m.status) {
                    setStatusOnly(m.status);
                }
            }
        });

        const line2Candidates = [];
        const line1Candidates = [];
        let fullText = '';
        try {
            for (passIndex = 0; passIndex < passes.length; passIndex++) {
                const pass = passes[passIndex];
                setProgress(10 + passIndex * span, pass.label);
                await worker.setParameters({
                    tessedit_char_whitelist: pass.whitelist,
                    tessedit_pageseg_mode: pass.psm
                });
                const { data } = await worker.recognize(prepareCanvas(img, pass.top));
                console.log('[Passport OCR] pass ' + (passIndex + 1) + ' text:\n' + data.text);

                const parsed = parseMrz(data.text);
                line2Candidates.push(...parsed.line2);
                line1Candidates.push(...parsed.line1);
                if (pass.top === 0) fullText = data.text;
            }
        } finally {
            await worker.terminate();
        }

        const result = {};
        const bestLine2 = line2Candidates.length ? pickLine2(line2Candidates) : null;
        if (bestLine2) {
            Object.assign(result, bestLine2.fields);
            result.issuingCode = bestLine2.fields.nationalityCode;
        }
        const names = pickNames(line1Candidates, result.nationalityCode, fullText);
        if (names) {
            result.lastName = names.lastName;
            result.firstName = names.firstName;
        }
        Object.assign(result, Object.fromEntries(Object.entries(parseBiodata(fullText)).filter(([, v]) => v)));
        console.log('[Passport OCR] result:', result);
        setProgress(100, 'Done');
        return result;
    }

    function setStatusOnly(status) {
        const map = {
            'loading tesseract core': 'Loading OCR engine...',
            'initializing tesseract': 'Initializing OCR engine...',
            'loading language traineddata': 'Downloading language data...',
            'initializing api': 'Initializing OCR engine...'
        };
        if (map[status]) statusText.textContent = map[status];
    }

    readBtn.addEventListener('click', async function () {
        const file = fileInput.files[0];
        if (!file) {
            alert('Please select a passport image first.');
            return;
        }
        if (!/^image\//.test(file.type)) {
            alert('Please select a JPG or PNG image.');
            return;
        }
        if (file.size > MAX_FILE_SIZE) {
            alert('File size exceeds 5MB limit.');
            return;
        }
        if (typeof Tesseract === 'undefined') {
            alert('OCR library could not be loaded. Please check your internet connection and reload the page.');
            return;
        }

        setBusy(true);
        try {
            const result = await runOcr(file);
            if (!result.passportNo && !result.firstName && !result.dateOfBirth) {
                alert('Could not read the machine readable zone (the two lines starting with P< at the bottom of the passport). Please upload a clearer, straight image that includes the full bottom of the page.');
                return;
            }
            extractedData = result;
            displayInModal(extractedData);
            modal.classList.remove('hidden');
        } catch (err) {
            console.error('[Passport OCR] failed:', err);
            alert('Error reading passport image: ' + (err && err.message ? err.message : err));
        } finally {
            setBusy(false);
        }
    });

    const closeModal = () => modal.classList.add('hidden');
    $('closePassportModal').addEventListener('click', closeModal);
    $('cancelPassportModal').addEventListener('click', closeModal);
    $('proceedPassportModal').addEventListener('click', function () {
        fillForm(extractedData);
        closeModal();
    });
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    // ---------- Form validation: duplicate passport number & date order ----------
    const form = $('createPilgrimForm');
    const passportInput = $('passportNoInput');
    const passportError = $('passportNoError');
    const issueInput = document.querySelector('input[name="passport_issue_date"]');
    const expiryInput = document.querySelector('input[name="passport_expiry_date"]');
    const CHECK_URL = '<?= site_url('pilgrims/check-passport') ?>';
    let checkTimer = null;

    function showPassportError(message) {
        passportError.textContent = message || '';
        passportError.classList.toggle('hidden', !message);
        passportInput.classList.toggle('border-rose-500', !!message);
        passportInput.classList.toggle('border-slate-300', !message);
    }

    async function checkPassportDuplicate() {
        const value = passportInput.value.replace(/\s+/g, '').toUpperCase();
        if (value.length < 5) {
            showPassportError('');
            return false;
        }
        try {
            const res = await fetch(CHECK_URL + '?passport_no=' + encodeURIComponent(value), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const json = await res.json();
            showPassportError(json.exists ? json.message : '');
            return !!json.exists;
        } catch (err) {
            console.error('[Passport check] failed:', err);
            return false; // the server re-checks on submit
        }
    }

    function scheduleDuplicateCheck() {
        showPassportError('');
        clearTimeout(checkTimer);
        checkTimer = setTimeout(checkPassportDuplicate, 400);
    }

    function validateDateOrder() {
        const bad = issueInput.value && expiryInput.value && expiryInput.value <= issueInput.value;
        expiryInput.setCustomValidity(bad ? 'Passport expiry date must be after the issue date.' : '');
    }

    passportInput.addEventListener('input', scheduleDuplicateCheck);
    passportInput.addEventListener('change', scheduleDuplicateCheck);
    issueInput.addEventListener('change', validateDateOrder);
    expiryInput.addEventListener('change', validateDateOrder);
    if (passportInput.value) checkPassportDuplicate();

    let submitting = false;
    form.addEventListener('submit', async function (e) {
        if (submitting) return;
        validateDateOrder();
        if (!form.checkValidity()) return; // browser shows the field errors
        e.preventDefault();
        if (await checkPassportDuplicate()) {
            passportInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            passportInput.focus();
            return;
        }
        submitting = true;
        form.requestSubmit ? form.requestSubmit() : form.submit();
    });
})();
</script>

<?php $this->endSection() ?>
