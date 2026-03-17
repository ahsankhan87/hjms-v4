<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-3 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-1">
            <h3 class="text-sm font-semibold text-slate-800">Edit Hotel</h3>
            <form method="post" action="<?= site_url('/hotels/update') ?>" enctype="multipart/form-data" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="hotel_id" value="<?= esc($row['id']) ?>">

                <!-- Basic Info -->
                <div class="space-y-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Basic Info</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Hotel Name <span class="text-rose-500">*</span></label>
                            <input name="name" value="<?= esc(old('name', $row['name'])) ?>" required placeholder="e.g. Hilton Makkah" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">City</label>
                            <input name="city" value="<?= esc(old('city', (string) ($row['city'] ?? ''))) ?>" placeholder="Makkah or Madinah" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Distance from Haram (m)</label>
                            <input type="number" min="0" name="distance_m" value="<?= esc(old('distance_m', (string) ($row['distance_m'] ?? ($row['distance_makkah_m'] ?? ($row['distance_madina_m'] ?? ''))))) ?>" placeholder="e.g. 500" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Star Rating</label>
                            <input type="number" name="star_rating" min="1" max="7" value="<?= esc(old('star_rating', (string) ($row['star_rating'] ?? '3'))) ?>" placeholder="1–7" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Address</label>
                        <textarea name="address" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('address', (string) ($row['address'] ?? ''))) ?></textarea>
                    </div>
                </div>

                <!-- Media & Links -->
                <div class="space-y-3 border-t border-slate-100 pt-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Media &amp; Links</p>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Cover Image URL</label>
                        <input name="image_url" value="<?= esc(old('image_url', (string) ($row['image_url'] ?? ''))) ?>" placeholder="https://..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Upload More Hotel Images</label>
                        <input type="file" name="hotel_images[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <p class="mt-1 text-xs text-slate-400">JPG, PNG, WEBP &mdash; max 5 MB each.</p>
                    </div>
                    <?php if (!empty($galleryImages)): ?>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Current Gallery</label>
                            <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                <?php foreach ($galleryImages as $galleryImage): ?>
                                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                        <button type="button" class="gallery-preview-btn block w-full" data-image-src="<?= esc($galleryImage) ?>">
                                            <img src="<?= esc($galleryImage) ?>" alt="Hotel image" class="h-20 w-full object-cover">
                                        </button>
                                        <div class="p-1">
                                            <button type="button" class="w-full rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-100 gallery-delete-btn" data-image-url="<?= esc($galleryImage) ?>">Delete Image</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Video URL</label>
                            <input name="video_url" value="<?= esc(old('video_url', (string) ($row['video_url'] ?? ''))) ?>" placeholder="https://..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">YouTube Link</label>
                            <input name="youtube_url" value="<?= esc(old('youtube_url', (string) ($row['youtube_url'] ?? ''))) ?>" placeholder="https://youtube.com/..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Google Maps Link</label>
                        <input name="map_url" value="<?= esc(old('map_url', (string) ($row['map_url'] ?? ''))) ?>" placeholder="https://maps.google.com/..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/hotels') ?>" class="btn btn-md btn-secondary">Back</a>
                    <button type="submit" class="btn btn-md btn-primary">
                        <i class="fa-solid fa-check"></i><span>Update Hotel</span>
                    </button>
                </div>
            </form>

            <hr class="my-5 border-slate-200">

            <h3 class="text-sm font-semibold text-slate-800">Delete Hotel</h3>
            <form method="post" action="<?= site_url('/hotels/delete') ?>" class="mt-4">
                <?= csrf_field() ?>
                <input type="hidden" name="hotel_id" value="<?= esc($row['id']) ?>">
                <button type="submit" class="btn btn-md btn-danger btn-block" onclick="return confirm('Delete this hotel and all room types?')">
                    <i class="fa-solid fa-trash"></i><span>Delete Hotel</span>
                </button>
            </form>

            <form id="galleryDeleteForm" method="post" action="<?= site_url('/hotels/gallery/delete') ?>" class="hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="hotel_id" value="<?= esc($row['id']) ?>">
                <input type="hidden" name="image_url" id="galleryDeleteImageUrl" value="">
            </form>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-2 overflow-auto">
            <h3 class="text-sm font-semibold text-slate-800">Room Types & Allocation</h3>

            <form method="post" action="<?= site_url('/hotels/rooms') ?>" class="mt-4 grid gap-3 md:grid-cols-4">
                <?= csrf_field() ?>
                <input type="hidden" name="hotel_id" value="<?= esc($row['id']) ?>">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Room Type</label>
                    <input name="room_type" list="room-type-options" required placeholder="Double / Triple / Custom" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <datalist id="room-type-options">
                        <?php foreach (($defaultRoomTypes ?? []) as $defaultRoomType): ?>
                            <option value="<?= esc((string) $defaultRoomType) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Total Rooms</label>
                    <input type="number" name="total_rooms" min="0" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Allocated Rooms</label>
                    <input type="number" name="allocated_rooms" min="0" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn btn-md btn-primary w-full">Add Room Type</button>
                </div>
            </form>

            <div class="mt-5 overflow-auto">
                <table class="list-table">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Type</th>
                            <th class="px-3 py-2 text-left">Total</th>
                            <th class="px-3 py-2 text-left">Allocated</th>
                            <th class="px-3 py-2 text-left">Available</th>
                            <th class="px-3 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rooms)): ?>
                            <tr>
                                <td colspan="5" class="px-3 py-5 text-center text-slate-500">No room types added.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rooms as $room): ?>
                                <?php $available = max(0, (int) $room['total_rooms'] - (int) $room['allocated_rooms']); ?>
                                <tr class="border-t border-slate-100">
                                    <td class="px-3 py-2">
                                        <form method="post" action="<?= site_url('/hotels/rooms/update') ?>" class="grid gap-2 md:grid-cols-5 items-center">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="hotel_id" value="<?= esc($row['id']) ?>">
                                            <input type="hidden" name="room_id" value="<?= esc($room['id']) ?>">
                                            <input name="room_type" value="<?= esc($room['room_type']) ?>" required class="rounded-lg border border-slate-300 px-2 py-1 text-sm">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" name="total_rooms" min="0" value="<?= esc($room['total_rooms']) ?>" required class="w-24 rounded-lg border border-slate-300 px-2 py-1 text-sm">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" name="allocated_rooms" min="0" value="<?= esc($room['allocated_rooms']) ?>" required class="w-24 rounded-lg border border-slate-300 px-2 py-1 text-sm">
                                    </td>
                                    <td class="px-3 py-2"><?= esc($available) ?></td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center space-x-2">
                                            <button type="submit" class="icon-btn" title="Update Room Allocation">
                                                <i class="fa-solid fa-floppy-disk"></i>
                                            </button>
                                            </form>
                                            <form method="post" action="<?= site_url('/hotels/rooms/delete') ?>" class="inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="hotel_id" value="<?= esc($row['id']) ?>">
                                                <input type="hidden" name="room_id" value="<?= esc($room['id']) ?>">
                                                <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this room type?')" title="Delete Room Type">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</main>

