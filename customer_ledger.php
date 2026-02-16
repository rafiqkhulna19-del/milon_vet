<?php
$pageTitle = 'কাস্টমার লেজার';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';

$customerId = (int) ($_GET['customer_id'] ?? 0);
if (!$customerId) {
    echo '<div class="alert alert-warning">কাস্টমার সিলেক্ট করা হয়নি।</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$customer = fetch_one('SELECT id, name, phone, address FROM customers WHERE id = :id LIMIT 1', [':id' => $customerId]);
if (!$customer) {
    echo '<div class="alert alert-warning">কাস্টমার পাওয়া যায়নি।</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$entries = fetch_all('SELECT * FROM customer_ledger WHERE customer_id = :id ORDER BY txn_date DESC, id DESC', [':id' => $customerId]);

$totalSales = 0.0;
$totalPayments = 0.0;
foreach ($entries as $e) {
    if ($e['entry_type'] === 'sale') {
        $totalSales += (float) $e['amount'];
    } elseif ($e['entry_type'] === 'payment') {
        $totalPayments += (float) $e['amount'];
    }
}

?>
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="section-title">কাস্টমার লেজার</h5>
        <a href="customers.php" class="btn btn-outline-secondary">Back to Customers</a>
    </div>
    <div class="mb-3">
        <strong><?= htmlspecialchars($customer['name']) ?></strong><br>
        <small class="text-muted"><?= htmlspecialchars($customer['phone'] ?? '') ?> <?= $customer['address'] ? ' • ' . htmlspecialchars($customer['address']) : '' ?></small>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card p-3">
                <div class="small text-muted">মোট সেল</div>
                <div class="h5 m-0"><?= format_currency($currency, $totalSales) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div class="small text-muted">মোট পেমেন্ট</div>
                <div class="h5 m-0"><?= format_currency($currency, $totalPayments) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div class="small text-muted">বাকি</div>
                    <div class="h5 m-0"><?= format_currency($currency, $totalSales - $totalPayments) ?></div>
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
                    <th class="text-end">টাকার পরিমাণ</th>
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
                            <td><?= htmlspecialchars(ucfirst($row['entry_type'])) ?></td>
                            <td><?= htmlspecialchars($row['reference'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['note'] ?? '') ?></td>
                            <td class="text-end"><?= format_currency($currency, $row['amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
