<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-4">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700\"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700\"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700\"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="space-y-4">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-600 px-4 py-4 text-white sm:px-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.16em] text-emerald-100">Agent Ledger</p>
                        <h2 class="mt-1 text-xl font-semibold">Post Manual Entry</h2>
                        <p class="mt-1 text-xs text-emerald-100">Add debit, credit, or adjustment entries in a controlled separate workflow.</p>
                    </div>
                    <a href="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger') ?>" class="inline-flex items-center gap-2 rounded-lg border border-white/40 bg-white/10 px-3 py-2 text-xs font-medium text-white transition hover:bg-white/20">
                        <i class="fa-solid fa-arrow-left text-[11px]"></i>
                        Back to Ledger
                    </a>
                </div>
            </div>

            <div class="grid gap-3 px-4 py-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">Agent</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800"><?= esc((string) ($agent['name'] ?? 'N/A')) ?></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">Code</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800"><?= esc((string) ($agent['code'] ?? 'N/A')) ?></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">Current Balance</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800"><?= esc(number_format((float) ($closingBalance ?? 0), 2)) ?></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">Entries Posted</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800"><?= esc((string) ($entryCount ?? 0)) ?></p>
                </div>
            </div>

            <div class="border-t border-slate-200 px-4 py-4 sm:px-6">
                <form method="post" action="<?= site_url('/agents/ledger') ?>" class="space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="agent_id" value="<?= esc((string) ($agent['id'] ?? 0)) ?>">

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label for="entry_date" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Entry Date</label>
                            <input id="entry_date" type="date" name="entry_date" value="<?= esc(old('entry_date', date('Y-m-d'))) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>
                        <div>
                            <label for="entry_type" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Entry Type</label>
                            <?php $entryType = old('entry_type', 'credit'); ?>
                            <select id="entry_type" name="entry_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="debit" <?= $entryType === 'debit' ? 'selected' : '' ?>>Debit (Increase Receivable)</option>
                                <option value="credit" <?= $entryType === 'credit' ? 'selected' : '' ?>>Credit (Payment Received)</option>
                                <option value="adjustment" <?= $entryType === 'adjustment' ? 'selected' : '' ?>>Adjustment</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label for="amount" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Amount</label>
                            <input id="amount" name="amount" value="<?= esc(old('amount')) ?>" placeholder="0.00" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>
                        <div>
                            <label for="description" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Description</label>
                            <input id="description" name="description" value="<?= esc(old('description')) ?>" placeholder="Reason or remarks" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 border-t border-slate-200 pt-4">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                            <i class="fa-solid fa-check text-[11px]"></i>
                            Post Entry
                        </button>
                        <a href="<?= site_url('/agents/' . (int) ($agent['id'] ?? 0) . '/ledger') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </article>
    </section>
</main>
<?php $this->endSection() ?>