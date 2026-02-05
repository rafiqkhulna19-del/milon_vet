<?php
$pageTitle = 'আয়-ব্যায়';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">আজকের আয়</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>সেলস ক্যাশ</span>
                    <span>৳ 9,800</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>ব্যাংক ট্রান্সফার</span>
                    <span>৳ 2,600</span>
                </li>
                <li class="list-group-item d-flex justify-content-between fw-bold">
                    <span>মোট</span>
                    <span>৳ 12,400</span>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">আজকের ব্যয়</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>স্টাফ পেমেন্ট</span>
                    <span>৳ 2,000</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>ডেলিভারি চার্জ</span>
                    <span>৳ 1,250</span>
                </li>
                <li class="list-group-item d-flex justify-content-between fw-bold">
                    <span>মোট</span>
                    <span>৳ 3,250</span>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="card p-4 mt-4">
    <h5 class="section-title">দৈনিক হিসাব যোগ করুন</h5>
    <form class="row g-3">
        <div class="col-md-4">
            <label class="form-label">ধরন</label>
            <select class="form-select">
                <option>আয়</option>
                <option>ব্যয়</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">খাত</label>
            <input class="form-control" type="text" placeholder="খাত লিখুন">
        </div>
        <div class="col-md-4">
            <label class="form-label">পরিমাণ</label>
            <input class="form-control" type="number" placeholder="৳">
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="button">সেভ করুন</button>
        </div>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
