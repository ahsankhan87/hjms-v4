<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$item = $item ?? [];
$errors = is_array($errors ?? null) ? $errors : [];
$bookingType = (string) ($bookingType ?? 'package');
$itemPrice = (float) ($item['price'] ?? 0);
?>
<section class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Book <?= esc(ucfirst($bookingType)) ?></h1>
        <p class="text-sm text-slate-500">Please provide lead traveler details to place your booking request.</p>
    </div>

    <?php if (! empty($error)): ?>
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc((string) $error) ?></div>
    <?php endif; ?>
    <?php if ($errors !== []): ?>
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <?php foreach ($errors as $msg): ?>
                <div><?= esc((string) $msg) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-12">
        <form action="<?= site_url('/booking/store') ?>" method="post" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-8">
            <?= csrf_field() ?>
            <input type="hidden" name="booking_type" value="<?= esc($bookingType) ?>">
            <input type="hidden" name="item_id" value="<?= esc((string) ($item['id'] ?? 0)) ?>">

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">Lead Passenger Name</label>
                    <input type="text" name="lead_name" value="<?= esc(old('lead_name') ?: (string) session('name')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="lead_email" value="<?= esc(old('lead_email')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">Phone</label>
                    <input type="text" name="lead_phone" value="<?= esc(old('lead_phone')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Passport Number<?= $bookingType === 'ticket' ? ' (Optional)' : '' ?></label>
                    <input type="text" name="passport_no" value="<?= esc(old('passport_no')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <?php if ($bookingType === 'hotel'): ?>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Check-in</label>
                        <input type="date" name="check_in" value="<?= esc(old('check_in')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Check-out</label>
                        <input type="date" name="check_out" value="<?= esc(old('check_out')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                <?php else: ?>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Travel Date</label>
                        <input type="date" name="travel_date" value="<?= esc(old('travel_date')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Number of Travelers</label>
                        <input type="number" min="1" max="50" name="travelers_count" value="<?= esc(old('travelers_count') ?: '1') ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($bookingType === 'hotel'): ?>
                <div>
                    <label class="text-sm font-medium text-slate-700">Number of Travelers</label>
                    <input type="number" min="1" max="50" name="travelers_count" value="<?= esc(old('travelers_count') ?: '1') ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            <?php endif; ?>

            <div>
                <label class="text-sm font-medium text-slate-700">Special Requests</label>
                <textarea name="special_requests" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Optional notes"><?= esc(old('special_requests')) ?></textarea>
            </div>

            <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Submit Booking Request</button>
        </form>

        <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-4">
            <h2 class="text-lg font-semibold text-slate-900">Booking Summary</h2>
            <div class="mt-4 space-y-2 text-sm">
                <p><span class="text-slate-500">Item:</span> <span class="font-medium text-slate-900"><?= esc((string) ($item['display_name'] ?? '')) ?></span></p>
                <p><span class="text-slate-500">Type:</span> <span class="font-medium text-slate-900"><?= esc(ucfirst($bookingType)) ?></span></p>
                <p><span class="text-slate-500">Details:</span> <span class="font-medium text-slate-900"><?= esc((string) ($item['subtitle'] ?? '-')) ?></span></p>
                <?php if (trim((string) ($item['date_hint'] ?? '')) !== ''): ?>
                    <p><span class="text-slate-500">Date Hint:</span> <span class="font-medium text-slate-900"><?= esc((string) $item['date_hint']) ?></span></p>
                <?php endif; ?>
                <p class="pt-2 text-base"><span class="text-slate-500">Estimated Price:</span> <span class="font-bold text-sky-700"><?= $itemPrice > 0 ? 'PKR ' . esc(number_format($itemPrice, 0)) : 'On request' ?></span></p>
            </div>
        </aside>
    </div>
</section>
<?= $this->endSection() ?>

