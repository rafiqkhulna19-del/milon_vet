<?php
$pageTitle = 'লেনদেন লেজার';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$type = $_GET['type'] ?? 'sales';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$params = [];
$dateFilter = '';
if ($startDate !== '' && $endDate !== '') {
    $dateFilter = 'AND record_date BETWEEN :start_date AND :end_date';
    $params[':start_date'] = $startDate;
    $params[':end_date'] = $endDate;
}

$rows = [];
if ($type === 'purchases') {
    $sql = "SELECT purchase_date AS record_date, s.name AS party, total_amount, paid_amount, due_amount
        FROM purchases p
        LEFT JOIN suppliers s ON s.id = p.supplier_id
        WHERE 1=1 $dateFilter
        ORDER BY purchase_date DESC";
    $rows = fetch_all($sql, $params);
} else {
    $sql = "SELECT created_at AS record_date, c.name AS party, total, paid, (total - paid) AS due_amount
        FROM sales s
        LEFT JOIN customers c ON c.id = s.customer_id
        WHERE 1=1 $dateFilter
        ORDER BY created_at DESC";
    $rows = fetch_all($sql, $params);
}
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">ক্রয়/বিক্রয় লেজার</h5>
    </div>
    <form class="row g-3 mt-2" method="get">
        <div class="col-md-3">
            <label class="form-label">ধরন</label>
            <select class="form-select" name="type">
                <option value="sales" <?= $type === 'sales' ? 'selected' : '' ?>>বিক্রয়</option>
                <option value="purchases" <?= $type === 'purchases' ? 'selected' : '' ?>>ক্রয়</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">শুরুর তারিখ</label>
            <input class="form-control" type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">শেষ তারিখ</label>
            <input class="form-control" type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">ফিল্টার</button>
        </div>
    </form>

    <div class="table-responsive mt-4">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>তারিখ</th>
                    <th><?= $type === 'purchases' ? 'সাপ্লায়ার' : 'কাস্টমার' ?></th>
                    <th>মোট</th>
                    <th>পরিশোধ</th>
                    <th>বকেয়া</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">কোনো লেনদেন নেই।</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($row['record_date'])) ?></td>
                            <td><?= htmlspecialchars($row['party'] ?? 'ওয়াক-ইন') ?></td>
                            <td><?= format_currency($currency, $row['total_amount'] ?? $row['total']) ?></td>
                            <td><?= format_currency($currency, $row['paid_amount'] ?? $row['paid']) ?></td>
                            <td><?= format_currency($currency, $row['due_amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
