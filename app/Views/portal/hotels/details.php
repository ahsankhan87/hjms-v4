<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-800"><?= esc((string) ($row['name'] ?? 'Hotel')) ?></h3>
                <p class="mt-1 text-xs text-slate-500"><?= esc((string) ($row['city'] ?? '-')) ?> • <?= esc((string) ($row['star_rating'] ?? '-')) ?> Star</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= site_url('/hotels/' . (int) ($row['id'] ?? 0) . '/edit') ?>" class="btn btn-md btn-secondary">
                    <i class="fa-solid fa-pen"></i><span>Edit</span>
                </a>
                <a href="<?= site_url('/hotels') ?>" class="btn btn-md btn-primary">
                    <i class="fa-solid fa-arrow-left"></i><span>Back</span>
                </a>
            </div>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-medium uppercase text-slate-500">Distance</p>
                <?php $distanceValue = $row['distance_m'] ?? ($row['distance_makkah_m'] ?? ($row['distance_madina_m'] ?? null)); ?>
                <p class="mt-1 text-xl font-semibold text-slate-800"><?= esc($distanceValue !== null && $distanceValue !== '' ? (string) $distanceValue . ' m' : '-') ?></p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-medium uppercase text-slate-500">Rooms Types</p>
                <p class="mt-1 text-xl font-semibold text-slate-800"><?= esc((string) count($rooms ?? [])) ?></p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-medium uppercase text-slate-500">Map</p>
                <p class="mt-1 text-sm font-semibold text-slate-800">
                    <?php if (!empty($row['map_url'])): ?>
                        <a href="<?= esc((string) $row['map_url']) ?>" target="_blank" class="text-blue-600">Open Google Maps</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if (!empty($row['address'])): ?>
            <div class="mt-5 rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium uppercase text-slate-500">Address</p>
                <p class="mt-1 text-sm text-slate-700"><?= esc((string) $row['address']) ?></p>
            </div>
        <?php endif; ?>
    </section>

    <?php
    $youtubeUrl = trim((string) ($row['youtube_url'] ?? ''));
    $videoUrl = trim((string) ($row['video_url'] ?? ''));
    $mapUrl = trim((string) ($row['map_url'] ?? ''));

    $youtubeEmbedUrl = '';
    if ($youtubeUrl !== '') {
        $youtubeId = '';
        $youtubeParts = parse_url($youtubeUrl);
        if (is_array($youtubeParts)) {
            $host = strtolower((string) ($youtubeParts['host'] ?? ''));
            $path = trim((string) ($youtubeParts['path'] ?? ''), '/');

            if (str_contains($host, 'youtu.be') && $path !== '') {
                $youtubeId = explode('/', $path)[0] ?? '';
            }

            if ($youtubeId === '' && str_contains($host, 'youtube.com')) {
                parse_str((string) ($youtubeParts['query'] ?? ''), $youtubeQuery);
                $youtubeId = (string) ($youtubeQuery['v'] ?? '');

                if ($youtubeId === '' && str_starts_with($path, 'embed/')) {
                    $youtubeId = (string) substr($path, 6);
                }
            }
        }

        if ($youtubeId !== '') {
            $youtubeEmbedUrl = 'https://www.youtube.com/embed/' . rawurlencode($youtubeId);
        }
    }

    $mapEmbedUrl = '';
    if ($mapUrl !== '') {
        $mapEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($mapUrl) . '&output=embed';
    }
    ?>

    <section class="grid gap-3 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <h4 class="text-sm font-semibold text-slate-800">Gallery</h4>
            <?php
            $coverImage = trim((string) ($row['image_url'] ?? ''));
            $allImages = [];
            if ($coverImage !== '') {
                $allImages[] = $coverImage;
            }
            foreach (($galleryImages ?? []) as $galleryImage) {
                if (!in_array($galleryImage, $allImages, true)) {
                    $allImages[] = $galleryImage;
                }
            }
            ?>
            <?php if (!empty($allImages)): ?>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <?php foreach ($allImages as $index => $image): ?>
                        <button type="button" class="detail-gallery-preview overflow-hidden rounded-xl border border-slate-200 bg-slate-50" data-image-src="<?= esc((string) $image) ?>" data-index="<?= esc((string) $index) ?>">
                            <img src="<?= esc((string) $image) ?>" alt="Hotel image" class="h-28 w-full object-cover">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="mt-4 text-sm text-slate-500">No gallery images added.</p>
            <?php endif; ?>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <h4 class="text-sm font-semibold text-slate-800">Media & Preview</h4>
            <div class="mt-4 space-y-3 text-sm">
                <div class="rounded-lg border border-slate-200 p-3">
                    <p class="text-xs font-medium uppercase text-slate-500">Video URL</p>
                    <?php if ($videoUrl !== ''): ?>
                        <a href="<?= esc($videoUrl) ?>" target="_blank" class="text-blue-600 break-all"><?= esc($videoUrl) ?></a>
                        <video controls class="mt-3 w-full rounded-lg border border-slate-200 bg-black/5">
                            <source src="<?= esc($videoUrl) ?>">
                        </video>
                    <?php else: ?>
                        <p class="text-slate-500">-</p>
                    <?php endif; ?>
                </div>
                <div class="rounded-lg border border-slate-200 p-3">
                    <p class="text-xs font-medium uppercase text-slate-500">YouTube</p>
                    <?php if ($youtubeUrl !== ''): ?>
                        <a href="<?= esc($youtubeUrl) ?>" target="_blank" class="text-blue-600 break-all"><?= esc($youtubeUrl) ?></a>
                        <?php if ($youtubeEmbedUrl !== ''): ?>
                            <iframe class="mt-3 h-56 w-full rounded-lg border border-slate-200" src="<?= esc($youtubeEmbedUrl) ?>" title="YouTube video player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-slate-500">-</p>
                    <?php endif; ?>
                </div>
                <div class="rounded-lg border border-slate-200 p-3">
                    <p class="text-xs font-medium uppercase text-slate-500">Google Map</p>
                    <?php if ($mapUrl !== ''): ?>
                        <a href="<?= esc($mapUrl) ?>" target="_blank" class="text-blue-600 break-all"><?= esc($mapUrl) ?></a>
                        <?php if ($mapEmbedUrl !== ''): ?>
                            <iframe class="mt-3 h-56 w-full rounded-lg border border-slate-200" src="<?= esc($mapEmbedUrl) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Hotel map"></iframe>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-slate-500">-</p>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <h4 class="text-sm font-semibold text-slate-800">Room Types & Allocation</h4>
        <div class="mt-4 overflow-auto">
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Room Type</th>
                        <th class="px-3 py-2 text-left">Total</th>
                        <th class="px-3 py-2 text-left">Allocated</th>
                        <th class="px-3 py-2 text-left">Available</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-center text-slate-500">No room types added.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                            <?php $available = max(0, (int) ($room['total_rooms'] ?? 0) - (int) ($room['allocated_rooms'] ?? 0)); ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2"><?= esc((string) ($room['room_type'] ?? '-')) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($room['total_rooms'] ?? 0)) ?></td>
                                <td class="px-3 py-2"><?= esc((string) ($room['allocated_rooms'] ?? 0)) ?></td>
                                <td class="px-3 py-2"><?= esc((string) $available) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<div id="detailGalleryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="relative w-full max-w-5xl overflow-hidden rounded-xl bg-white">
        <button type="button" id="detailGalleryClose" class="absolute right-3 top-3 z-10 rounded-md bg-black/60 px-3 py-1 text-sm text-white hover:bg-black/80">Close</button>
        <button type="button" id="detailGalleryPrev" class="absolute left-3 top-1/2 z-10 -translate-y-1/2 rounded-md bg-black/60 px-3 py-2 text-sm text-white hover:bg-black/80">&#10094;</button>
        <button type="button" id="detailGalleryNext" class="absolute right-3 top-1/2 z-10 -translate-y-1/2 rounded-md bg-black/60 px-3 py-2 text-sm text-white hover:bg-black/80">&#10095;</button>
        <img id="detailGalleryImage" src="" alt="Gallery preview" class="max-h-[85vh] w-full object-contain bg-black/5">
    </div>
