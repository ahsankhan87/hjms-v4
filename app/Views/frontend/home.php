<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$testimonialItems = [];
foreach (($testimonials ?? []) as $item) {
    $testimonialItems[] = [
        'name' => (string) ($item['name'] ?? ''),
        'role' => (string) ($item['role'] ?? ''),
        'message' => (string) ($item['message'] ?? ''),
        'rating' => (int) ($item['rating'] ?? 5),
    ];
}
?>
<section class="space-y-14" x-data='{
    tab: "packages",
    tIndex: 0,
    testimonials: <?= json_encode($testimonialItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
}'>
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-sky-900 to-indigo-900 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.14),transparent_40%)]"></div>
        <div class="relative grid gap-8 px-6 py-10 md:px-10 lg:grid-cols-2 lg:items-center lg:py-14">
            <div>
                <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold">Premium Hajj & Umrah Services</span>
                <h1 class="mt-4 text-3xl font-bold leading-tight md:text-5xl">Plan your sacred journey with confidence and comfort.</h1>
                <p class="mt-4 text-sm text-sky-100 md:text-base">From curated Hajj and Umrah packages to flights, hotels, and transport — everything in one trusted platform.</p>
            </div>
            <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:p-6">
                <div class="grid grid-cols-4 rounded-xl bg-black/20 p-1 text-xs sm:text-sm">
                    <button type="button" @click="tab='packages'" :class="tab==='packages' ? 'bg-white text-slate-900' : 'text-white/80'" class="rounded-lg py-2 font-semibold">Packages</button>
                    <button type="button" @click="tab='tickets'" :class="tab==='tickets' ? 'bg-white text-slate-900' : 'text-white/80'" class="rounded-lg py-2 font-semibold">Tickets</button>
                    <button type="button" @click="tab='hotels'" :class="tab==='hotels' ? 'bg-white text-slate-900' : 'text-white/80'" class="rounded-lg py-2 font-semibold">Hotels</button>
                    <button type="button" @click="tab='transport'" :class="tab==='transport' ? 'bg-white text-slate-900' : 'text-white/80'" class="rounded-lg py-2 font-semibold">Transport</button>
                </div>

                <form x-show="tab==='packages'" action="<?= site_url('/packages') ?>" method="get" class="mt-4 space-y-3">
                    <?= csrf_field() ?>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <select name="package_type" class="w-full rounded-xl border border-white/30 bg-white/95 px-3 py-2 text-sm text-slate-800">
                            <option value="all">All Packages</option>
                            <option value="hajj">Hajj</option>
                            <option value="umrah">Umrah</option>
                        </select>
                        <input type="text" name="search" placeholder="Search package or airline" class="w-full rounded-xl border border-white/30 bg-white/95 px-3 py-2 text-sm text-slate-800">
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-amber-400 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-300">Search Packages</button>
                </form>

                <form x-show="tab==='tickets'" action="<?= site_url('/tickets') ?>" method="get" class="mt-4 space-y-3">
                    <?= csrf_field() ?>
                    <input type="text" name="search" placeholder="Airline, flight number, route" class="w-full rounded-xl border border-white/30 bg-white/95 px-3 py-2 text-sm text-slate-800">
                    <button type="submit" class="w-full rounded-xl bg-amber-400 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-300">Search Tickets</button>
                </form>

                <form x-show="tab==='hotels'" action="<?= site_url('/hotels') ?>" method="get" class="mt-4 space-y-3">
                    <?= csrf_field() ?>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <input type="text" name="search" placeholder="Hotel name" class="w-full rounded-xl border border-white/30 bg-white/95 px-3 py-2 text-sm text-slate-800">
                        <select name="city" class="w-full rounded-xl border border-white/30 bg-white/95 px-3 py-2 text-sm text-slate-800">
                            <option value="">Any City</option>
                            <option value="Makkah">Makkah</option>
                            <option value="Madinah">Madinah</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-amber-400 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-300">Search Hotels</button>
                </form>

                <form x-show="tab==='transport'" action="<?= site_url('/transport') ?>" method="get" class="mt-4 space-y-3">
                    <?= csrf_field() ?>
                    <input type="text" name="search" placeholder="Transport or provider name" class="w-full rounded-xl border border-white/30 bg-white/95 px-3 py-2 text-sm text-slate-800">
                    <button type="submit" class="w-full rounded-xl bg-amber-400 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-300">Search Transport</button>
                </form>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sky-700"><i class="fa-solid fa-certificate text-2xl"></i></div>
            <h3 class="mt-3 font-semibold text-slate-900">Licensed by Ministry</h3>
            <p class="mt-1 text-sm text-slate-500">Fully compliant operations with approved licensing and documentation standards.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sky-700"><i class="fa-solid fa-headset text-2xl"></i></div>
            <h3 class="mt-3 font-semibold text-slate-900">24/7 Support</h3>
            <p class="mt-1 text-sm text-slate-500">Round-the-clock support before departure and during your sacred journey.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sky-700"><i class="fa-solid fa-users text-2xl"></i></div>
            <h3 class="mt-3 font-semibold text-slate-900">Thousands of Pilgrims Served</h3>
            <p class="mt-1 text-sm text-slate-500">Trusted by families and agents with a long record of successful departures.</p>
        </div>
    </section>

    <section class="space-y-5">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Featured Hajj Packages</h2>
            <a href="<?= site_url('/packages/hajj') ?>" class="text-sm font-semibold text-sky-700 hover:underline">View all</a>
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            <?php foreach (($featuredHajj ?? []) as $pkg): ?>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900"><?= esc((string) ($pkg['name'] ?? 'Hajj Package')) ?></h3>
                    <p class="mt-1 text-xs text-slate-500"><?= esc((string) ($pkg['code'] ?? '-')) ?> · <?= esc((string) ($pkg['duration_days'] ?? 0)) ?> Days</p>
                    <p class="mt-3 text-sm text-slate-600"><?= esc((string) ($pkg['airline'] ?? 'Airline details on listing page')) ?></p>
                    <div class="mt-3 rounded-lg bg-sky-50 px-3 py-2 text-sm">
                        <div class="text-xs text-slate-500"><?= esc((string) ($pkg['price_snippet'] ?? 'Price on request')) ?></div>
                        <div class="font-semibold text-sky-700"><?= (float) ($pkg['price_amount'] ?? 0) > 0 ? 'PKR ' . number_format((float) $pkg['price_amount'], 0) : 'Contact for price' ?></div>
                    </div>
                    <?php
                    $hajjSlugBase = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim((string) ($pkg['name'] ?? 'package'))));
                    $hajjSlug = trim((string) $hajjSlugBase, '-') . '-' . (int) ($pkg['id'] ?? 0);
                    ?>
                    <a href="<?= site_url('/packages/' . $hajjSlug) ?>" class="mt-4 inline-flex rounded-lg bg-sky-600 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-700">View Details</a>
                </article>
            <?php endforeach; ?>
            <?php if (empty($featuredHajj)): ?>
                <p class="text-sm text-slate-500">No featured Hajj packages available right now.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="space-y-5">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Featured Umrah Packages</h2>
            <a href="<?= site_url('/packages/umrah') ?>" class="text-sm font-semibold text-sky-700 hover:underline">View all</a>
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            <?php foreach (($featuredUmrah ?? []) as $pkg): ?>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900"><?= esc((string) ($pkg['name'] ?? 'Umrah Package')) ?></h3>
                    <p class="mt-1 text-xs text-slate-500"><?= esc((string) ($pkg['code'] ?? '-')) ?> · <?= esc((string) ($pkg['duration_days'] ?? 0)) ?> Days</p>
                    <p class="mt-3 text-sm text-slate-600"><?= esc((string) ($pkg['airline'] ?? 'Airline details on listing page')) ?></p>
                    <div class="mt-3 rounded-lg bg-indigo-50 px-3 py-2 text-sm">
                        <div class="text-xs text-slate-500"><?= esc((string) ($pkg['price_snippet'] ?? 'Price on request')) ?></div>
                        <div class="font-semibold text-indigo-700"><?= (float) ($pkg['price_amount'] ?? 0) > 0 ? 'PKR ' . number_format((float) $pkg['price_amount'], 0) : 'Contact for price' ?></div>
                    </div>
                    <?php
                    $umrahSlugBase = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim((string) ($pkg['name'] ?? 'package'))));
                    $umrahSlug = trim((string) $umrahSlugBase, '-') . '-' . (int) ($pkg['id'] ?? 0);
                    ?>
                    <a href="<?= site_url('/packages/' . $umrahSlug) ?>" class="mt-4 inline-flex rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">View Details</a>
                </article>
            <?php endforeach; ?>
            <?php if (empty($featuredUmrah)): ?>
                <p class="text-sm text-slate-500">No featured Umrah packages available right now.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-bold text-slate-900">Why Book With Us</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl bg-slate-50 p-4">
                <i class="fa-solid fa-tags text-xl text-sky-700"></i>
                <h3 class="mt-3 font-semibold">Best Price</h3>
                <p class="mt-1 text-sm text-slate-500">Competitive pricing with transparent inclusions.</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <i class="fa-solid fa-passport text-xl text-sky-700"></i>
                <h3 class="mt-3 font-semibold">Visa Support</h3>
                <p class="mt-1 text-sm text-slate-500">Guided visa documentation and updates.</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <i class="fa-solid fa-hotel text-xl text-sky-700"></i>
                <h3 class="mt-3 font-semibold">Haram-close Hotels</h3>
                <p class="mt-1 text-sm text-slate-500">Comfortable stays near holy sites.</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <i class="fa-solid fa-bus text-xl text-sky-700"></i>
                <h3 class="mt-3 font-semibold">Ground Transport</h3>
                <p class="mt-1 text-sm text-slate-500">Reliable city-to-city and local mobility.</p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Testimonials</h2>
            <div class="flex items-center gap-2">
                <button type="button" @click="if (testimonials.length > 0) { tIndex = (tIndex - 1 + testimonials.length) % testimonials.length }" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm"><i class="fa-solid fa-chevron-left"></i></button>
                <button type="button" @click="if (testimonials.length > 0) { tIndex = (tIndex + 1) % testimonials.length }" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>
        <div class="mt-5">
            <blockquote class="rounded-2xl bg-slate-50 p-5 text-slate-700" x-show="testimonials.length > 0">
                <p class="text-base" x-text="testimonials[tIndex].message"></p>
                <footer class="mt-3 text-sm font-semibold text-slate-900">
                    <span x-text="testimonials[tIndex].name"></span>
                    <span class="text-slate-500 font-normal" x-text="' · ' + testimonials[tIndex].role"></span>
                </footer>
            </blockquote>
            <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500" x-show="testimonials.length === 0">
                Testimonials will appear here once records are added.
            </div>
        </div>
    </section>
</section>
<?= $this->endSection() ?>
