<?php
$pageTitle = 'প্রোফাইল আপডেট';
require __DIR__ . '/includes/header.php';

$user = fetch_one('SELECT name, role, email, phone, address FROM users WHERE id = :id', [
    ':id' => $_SESSION['user']['id'] ?? 0,
]);
?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="section-title">ব্যবহারকারীর প্রোফাইল</h5>
            <form class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">নাম</label>
                    <input class="form-control" type="text" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">পদবি</label>
                    <input class="form-control" type="text" value="<?= htmlspecialchars($user['role'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ইমেইল</label>
                    <input class="form-control" type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ফোন</label>
                    <input class="form-control" type="text" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">ঠিকানা</label>
                    <input class="form-control" type="text" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="button">আপডেট করুন</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4">
            <h6 class="section-title">নিরাপত্তা</h6>
            <div class="mb-3">
                <label class="form-label">নতুন পাসওয়ার্ড</label>
                <input type="password" class="form-control" placeholder="••••••">
            </div>
            <div class="mb-3">
                <label class="form-label">পাসওয়ার্ড নিশ্চিত করুন</label>
                <input type="password" class="form-control" placeholder="••••••">
            </div>
            <button class="btn btn-outline-primary" type="button">পাসওয়ার্ড পরিবর্তন</button>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
