<?php
$pageTitle = 'নতুন মেমো';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';

$customers = fetch_all('SELECT id, name FROM customers ORDER BY name ASC');
$products = fetch_all('SELECT id, name, selling_price, stock FROM products ORDER BY name ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = (int) ($_POST['customer_id'] ?? 0);
    $memoNo = trim($_POST['memo_no'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $paid = (float) ($_POST['paid'] ?? 0);

    if ($memoNo !== '' && $productId > 0 && $quantity > 0 && $price > 0) {
        $total = $quantity * $price;
        [$pdo] = db_connection();
        if ($pdo) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('INSERT INTO sales (memo_no, customer_id, total, paid, payment_method) VALUES (:memo_no, :customer_id, :total, :paid, :payment_method)');
                $stmt->execute([
                    ':memo_no' => $memoNo,
                    ':customer_id' => $customerId ?: null,
                    ':total' => $total,
                    ':paid' => $paid,
                    ':payment_method' => $paymentMethod,
                ]);
                $saleId = (int) $pdo->lastInsertId();

                $itemStmt = $pdo->prepare('INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (:sale_id, :product_id, :quantity, :price)');
                $itemStmt->execute([
                    ':sale_id' => $saleId,
                    ':product_id' => $productId,
                    ':quantity' => $quantity,
                    ':price' => $price,
                ]);

                $stockStmt = $pdo->prepare('UPDATE products SET stock = GREATEST(stock - :quantity, 0) WHERE id = :id');
                $stockStmt->execute([
                    ':quantity' => $quantity,
                    ':id' => $productId,
                ]);

                $pdo->commit();
                $message = 'মেমো সংরক্ষণ হয়েছে।';
            } catch (Throwable $error) {
                $pdo->rollBack();
                $message = 'মেমো সংরক্ষণ ব্যর্থ হয়েছে।';
            }
        }
    } else {
        $message = 'সব তথ্য ঠিকভাবে পূরণ করুন।';
    }
}
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h5 class="section-title">নতুন মেমো</h5>
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
                <div class="col-md-6">
                    <label class="form-label">পণ্য</label>
                    <select class="form-select" name="product_id" required>
                        <option value="">পণ্য নির্বাচন করুন</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= (int) $product['id'] ?>">
                                <?= htmlspecialchars($product['name']) ?> (স্টক: <?= (int) $product['stock'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">পরিমাণ</label>
                    <input class="form-control" type="number" name="quantity" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">দাম</label>
                    <input class="form-control" type="number" step="0.01" name="price" required>
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
                    <label class="form-label">পরিশোধ</label>
                    <input class="form-control" type="number" step="0.01" name="paid" placeholder="<?= $currency ?>">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">মেমো সংরক্ষণ</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="section-title">নির্দেশনা</h6>
            <ul class="small text-muted mb-0">
                <li>একটি মেমোর জন্য অন্তত একটি পণ্য নির্বাচন করুন।</li>
                <li>স্টক কমে গেলে ইনভেন্টরি থেকে আপডেট করুন।</li>
                <li>অংশ পরিশোধ করলে ডিউ রিপোর্টে দেখা যাবে।</li>
            </ul>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
