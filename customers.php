<?php
$pageTitle = 'কাস্টমার ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">কাস্টমার তালিকা</h5>
        <button class="btn btn-primary" type="button">নতুন কাস্টমার</button>
    </div>
    <div class="table-responsive mt-3">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>নাম</th>
                    <th>মোবাইল</th>
                    <th>এলাকা</th>
                    <th>ডিউ</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>রহিম এন্টারপ্রাইজ</td>
                    <td>+88017XXXXXXX</td>
                    <td>সাভার</td>
                    <td>৳ 8,200</td>
                </tr>
                <tr>
                    <td>সাথী ফিড</td>
                    <td>+88019XXXXXXX</td>
                    <td>মানিকগঞ্জ</td>
                    <td>৳ 4,500</td>
                </tr>
                <tr>
                    <td>কৃষ্ণা ভেট ক্লিনিক</td>
                    <td>+88018XXXXXXX</td>
                    <td>গাজীপুর</td>
                    <td>৳ 6,300</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
