<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-3xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold">Add Package</h3>
            <form method="post" action="<?php echo site_url('/app/packages') ?>" enctype="multipart/form-data" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="text-sm font-medium">Code</label>
                    <input name="code" value="<?= esc(old('code')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Name</label>
                    <input name="name" value="<?= esc(old('name')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Type</label>
                        <select name="package_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="hajj" <?= old('package_type') === 'hajj' ? 'selected' : '' ?>>Hajj</option>
                            <option value="umrah" <?= old('package_type') === 'umrah' ? 'selected' : '' ?>>Umrah</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Duration (days)</label>
                        <input type="number" name="duration_days" value="<?= esc(old('duration_days')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Departure Date</label>
                        <input type="date" name="departure_date" value="<?= esc(old('departure_date')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Arrival Date</label>
                        <input type="date" name="arrival_date" value="<?= esc(old('arrival_date')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Total Seats</label>
                        <input type="number" name="total_seats" value="<?= esc(old('total_seats')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Selling Price</label>
                        <input name="selling_price" value="<?= esc(old('selling_price')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="0.00">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium">Passport Attachment (URL)</label>
                    <input name="passport_attachment" value="<?= esc(old('passport_attachment')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="https://...">
                </div>
                <div>
                    <label class="text-sm font-medium">Notes</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('notes')) ?></textarea>
                </div>
                <p class="text-xs text-slate-500">After creating the package, open edit page to link Flights, Hotels, Transports, and Sharing/Quad/Triple/Double costs from their modules.</p>
                <button type="submit" class="btn btn-md btn-primary btn-block">Create Package</button>
            </form>
        </article>
    </section>
</main>
<script>
    (function() {
        const form = document.querySelector('form[action*="/app/packages"]');
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

            const departureDate = new Date(departureInput.value + 'T00:00:00');
            if (Number.isNaN(departureDate.getTime())) {
                return;
            }

            departureDate.setDate(departureDate.getDate() + duration);
            arrivalInput.value = formatDate(departureDate);
        };

        durationInput.addEventListener('input', updateArrivalDate);
        departureInput.addEventListener('change', updateArrivalDate);
        updateArrivalDate();
    })();
</script>
<?php $this->endSection() ?>