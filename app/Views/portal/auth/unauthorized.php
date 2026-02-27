<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <article class="mx-auto max-w-2xl rounded-xl border border-rose-200 bg-white p-8 shadow-sm">
        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h1 class="text-2xl font-semibold text-slate-900">Access Denied</h1>
        <p class="mt-2 text-sm text-slate-600"><?= esc($error ?? 'You do not have permission to access this section.') ?></p>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="<?= site_url('/app') ?>" class="btn btn-md btn-primary">Go to Dashboard</a>
            <a href="<?= site_url('/app/login') ?>" class="btn btn-md btn-secondary">Back to Login</a>
        </div>
    </article>
</main>
<?php $this->endSection() ?>