<div id="galleryPreviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="relative w-full max-w-4xl overflow-hidden rounded-xl bg-white">
        <button type="button" id="galleryPreviewClose" class="absolute right-3 top-3 z-10 rounded-md bg-black/60 px-3 py-1 text-sm text-white hover:bg-black/80">Close</button>
        <button type="button" id="galleryPreviewPrev" class="absolute left-3 top-1/2 z-10 -translate-y-1/2 rounded-md bg-black/60 px-3 py-2 text-sm text-white hover:bg-black/80">&#10094;</button>
        <button type="button" id="galleryPreviewNext" class="absolute right-3 top-1/2 z-10 -translate-y-1/2 rounded-md bg-black/60 px-3 py-2 text-sm text-white hover:bg-black/80">&#10095;</button>
        <img id="galleryPreviewImage" src="" alt="Preview" class="max-h-[85vh] w-full object-contain bg-black/5">
    </div>
</div>

<script>
    (function() {
        var deleteButtons = document.querySelectorAll('.gallery-delete-btn');
        var deleteForm = document.getElementById('galleryDeleteForm');
        var deleteInput = document.getElementById('galleryDeleteImageUrl');

        if (deleteButtons.length && deleteForm && deleteInput) {
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var imageUrl = button.getAttribute('data-image-url') || '';
                    if (!imageUrl) {
                        return;
                    }
                    if (!window.confirm('Remove this image from gallery?')) {
                        return;
                    }
                    deleteInput.value = imageUrl;
                    deleteForm.submit();
                });
            });
        }

        var previewButtons = document.querySelectorAll('.gallery-preview-btn');
        var modal = document.getElementById('galleryPreviewModal');
        var modalImage = document.getElementById('galleryPreviewImage');
        var closeButton = document.getElementById('galleryPreviewClose');
        var prevButton = document.getElementById('galleryPreviewPrev');
        var nextButton = document.getElementById('galleryPreviewNext');
        var previewImages = [];
        var currentIndex = 0;

        function renderImageAt(index) {
            if (!previewImages.length) {
                return;
            }

            if (index < 0) {
                index = previewImages.length - 1;
            }
            if (index >= previewImages.length) {
                index = 0;
            }

            currentIndex = index;
            modalImage.src = previewImages[currentIndex];
        }

        if (previewButtons.length && modal && modalImage && closeButton && prevButton && nextButton) {
            previewButtons.forEach(function(button, index) {
                var src = button.getAttribute('data-image-src') || '';
                if (src) {
                    previewImages.push(src);
                }

                button.addEventListener('click', function() {
                    var imageSrc = button.getAttribute('data-image-src') || '';
                    if (!imageSrc) {
                        return;
                    }

                    var clickedIndex = previewImages.indexOf(imageSrc);
                    if (clickedIndex === -1) {
                        clickedIndex = index;
                    }

                    renderImageAt(clickedIndex);
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            });

            prevButton.addEventListener('click', function(event) {
                event.stopPropagation();
                renderImageAt(currentIndex - 1);
            });

            nextButton.addEventListener('click', function(event) {
                event.stopPropagation();
                renderImageAt(currentIndex + 1);
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
                    renderImageAt(currentIndex - 1);
                } else if (event.key === 'ArrowRight') {
                    renderImageAt(currentIndex + 1);
                } else if (event.key === 'Escape') {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modalImage.src = '';
                }
            });
        }
    })();
</script>
<?php $this->endSection() ?>