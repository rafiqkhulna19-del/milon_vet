<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
[$pdo, $db_error] = db_connection();

$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare('SELECT user_id, expires_at FROM password_resets WHERE token = :token LIMIT 1');
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch();
    if ($row && strtotime($row['expires_at']) > time()) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            if ($new_password !== $confirm_password) {
                $error = 'পাসওয়ার্ড মিলছে না।';
            } elseif (strlen($new_password) < 6) {
                $error = 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।';
            } else {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
                $update->execute([':hash' => $hash, ':id' => $row['user_id']]);
                $pdo->prepare('DELETE FROM password_resets WHERE token = :token')->execute([':token' => $token]);
                $success = 'পাসওয়ার্ড সফলভাবে রিসেট হয়েছে।';
            }
        }
    } else {
        $error = 'লিংকটি অবৈধ বা মেয়াদোত্তীর্ণ।';
    }
} else {
    $error = 'লিংকটি অবৈধ।';
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>নতুন পাসওয়ার্ড সেট করুন</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm p-4" style="max-width: 420px; width: 100%;">
        <h3 class="fw-bold mb-1 text-center">নতুন পাসওয়ার্ড সেট করুন</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success text-center">
                <?= htmlspecialchars($success) ?><br>
                <span class="small text-muted">৫ সেকেন্ড পরে স্বয়ংক্রিয়ভাবে লগইন পেজে চলে যাবে।</span><br>
                <a href="index.php" class="btn btn-outline-primary btn-sm mt-3">লগইন পেজে যান</a>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 5000);
            </script>
        <?php endif; ?>
        <?php if (!$success && isset($_GET['token']) && $row && strtotime($row['expires_at']) > time()): ?>
        <form method="post" class="vstack gap-3">
            <div>
                <label class="form-label">নতুন পাসওয়ার্ড</label>
                <input type="password" name="new_password" class="form-control" required minlength="6" placeholder="কমপক্ষে ৬ অক্ষর">
            </div>
            <div>
                <label class="form-label">পাসওয়ার্ড নিশ্চিত করুন</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6" placeholder="আবার লিখুন">
            </div>
            <button class="btn btn-success w-100">সেভ করুন</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
