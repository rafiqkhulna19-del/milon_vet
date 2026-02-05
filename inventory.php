<?php
$pageTitle = 'ইনভেন্টরি';
require __DIR__ . '/includes/header.php';
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">স্টক তালিকা</h5>
        <button class="btn btn-primary" type="button">নতুন পণ্য যোগ করুন</button>
    </div>
    <div class="table-responsive mt-3">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>পণ্য</th>
                    <th>ক্যাটেগরি</th>
                    <th>স্টক</th>
                    <th>ক্রয় মূল্য</th>
                    <th>বিক্রয় মূল্য</th>
                    <th>স্ট্যাটাস</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ভিটামিন ফিড প্রিমিক্স</td>
                    <td>ফিড</td>
                    <td>120 ব্যাগ</td>
                    <td>৳ 1,200</td>
                    <td>৳ 1,450</td>
                    <td><span class="badge bg-success">ইন স্টক</span></td>
                </tr>
                <tr>
                    <td>এন্টি বায়োটিক ভেট</td>
                    <td>মেডিসিন</td>
                    <td>25 বোতল</td>
                    <td>৳ 350</td>
                    <td>৳ 450</td>
                    <td><span class="badge bg-warning text-dark">লো স্টক</span></td>
                </tr>
                <tr>
                    <td>ক্যালসিয়াম সাপ্লিমেন্ট</td>
                    <td>সাপ্লিমেন্ট</td>
                    <td>8 প্যাক</td>
                    <td>৳ 220</td>
                    <td>৳ 300</td>
                    <td><span class="badge bg-danger">রিস্টক</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
