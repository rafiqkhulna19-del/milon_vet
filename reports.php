<?php
$pageTitle = 'রিপোর্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$monthly = fetch_all('SELECT DATE_FORMAT(s.created_at, "%Y-%m") AS month,
    COALESCE(SUM(s.total), 0) AS sales_total,
    COALESCE((SELECT SUM(e.amount) FROM expenses e WHERE DATE_FORMAT(e.expense_date, "%Y-%m") = DATE_FORMAT(s.created_at, "%Y-%m")), 0) AS expense_total
    FROM sales s
    GROUP BY DATE_FORMAT(s.created_at, "%Y-%m")
    ORDER BY month DESC
    LIMIT 6');
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
                        <?php if (empty($monthly)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">কোনো রিপোর্ট ডাটা নেই।</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($monthly as $row): ?>
                                <?php
                                    $profit = (float) $row['sales_total'] - (float) $row['expense_total'];
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['month']) ?></td>
                                    <td><?= format_currency($currency, $row['sales_total']) ?></td>
                                    <td><?= format_currency($currency, $row['expense_total']) ?></td>
                                    <td><?= format_currency($currency, $profit) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
