<?php
$pageTitle = 'মেমো প্রিন্ট';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">মেমো খুঁজুন</h5>
            <form class="vstack gap-3">
                <input class="form-control" type="text" placeholder="মেমো নম্বর লিখুন">
                <button class="btn btn-primary" type="button">প্রিভিউ</button>
            </form>
        </div>
        <div class="card p-4 mt-4">
            <h6 class="section-title">প্রিন্ট অপশন</h6>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" type="button">প্রিন্ট</button>
                <button class="btn btn-outline-secondary" type="button">PDF</button>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">মেমো প্রিভিউ</h5>
            <div class="border p-3 rounded bg-body-secondary">
                <p class="fw-bold mb-1">Milon Veterinary</p>
                <p class="mb-1">ফার্মগেট, ঢাকা</p>
                <p class="mb-1">মেমো: #MV-1206</p>
                <hr>
                <p>আইটেম: এন্টি বায়োটিক ভেট x2</p>
                <p>মোট: ৳ 2,750</p>
                <p class="mb-0">ধন্যবাদ!</p>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
