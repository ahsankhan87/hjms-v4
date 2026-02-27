<?php $this->extend('portal/layouts/app') ?>

<?php $this->section('main') ?>
<main class="space-y-6">
    <?php if (!empty($success)): ?><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc($error) ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?php foreach ($errors as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold">Create Permission</h3>
            <form method="post" action="<?= site_url('/app/rbac/permissions') ?>" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input name="name" required placeholder="permission.name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="module" placeholder="module" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <textarea name="description" rows="2" placeholder="Description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                <div class="flex gap-2">
                    <button class="btn btn-md btn-primary" type="submit">Create Permission</button>
                    <a class="btn btn-md btn-secondary" href="<?= site_url('/app/rbac') ?>">Back</a>
                </div>
            </form>
        </article>

        <article class="list-card">
            <div class="p-4 border-b border-gray-100 font-semibold">Permissions</div>
            <table class="list-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Module</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissions as $permission): ?>
                        <tr>
                            <td><?= esc($permission['name']) ?></td>
                            <td><?= esc($permission['module'] ?? '-') ?></td>
                            <td><?= esc($permission['description'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </article>
    </section>

    <article class="list-card">
        <div class="p-4 border-b border-gray-100">
            <div class="font-semibold">Role → Permission Mapping</div>
            <p class="mt-1 text-sm text-slate-500">Select a role, search permissions, check what should be allowed, then save once.</p>
        </div>
        <?php
        $permissionIdByName = [];
        foreach ($permissions as $permission) {
            $permissionIdByName[(string) $permission['name']] = (int) $permission['id'];
        }

        $rolePermissionIds = [];
        foreach ($roles as $role) {
            $roleId = (int) $role['id'];
            $currentNames = $rolePermissions[$roleId] ?? [];
            $rolePermissionIds[$roleId] = [];
            foreach ($currentNames as $permissionName) {
                if (isset($permissionIdByName[$permissionName])) {
                    $rolePermissionIds[$roleId][] = $permissionIdByName[$permissionName];
                }
            }
        }
        ?>
        <form method="post" action="<?= site_url('/app/rbac/role-permissions') ?>" class="p-4 space-y-4" id="rolePermissionForm">
            <?= csrf_field() ?>
            <div class="grid gap-3 lg:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Role</label>
                    <select name="role_id" id="roleSelect" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= esc($role['id']) ?>"><?= esc($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Search Permission</label>
                    <input type="text" id="permissionSearch" placeholder="Type permission name or module" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="selectVisibleBtn" class="btn btn-sm btn-secondary">Select Visible</button>
                <button type="button" id="clearAllBtn" class="btn btn-sm btn-secondary">Clear All</button>
                <span id="selectedCount" class="text-sm text-slate-500">0 selected</span>
            </div>

            <div id="permissionList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
                <?php foreach ($permissions as $permission): ?>
                    <?php
                    $module = (string) ($permission['module'] ?? 'general');
                    $searchText = strtolower((string) $permission['name'] . ' ' . $module . ' ' . (string) ($permission['description'] ?? ''));
                    ?>
                    <label class="inline-flex items-start gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 permission-item" data-search="<?= esc($searchText) ?>">
                        <input type="checkbox" name="permission_ids[]" value="<?= esc($permission['id']) ?>" class="permission-checkbox mt-0.5">
                        <span>
                            <span class="block font-medium text-slate-800"><?= esc($permission['name']) ?></span>
                            <span class="block text-xs text-slate-500"><?= esc($module) ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div>
                <button class="btn btn-md btn-primary" type="submit">Save Mapping</button>
            </div>
        </form>
    </article>
</main>
<script>
    (function() {
        var roleMap = <?= json_encode($rolePermissionIds, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        var roleSelect = document.getElementById('roleSelect');
        var permissionSearch = document.getElementById('permissionSearch');
        var permissionItems = Array.prototype.slice.call(document.querySelectorAll('.permission-item'));
        var permissionCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.permission-checkbox'));
        var selectVisibleBtn = document.getElementById('selectVisibleBtn');
        var clearAllBtn = document.getElementById('clearAllBtn');
        var selectedCount = document.getElementById('selectedCount');

        function refreshSelectedCount() {
            var count = permissionCheckboxes.filter(function(item) {
                return item.checked;
            }).length;
            selectedCount.textContent = count + ' selected';
        }

        function applyRolePermissions() {
            permissionCheckboxes.forEach(function(item) {
                item.checked = false;
            });
            var roleId = roleSelect.value;
            if (!roleId || !roleMap[roleId]) {
                refreshSelectedCount();
                return;
            }

            var activeSet = {};
            roleMap[roleId].forEach(function(id) {
                activeSet[String(id)] = true;
            });

            permissionCheckboxes.forEach(function(item) {
                if (activeSet[item.value]) {
                    item.checked = true;
                }
            });

            refreshSelectedCount();
        }

        function applySearchFilter() {
            var keyword = (permissionSearch.value || '').toLowerCase().trim();
            permissionItems.forEach(function(item) {
                var haystack = (item.getAttribute('data-search') || '').toLowerCase();
                var visible = keyword === '' || haystack.indexOf(keyword) !== -1;
                item.style.display = visible ? '' : 'none';
            });
        }

        roleSelect.addEventListener('change', applyRolePermissions);
        permissionSearch.addEventListener('input', applySearchFilter);

        permissionCheckboxes.forEach(function(item) {
            item.addEventListener('change', refreshSelectedCount);
        });

        selectVisibleBtn.addEventListener('click', function() {
            permissionItems.forEach(function(item) {
                if (item.style.display === 'none') {
                    return;
                }
                var checkbox = item.querySelector('.permission-checkbox');
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
            refreshSelectedCount();
        });

        clearAllBtn.addEventListener('click', function() {
            permissionCheckboxes.forEach(function(item) {
                item.checked = false;
            });
            refreshSelectedCount();
        });

        refreshSelectedCount();
    })();
</script>
<?php $this->endSection() ?>