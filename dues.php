<?php
$pageTitle = 'ডিউ ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['collect_due'])) {
    $saleId = (int) ($_POST['sale_id'] ?? 0);
    $amount = (float) ($_POST['amount'] ?? 0);
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');

    if ($saleId > 0 && $amount > 0) {
        [$pdo] = db_connection();
        if ($pdo) {
            $sale = fetch_one('SELECT id, paid, total, customer_id FROM sales WHERE id = :id', [':id' => $saleId]);
            if ($sale) {
                $newPaid = min((float) $sale['paid'] + $amount, (float) $sale['total']);
                $pdo->prepare('UPDATE sales SET paid = :paid WHERE id = :id')->execute([
                    ':paid' => $newPaid,
                    ':id' => $saleId,
                ]);
                if (!empty($sale['customer_id'])) {
                    $pdo->prepare('UPDATE customers SET due_balance = GREATEST(due_balance - :amount, 0) WHERE id = :id')->execute([
                        ':amount' => $amount,
                        ':id' => $sale['customer_id'],
                    ]);
                }
                $pdo->prepare('INSERT INTO incomes (source, amount, income_date, note) VALUES (:source, :amount, :income_date, :note)')->execute([
                    ':source' => 'ডিউ পেমেন্ট',
                    ':amount' => $amount,
                    ':income_date' => $paymentDate,
                    ':note' => 'কাস্টমার বকেয়া পরিশোধ',
                ]);
                $message = 'ডিউ পরিশোধ আপডেট হয়েছে।';
            }
        }
    } else {
        $message = 'সঠিক তথ্য দিন।';
    }
}

$dues = fetch_all('SELECT s.id, s.memo_no, s.total, s.paid, s.created_at, c.name AS customer
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
                            <th>বকেয়া</th>
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
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form class="vstack gap-3" method="post">
                <input type="hidden" name="collect_due" value="1">
                <select class="form-select" name="sale_id" required>
                    <option value="">মেমো নির্বাচন করুন</option>
                    <?php foreach ($dues as $due): ?>
                        <?php $amount = (float) $due['total'] - (float) $due['paid']; ?>
                        <option value="<?= (int) $due['id'] ?>">
                            <?= htmlspecialchars($due['memo_no']) ?> - <?= htmlspecialchars($due['customer'] ?? 'ওয়াক-ইন') ?> (<?= format_currency($currency, $amount) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input class="form-control" type="number" step="0.01" name="amount" placeholder="পরিমাণ" required>
                <input class="form-control" type="date" name="payment_date" value="<?= date('Y-m-d') ?>">
                <button class="btn btn-primary" type="submit">সংরক্ষণ করুন</button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
