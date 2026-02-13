<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 // The rest of the header.php content follows...
 // (The rest of the file remains unchanged)
 // ...
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$settings = require __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';
[, $db_error] = db_connection();

$user = $_SESSION['user'] ?? null;
$userName = $user['name'] ?? '';
$userRole = $user['role'] ?? '';

$parts = preg_split('/\s+/', trim($userName));
$initials = '';
if (!empty($parts)) {
    $initials .= mb_substr($parts[0], 0, 1, 'UTF-8');
    if (count($parts) > 1) {
        $initials .= mb_substr($parts[count($parts) - 1], 0, 1, 'UTF-8');
    }
}

$pages = [
    'dashboard.php' => ['label' => 'ড্যাশবোর্ড', 'icon' => 'bi-speedometer2'],
    'new_memo.php' => ['label' => 'নতুন মেমো', 'icon' => 'bi-journal-plus'],
    'sales.php' => ['label' => 'মেমো ম্যানেজমেন্ট', 'icon' => 'bi-receipt'],
    'customers.php' => ['label' => 'কাস্টমার ম্যানেজমেন্ট', 'icon' => 'bi-people'],
    'dues.php' => ['label' => 'ডিউ ম্যানেজমেন্ট', 'icon' => 'bi-wallet2'],
    'inventory.php' => ['label' => 'ইনভেন্টরি', 'icon' => 'bi-box-seam'],
    'purchases.php' => ['label' => 'পণ্য ক্রয়', 'icon' => 'bi-cart-plus'],
    'suppliers.php' => ['label' => 'সাপ্লায়ার ম্যানেজমেন্ট', 'icon' => 'bi-truck'],
    'expenses.php' => ['label' => 'খরচ ম্যানেজমেন্ট', 'icon' => 'bi-journal-minus'],
    'income_expense.php' => ['label' => 'আয়-ব্যয়', 'icon' => 'bi-cash-coin'],
    'assets_liabilities.php' => ['label' => 'দায়-পরিসম্পদ', 'icon' => 'bi-bar-chart'],
    'ledger.php' => ['label' => 'ক্রয়/বিক্রয় লেজার', 'icon' => 'bi-journal-text'],
    'reports.php' => ['label' => 'রিপোর্ট', 'icon' => 'bi-clipboard-data'],
    'categories.php' => ['label' => 'ক্যাটেগরি ম্যানেজমেন্ট', 'icon' => 'bi-tags'],
];
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="bn" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['app_name']) ?> | <?= htmlspecialchars($pageTitle ?? 'ড্যাশবোর্ড') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (() => {
            const stored = localStorage.getItem('milon-sidebar-mode');
            const legacy = localStorage.getItem('milon-sidebar');
            const mode = stored || (legacy === 'collapsed' ? 'compact' : 'expanded');
            if (mode === 'compact') {
                document.documentElement.classList.add('sidebar-compact');
            }
        })();
    </script>
</head>

<body class="bg-body-tertiary">
    <nav class="navbar navbar-expand-lg border-bottom bg-body navbar-glass sticky-top">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-2">
                <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="dashboard.php">
                    <span
                        class="brand-mark"><?= htmlspecialchars(mb_substr($settings['app_name'], 0, 1, 'UTF-8')) ?></span>
                    <span><?= htmlspecialchars($settings['app_name']) ?></span>
                </a>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <div class="header-actions d-none d-md-flex align-items-center gap-2 me-1">
                    <a class="btn btn-primary btn-sm" href="new_memo.php">
                        <i class="bi bi-plus-lg me-1"></i>New
                    </a>
                    <button class="btn btn-outline-secondary btn-sm position-relative" type="button">
                        <i class="bi bi-bell"></i>
                        <span
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                    </button>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-1"
                        id="themeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-palette"></i> <span class="d-none d-md-inline theme-label">থিম</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button class="dropdown-item d-flex align-items-center justify-content-between"
                                type="button" data-theme-value="light">
                                <span class="d-flex align-items-center gap-2"><i
                                        class="bi bi-brightness-high"></i>Light</span>
                                <i class="bi bi-check theme-check opacity-0"></i>
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item d-flex align-items-center justify-content-between"
                                type="button" data-theme-value="dark">
                                <span class="d-flex align-items-center gap-2"><i
                                        class="bi bi-moon-stars"></i>Dark</span>
                                <i class="bi bi-check theme-check opacity-0"></i>
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item d-flex align-items-center justify-content-between"
                                type="button" data-theme-value="system">
                                <span class="d-flex align-items-center gap-2"><i class="bi bi-laptop"></i>System</span>
                                <i class="bi bi-check theme-check opacity-0"></i>
                            </button>
                        </li>
                    </ul>
                </div>
                <?php if (!empty($_SESSION['user'])): ?>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none" id="userDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                title="<?= htmlspecialchars($userName) ?>"><?= htmlspecialchars($initials ?: 'U') ?></div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                    data-bs-target="#appSettingsModal"><i class="bi bi-gear me-2"></i>অ্যাপ সেটিংস</a></li>
                            <li><a class="dropdown-item" href="profile.php"><i
                                        class="bi bi-person-circle me-2"></i>প্রোফাইল</a></li>
                            <li><a class="dropdown-item" href="business_info.php"><i class="bi bi-building me-2"></i>বিজনেস
                                    ইনফো</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i
                                        class="bi bi-box-arrow-right me-2"></i>লগআউট</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="modal fade" id="appSettingsModal" tabindex="-1" aria-labelledby="appSettingsLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appSettingsLabel">অ্যাপ সেটিংস</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sidebar behaviour</label>
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sidebarMode" id="sbExpanded"
                                    value="expanded">
                                <label class="form-check-label" for="sbExpanded">Expanded</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sidebarMode" id="sbCompact"
                                    value="compact">
                                <label class="form-check-label" for="sbCompact">Compact (icons)</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="sbHoverToggle">
                        <label class="form-check-label" for="sbHoverToggle">Hover to expand when compact</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="button" class="btn btn-primary" id="saveAppSettings">সেভ</button>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="app-shell">
            <aside class="sidebar-panel border-end min-vh-100 bg-body sidebar">
                <button class="sidebar-switcher" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
                    <i class="bi bi-chevron-double-left"></i>
                </button>
                <div class="">
                    <ul class="nav nav-pills flex-column gap-1">
                        <?php foreach ($pages as $file => $meta): ?>
                            <li class="nav-item mx-4 py-2">
                                <a class="nav-link <?= $current === $file ? 'active' : '' ?>" href="<?= $file ?>"
                                    data-label="<?= htmlspecialchars($meta['label']) ?>" <?= $current === $file ? 'aria-current="page"' : '' ?>>
                                    <i class="bi <?= $meta['icon'] ?>"></i><span
                                        class="nav-label"><?= $meta['label'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
            <main class="app-main px-4 py-4">
                <?php if (!empty($db_error)): ?>
                    <div class="alert alert-danger">
                        ডাটাবেসে সংযোগ হয়নি: <?= htmlspecialchars($db_error) ?>
                    </div>
                <?php endif; ?>