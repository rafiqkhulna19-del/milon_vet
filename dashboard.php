<?php
$pageTitle = 'ড্যাশবোর্ড';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-12">
        <div class="card hero-card p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div>
                    <h2 class="fw-bold">স্বাগতম, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'ম্যানেজার') ?></h2>
                    <p class="mb-0">আজকের ব্যবসার সারসংক্ষেপ ও দ্রুত অ্যাকশনগুলো এখানে দেখুন।</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <span class="badge bg-light text-dark">তারিখ: <?= date('d M Y') ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card p-3">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted mb-1">আজকের সেলস</p>
                    <h4 class="fw-bold">৳ 12,400</h4>
                </div>
                <i class="bi bi-receipt fs-3 text-primary"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card p-3">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted mb-1">স্টক এলার্ট</p>
                    <h4 class="fw-bold">8 আইটেম</h4>
                </div>
                <i class="bi bi-box-seam fs-3 text-warning"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card p-3">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted mb-1">বকেয়া</p>
                    <h4 class="fw-bold">৳ 32,100</h4>
                </div>
                <i class="bi bi-wallet2 fs-3 text-danger"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card p-3">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted mb-1">খরচ</p>
                    <h4 class="fw-bold">৳ 5,250</h4>
                </div>
                <i class="bi bi-journal-minus fs-3 text-success"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="section-title">চলতি সেলস</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>মেমো নং</th>
                            <th>কাস্টমার</th>
                            <th>মোট</th>
                            <th>স্ট্যাটাস</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#MV-1204</td>
                            <td>রহিম এন্টারপ্রাইজ</td>
                            <td>৳ 3,200</td>
                            <td><span class="badge bg-success">পেইড</span></td>
                        </tr>
                        <tr>
                            <td>#MV-1205</td>
                            <td>সাথী ফিড</td>
                            <td>৳ 4,850</td>
                            <td><span class="badge bg-warning text-dark">আংশিক</span></td>
                        </tr>
                        <tr>
                            <td>#MV-1206</td>
                            <td>কৃষ্ণা ভেট ক্লিনিক</td>
                            <td>৳ 2,750</td>
                            <td><span class="badge bg-danger">বকেয়া</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4 h-100">
            <h5 class="section-title">দ্রুত কাজ</h5>
            <div class="list-group">
                <a class="list-group-item list-group-item-action" href="sales.php"><i class="bi bi-plus-circle me-2"></i>নতুন সেলস যোগ করুন</a>
                <a class="list-group-item list-group-item-action" href="inventory.php"><i class="bi bi-box-arrow-in-down me-2"></i>স্টক আপডেট করুন</a>
                <a class="list-group-item list-group-item-action" href="expenses.php"><i class="bi bi-journal-minus me-2"></i>খরচ লিপিবদ্ধ করুন</a>
                <a class="list-group-item list-group-item-action" href="reports.php"><i class="bi bi-clipboard-data me-2"></i>রিপোর্ট দেখুন</a>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
