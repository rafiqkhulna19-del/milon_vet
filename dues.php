<?php
$pageTitle = 'ডিউ ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h5 class="section-title">বকেয়া তালিকা</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>কাস্টমার</th>
                            <th>মেমো</th>
                            <th>পরিমাণ</th>
                            <th>শেষ তারিখ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>সাথী ফিড</td>
                            <td>#MV-1205</td>
                            <td>৳ 2,450</td>
                            <td>15/02/2024</td>
                        </tr>
                        <tr>
                            <td>কৃষ্ণা ভেট ক্লিনিক</td>
                            <td>#MV-1206</td>
                            <td>৳ 2,750</td>
                            <td>20/02/2024</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h5 class="section-title">ডিউ সংগ্রহ</h5>
            <form class="vstack gap-3">
                <input class="form-control" type="text" placeholder="কাস্টমার নাম">
                <input class="form-control" type="number" placeholder="পরিমাণ">
                <button class="btn btn-primary" type="button">সংরক্ষণ করুন</button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
