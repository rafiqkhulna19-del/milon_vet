<?php
$pageTitle = 'বিজনেস ইনফো';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h5 class="section-title">ব্যবসার তথ্য</h5>
            <form class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">ব্যবসার নাম</label>
                    <input type="text" class="form-control" value="Milon Veterinary">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ফোন</label>
                    <input type="text" class="form-control" value="+8801XXXXXXXXX">
                </div>
                <div class="col-12">
                    <label class="form-label">ঠিকানা</label>
                    <input type="text" class="form-control" value="ফার্মগেট, ঢাকা">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ইমেইল</label>
                    <input type="email" class="form-control" value="info@milonvet.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label">মুদ্রা</label>
                    <select class="form-select">
                        <option selected>৳ (BDT)</option>
                        <option>₹ (INR)</option>
                        <option>$ (USD)</option>
                    </select>
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
                    <span class="badge bg-success">সম্পূর্ণ</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>ট্যাক্স রেট</span>
                    <span class="badge bg-warning text-dark">পর্যালোচনা</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>ব্রাঞ্চ</span>
                    <span class="badge bg-secondary">১ টি</span>
                </li>
            </ul>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
