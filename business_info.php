<?php
$pageTitle = 'বিজনেস ইনফো';
require __DIR__ . '/includes/header.php';

$message = '';
$business = fetch_one('SELECT * FROM business_info ORDER BY id DESC LIMIT 1');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $businessName = trim($_POST['business_name'] ?? '');
    $logoUrl = trim($_POST['logo_url'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $currency = trim($_POST['currency'] ?? '');

    if ($businessName !== '') {
        [$pdo] = db_connection();
        if ($pdo) {
            if ($business) {
                $stmt = $pdo->prepare('UPDATE business_info SET business_name = :business_name, logo_url = :logo_url, phone = :phone, email = :email, address = :address, currency = :currency WHERE id = :id');
                $stmt->execute([
                    ':business_name' => $businessName,
                    ':logo_url' => $logoUrl,
                    ':phone' => $phone,
                    ':email' => $email,
                    ':address' => $address,
                    ':currency' => $currency,
                    ':id' => $business['id'],
                ]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO business_info (business_name, logo_url, phone, email, address, currency) VALUES (:business_name, :logo_url, :phone, :email, :address, :currency)');
                $stmt->execute([
                    ':business_name' => $businessName,
                    ':logo_url' => $logoUrl,
                    ':phone' => $phone,
                    ':email' => $email,
                    ':address' => $address,
                    ':currency' => $currency,
                ]);
            }
            $message = 'বিজনেস ইনফো আপডেট হয়েছে।';
            $business = fetch_one('SELECT * FROM business_info ORDER BY id DESC LIMIT 1');
        }
    } else {
        $message = 'ব্যবসার নাম দিন।';
    }
}
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h5 class="section-title">ব্যবসার তথ্য</h5>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form class="row g-3" method="post">
                <div class="col-md-6">
                    <label class="form-label">ব্যবসার নাম</label>
                    <input type="text" name="business_name" class="form-control" value="<?= htmlspecialchars($business['business_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">লোগো URL</label>
                    <input type="text" name="logo_url" class="form-control" value="<?= htmlspecialchars($business['logo_url'] ?? '') ?>" placeholder="https://">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ফোন</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($business['phone'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">ঠিকানা</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($business['address'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ইমেইল</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($business['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">মুদ্রা</label>
                    <input type="text" name="currency" class="form-control" value="<?= htmlspecialchars($business['currency'] ?? ($settings['currency'] ?? '৳')) ?>">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="section-title">সেটিংস স্ট্যাটাস</h6>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>বিজনেস প্রোফাইল</span>
                    <span class="badge <?= $business ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= $business ? 'সম্পূর্ণ' : 'অপূর্ণ' ?>
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>ট্যাক্স রেট</span>
                    <span class="badge bg-secondary">সেট করুন</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>ব্রাঞ্চ</span>
                    <span class="badge bg-secondary">০ টি</span>
                </li>
            </ul>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
