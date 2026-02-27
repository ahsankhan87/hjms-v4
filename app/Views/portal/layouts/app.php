<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'HJMS ERP') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= base_url('assets/js/jquery-3.6.0.min.js') ?>"></script>

    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.local.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/datatable-1.11.5/jquery.dataTables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/datatable-1.11.5/buttons.dataTables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/js/select2/select2.min.css') ?>">

    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.0.0-web/css/all.min.css') ?>">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --text: #1f2937;
            --text-muted: #6b7280;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
        }

        #appShell .card,
        main article.rounded-xl,
        main article.rounded-2xl {
            border: 1px solid #e2e8f0;
            box-shadow: 0 8px 24px -14px rgba(15, 23, 42, 0.2);
            border-radius: 14px;
            background: var(--surface);
        }

        main input,
        main select,
        main textarea {
            border-color: #cbd5e1;
            transition: all 0.2s ease;
            background: #fff;
        }

        main input:focus,
        main select:focus,
        main textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
        }

        main table thead {
            background-color: #f8fafc;
        }

        .gradient-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .sidebar-item {
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-item:hover,
        .sidebar-item.active {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.12) 0%, transparent 100%);
            border-left-color: var(--primary);
            color: #0f172a;
        }

        .top-search:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.16);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .btn-md {
            padding: 0.5rem 1rem;
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-block {
            width: 100%;
        }

        .btn-primary {
            background: #16a34a;
            color: #fff;
        }

        .btn-primary:hover {
            background: #15803d;
        }

        .btn-secondary {
            border-color: #d1d5db;
            color: #374151;
            background: #fff;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .btn-danger {
            background: #dc2626;
            color: #fff;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            color: #4b5563;
            background: transparent;
            border: 1px solid transparent;
        }

        .icon-btn:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .icon-btn-danger {
            color: #dc2626;
        }

        .icon-btn-danger:hover {
            background: #fef2f2;
            color: #b91c1c;
        }

        .list-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
        }

        .list-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .list-card .list-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .list-card .list-table thead {
            background: #f9fafb;
        }

        .list-card .list-table th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .list-card .list-table td {
            padding: 1rem 1.5rem;
            color: #4b5563;
            border-top: 1px solid #f3f4f6;
        }

        .list-card .list-table tbody tr:hover {
            background: #f9fafb;
        }

        .list-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            color: #6b7280;
            font-size: 0.875rem;
            flex-wrap: wrap;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.35rem 0.6rem;
            background: #fff;
            color: #374151;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.5rem !important;
            border: 1px solid #d1d5db !important;
            background: #fff !important;
            color: #374151 !important;
            margin-left: 0.25rem;
            padding: 0.25rem 0.6rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #16a34a !important;
            color: #fff !important;
            border-color: #16a34a !important;
        }

        .dataTables_wrapper .dt-buttons {
            display: inline-flex;
            gap: 0.35rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .dataTables_wrapper .dt-buttons .dt-button {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            background: #fff !important;
            color: #374151 !important;
            font-size: 0.875rem !important;
            line-height: 1.25rem;
            padding: 0.3rem 0.65rem !important;
        }

        .dataTables_wrapper .dt-buttons .dt-button:hover {
            background: #f9fafb !important;
            border-color: #9ca3af !important;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
        }

        .list-card .dataTables_wrapper {
            padding: 0.85rem 1rem 1rem;
        }

        .list-card .dataTables_wrapper table.dataTable {
            margin-top: 0.35rem !important;
            margin-bottom: 0.35rem !important;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-approved {
            background: #d1fae5;
            color: #059669;
        }

        .status-rejected {
            background: #fee2e2;
            color: #dc2626;
        }

        main .bg-blue-600 {
            background-color: var(--primary) !important;
        }

        main .hover\:bg-blue-700:hover {
            background-color: var(--primary-dark) !important;
        }

        main .text-blue-600 {
            color: var(--primary) !important;
        }

        main .bg-blue-50 {
            background-color: rgba(16, 185, 129, 0.1) !important;
        }

        main .focus\:border-slate-500:focus {
            border-color: var(--primary) !important;
        }

        main .focus\:ring-blue-500:focus {
            --tw-ring-color: rgba(16, 185, 129, 0.35) !important;
        }

        main button,
        main a.btn {
            transition: all 0.2s ease;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased">
    <div id="appShell" class="flex h-screen overflow-hidden">
        <aside id="sidebar" class="w-72 bg-white shadow-lg flex flex-col transition-all duration-300 -translate-x-full md:translate-x-0 fixed md:static inset-y-0 left-0 z-40">
            <div class="flex h-full flex-col">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center text-white">
                            <i class="fa-solid fa-kaaba"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-800">Karwane Taif</h1>
                            <p class="text-xs text-gray-500">Hajj & Umrah Management</p>
                        </div>
                    </div>
                </div>

                <nav class="flex-1 overflow-y-auto py-4">
                    <?php
                    $seasonRows = all_seasons();
                    $activeSeason = active_season();
                    $canRbacManage = auth_can('rbac.manage');
                    $canSeasons = $canRbacManage;
                    $canDashboard = auth_can('dashboard.view');
                    $canPackages = auth_can('packages.view');
                    $canPilgrims = auth_can('pilgrims.view');
                    $canVisas = auth_can('visas.view');
                    $canHotels = auth_can('hotels.view');
                    $canFlights = auth_can('flights.view');
                    $canTransports = auth_can('transports.view');
                    $canPayments = auth_can('payments.view');
                    $canBookings = auth_can('bookings.view');
                    $canBranches = auth_can('branches.view');
                    $canAgents = auth_can('agents.view');
                    $canUsers = auth_can('users.view');
                    $canReports = auth_can('reports.view');
                    $canAudit = auth_can('audit.view');
                    ?>
                    <div class="px-4 mb-2">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Main Menu</p>
                    </div>
                    <?php if ($canDashboard): ?>
                        <a href="<?= site_url('/app') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                            <i class="fa-solid fa-chart-line w-4"></i><span>Dashboard</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canPackages): ?>
                        <a href="<?= site_url('/app/packages') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'packages' ? 'active' : '' ?>">
                            <i class="fa-solid fa-box-open w-4"></i><span>Packages</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canPilgrims): ?>
                        <a href="<?= site_url('/app/pilgrims') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'pilgrims' ? 'active' : '' ?>">
                            <i class="fa-solid fa-users w-4"></i><span>Pilgrims</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canVisas): ?>
                        <a href="<?= site_url('/app/visas') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'visas' ? 'active' : '' ?>">
                            <i class="fa-solid fa-passport w-4"></i><span>Visa Processing</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canFlights): ?>
                        <a href="<?= site_url('/app/flights') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'flights' ? 'active' : '' ?>">
                            <i class="fa-solid fa-plane w-4"></i><span>Flights</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canHotels): ?>
                        <a href="<?= site_url('/app/hotels') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'hotels' ? 'active' : '' ?>">
                            <i class="fa-solid fa-hotel w-4"></i><span>Hotels</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canTransports): ?>
                        <a href="<?= site_url('/app/transports') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'transports' ? 'active' : '' ?>">
                            <i class="fa-solid fa-bus w-4"></i><span>Transport</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($canPayments): ?>
                        <div class="px-4 mt-6 mb-2">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Finance</p>
                        </div>
                        <a href="<?= site_url('/app/payments') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'payments' ? 'active' : '' ?>">
                            <i class="fa-solid fa-wallet w-4"></i><span>Payments</span>
                        </a>
                        <a href="<?= site_url('/app/suppliers') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'suppliers' ? 'active' : '' ?>">
                            <i class="fa-solid fa-handshake w-4"></i><span>Suppliers</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($canBookings || $canBranches || $canAgents || $canReports): ?>
                        <div class="px-4 mt-6 mb-2">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Operations</p>
                        </div>
                    <?php endif; ?>
                    <?php if ($canBookings): ?>
                        <a href="<?= site_url('/app/bookings') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'bookings' ? 'active' : '' ?>">
                            <i class="fa-solid fa-file-contract w-4"></i><span>Bookings</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canBranches): ?>
                        <a href="<?= site_url('/app/branches') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'branches' ? 'active' : '' ?>">
                            <i class="fa-solid fa-code-branch w-4"></i><span>Branches</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canAgents): ?>
                        <a href="<?= site_url('/app/agents') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'agents' ? 'active' : '' ?>">
                            <i class="fa-solid fa-user-tie w-4"></i><span>Agents</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canUsers): ?>
                        <a href="<?= site_url('/app/users') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>">
                            <i class="fa-solid fa-user-gear w-4"></i><span>Users</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canReports): ?>
                        <a href="<?= site_url('/app/reports') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'reports' ? 'active' : '' ?>">
                            <i class="fa-solid fa-chart-pie w-4"></i><span>Reports</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canAudit): ?>
                        <a href="<?= site_url('/app/audit') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'audit' ? 'active' : '' ?>">
                            <i class="fa-solid fa-shield-halved w-4"></i><span>Audit Log</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canRbacManage): ?>
                        <a href="<?= site_url('/app/rbac') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'rbac' ? 'active' : '' ?>">
                            <i class="fa-solid fa-user-shield w-4"></i><span>Access Control</span>
                        </a>

                    <?php endif; ?>
                    <?php if ($canSeasons): ?>
                        <a href="<?= site_url('/app/seasons') ?>" class="sidebar-item flex items-center gap-3 px-6 py-3 text-gray-700 <?= ($activePage ?? '') === 'seasons' ? 'active' : '' ?>">
                            <i class="fa-solid fa-calendar-days w-4"></i><span>Seasons</span>
                        </a>
                    <?php endif; ?>
                </nav>

                <div class="p-4 border-t border-gray-100">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 gradient-bg rounded-full flex items-center justify-center text-white font-semibold">
                            <?= esc(strtoupper(substr((string) ($userEmail ?? 'A'), 0, 1))) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800">Admin User</p>
                            <p class="text-xs text-gray-500 truncate"><?= esc($userEmail ?? '') ?></p>
                        </div>
                        <i class="fa-solid fa-gear text-gray-400"></i>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <header class="bg-white shadow-sm border-b border-gray-100 px-4 md:px-8 py-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button id="menuToggle" class="p-2 hover:bg-gray-100 rounded-lg md:hidden" type="button">
                            <i class="fa-solid fa-bars"></i>
                        </button>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800"><?= esc($headerTitle ?? $title ?? 'Dashboard') ?></h2>
                            <p class="text-sm text-gray-500">Welcome back! Here's what's happening today.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <?php if ($canSeasons && !empty($seasonRows)): ?>
                            <form method="post" action="<?= site_url('/app/seasons/activate') ?>" class="hidden xl:flex items-center gap-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_return" value="<?= current_url() ?>">
                                <label class="text-xs font-medium text-slate-600">Season</label>
                                <select name="season_id" class="rounded-lg border border-gray-200 px-2 py-2 text-sm" onchange="this.form.submit()">
                                    <?php foreach ($seasonRows as $seasonRow): ?>
                                        <option value="<?= esc($seasonRow['id']) ?>" <?= (int) ($seasonRow['id'] ?? 0) === (int) ($activeSeason['id'] ?? 0) ? 'selected' : '' ?>>
                                            <?= esc($seasonRow['name'] ?? (($seasonRow['year_start'] ?? '') . ' - ' . ($seasonRow['year_end'] ?? ''))) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        <?php endif; ?>
                        <div class="relative hidden lg:block">
                            <input type="text" placeholder="Search..." class="top-search pl-10 pr-4 py-2 border border-gray-200 rounded-lg w-64 text-sm">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        </div>
                        <form method="post" action="<?= site_url('/app/logout') ?>">
                            <?= csrf_field() ?>
                            <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700" type="submit">
                                <i class="fa-solid fa-right-from-bracket mr-1"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto px-4 py-6 md:px-8">
                <?= $this->renderSection('main') ?>
            </main>

            <footer class="mt-auto border-t border-gray-100 bg-white px-4 py-3 text-xs text-gray-500 md:px-8">
                HJMS ERP © <?= date('Y') ?>
            </footer>
        </div>
    </div>

    <script>
        (function() {
            var menuToggle = document.getElementById('menuToggle');
            var sidebar = document.getElementById('sidebar');
            if (!menuToggle || !sidebar) {
                return;
            }
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
            });
        })();
    </script>
    <script src="<?= base_url('assets/datatable-1.11.5/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/datatable-1.11.5/dataTables.buttons.min.js') ?>"></script>
    <script src="<?= base_url('assets/datatable-1.11.5/jszip.min.js') ?>"></script>
    <script src="<?= base_url('assets/datatable-1.11.5/buttons.html5.min.js') ?>"></script>
    <script src="<?= base_url('assets/datatable-1.11.5/buttons.print.min.js') ?>"></script>
    <script src="<?= base_url('assets/datatable-1.11.5/buttons.colVis.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/select2/select2.min.js') ?>"></script>
    <script>
        (function($) {
            if (!$ || !$.fn) {
                return;
            }

            if ($.fn.select2) {
                $('.js-select2').each(function() {
                    var $select = $(this);
                    var hasEmptyOption = $select.find('option[value=""]').length > 0;

                    $select.select2({
                        width: '100%',
                        placeholder: hasEmptyOption ? 'Select option' : '',
                        allowClear: hasEmptyOption
                    });
                });

                $(document).on('select2:open', function() {
                    var searchField = document.querySelector('.select2-container--open .select2-search__field');
                    if (searchField) {
                        searchField.focus();
                    }
                });
            }

            if (!$.fn.DataTable) {
                return;
            }

            $('table.list-table').each(function() {
                var table = this;
                if ($.fn.DataTable.isDataTable(table)) {
                    return;
                }

                $(table).DataTable({
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    order: [],
                    autoWidth: false,
                    dom: 'Bfrtip',
                    buttons: ['csvHtml5', 'excelHtml5', 'print', 'colvis'],
                    language: {
                        search: 'Search:',
                        lengthMenu: 'Show _MENU_ entries',
                        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                        paginate: {
                            previous: 'Previous',
                            next: 'Next'
                        }
                    }
                });

                $(table).closest('.list-card').find('.list-footer').hide();
            });
        })(window.jQuery);
    </script>
</body>

</html>