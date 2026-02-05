<?php
$pageTitle = 'বিজনেস ইনফো';
require __DIR__ . '/includes/header.php';

$business = fetch_one('SELECT * FROM business_info ORDER BY id DESC LIMIT 1');
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h5 class="section-title">ব্যবসার তথ্য</h5>
            <form class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">ব্যবসার নাম</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($business['business_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ফোন</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($business['phone'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">ঠিকানা</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($business['address'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ইমেইল</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($business['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">মুদ্রা</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($business['currency'] ?? ($settings['currency'] ?? '৳')) ?>">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="button">সংরক্ষণ করুন</button>
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
