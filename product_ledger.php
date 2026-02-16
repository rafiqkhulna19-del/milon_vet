<?php
$pageTitle = 'পণ্য লেজার';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';

$productId = (int) ($_GET['product_id'] ?? 0);
if (!$productId) {
    echo '<div class="alert alert-warning">পণ্য সিলেক্ট করা হয়নি।</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$product = fetch_one('SELECT id, name, purchase_price, selling_price, stock FROM products WHERE id = :id LIMIT 1', [':id' => $productId]);
if (!$product) {
    echo '<div class="alert alert-warning">পণ্য পাওয়া যায়নি।</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$entries = fetch_all('SELECT * FROM inventory_ledger WHERE product_id = :id ORDER BY txn_date DESC, id DESC', [':id' => $productId]);

$totalIn = 0;
$totalOut = 0;
foreach ($entries as $e) {
    if ($e['entry_type'] === 'purchase' || $e['entry_type'] === 'initial') {
        $totalIn += (int) $e['quantity'];
    } elseif ($e['entry_type'] === 'sale') {
        $totalOut += (int) $e['quantity'];
    }
}
$currentStock = max($totalIn - $totalOut, 0);

?>
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="section-title">পণ্য লেজার</h5>
        <a href="inventory.php" class="btn btn-outline-secondary">পণ্য তালিকায় ফিরুন</a>
    </div>
    <div class="mb-3">
        <strong><?= htmlspecialchars($product['name']) ?></strong><br>
        <small class="text-muted">ক্রয় মূল্য: <?= format_currency($currency, $product['purchase_price']) ?> | বিক্রয় মূল্য: <?= format_currency($currency, $product['selling_price']) ?></small>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card p-3">
                <div class="small text-muted">মোট ইনবাউন্ড</div>
                <div class="h5 m-0"><?= $totalIn ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="small text-muted">মোট আউটবাউন্ড</div>
                <div class="h5 m-0"><?= $totalOut ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="small text-muted">বর্তমান স্টক (লেজার)</div>
                <div class="h5 m-0"><?= $currentStock ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="small text-muted">ডাটাবেস স্টক</div>
                <div class="h5 m-0"><?= (int) $product['stock'] ?></div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>তারিখ</th>
                    <th>টাইপ</th>
                    <th>রেফারেন্স</th>
                    <th>নোট</th>
                    <th class="text-end">পরিমাণ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entries)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">কোনো লেনদেন নেই।</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entries as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['txn_date']) ?></td>
                            <td>
                                <?php
                                    $typeLabel = ucfirst($row['entry_type']);
                                    $badge = 'bg-secondary';
                                    if ($row['entry_type'] === 'purchase') $badge = 'bg-info';
                                    elseif ($row['entry_type'] === 'sale') $badge = 'bg-warning text-dark';
                                    elseif ($row['entry_type'] === 'initial') $badge = 'bg-success';
                                ?>
                                <span class="badge <?= $badge ?>"><?= htmlspecialchars($typeLabel) ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['reference'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['note'] ?? '') ?></td>
                            <td class="text-end fw-semibold"><?= (int) $row['quantity'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            </table>
        </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>