<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<section class="rounded-xl border border-slate-200 bg-white px-4 py-3">
    <h1 class="text-base font-semibold text-slate-800">Create Package</h1>
    <p class="mt-1 text-xs text-slate-500">Start with the flight: the trip dates, hotel stay dates and package duration are all derived from it, so everything for the package is captured on this single page.</p>
</section>

<?= $this->include('portal/packages/_form') ?>
<?php $this->endSection() ?>