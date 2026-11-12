<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$rows = is_array($rows ?? null) ? $rows : [];
$editRow = is_array($editRow ?? null) ? $editRow : null;
$errors = is_array($errors ?? null) ? $errors : [];
$isEdit = is_array($editRow);
?>
<section x-data="{sidebarOpen:false}" class="lg:grid lg:grid-cols-12 lg:gap-6">
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen=false"></div>
    <aside class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full overflow-y-auto bg-slate-50 p-4 transition-transform duration-200 lg:static lg:col-span-3 lg:w-auto lg:translate-x-0 lg:bg-transparent lg:p-0" :class="sidebarOpen ? 'translate-x-0' : ''">
        <?= $this->include('frontend/portal/agent/_sidebar') ?>
    </aside>

    <div class="lg:col-span-9 space-y-6">
        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium lg:hidden" @click="sidebarOpen=true">
            <i class="fa-solid fa-bars"></i> Menu
        </button>

        <div>
            <h1 class="text-2xl font-bold text-slate-900">Clients</h1>
            <p class="text-sm text-slate-500">Manage client passport and contact details.</p>
        </div>

        <?php if ($errors !== []): ?>
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <?php foreach ($errors as $msg): ?>
                    <div><?= esc((string) $msg) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= $isEdit ? site_url('/portal/agent/clients/' . (int) ($editRow['id'] ?? 0) . '/update') : site_url('/portal/agent/clients') ?>" method="post" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <?= csrf_field() ?>
            <h2 class="text-lg font-semibold text-slate-900"><?= $isEdit ? 'Edit Client' : 'Add New Client' ?></h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name" required value="<?= esc(old('name') ?: (string) ($editRow['name'] ?? '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Passport No</label>
                    <input type="text" name="passport_no" value="<?= esc(old('passport_no') ?: (string) ($editRow['passport_no'] ?? '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Phone</label>
                    <input type="text" name="phone" value="<?= esc(old('phone') ?: (string) ($editRow['phone'] ?? '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="<?= esc(old('email') ?: (string) ($editRow['email'] ?? '')) ?>" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700"><?= $isEdit ? 'Update Client' : 'Add Client' ?></button>
                <?php if ($isEdit): ?>
                    <a href="<?= site_url('/portal/agent/clients') ?>" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($rows !== []): ?>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Passport</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3 font-medium text-slate-900"><?= esc((string) ($row['name'] ?? '-')) ?></td>
                                <td class="px-4 py-3"><?= esc((string) ($row['passport_no'] ?? '-')) ?></td>
                                <td class="px-4 py-3"><?= esc((string) ($row['phone'] ?? '-')) ?></td>
                                <td class="px-4 py-3"><?= esc((string) ($row['email'] ?? '-')) ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="<?= site_url('/portal/agent/clients?edit=' . (int) ($row['id'] ?? 0)) ?>" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:border-sky-300 hover:text-sky-700">Edit</a>
                                        <form action="<?= site_url('/portal/agent/clients/' . (int) ($row['id'] ?? 0) . '/delete') ?>" method="post" onsubmit="return confirm('Delete this client?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">No clients added yet.</div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
