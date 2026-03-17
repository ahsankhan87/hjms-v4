<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="w-full space-y-3">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Import Pilgrims</h3>
            <p class="text-xs text-slate-500">Upload MOFA CSV and create pilgrim records in bulk using the standard template.</p>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <a href="<?= base_url('assets/samples/mofa_pilgrims_sample.csv') ?>" class="btn btn-sm btn-secondary" download>
                    <i class="fa-solid fa-download"></i><span>Download Sample CSV</span>
                </a>
                <a href="<?= site_url('/pilgrims') ?>" class="btn btn-sm btn-secondary">Back to Pilgrims</a>
            </div>

            <form method="post" action="<?= site_url('/pilgrims/import') ?>" enctype="multipart/form-data" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">MOFA CSV File</label>
                    <input type="file" name="mofa_csv" accept=".csv" required class="w-full border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <a href="<?= site_url('/pilgrims') ?>" class="btn btn-md btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-md btn-primary">
                        <i class="fa-solid fa-file-arrow-up"></i><span>Import CSV</span>
                    </button>
                </div>
            </form>
        </article>
    </section>
</main>
<?php $this->endSection() ?>