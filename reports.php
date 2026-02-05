<?php
$pageTitle = 'রিপোর্ট';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="section-title">বিক্রয় রিপোর্ট</h6>
            <p class="text-muted">তারিখ ফিল্টার দিয়ে রিপোর্ট বের করুন।</p>
            <form class="vstack gap-3">
                <input type="date" class="form-control">
                <input type="date" class="form-control">
                <button class="btn btn-primary" type="button">রিপোর্ট দেখুন</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h5 class="section-title">মাসিক পারফরম্যান্স</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>মাস</th>
                            <th>বিক্রয়</th>
                            <th>খরচ</th>
                            <th>লাভ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>জানুয়ারি</td>
                            <td>৳ 210,000</td>
                            <td>৳ 140,000</td>
                            <td>৳ 70,000</td>
                        </tr>
                        <tr>
                            <td>ফেব্রুয়ারি</td>
                            <td>৳ 198,000</td>
                            <td>৳ 132,500</td>
                            <td>৳ 65,500</td>
                        </tr>
                        <tr>
                            <td>মার্চ</td>
                            <td>৳ 235,000</td>
                            <td>৳ 150,000</td>
                            <td>৳ 85,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
