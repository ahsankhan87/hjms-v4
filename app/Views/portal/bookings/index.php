<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold">Create Booking</h3>
        <form method="post" action="<?= site_url('/app/bookings') ?>" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <?= csrf_field() ?>
            <div><label class="text-sm font-medium">Package</label><select name="package_id" required class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select package</option><?php foreach ($packages as $item): ?><option value="<?= esc($item['id']) ?>" <?= (string) old('package_id', (string) ($selectedPackageId ?? '')) === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?> (<?= esc($item['code']) ?>)</option><?php endforeach; ?>
                </select></div>
            <div><label class="text-sm font-medium">Status</label><select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="draft">Draft</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                </select></div>
            <div><label class="text-sm font-medium">Agent (optional)</label><select name="agent_id" class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">None</option><?php foreach ($agents as $item): ?><option value="<?= esc($item['id']) ?>"><?= esc($item['name']) ?></option><?php endforeach; ?>
                </select></div>
            <div><label class="text-sm font-medium">Branch (optional)</label><select name="branch_id" class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">None</option><?php foreach ($branches as $item): ?><option value="<?= esc($item['id']) ?>"><?= esc($item['name']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="md:col-span-2"><label class="text-sm font-medium">Select Pilgrims</label><select name="pilgrim_ids[]" multiple required class="js-select2 mt-1 h-36 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?php foreach ($pilgrims as $item): ?><option value="<?= esc($item['id']) ?>">#<?= esc($item['id']) ?> - <?= esc($item['first_name'] . ' ' . $item['last_name']) ?><?= !empty($item['passport_no']) ? ' (' . esc($item['passport_no']) . ')' : '' ?></option><?php endforeach; ?></select>
                <p class="mt-1 text-xs text-slate-500">Hold Ctrl/Cmd to select multiple pilgrims.</p>
            </div>
            <div class="md:col-span-2"><label class="text-sm font-medium">Remarks</label><textarea name="remarks" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea></div>
            <div class="md:col-span-2"><button type="submit" class="btn btn-md btn-primary">Create Booking</button></div>
        </form>

        <hr class="my-5 border-slate-200">

        <h3 class="text-lg font-semibold">Update Booking</h3>
        <form method="post" action="<?= site_url('/app/bookings/update') ?>" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <?= csrf_field() ?>
            <div><label class="text-sm font-medium">Booking ID</label><input type="number" name="booking_id" min="1" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Status (optional)</label><select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">No change</option>
                    <option value="draft">Draft</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                </select></div>
            <div><label class="text-sm font-medium">Package (optional)</label><select name="package_id" class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">No change</option><?php foreach ($packages as $item): ?><option value="<?= esc($item['id']) ?>"><?= esc($item['name']) ?> (<?= esc($item['code']) ?>)</option><?php endforeach; ?>
                </select></div>
            <div><label class="text-sm font-medium">Agent (optional)</label><select name="agent_id" class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">No change</option><?php foreach ($agents as $item): ?><option value="<?= esc($item['id']) ?>"><?= esc($item['name']) ?></option><?php endforeach; ?>
                </select></div>
            <div><label class="text-sm font-medium">Branch (optional)</label><select name="branch_id" class="js-select2 mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">No change</option><?php foreach ($branches as $item): ?><option value="<?= esc($item['id']) ?>"><?= esc($item['name']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="md:col-span-2"><label class="text-sm font-medium">Replace Pilgrims (optional)</label><select name="pilgrim_ids[]" multiple class="js-select2 mt-1 h-32 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><?php foreach ($pilgrims as $item): ?><option value="<?= esc($item['id']) ?>">#<?= esc($item['id']) ?> - <?= esc($item['first_name'] . ' ' . $item['last_name']) ?></option><?php endforeach; ?></select></div>
            <div class="md:col-span-2"><label class="text-sm font-medium">Remarks (optional)</label><textarea name="remarks" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea></div>
            <div class="md:col-span-2"><button type="submit" class="btn btn-md btn-primary">Update Booking</button></div>
        </form>

        <hr class="my-5 border-slate-200">

        <h3 class="text-lg font-semibold">Delete Booking</h3>
        <form method="post" action="<?= site_url('/app/bookings/delete') ?>" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <?= csrf_field() ?>
            <div class="md:col-span-2"><label class="text-sm font-medium">Booking ID</label><input type="number" name="booking_id" min="1" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div class="md:col-span-1 flex items-end"><button type="submit" class="btn btn-md btn-danger btn-block">Delete Booking</button></div>
        </form>
    </section>

    <section class="list-card overflow-auto">
        <h3 class="text-lg font-semibold mb-4">Booking List</h3>
        <table class="list-table">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">Booking No</th>
                    <th class="px-3 py-2 text-left">Package</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Pilgrims</th>
                    <th class="px-3 py-2 text-left">Created</th>
                    <th class="px-3 py-2 text-left">Voucher</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 font-medium"><?= esc($row['booking_no']) ?></td>
                        <td class="px-3 py-2">#<?= esc($row['package_id']) ?></td>
                        <td class="px-3 py-2"><?= esc($row['status']) ?></td>
                        <td class="px-3 py-2"><?= esc($row['total_pilgrims']) ?></td>
                        <td class="px-3 py-2"><?= esc($row['created_at']) ?></td>
                        <td class="px-3 py-2">
                            <a href="<?= site_url('/app/bookings/' . (int) $row['id'] . '/voucher') ?>" target="_blank" class="btn btn-sm btn-secondary">Open</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<?php $this->endSection() ?>