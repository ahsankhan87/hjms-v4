<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="max-w-3xl">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 space-y-4">
            <h2 class="text-sm font-semibold text-slate-900">Import Pilgrims from MOFA CSV</h2>
            <p class="text-sm text-slate-600">Upload a CSV file received from MOFA. Required columns include Mutamer Name and Passport No.</p>

            <div class="flex flex-wrap gap-2">
                <a href="<?= base_url('assets/samples/mofa_pilgrims_sample.csv') ?>" class="btn btn-sm btn-secondary" download>
                    <i class="fa-solid fa-download mr-2"></i>Download Sample CSV
                </a>
                <a href="<?= site_url('/app/pilgrims') ?>" class="btn btn-sm btn-secondary">Back to Pilgrims</a>
            </div>

            <form method="post" action="<?= site_url('/app/pilgrims/import') ?>" enctype="multipart/form-data" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">MOFA CSV File</label>
                    <input type="file" name="mofa_csv" accept=".csv" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="btn btn-md btn-primary">Import CSV</button>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>