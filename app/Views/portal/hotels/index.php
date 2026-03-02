<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-4">
        <div class="list-toolbar">
            <div class="flex space-x-3">
                <a href="<?= site_url('/hotels/add') ?>" class="btn btn-md btn-primary">
                    <i class="fa-solid fa-plus mr-2"></i>Add Hotel
                </a>
                <a href="<?= site_url('/hotels/rooming-list') ?>" class="btn btn-md btn-secondary">
                    <i class="fa-solid fa-bed mr-2"></i>Rooming List Report
                </a>
            </div>
        </div>

        <div class="list-card overflow-auto">
            <h3 class="text-lg font-semibold mb-4 px-4 pt-4">Hotel List</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Hotel</th>
                        <th class="px-3 py-2 text-left">Location & Distance</th>
                        <th class="px-3 py-2 text-left">Rooms</th>
                        <th class="px-3 py-2 text-left">Media</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-slate-500">No hotels found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $totalRooms = (int) ($row['total_rooms'] ?? 0);
                            $allocatedRooms = (int) ($row['allocated_rooms'] ?? 0);
                            $availableRooms = max(0, $totalRooms - $allocatedRooms);
                            $galleryImages = [];
                            if (! empty($row['image_gallery'])) {
                                $decodedGallery = json_decode((string) $row['image_gallery'], true);
                                if (is_array($decodedGallery)) {
                                    $galleryImages = array_values(array_filter($decodedGallery, static fn($url) => is_string($url) && trim($url) !== ''));
                                }
                            }
                            ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 align-top">
                                    <div class="flex items-start gap-3">
                                        <?php if (!empty($row['image_url'])): ?>
                                            <img src="<?= esc((string) $row['image_url']) ?>" alt="Hotel" class="h-12 w-12 rounded-lg border border-slate-200 object-cover">
                                        <?php else: ?>
                                            <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-500">H</div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="font-semibold text-slate-800"><?= esc((string) ($row['name'] ?? '-')) ?></p>
                                            <p class="text-xs text-slate-500">ID #<?= esc((string) ($row['id'] ?? '')) ?> • <?= esc((string) ($row['star_rating'] ?? '-')) ?> Star</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top text-sm text-slate-700">
                                    <p><?= esc((string) ($row['city'] ?: '-')) ?></p>
                                    <?php $distanceValue = $row['distance_m'] ?? ($row['distance_makkah_m'] ?? ($row['distance_madina_m'] ?? null)); ?>
                                    <p class="text-xs text-slate-500 mt-1">Distance: <?= esc($distanceValue !== null && $distanceValue !== '' ? (string) $distanceValue . ' m' : '-') ?></p>
                                </td>
                                <td class="px-3 py-2 align-top text-sm text-slate-700">
                                    <p>Types: <?= esc((string) ((int) ($row['room_types'] ?? 0))) ?></p>
                                    <p class="text-xs text-slate-500 mt-1">Total: <?= esc((string) $totalRooms) ?> • Allocated: <?= esc((string) $allocatedRooms) ?></p>
                                    <p class="text-xs font-medium <?= $availableRooms > 0 ? 'text-emerald-700' : 'text-rose-700' ?>">Available: <?= esc((string) $availableRooms) ?></p>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="space-y-1 text-xs">
                                        <p class="text-slate-700">Images: <?= esc(!empty($galleryImages) ? (string) count($galleryImages) : (!empty($row['image_url']) ? '1' : '0')) ?></p>
                                        <p class="text-slate-700">Video: <?= !empty($row['video_url']) ? 'Yes' : 'No' ?></p>
                                        <p class="text-slate-700">YouTube: <?= !empty($row['youtube_url']) ? 'Yes' : 'No' ?></p>
                                        <p class="text-slate-700">Map: <?= !empty($row['map_url']) ? 'Yes' : 'No' ?></p>
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="<?= site_url('/hotels/' . (int) $row['id']) ?>" class="text-xs font-medium text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-eye mr-1"></i></a>
                                        <a href="<?= site_url('/hotels/' . (int) $row['id'] . '/edit') ?>" class="text-xs font-medium text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-pen mr-1"></i></a>
                                        <form method="post" action="<?= site_url('/hotels/delete') ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="hotel_id" value="<?= esc($row['id']) ?>">
                                            <button type="submit" class="text-xs font-medium text-rose-700 hover:bg-rose-100" onclick="return confirm('Delete this hotel and all room types?')"><i class="fa-solid fa-trash mr-1"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php $this->endSection() ?>