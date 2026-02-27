<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-users text-green-600 text-xl"></i>
                </div>
                <span class="text-green-600 text-sm font-semibold">Live</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-800 mb-1"><?= esc(number_format((int) ($stats['pilgrims'] ?? 0))) ?></h3>
            <p class="text-gray-500 text-sm">Total Pilgrims</p>
            <div class="mt-4 flex items-center text-xs text-gray-400">
                <i class="fa-solid fa-arrow-trend-up text-green-500 mr-1"></i>
                <span>Current total</span>
            </div>
        </article>

        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-file-contract text-blue-600 text-xl"></i>
                </div>
                <span class="text-blue-600 text-sm font-semibold">Live</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-800 mb-1"><?= esc(number_format((int) ($stats['bookings'] ?? 0))) ?></h3>
            <p class="text-gray-500 text-sm">Active Bookings</p>
            <div class="mt-4 flex items-center text-xs text-gray-400">
                <i class="fa-solid fa-arrow-trend-up text-green-500 mr-1"></i>
                <span>Current total</span>
            </div>
        </article>

        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-wallet text-amber-600 text-xl"></i>
                </div>
                <span class="text-amber-600 text-sm font-semibold">Live</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-800 mb-1">PKR <?= esc(number_format((float) ($stats['totalPaid'] ?? 0), 2)) ?></h3>
            <p class="text-gray-500 text-sm">Total Revenue</p>
            <div class="mt-4 flex items-center text-xs text-gray-400">
                <i class="fa-solid fa-arrow-trend-up text-green-500 mr-1"></i>
                <span>Total posted collections</span>
            </div>
        </article>

        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-check-double text-purple-600 text-xl"></i>
                </div>
                <span class="text-purple-600 text-sm font-semibold">Live</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-800 mb-1"><?= esc(number_format((int) ($stats['visas'] ?? 0))) ?></h3>
            <p class="text-gray-500 text-sm">Visa Records</p>
            <div class="mt-4 flex items-center text-xs text-gray-400">
                <i class="fa-solid fa-arrow-trend-up text-green-500 mr-1"></i>
                <span>Current total</span>
            </div>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">Recent Pilgrims</h2>
            <?php if (empty($recentPilgrims)): ?>
                <p class="mt-4 text-sm text-slate-500">No pilgrims added yet.</p>
            <?php else: ?>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-3 py-2">Name</th>
                                <th class="px-3 py-2">Passport</th>
                                <th class="px-3 py-2">Phone</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($recentPilgrims as $pilgrim): ?>
                                <tr>
                                    <td class="px-3 py-2 font-medium text-slate-800"><?= esc($pilgrim['full_name']) ?></td>
                                    <td class="px-3 py-2 text-slate-600"><?= esc($pilgrim['passport_no']) ?></td>
                                    <td class="px-3 py-2 text-slate-600"><?= esc($pilgrim['phone'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">Recent Payments</h2>
            <?php if (empty($recentPayments)): ?>
                <p class="mt-4 text-sm text-slate-500">No payments recorded yet.</p>
            <?php else: ?>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-3 py-2">Booking Ref</th>
                                <th class="px-3 py-2">Amount</th>
                                <th class="px-3 py-2">Method</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($recentPayments as $payment): ?>
                                <tr>
                                    <td class="px-3 py-2 font-medium text-slate-800"><?= esc($payment['booking_ref']) ?></td>
                                    <td class="px-3 py-2 text-emerald-600">PKR <?= esc(number_format((float) $payment['amount'], 2)) ?></td>
                                    <td class="px-3 py-2 text-slate-600"><?= esc($payment['method']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>
    </section>
</main>
<?php $this->endSection() ?>