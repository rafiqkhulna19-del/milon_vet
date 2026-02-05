<?php
$pageTitle = 'কাস্টমার ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$customers = fetch_all('SELECT name, phone, address, due_balance FROM customers ORDER BY id DESC');
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">কাস্টমার তালিকা</h5>
        <button class="btn btn-primary" type="button">নতুন কাস্টমার</button>
    </div>
    <div class="table-responsive mt-3">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>নাম</th>
                    <th>মোবাইল</th>
                    <th>এলাকা</th>
                    <th>ডিউ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">কোনো কাস্টমার নেই।</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?= htmlspecialchars($customer['name']) ?></td>
                            <td><?= htmlspecialchars($customer['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($customer['address'] ?? '') ?></td>
                            <td><?= format_currency($currency, $customer['due_balance'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
