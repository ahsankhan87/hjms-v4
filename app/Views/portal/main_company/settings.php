<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <section class="rounded-xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-base font-semibold text-slate-800">Voucher & Contact Settings</h1>
                <p class="mt-1 text-xs text-slate-500">Voucher instructions, shirka mapping, and contact blocks are now managed per package.</p>
            </div>
            <a href="<?= site_url('/packages') ?>" class="btn btn-md btn-primary inline-flex items-center gap-2">
                <i class="fa-solid fa-box-open"></i>
                <span>Open Packages</span>
            </a>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-sm text-slate-700">Open a package and update its voucher settings from the package add/edit form. Voucher print now reads those values directly from the selected package.</p>
        <div class="mt-4">
            <a href="<?= site_url('/main-company') ?>" class="btn btn-md btn-outline inline-flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back To Main Company Profile</span>
            </a>
        </div>
    </section>
</main>
<?php $this->endSection() ?>