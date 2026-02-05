<?php
$pageTitle = 'দায়-পরিসম্পদ';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">পরিসম্পদ</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>বিবরণ</th>
                        <th>মূল্য</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>ইনভেন্টরি স্টক</td>
                        <td>৳ 120,000</td>
                    </tr>
                    <tr>
                        <td>ক্যাশ ইন হ্যান্ড</td>
                        <td>৳ 25,000</td>
                    </tr>
                    <tr>
                        <td>ব্যাংক ব্যালেন্স</td>
                        <td>৳ 80,000</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">দায়</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>বিবরণ</th>
                        <th>মূল্য</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>সাপ্লায়ার বকেয়া</td>
                        <td>৳ 45,000</td>
                    </tr>
                    <tr>
                        <td>স্টাফ বেতন</td>
                        <td>৳ 15,000</td>
                    </tr>
                    <tr>
                        <td>ইউটিলিটি বিল</td>
                        <td>৳ 6,500</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="card p-4 mt-4">
    <h5 class="section-title">নেট ওয়ার্থ</h5>
    <div class="d-flex align-items-center justify-content-between">
        <span class="fs-5">পরিসম্পদ - দায়</span>
        <span class="fs-4 fw-bold text-success">৳ 158,500</span>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
