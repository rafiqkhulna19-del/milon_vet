<?php
session_start();
$settings = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
[$pdo, $db_error] = db_connection();

if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$info = '';
$userCount = 0;
if ($pdo) {
    $countRow = fetch_one('SELECT COUNT(*) AS total FROM users');
    $userCount = (int) ($countRow['total'] ?? 0);
    if ($userCount === 0) {
        $info = 'প্রথমে ডাটাবেসে একজন অ্যাডমিন ব্যবহারকারী তৈরি করুন।';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && $userCount > 0) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $user = fetch_one('SELECT id, name, username, password_hash, role FROM users WHERE username = :username LIMIT 1', [
        ':username' => $username,
    ]);

    $isValid = false;
    if ($user) {
        $hash = $user['password_hash'];
        $isValid = password_verify($password, $hash) || hash_equals((string) $hash, (string) $password);
    }

    if ($isValid) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];
        header('Location: dashboard.php');
        exit;
    }

    $error = 'ইউজারনেম অথবা পাসওয়ার্ড সঠিক নয়।';
}
?>
<!DOCTYPE html>
<html lang="bn" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['app_name']) ?> | লগইন</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm p-4" style="max-width: 420px; width: 100%;">
        <h3 class="fw-bold mb-1 text-center"><?= htmlspecialchars($settings['app_name']) ?></h3>
        <p class="text-center text-muted mb-4"><?= htmlspecialchars($settings['app_tagline']) ?></p>
        <?php if (!empty($db_error)): ?>
            <div class="alert alert-danger">ডাটাবেসে সংযোগ হয়নি: <?= htmlspecialchars($db_error) ?></div>
        <?php endif; ?>
        <?php if ($info): ?>
            <div class="alert alert-info"><?= htmlspecialchars($info) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" class="vstack gap-3">
            <div>
                <label class="form-label">ইউজারনেম</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div>
                <label class="form-label">পাসওয়ার্ড</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100" <?= $userCount === 0 ? 'disabled' : '' ?>>লগইন</button>
        </form>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
