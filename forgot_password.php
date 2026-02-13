<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
[$pdo, $db_error] = db_connection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!$email) {
        $error = 'ইমেইল দিন।';
    } else {
        $user = fetch_one('SELECT id, name, email FROM users WHERE email = :email LIMIT 1', [':email' => $email]);
        if ($user) {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            [$pdo] = db_connection();
            $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)');
            $stmt->execute([
                ':user_id' => $user['id'],
                ':token' => $token,
                ':expires_at' => $expires,
            ]);
            // Show reset link for testing
            $reset_link = 'reset_password.php?token=' . $token;
            $success = 'রিসেট লিংক ইমেইলে পাঠানো হয়েছে।<br><a href="' . htmlspecialchars($reset_link) . '" target="_blank">রিসেট লিংক (পরীক্ষার জন্য)</a>';
        } else {
            $error = 'এই ইমেইল দিয়ে কোন ইউজার নেই।';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>পাসওয়ার্ড রিসেট</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm p-4" style="max-width: 420px; width: 100%;">
        <h3 class="fw-bold mb-1 text-center">পাসওয়ার্ড রিসেট</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" class="vstack gap-3">
            <div>
                <label class="form-label">ইমেইল</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">রিসেট লিংক পাঠান</button>
        </form>
    </div>
</body>
</html>
