<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<section x-data="{sidebarOpen:false}" class="lg:grid lg:grid-cols-12 lg:gap-6">
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen=false"></div>
    <aside class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full overflow-y-auto bg-slate-50 p-4 transition-transform duration-200 lg:static lg:col-span-3 lg:w-auto lg:translate-x-0 lg:bg-transparent lg:p-0" :class="sidebarOpen ? 'translate-x-0' : ''">
        <?= $this->include('frontend/portal/_sidebar') ?>
    </aside>

    <div class="lg:col-span-9 space-y-6">
        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium lg:hidden" @click="sidebarOpen=true">
            <i class="fa-solid fa-bars"></i> Menu
        </button>

        <div>
            <h1 class="text-2xl font-bold text-slate-900">My Bookings</h1>
            <p class="text-sm text-slate-500">Track your booking requests and current status.</p>
        </div>

        <?php if (! empty($rows)): ?>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Booking #</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3 font-medium text-slate-900">#<?= esc((string) ($row['id'] ?? 0)) ?></td>
                                <td class="px-4 py-3 capitalize"><?= esc((string) ($row['booking_type'] ?? '-')) ?></td>
                                <td class="px-4 py-3"><?= esc((string) ($row['display_item'] ?? '-')) ?></td>
                                <td class="px-4 py-3"><?= esc((string) ($row['display_date'] ?? '-')) ?></td>
                                <td class="px-4 py-3"><?= 'PKR ' . esc(number_format((float) ($row['total_amount'] ?? 0), 0)) ?></td>
                                <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium capitalize text-slate-700"><?= esc((string) ($row['status'] ?? '-')) ?></span></td>
                                <td class="px-4 py-3">
                                    <a href="<?= site_url('/portal/bookings/' . (int) ($row['id'] ?? 0)) ?>" class="inline-flex rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-sky-300 hover:text-sky-700">View Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">No bookings yet. Start by selecting a package, ticket, hotel, or transport route.</div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
