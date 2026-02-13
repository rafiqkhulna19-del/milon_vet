<?php
$pageTitle = 'দায়-পরিসম্পদ';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';

$assets = fetch_all('SELECT name, amount FROM assets ORDER BY id DESC');
$liabilities = fetch_all('SELECT name, amount FROM liabilities ORDER BY id DESC');
$assetTotalRow = fetch_one('SELECT COALESCE(SUM(amount), 0) AS total FROM assets');
$liabilityTotalRow = fetch_one('SELECT COALESCE(SUM(amount), 0) AS total FROM liabilities');
$assetTotal = $assetTotalRow['total'] ?? 0;
$liabilityTotal = $liabilityTotalRow['total'] ?? 0;

$stockValueRow = fetch_one('SELECT COALESCE(SUM(stock * purchase_price), 0) AS total FROM products');
$stockValue = $stockValueRow['total'] ?? 0;
$incomeRow = fetch_one('SELECT COALESCE(SUM(amount), 0) AS total FROM incomes');
$expenseRow = fetch_one('SELECT COALESCE(SUM(amount), 0) AS total FROM expenses');
$salesPaidRow = fetch_one('SELECT COALESCE(SUM(paid), 0) AS total FROM sales');
$purchasePaidRow = fetch_one('SELECT COALESCE(SUM(paid_amount), 0) AS total FROM purchases');

$autoCash = ($incomeRow['total'] ?? 0) + ($salesPaidRow['total'] ?? 0) - ($expenseRow['total'] ?? 0) - ($purchasePaidRow['total'] ?? 0);
$autoAssets = $stockValue + $autoCash;
$autoLiabilitiesRow = fetch_one('SELECT COALESCE(SUM(balance), 0) AS total FROM suppliers');
$autoLiabilities = $autoLiabilitiesRow['total'] ?? 0;

$netWorth = ($assetTotal + $autoAssets) - ($liabilityTotal + $autoLiabilities);
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">অটোমেটিক পরিসম্পদ</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>স্টক ভ্যালু</span>
                    <span><?= format_currency($currency, $stockValue) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>ক্যাশ ফ্লো</span>
                    <span><?= format_currency($currency, $autoCash) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between fw-bold">
                    <span>মোট</span>
                    <span><?= format_currency($currency, $autoAssets) ?></span>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">অটোমেটিক দায়</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>সাপ্লায়ার বকেয়া</span>
                    <span><?= format_currency($currency, $autoLiabilities) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between fw-bold">
                    <span>মোট</span>
                    <span><?= format_currency($currency, $autoLiabilities) ?></span>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">ম্যানুয়াল পরিসম্পদ</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>বিবরণ</th>
                        <th>মূল্য</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr>
                            <td colspan="2" class="text-center text-muted">কোনো পরিসম্পদ নেই।</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assets as $asset): ?>
                            <tr>
                                <td><?= htmlspecialchars($asset['name']) ?></td>
                                <td><?= format_currency($currency, $asset['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">ম্যানুয়াল দায়</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>বিবরণ</th>
                        <th>মূল্য</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($liabilities)): ?>
                        <tr>
                            <td colspan="2" class="text-center text-muted">কোনো দায় নেই।</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($liabilities as $liability): ?>
                            <tr>
                                <td><?= htmlspecialchars($liability['name']) ?></td>
                                <td><?= format_currency($currency, $liability['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card p-4 mt-4">
    <h5 class="section-title">নেট ওয়ার্থ</h5>
    <div class="d-flex align-items-center justify-content-between">
        <span class="fs-5">পরিসম্পদ - দায়</span>
        <span class="fs-4 fw-bold text-success"><?= format_currency($currency, $netWorth) ?></span>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
