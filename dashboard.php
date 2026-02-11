<?php
$pageTitle = 'ড্যাশবোর্ড';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$today = date('Y-m-d');

$salesTotalRow = fetch_one('SELECT COALESCE(SUM(total), 0) AS total FROM sales WHERE DATE(created_at) = :today', [
    ':today' => $today,
]);
$todaySales = $salesTotalRow['total'] ?? 0;

$expenseTotalRow = fetch_one('SELECT COALESCE(SUM(amount), 0) AS total FROM expenses WHERE expense_date = :today', [
    ':today' => $today,
]);
$todayExpenses = $expenseTotalRow['total'] ?? 0;

$dueRow = fetch_one('SELECT COALESCE(SUM(total - paid), 0) AS total FROM sales WHERE total > paid');
$dueTotal = $dueRow['total'] ?? 0;

$lowStockRow = fetch_one('SELECT COUNT(*) AS total FROM products WHERE stock <= 10');
$lowStockCount = $lowStockRow['total'] ?? 0;

$recentSales = fetch_all('SELECT s.memo_no, s.total, s.paid, c.name AS customer
    FROM sales s
    LEFT JOIN customers c ON c.id = s.customer_id
    ORDER BY s.created_at DESC
    LIMIT 5');
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
                    <h4 class="fw-bold"><?= format_currency($currency, $todaySales) ?></h4>
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
                    <h4 class="fw-bold"><?= (int) $lowStockCount ?> আইটেম</h4>
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
                    <h4 class="fw-bold"><?= format_currency($currency, $dueTotal) ?></h4>
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
                    <h4 class="fw-bold"><?= format_currency($currency, $todayExpenses) ?></h4>
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
                        <?php if (empty($recentSales)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">কোনো সেলস পাওয়া যায়নি।</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentSales as $sale): ?>
                                <?php
                                    $status = 'বকেয়া';
                                    $badge = 'bg-danger';
                                    if ((float) $sale['paid'] >= (float) $sale['total']) {
                                        $status = 'পেইড';
                                        $badge = 'bg-success';
                                    } elseif ((float) $sale['paid'] > 0) {
                                        $status = 'আংশিক';
                                        $badge = 'bg-warning text-dark';
                                    }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($sale['memo_no']) ?></td>
                                    <td><?= htmlspecialchars($sale['customer'] ?? 'ওয়াক-ইন') ?></td>
                                    <td><?= format_currency($currency, $sale['total']) ?></td>
                                    <td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
