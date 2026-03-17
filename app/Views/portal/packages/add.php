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