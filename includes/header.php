<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$settings = require __DIR__ . '/../config.php';
$pages = [
    'dashboard.php' => ['label' => 'ড্যাশবোর্ড', 'icon' => 'bi-speedometer2'],
    'business_info.php' => ['label' => 'বিজনেস ইনফো', 'icon' => 'bi-building'],
    'profile.php' => ['label' => 'প্রোফাইল আপডেট', 'icon' => 'bi-person-circle'],
    'income_expense.php' => ['label' => 'আয়-ব্যায়', 'icon' => 'bi-cash-coin'],
    'assets_liabilities.php' => ['label' => 'দায়-পরিসম্পদ', 'icon' => 'bi-bar-chart'],
    'inventory.php' => ['label' => 'ইনভেন্টরি', 'icon' => 'bi-box-seam'],
    'reports.php' => ['label' => 'রিপোর্ট', 'icon' => 'bi-clipboard-data'],
    'sales.php' => ['label' => 'সেলস ম্যানেজমেন্ট', 'icon' => 'bi-receipt'],
    'memo_print.php' => ['label' => 'মেমো প্রিন্ট', 'icon' => 'bi-printer'],
    'customers.php' => ['label' => 'কাস্টমার ম্যানেজমেন্ট', 'icon' => 'bi-people'],
    'dues.php' => ['label' => 'ডিউ ম্যানেজমেন্ট', 'icon' => 'bi-wallet2'],
    'expenses.php' => ['label' => 'খরচ ম্যানেজমেন্ট', 'icon' => 'bi-journal-minus'],
    'suppliers.php' => ['label' => 'সাপ্লায়ার ম্যানেজমেন্ট', 'icon' => 'bi-truck'],
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-lg border-bottom bg-body">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <?= htmlspecialchars($settings['app_name']) ?>
        </a>
        <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-outline-secondary btn-sm" id="themeToggle" type="button">
                <i class="bi bi-moon-stars"></i> <span class="d-none d-md-inline">লাইট/ডার্ক</span>
            </button>
            <?php if (!empty($_SESSION['user'])): ?>
                <span class="small text-muted">স্বাগতম, <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                <a class="btn btn-sm btn-primary" href="logout.php">লগআউট</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <aside class="col-lg-2 d-none d-lg-block border-end min-vh-100 bg-body">
            <div class="p-3">
                <h6 class="text-uppercase text-muted">মেনু</h6>
                <ul class="nav nav-pills flex-column gap-1">
                    <?php foreach ($pages as $file => $meta): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $current === $file ? 'active' : '' ?>" href="<?= $file ?>">
                                <i class="bi <?= $meta['icon'] ?> me-2"></i><?= $meta['label'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>
        <main class="col-lg-10 px-4 py-4">
