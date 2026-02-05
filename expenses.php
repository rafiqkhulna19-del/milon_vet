<?php
$pageTitle = 'খরচ ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$expenses = fetch_all('SELECT category, amount, expense_date FROM expenses ORDER BY expense_date DESC LIMIT 10');
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h5 class="section-title">খরচ যোগ করুন</h5>
            <form class="vstack gap-3">
                <input class="form-control" type="text" placeholder="খরচের ধরন">
                <input class="form-control" type="number" placeholder="পরিমাণ">
                <input class="form-control" type="date">
                <button class="btn btn-primary" type="button">সেভ করুন</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="section-title">সাম্প্রতিক খরচ</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>খাত</th>
                        <th>পরিমাণ</th>
                        <th>তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">কোনো খরচ নেই।</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?= htmlspecialchars($expense['category']) ?></td>
                                <td><?= format_currency($currency, $expense['amount']) ?></td>
                                <td><?= date('d/m/Y', strtotime($expense['expense_date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
