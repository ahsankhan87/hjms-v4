<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-3">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 lg:col-span-1">
            <h3 class="text-lg font-semibold">Edit Hotel</h3>
            <form method="post" action="<?= site_url('/app/hotels/update') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="hotel_id" value="<?= esc($row['id']) ?>">
                <div>
                    <label class="text-sm font-medium">Hotel Name</label>
                    <input name="name" value="<?= esc(old('name', $row['name'])) ?>" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">City</label>
                    <input name="city" value="<?= esc(old('city', (string) ($row['city'] ?? ''))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Star Rating</label>
                    <input type="number" name="star_rating" min="1" max="7" value="<?= esc(old('star_rating', (string) ($row['star_rating'] ?? '3'))) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Address</label>
                    <textarea name="address" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?= esc(old('address', (string) ($row['address'] ?? ''))) ?></textarea>
                </div>
                <div>
                    <label class="text-sm font-medium">Cover Image URL</label>
                    <input name="image_url" value="<?= esc(old('image_url', (string) ($row['image_url'] ?? ''))) ?>" placeholder="https://..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Video URL</label>
                    <input name="video_url" value="<?= esc(old('video_url', (string) ($row['video_url'] ?? ''))) ?>" placeholder="https://..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">YouTube Link</label>
                    <input name="youtube_url" value="<?= esc(old('youtube_url', (string) ($row['youtube_url'] ?? ''))) ?>" placeholder="https://youtube.com/..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Google Maps Link</label>
                    <input name="map_url" value="<?= esc(old('map_url', (string) ($row['map_url'] ?? ''))) ?>" placeholder="https://maps.google.com/..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="btn btn-md btn-primary btn-block">Update Hotel</button>
            </form>

            <hr class="my-5 border-slate-200">

            <h3 class="text-lg font-semibold">Delete Hotel</h3>
            <form method="post" action="<?= site_url('/app/hotels/delete') ?>" class="mt-4">
                <?= csrf_field() ?>
                <input type="hidden" name="hotel_id" value="<?= esc($row['id']) ?>">
                <button type="submit" class="btn btn-md btn-danger btn-block" onclick="return confirm('Delete this hotel and all room types?')">Delete Hotel</button>
            </form>
        </article>

        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 lg:col-span-2 overflow-auto">
            <h3 class="text-lg font-semibold">Room Types & Allocation</h3>

            <form method="post" action="<?= site_url('/app/hotels/rooms') ?>" class="mt-4 grid gap-3 md:grid-cols-4">
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
                                        <form method="post" action="<?= site_url('/app/hotels/rooms/update') ?>" class="grid gap-2 md:grid-cols-5 items-center">
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
                                            <form method="post" action="<?= site_url('/app/hotels/rooms/delete') ?>" class="inline">
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
<?php $this->endSection() ?>