</div>

<script>
    (function() {
        var previewButtons = document.querySelectorAll('.detail-gallery-preview');
        var modal = document.getElementById('detailGalleryModal');
        var modalImage = document.getElementById('detailGalleryImage');
        var closeButton = document.getElementById('detailGalleryClose');
        var prevButton = document.getElementById('detailGalleryPrev');
        var nextButton = document.getElementById('detailGalleryNext');

        if (!previewButtons.length || !modal || !modalImage || !closeButton || !prevButton || !nextButton) {
            return;
        }

        var images = [];
        previewButtons.forEach(function(button) {
            var src = button.getAttribute('data-image-src') || '';
            if (src) {
                images.push(src);
            }
        });

        var currentIndex = 0;

        function render(index) {
            if (!images.length) {
                return;
            }
            if (index < 0) {
                index = images.length - 1;
            }
            if (index >= images.length) {
                index = 0;
            }

            currentIndex = index;
            modalImage.src = images[currentIndex];
        }

        previewButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var index = Number(button.getAttribute('data-index') || 0);
                render(index);
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        prevButton.addEventListener('click', function(event) {
            event.stopPropagation();
            render(currentIndex - 1);
        });

        nextButton.addEventListener('click', function(event) {
            event.stopPropagation();
            render(currentIndex + 1);
        });

        closeButton.addEventListener('click', function() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modalImage.src = '';
        });

        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modalImage.src = '';
            }
        });

        document.addEventListener('keydown', function(event) {
            if (modal.classList.contains('hidden')) {
                return;
            }
            if (event.key === 'ArrowLeft') {
                render(currentIndex - 1);
            } else if (event.key === 'ArrowRight') {
                render(currentIndex + 1);
            } else if (event.key === 'Escape') {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modalImage.src = '';
            }
        });
    })();
</script>
<?php $this->endSection() ?>