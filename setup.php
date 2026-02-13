<?php
session_start();
$settings = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
[$pdo, $db_error] = db_connection();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business_name = trim($_POST['business_name'] ?? $settings['app_name'] ?? '');
    $tagline = trim($_POST['tagline'] ?? $settings['app_tagline'] ?? '');
    $currency = trim($_POST['currency'] ?? $settings['currency'] ?? '৳');

    $admin_name = trim($_POST['admin_name'] ?? '');
    $admin_username = trim($_POST['admin_username'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';
    $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';

    $do_import = isset($_POST['import_db']);

    if ($admin_password !== $admin_password_confirm) {
        $errors[] = 'পাস�"�Yার্ড মিল�>�? না।';
    }

    if ($do_import && !$pdo) {
        $errors[] = 'ডা�Yাব�?স�? স�,য�<�- �.রা যা�Yনি, Import �.রত�? পার�>ি না: ' . htmlspecialchars($db_error);
    }

    if (empty($errors)) {
        // Import database.sql if requested
        if ($do_import && $pdo) {
            $sqlFile = __DIR__ . '/database.sql';
            if (is_readable($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                // Split statements by semicolon followed by newline to avoid breaking content
                $stmts = preg_split('/;\s*\n/', $sql);
                try {
                    foreach ($stmts as $stmt) {
                        $stmt = trim($stmt);
                        if ($stmt === '') continue;
                        $pdo->exec($stmt);
                    }
                    $success .= 'ডা�Yাব�?স �?ম্প�<র্�Y সম্পন্ন হ�Y�?�>�?। ';
                } catch (PDOException $e) {
                    $errors[] = 'SQL �?ম্প�<র্�Y�? ত্রু�Yি: ' . $e->getMessage();
                }
            } else {
                $errors[] = 'database.sql ফা�?ল�Yি �-�<লা যা�Yনি।';
            }
        }

        // Create admin user if users table exists and no users present
        if ($pdo) {
            try {
                $countRow = fetch_one('SELECT COUNT(*) AS total FROM users');
                $userCount = (int) ($countRow['total'] ?? 0);
            } catch (Exception $e) {
                $userCount = 0;
            }

            if ($userCount === 0) {
                if ($admin_username === '' || $admin_password === '') {
                    $errors[] = '�.্যাডমিন�?র �oন্য �?�?�oারন�?ম �" পাস�"�Yার্ড দিত�? হব�?।';
                } else {
                    try {
                        $hash = password_hash($admin_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('INSERT INTO users (name, username, password_hash, role, email, phone, address) VALUES (:name, :username, :hash, :role, :email, :phone, :address)');
                        $stmt->execute([
                            ':name' => $admin_name ?: 'Admin',
                            ':username' => $admin_username,
                            ':hash' => $hash,
                            ':role' => 'Owner',
                            ':email' => null,
                            ':phone' => null,
                            ':address' => null,
                        ]);
                        $success .= '�.্যাডমিন ত�^রি �.রা হ�Y�?�>�?। ';
                    } catch (PDOException $e) {
                        $errors[] = '�.্যাডমিন ত�^রি �.রত�? ব্যর্থ: ' . $e->getMessage();
                    }
                }
            }
        }

        // Save business info into DB if table exists
        if ($pdo) {
            try {
                $hasBiz = fetch_one('SELECT id FROM business_info LIMIT 1');
                if ($hasBiz) {
                    $stmt = $pdo->prepare('UPDATE business_info SET business_name = :name, phone = :phone, email = :email, address = :address, currency = :currency WHERE id = :id');
                    $stmt->execute([
                        ':name' => $business_name,
                        ':phone' => $_POST['phone'] ?? null,
                        ':email' => $_POST['email'] ?? null,
                        ':address' => $_POST['address'] ?? null,
                        ':currency' => $currency,
                        ':id' => $hasBiz['id'],
                    ]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO business_info (business_name, logo_url, phone, email, address, currency) VALUES (:name, :logo, :phone, :email, :address, :currency)');
                    $stmt->execute([
                        ':name' => $business_name,
                        ':logo' => '',
                        ':phone' => $_POST['phone'] ?? null,
                        ':email' => $_POST['email'] ?? null,
                        ':address' => $_POST['address'] ?? null,
                        ':currency' => $currency,
                    ]);
                }
                $success .= 'ব্যবসার তথ্য স�,র�.্ষিত হ�Y�?�>�?। ';
            } catch (PDOException $e) {
                $errors[] = 'ব্যবসার তথ্য স�,র�.্ষণ�? ত্রু�Yি: ' . $e->getMessage();
            }
        }

        // Update config.php with app_name, app_tagline and currency
        $configFile = __DIR__ . '/config.php';
        try {
            $current = is_readable($configFile) ? (require $configFile) : [];
            if (!is_array($current)) $current = [];
            $current['app_name'] = $business_name ?: ($current['app_name'] ?? 'Milon Veterinary');
            $current['app_tagline'] = $tagline ?: ($current['app_tagline'] ?? 'Veterinary Pharmacy & Feed Management');
            $current['currency'] = $currency ?: ($current['currency'] ?? '৳');
            // no edition field
            $export = var_export($current, true);
            $content = "<?php\nreturn " . $export . ";\n";
            file_put_contents($configFile, $content);
            $success .= '�.নফি�- �?পড�?�Y হ�Y�?�>�?। ';
        } catch (Exception $e) {
            $errors[] = '�.নফি�- �?পড�?�Y �.রত�? ব্যর্থ: ' . $e->getMessage();
        }

        if (empty($errors)) {
            // Redirect to login page on successful setup
            header('Location: index.php?setup=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>প্রাতিষ্ঠানি�. স�?�Y�?প | <?= htmlspecialchars($settings['app_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm p-4" style="max-width: 820px; width: 100%;">
        <h4 class="fw-bold mb-1 text-center">প্রথমবার স�?�Y�?প �?" <?= htmlspecialchars($settings['app_name']) ?></h4>
        <p class="text-center text-muted mb-4">এ�-ান�? ডা�Yাব�?স �?ম্প�<র্�Y, �.্যাডমিন ত�^রি এব�, ব্যবসা তথ্য স�,র�.্ষণ �.রা হব�?।</p>

        <?php if ($db_error): ?>
            <div class="alert alert-danger">ডা�Yাব�?স: <?= htmlspecialchars($db_error) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" class="row g-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="import_db" name="import_db" checked>
                    <label class="form-check-label" for="import_db">Sample data �" �Y�?বিল Import �.রুন (database.sql)</label>
                </div>
            </div>

            <div class="col-12"><h6>ব্যবসার তথ্য</h6></div>
            <div class="col-md-6">
                <label class="form-label">ব্যবসার নাম <span class="text-danger">*</span></label>
                <input type="text" name="business_name" value="<?= htmlspecialchars($_POST['business_name'] ?? $settings['app_name']) ?>" class="form-control" placeholder="�?পনার ব্যবসার নাম" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">�Y্যা�-লা�?ন</label>
                <input type="text" name="tagline" value="<?= htmlspecialchars($_POST['tagline'] ?? $settings['app_tagline']) ?>" class="form-control" placeholder="স�,�.্ষিপ্ত �Y্যা�-লা�?ন">
            </div>
            <div class="col-md-4">
                <label class="form-label">ফ�<ন</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" class="form-control" placeholder="০১XXXXXXXXX">
            </div>
            <div class="col-md-4">
                <label class="form-label">�?ম�?�?ল</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="form-control" placeholder="info@example.com">
            </div>
            <div class="col-md-4">
                <label class="form-label">�.ার�?ন্সি প্রত�?�.</label>
                <input type="text" name="currency" value="<?= htmlspecialchars($_POST['currency'] ?? $settings['currency']) ?>" class="form-control" placeholder="৳">
            </div>
            <div class="col-12">
                <label class="form-label">ঠি�.ানা</label>
                <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" class="form-control" placeholder="ঠি�.ানা">
            </div>

            <div class="col-12"><h6 class="mt-3">�.্যাডমিন �?�?�oার</h6></div>
            <div class="col-md-4">
                <label class="form-label">নাম</label>
                <input type="text" name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? '') ?>" class="form-control" placeholder="প�,র্ণ নাম">
            </div>
            <div class="col-md-4">
                <label class="form-label">�?�?�oারন�?ম <span class="text-danger">*</span></label>
                <input type="text" name="admin_username" value="<?= htmlspecialchars($_POST['admin_username'] ?? 'admin') ?>" class="form-control" placeholder="admin" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">পাস�"�Yার্ড <span class="text-danger">*</span></label>
                <input type="password" name="admin_password" class="form-control" placeholder="পাস�"�Yার্ড" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">পাস�"�Yার্ড নিশ্�sিত �.রুন <span class="text-danger">*</span></label>
                <input type="password" name="admin_password_confirm" class="form-control" placeholder="পাস�"�Yার্ড নিশ্�sিত �.রুন" required>
            </div>

            <div class="col-12 text-end">
                <a href="index.php" class="btn btn-outline-secondary">বাতিল</a>
                <button class="btn btn-primary">স�?�Y�?প �sালান</button>
            </div>
        </form>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>

