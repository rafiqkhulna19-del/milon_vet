<?php
$pageTitle = 'ডিউ ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$dues = fetch_all('SELECT s.memo_no, s.total, s.paid, s.created_at, c.name AS customer
    FROM sales s
    LEFT JOIN customers c ON c.id = s.customer_id
    WHERE s.total > s.paid
    ORDER BY s.created_at DESC');
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h5 class="section-title">বকেয়া তালিকা</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>কাস্টমার</th>
                            <th>মেমো</th>
                            <th>পরিমাণ</th>
                            <th>তারিখ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dues)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">কোনো বকেয়া নেই।</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dues as $due): ?>
                                <?php $amount = (float) $due['total'] - (float) $due['paid']; ?>
                                <tr>
                                    <td><?= htmlspecialchars($due['customer'] ?? 'ওয়াক-ইন') ?></td>
                                    <td><?= htmlspecialchars($due['memo_no']) ?></td>
                                    <td><?= format_currency($currency, $amount) ?></td>
                                    <td><?= date('d/m/Y', strtotime($due['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h5 class="section-title">ডিউ সংগ্রহ</h5>
            <form class="vstack gap-3">
                <input class="form-control" type="text" placeholder="কাস্টমার নাম">
                <input class="form-control" type="number" placeholder="পরিমাণ">
                <button class="btn btn-primary" type="button">সংরক্ষণ করুন</button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
