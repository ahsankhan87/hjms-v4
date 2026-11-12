<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$filters = $filters ?? [];
$minPrice = (float) ($filters['min_price'] ?? 0);
$maxPrice = (float) ($filters['max_price'] ?? 0);
$durationMin = (int) ($filters['duration_min'] ?? 0);
$durationMax = (int) ($filters['duration_max'] ?? 0);
$hotelRating = (int) ($filters['hotel_rating'] ?? 0);
$transportIncluded = (string) ($filters['transport_included'] ?? 'any');
?>
<section x-data="packageListingPage()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900"><?= ucfirst(esc((string) ($packageType ?? 'all'))) ?> Packages</h1>
            <p class="text-sm text-slate-500">Explore carefully curated Hajj and Umrah packages.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= site_url('/packages/hajj') ?>" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Hajj</a>
            <a href="<?= site_url('/packages/umrah') ?>" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Umrah</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-12">
        <aside class="lg:col-span-3">
            <button type="button" @click="showFilters = !showFilters" class="mb-3 inline-flex rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium lg:hidden">
                Filters
            </button>
            <div x-show="showFilters" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="text-base font-semibold text-slate-900">Filter Packages</h2>
                <form method="get" class="mt-4 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Min Price (PKR)</label>
                        <input type="number" name="min_price" value="<?= esc((string) ($minPrice > 0 ? $minPrice : '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Max Price (PKR)</label>
                        <input type="number" name="max_price" value="<?= esc((string) ($maxPrice > 0 ? $maxPrice : '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Min Duration</label>
                            <input type="number" name="duration_min" value="<?= esc((string) ($durationMin > 0 ? $durationMin : '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Max Duration</label>
                            <input type="number" name="duration_max" value="<?= esc((string) ($durationMax > 0 ? $durationMax : '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Hotel Rating</label>
                        <select name="hotel_rating" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="0" <?= $hotelRating === 0 ? 'selected' : '' ?>>Any</option>
                            <option value="3" <?= $hotelRating === 3 ? 'selected' : '' ?>>3+ Star</option>
                            <option value="4" <?= $hotelRating === 4 ? 'selected' : '' ?>>4+ Star</option>
                            <option value="5" <?= $hotelRating === 5 ? 'selected' : '' ?>>5 Star</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Transport Included</label>
                        <select name="transport_included" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="any" <?= $transportIncluded === 'any' ? 'selected' : '' ?>>Any</option>
                            <option value="yes" <?= $transportIncluded === 'yes' ? 'selected' : '' ?>>Yes</option>
                            <option value="no" <?= $transportIncluded === 'no' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    <button class="w-full rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Apply Filters</button>
                </form>
            </div>
        </aside>

        <div class="space-y-4 lg:col-span-9">
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-3">
                <p class="text-sm text-slate-600"><?= count($packages ?? []) ?> packages found</p>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-slate-600">Sort by price:</label>
                    <select x-model="sortOrder" @change="sortByPrice" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                        <option value="default">Default</option>
                        <option value="asc">Low to High</option>
                        <option value="desc">High to Low</option>
                    </select>
                </div>
            </div>

            <div id="package-grid" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach (($packages ?? []) as $i => $pkg): ?>
                    <?= view('frontend/components/package-card', ['pkg' => $pkg, 'index' => $i]) ?>
                <?php endforeach; ?>
            </div>

            <?php if (empty($packages)): ?>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
                    No packages matched your selected filters. Please broaden your criteria.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function packageListingPage() {
        return {
            showFilters: window.innerWidth >= 1024,
            sortOrder: 'default',
            sortByPrice() {
                const grid = document.getElementById('package-grid');
                if (!grid) {
                    return;
                }
                const cards = Array.from(grid.querySelectorAll('[data-price-card="1"]'));
                if (this.sortOrder === 'default') {
                    cards.sort((a, b) => parseInt(a.dataset.initialIndex || '0', 10) - parseInt(b.dataset.initialIndex || '0', 10));
                } else if (this.sortOrder === 'asc') {
                    cards.sort((a, b) => parseFloat(a.dataset.price || '0') - parseFloat(b.dataset.price || '0'));
                } else {
                    cards.sort((a, b) => parseFloat(b.dataset.price || '0') - parseFloat(a.dataset.price || '0'));
                }
                cards.forEach((card) => grid.appendChild(card));
            }
        };
    }
</script>
<?= $this->endSection() ?>
