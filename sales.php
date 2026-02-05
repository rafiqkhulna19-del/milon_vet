<?php
$pageTitle = 'সেলস ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="section-title">সেলস এন্ট্রি</h5>
            <form class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">কাস্টমার</label>
                    <input class="form-control" type="text" placeholder="কাস্টমার নাম">
                </div>
                <div class="col-md-6">
                    <label class="form-label">মেমো নং</label>
                    <input class="form-control" type="text" placeholder="#MV-1207">
                </div>
                <div class="col-md-6">
                    <label class="form-label">পেমেন্ট পদ্ধতি</label>
                    <select class="form-select">
                        <option>ক্যাশ</option>
                        <option>ব্যাংক</option>
                        <option>মোবাইল ব্যাংকিং</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">মোট পরিমাণ</label>
                    <input class="form-control" type="number" placeholder="৳">
                </div>
                <div class="col-12">
                    <label class="form-label">নোট</label>
                    <textarea class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="button">সেলস সংরক্ষণ</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4">
            <h5 class="section-title">সাম্প্রতিক সেলস</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>#MV-1206</span>
                    <span>৳ 2,750</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>#MV-1205</span>
                    <span>৳ 4,850</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>#MV-1204</span>
                    <span>৳ 3,200</span>
                </li>
            </ul>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
