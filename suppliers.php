<?php
$pageTitle = 'সাপ্লায়ার ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">সাপ্লায়ার তালিকা</h5>
        <button class="btn btn-primary" type="button">নতুন সাপ্লায়ার</button>
    </div>
    <div class="table-responsive mt-3">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>নাম</th>
                    <th>মোবাইল</th>
                    <th>শেষ ক্রয়</th>
                    <th>বকেয়া</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Vet Pharma Ltd.</td>
                    <td>+88016XXXXXXX</td>
                    <td>10/02/2024</td>
                    <td>৳ 12,000</td>
                </tr>
                <tr>
                    <td>Green Feed</td>
                    <td>+88015XXXXXXX</td>
                    <td>08/02/2024</td>
                    <td>৳ 7,500</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
