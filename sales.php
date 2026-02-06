<?php
$pageTitle = 'সেলস ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';

$customers = fetch_all('SELECT id, name FROM customers ORDER BY name ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = (int) ($_POST['customer_id'] ?? 0);
    $memoNo = trim($_POST['memo_no'] ?? '');
    $total = (float) ($_POST['total'] ?? 0);
    $paid = (float) ($_POST['paid'] ?? 0);
    $paymentMethod = trim($_POST['payment_method'] ?? '');

    if ($memoNo !== '' && $total > 0) {
        [$pdo] = db_connection();
        if ($pdo) {
            $stmt = $pdo->prepare('INSERT INTO sales (memo_no, customer_id, total, paid, payment_method) VALUES (:memo_no, :customer_id, :total, :paid, :payment_method)');
            $stmt->execute([
                ':memo_no' => $memoNo,
                ':customer_id' => $customerId ?: null,
                ':total' => $total,
                ':paid' => $paid,
                ':payment_method' => $paymentMethod,
            ]);
            $message = 'নতুন সেলস সংরক্ষণ হয়েছে।';
        }
    } else {
        $message = 'মেমো নম্বর ও মোট পরিমাণ দিন।';
    }
}

$sales = fetch_all('SELECT s.memo_no, s.total, c.name AS customer
    FROM sales s
    LEFT JOIN customers c ON c.id = s.customer_id
    ORDER BY s.created_at DESC
    LIMIT 8');
?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="section-title">সেলস এন্ট্রি</h5>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form class="row g-3" method="post">
                <div class="col-md-6">
                    <label class="form-label">কাস্টমার</label>
                    <select class="form-select" name="customer_id">
                        <option value="">ওয়াক-ইন</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= (int) $customer['id'] ?>"><?= htmlspecialchars($customer['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">মেমো নং</label>
                    <input class="form-control" type="text" name="memo_no" placeholder="#MV-0001" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">পেমেন্ট পদ্ধতি</label>
                    <select class="form-select" name="payment_method">
                        <option value="ক্যাশ">ক্যাশ</option>
                        <option value="ব্যাংক">ব্যাংক</option>
                        <option value="মোবাইল ব্যাংকিং">মোবাইল ব্যাংকিং</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">মোট পরিমাণ</label>
                    <input class="form-control" type="number" step="0.01" name="total" placeholder="<?= $currency ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">পরিশোধ</label>
                    <input class="form-control" type="number" step="0.01" name="paid" placeholder="<?= $currency ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">নোট</label>
                    <textarea class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">সেলস সংরক্ষণ</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4">
            <h5 class="section-title">সাম্প্রতিক সেলস</h5>
            <ul class="list-group list-group-flush">
                <?php if (empty($sales)): ?>
                    <li class="list-group-item text-center text-muted">কোনো সেলস নেই।</li>
                <?php else: ?>
                    <?php foreach ($sales as $sale): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= htmlspecialchars($sale['memo_no']) ?></span>
                            <span><?= format_currency($currency, $sale['total']) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
