<?php
$pageTitle = 'ক্যাটেগরি ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h5 class="section-title">নতুন ক্যাটেগরি</h5>
            <form class="vstack gap-3">
                <input class="form-control" type="text" placeholder="ক্যাটেগরি নাম">
                <button class="btn btn-primary" type="button">সংরক্ষণ করুন</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="section-title">ক্যাটেগরি তালিকা</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>ভেট মেডিসিন</span>
                    <span class="badge bg-secondary">42 আইটেম</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>ফিড</span>
                    <span class="badge bg-secondary">30 আইটেম</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>সাপ্লিমেন্ট</span>
                    <span class="badge bg-secondary">18 আইটেম</span>
                </li>
            </ul>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
