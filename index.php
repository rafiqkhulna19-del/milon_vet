<?php
session_start();
$settings = require __DIR__ . '/config.php';

if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === 'admin' && $password === '1234') {
        $_SESSION['user'] = [
            'name' => 'Milon Manager',
            'role' => 'Owner',
        ];
        header('Location: dashboard.php');
        exit;
    }

    $error = 'সঠিক ইউজারনেম ও পাসওয়ার্ড দিন (ডেমো: admin / 1234)।';
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
            <button class="btn btn-primary w-100">লগইন</button>
        </form>
        <div class="mt-3 text-center small text-muted">ডেমো ইউজার: admin / 1234</div>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
