<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="w-full space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Create Hotel</h3>
            <p class="text-xs text-slate-500">Add hotel profile, distance, and media details.</p>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <form method="post" action="<?= site_url('/hotels') ?>" enctype="multipart/form-data" class="space-y-3">
                <?= csrf_field() ?>

                <!-- Basic Info -->
                <div class="space-y-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Basic Info</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Hotel Name <span class="text-rose-500">*</span></label>
                            <input name="name" value="<?= esc(old('name')) ?>" required placeholder="e.g. Hilton Makkah" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">City</label>
                            <input name="city" value="<?= esc(old('city')) ?>" placeholder="Makkah or Madinah" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Distance from Haram (m)</label>
                            <input type="number" min="0" name="distance_m" value="<?= esc(old('distance_m')) ?>" placeholder="e.g. 500" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Star Rating</label>
                            <input type="number" name="star_rating" min="1" max="7" value="<?= esc(old('star_rating', '3')) ?>" placeholder="1–7" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Address</label>
                        <textarea name="address" rows="3" placeholder="Full hotel address" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('address')) ?></textarea>
                    </div>
                </div>

                <!-- Media & Links -->
                <div class="space-y-3 border-t border-slate-100 pt-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Media &amp; Links</p>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Cover Image URL</label>
                        <input name="image_url" value="<?= esc(old('image_url')) ?>" placeholder="https://..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Upload Hotel Images</label>
                        <input type="file" name="hotel_images[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <p class="mt-1 text-xs text-slate-400">JPG, PNG, WEBP &mdash; max 5 MB each. Multiple selection allowed.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Video URL</label>
                            <input name="video_url" value="<?= esc(old('video_url')) ?>" placeholder="https://..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">YouTube Link</label>
                            <input name="youtube_url" value="<?= esc(old('youtube_url')) ?>" placeholder="https://youtube.com/..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Google Maps Link</label>
                        <input name="map_url" value="<?= esc(old('map_url')) ?>" placeholder="https://maps.google.com/..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/hotels') ?>" class="btn btn-md btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-md btn-primary">
                        <i class="fa-solid fa-check"></i><span>Create Hotel</span>
                    </button>
                </div>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>