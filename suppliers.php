<?php
$pageTitle = 'সাপ্লায়ার ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$suppliers = fetch_all('SELECT name, phone, address, balance FROM suppliers ORDER BY id DESC');
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">সাপ্লায়ার তালিকা</h5>
        <button class="btn btn-primary" type="button">নতুন সাপ্লায়ার</button>
    </div>
    <div class="table-responsive mt-3">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>নাম</th>
                    <th>মোবাইল</th>
                    <th>ঠিকানা</th>
                    <th>বকেয়া</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">কোনো সাপ্লায়ার নেই।</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><?= htmlspecialchars($supplier['name']) ?></td>
                            <td><?= htmlspecialchars($supplier['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($supplier['address'] ?? '') ?></td>
                            <td><?= format_currency($currency, $supplier['balance'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
