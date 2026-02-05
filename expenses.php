<?php
$pageTitle = 'খরচ ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h5 class="section-title">খরচ যোগ করুন</h5>
            <form class="vstack gap-3">
                <input class="form-control" type="text" placeholder="খরচের ধরন">
                <input class="form-control" type="number" placeholder="পরিমাণ">
                <input class="form-control" type="date">
                <button class="btn btn-primary" type="button">সেভ করুন</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="section-title">সাম্প্রতিক খরচ</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>খাত</th>
                        <th>পরিমাণ</th>
                        <th>তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>ইউটিলিটি বিল</td>
                        <td>৳ 1,500</td>
                        <td>12/02/2024</td>
                    </tr>
                    <tr>
                        <td>ডেলিভারি চার্জ</td>
                        <td>৳ 750</td>
                        <td>12/02/2024</td>
                    </tr>
                    <tr>
                        <td>স্টাফ চা নাস্তা</td>
                        <td>৳ 400</td>
                        <td>11/02/2024</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
