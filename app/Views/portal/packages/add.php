<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<section class="rounded-xl border border-slate-200 bg-white px-4 py-3">
    <h1 class="text-base font-semibold text-slate-800">Create Package</h1>
    <p class="mt-1 text-xs text-slate-500">Flight dates come first — hotel stays, nights and duration follow automatically. Sections expand as you need them.</p>
</section>

<?= $this->include('portal/packages/_form') ?>
<?php $this->endSection() ?>