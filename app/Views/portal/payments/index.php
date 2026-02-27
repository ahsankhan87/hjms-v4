<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold">Post Payment / Refund</h3>
        <form method="post" action="<?= site_url('/app/payments') ?>" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <?= csrf_field() ?>
            <div><label class="text-sm font-medium">Booking</label><select name="booking_id" required class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select booking</option><?php foreach ($bookings as $item): ?><option value="<?= esc($item['id']) ?>"><?= esc($item['booking_no']) ?> (<?= esc($item['status']) ?>)</option><?php endforeach; ?>
                </select></div>
            <div><label class="text-sm font-medium">Installment ID (optional)</label><input type="number" name="installment_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Amount</label><input name="amount" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="0.00"></div>
            <div><label class="text-sm font-medium">Type</label><select name="payment_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="payment">Payment</option>
                    <option value="refund">Refund</option>
                </select></div>
            <div><label class="text-sm font-medium">Channel</label><select name="channel" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="manual">Manual</option>
                    <option value="bank">Bank</option>
                    <option value="online">Online</option>
                </select></div>
            <div><label class="text-sm font-medium">Payment Date/Time</label><input type="datetime-local" name="payment_date" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Gateway Ref</label><input name="gateway_reference" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div class="md:col-span-2"><label class="text-sm font-medium">Note</label><input name="note" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div class="md:col-span-3"><button type="submit" class="btn btn-md btn-primary">Post Entry</button></div>
        </form>

        <hr class="my-5 border-slate-200">

        <h3 class="text-lg font-semibold">Update Payment</h3>
        <form method="post" action="<?= site_url('/app/payments/update') ?>" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <?= csrf_field() ?>
            <div><label class="text-sm font-medium">Payment ID</label><input type="number" name="payment_id" min="1" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Booking (optional)</label><select name="booking_id" class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">No change</option><?php foreach ($bookings as $item): ?><option value="<?= esc($item['id']) ?>"><?= esc($item['booking_no']) ?></option><?php endforeach; ?>
                </select></div>
            <div><label class="text-sm font-medium">Amount (optional)</label><input name="amount" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="0.00"></div>
            <div><label class="text-sm font-medium">Type (optional)</label><select name="payment_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">No change</option>
                    <option value="payment">Payment</option>
                    <option value="refund">Refund</option>
                </select></div>
            <div><label class="text-sm font-medium">Channel (optional)</label><select name="channel" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">No change</option>
                    <option value="manual">Manual</option>
                    <option value="bank">Bank</option>
                    <option value="online">Online</option>
                </select></div>
            <div><label class="text-sm font-medium">Date/Time (optional)</label><input type="datetime-local" name="payment_date" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Gateway Ref</label><input name="gateway_reference" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Status</label><input name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="posted"></div>
            <div class="md:col-span-3"><label class="text-sm font-medium">Note</label><input name="note" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div class="md:col-span-3"><button type="submit" class="btn btn-md btn-primary">Update Payment</button></div>
        </form>

        <hr class="my-5 border-slate-200">

        <h3 class="text-lg font-semibold">Delete Payment</h3>
        <form method="post" action="<?= site_url('/app/payments/delete') ?>" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <?= csrf_field() ?>
            <div class="md:col-span-2"><label class="text-sm font-medium">Payment ID</label><input type="number" name="payment_id" min="1" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div class="md:col-span-1 flex items-end"><button type="submit" class="btn btn-md btn-danger btn-block">Delete Payment</button></div>
        </form>
    </section>

    <section class="list-card overflow-auto">
        <h3 class="text-lg font-semibold mb-4">Recent Payments</h3>
        <table class="list-table">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">Payment No</th>
                    <th class="px-3 py-2 text-left">Booking</th>
                    <th class="px-3 py-2 text-left">Type</th>
                    <th class="px-3 py-2 text-left">Channel</th>
                    <th class="px-3 py-2 text-left">Amount</th>
                    <th class="px-3 py-2 text-left">Date</th>
                    <th class="px-3 py-2 text-left">Voucher</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 font-medium"><?= esc($row['payment_no']) ?></td>
                        <td class="px-3 py-2">#<?= esc($row['booking_id']) ?></td>
                        <td class="px-3 py-2"><?= esc($row['payment_type']) ?></td>
                        <td class="px-3 py-2"><?= esc($row['channel']) ?></td>
                        <td class="px-3 py-2"><?= esc($row['amount']) ?></td>
                        <td class="px-3 py-2"><?= esc($row['payment_date']) ?></td>
                        <td class="px-3 py-2">
                            <a href="<?= site_url('/app/bookings/' . (int) $row['booking_id'] . '/voucher') ?>" target="_blank" class="btn btn-sm btn-secondary">Open</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<?php $this->endSection() ?>