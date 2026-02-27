<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-3xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold">Add Visa</h3>
            <form method="post" action="<?= site_url('/app/visas') ?>" enctype="multipart/form-data" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="text-sm font-medium">Pilgrim</label>
                    <select name="pilgrim_id" required class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select pilgrim</option>
                        <?php foreach ($pilgrims as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= old('pilgrim_id') == $item['id'] ? 'selected' : '' ?>>
                                <?= esc($item['first_name'] . ' ' . $item['last_name']) ?><?= !empty($item['passport_no']) ? ' (' . esc($item['passport_no']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Booking (optional)</label>
                    <select name="booking_id" class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">None</option>
                        <?php foreach ($bookings as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= old('booking_id') == $item['id'] ? 'selected' : '' ?>><?= esc($item['booking_no']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Visa No</label>
                    <input type="text" name="visa_no" value="<?= esc(old('visa_no')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Enter visa number">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Visa Type</label>
                        <select name="visa_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php foreach ($visaTypes as $type => $label): ?>
                                <option value="<?= esc($type) ?>" <?= old('visa_type', 'umrah') === $type ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Status</label>
                        <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <?php foreach ($visaStatuses as $status => $label): ?>
                                <option value="<?= esc($status) ?>" <?= old('status', 'draft') === $status ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium">Submission Date</label>
                        <input type="date" name="submission_date" value="<?= esc(old('submission_date')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Approval Date</label>
                        <input type="date" name="approval_date" value="<?= esc(old('approval_date')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium">Rejection Reason</label>
                    <textarea name="rejection_reason" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('rejection_reason')) ?></textarea>
                </div>
                <div>
                    <label class="text-sm font-medium">Visa File (PDF/JPG/PNG, max 5MB)</label>
                    <input type="file" name="visa_file" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Notes</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('notes')) ?></textarea>
                </div>
                <button type="submit" class="btn btn-md btn-primary btn-block">Create Visa</button>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>