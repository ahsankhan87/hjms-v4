<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <article class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800">Roles</h3>
            <p class="mt-2 text-sm text-gray-500">Create and review roles.</p>
            <div class="mt-4 text-2xl font-bold text-gray-800"><?= esc((string) count($roles)) ?></div>
            <a href="<?= site_url('/app/rbac/roles') ?>" class="btn btn-md btn-primary mt-4">Open Roles</a>
        </article>

        <article class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800">Permissions</h3>
            <p class="mt-2 text-sm text-gray-500">Create permissions and map them to roles.</p>
            <div class="mt-4 text-2xl font-bold text-gray-800"><?= esc((string) count($permissions)) ?></div>
            <a href="<?= site_url('/app/rbac/permissions') ?>" class="btn btn-md btn-primary mt-4">Open Permissions</a>
        </article>

        <article class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800">Assignments</h3>
            <p class="mt-2 text-sm text-gray-500">Assign roles to users and review mappings.</p>
            <div class="mt-4 text-2xl font-bold text-gray-800"><?= esc((string) count($users)) ?></div>
            <a href="<?= site_url('/app/rbac/assign') ?>" class="btn btn-md btn-primary mt-4">Open Assignments</a>
        </article>
    </section>
</main>
<?php $this->endSection() ?>