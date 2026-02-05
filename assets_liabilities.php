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
$netWorth = $assetTotal - $liabilityTotal;
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
            <h5 class="section-title">দায়</h5>
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
