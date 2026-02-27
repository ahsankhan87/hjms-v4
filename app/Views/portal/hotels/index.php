<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-4">
        <div class="list-toolbar">
            <div class="flex space-x-3">
                <a href="<?= site_url('/app/hotels/add') ?>" class="btn btn-md btn-primary">
                    <i class="fa-solid fa-plus mr-2"></i>Add Hotel
                </a>
                <a href="<?= site_url('/app/hotels/rooming-list') ?>" class="btn btn-md btn-secondary">
                    <i class="fa-solid fa-bed mr-2"></i>Rooming List Report
                </a>
            </div>
        </div>

        <div class="list-card overflow-auto">
            <h3 class="text-lg font-semibold mb-4 px-4 pt-4">Hotel List</h3>
            <table class="list-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">ID</th>
                        <th class="px-3 py-2 text-left">Hotel</th>
                        <th class="px-3 py-2 text-left">City</th>
                        <th class="px-3 py-2 text-left">Star</th>
                        <th class="px-3 py-2 text-left">Room Types</th>
                        <th class="px-3 py-2 text-left">Total Rooms</th>
                        <th class="px-3 py-2 text-left">Allocated</th>
                        <th class="px-3 py-2 text-left">Available</th>
                        <th class="px-3 py-2 text-left">Media</th>
                        <th class="px-3 py-2 text-left">Map</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="11" class="px-3 py-6 text-center text-slate-500">No hotels found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $totalRooms = (int) ($row['total_rooms'] ?? 0);
                            $allocatedRooms = (int) ($row['allocated_rooms'] ?? 0);
                            $availableRooms = max(0, $totalRooms - $allocatedRooms);
                            ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 font-medium">#<?= esc($row['id']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['name']) ?></td>
                                <td class="px-3 py-2"><?= esc($row['city'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc($row['star_rating'] ?: '-') ?></td>
                                <td class="px-3 py-2"><?= esc((int) ($row['room_types'] ?? 0)) ?></td>
                                <td class="px-3 py-2"><?= esc($totalRooms) ?></td>
                                <td class="px-3 py-2"><?= esc($allocatedRooms) ?></td>
                                <td class="px-3 py-2"><?= esc($availableRooms) ?></td>
                                <td class="px-3 py-2">
                                    <?php if (!empty($row['image_url']) || !empty($row['video_url']) || !empty($row['youtube_url'])): ?>
                                        <div class="flex items-center space-x-2">
                                            <?php if (!empty($row['image_url'])): ?><a href="<?= esc($row['image_url']) ?>" target="_blank" class="text-blue-600 text-xs">Image</a><?php endif; ?>
                                            <?php if (!empty($row['video_url'])): ?><a href="<?= esc($row['video_url']) ?>" target="_blank" class="text-blue-600 text-xs">Video</a><?php endif; ?>
                                            <?php if (!empty($row['youtube_url'])): ?><a href="<?= esc($row['youtube_url']) ?>" target="_blank" class="text-blue-600 text-xs">YouTube</a><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2"><?= !empty($row['map_url']) ? '<a href="' . esc($row['map_url']) . '" target="_blank" class="text-blue-600 text-xs">Open Map</a>' : '-' ?></td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= site_url('/app/hotels/' . (int) $row['id'] . '/edit') ?>" class="icon-btn" title="Edit Hotel">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="post" action="<?= site_url('/app/hotels/delete') ?>" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="hotel_id" value="<?= esc($row['id']) ?>">
                                            <button type="submit" class="icon-btn icon-btn-danger" onclick="return confirm('Delete this hotel and all room types?')" title="Delete Hotel">
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
    </section>
</main>
<?php $this->endSection() ?>