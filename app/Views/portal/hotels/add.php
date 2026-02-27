<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-3xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold">Add Hotel</h3>
            <form method="post" action="<?= site_url('/app/hotels') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="text-sm font-medium">Hotel Name</label>
                    <input name="name" value="<?= esc(old('name')) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">City</label>
                    <input name="city" value="<?= esc(old('city')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Star Rating</label>
                    <input type="number" name="star_rating" min="1" max="7" value="<?= esc(old('star_rating', '3')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Address</label>
                    <textarea name="address" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('address')) ?></textarea>
                </div>
                <div>
                    <label class="text-sm font-medium">Cover Image URL</label>
                    <input name="image_url" value="<?= esc(old('image_url')) ?>" placeholder="https://..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Video URL</label>
                    <input name="video_url" value="<?= esc(old('video_url')) ?>" placeholder="https://..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">YouTube Link</label>
                    <input name="youtube_url" value="<?= esc(old('youtube_url')) ?>" placeholder="https://youtube.com/..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Google Maps Link</label>
                    <input name="map_url" value="<?= esc(old('map_url')) ?>" placeholder="https://maps.google.com/..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="btn btn-md btn-primary btn-block">Create Hotel</button>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>