<?php
$pageTitle = 'পণ্য ক্রয়';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';
$paymentMessage = '';

$products = fetch_all('SELECT id, name FROM products ORDER BY name ASC');
$suppliers = fetch_all('SELECT id, name, balance FROM suppliers ORDER BY name ASC');
$expenseCategories = fetch_all('SELECT id, name FROM expense_categories ORDER BY name ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_submit'])) {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $supplierId = (int) ($_POST['supplier_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $unitCost = (float) ($_POST['unit_cost'] ?? 0);
    $paymentType = $_POST['payment_type'] ?? 'cash';
    $paidAmount = (float) ($_POST['paid_amount'] ?? 0);
    $purchaseDate = $_POST['purchase_date'] ?? date('Y-m-d');

    if ($productId > 0 && $quantity > 0 && $unitCost > 0) {
        $totalAmount = $quantity * $unitCost;
        if ($paymentType === 'cash') {
            $paidAmount = $totalAmount;
        } elseif ($paymentType === 'credit') {
            $paidAmount = 0;
        } else {
            $paidAmount = min($paidAmount, $totalAmount);
        }
        $dueAmount = $totalAmount - $paidAmount;

        [$pdo] = db_connection();
        if ($pdo) {
            $stmt = $pdo->prepare('INSERT INTO purchases (product_id, supplier_id, quantity, unit_cost, total_amount, paid_amount, due_amount, payment_type, purchase_date) VALUES (:product_id, :supplier_id, :quantity, :unit_cost, :total_amount, :paid_amount, :due_amount, :payment_type, :purchase_date)');
            $stmt->execute([
                ':product_id' => $productId,
                ':supplier_id' => $supplierId ?: null,
                ':quantity' => $quantity,
                ':unit_cost' => $unitCost,
                ':total_amount' => $totalAmount,
                ':paid_amount' => $paidAmount,
                ':due_amount' => $dueAmount,
                ':payment_type' => $paymentType,
                ':purchase_date' => $purchaseDate,
            ]);

            $update = $pdo->prepare('UPDATE products SET stock = stock + :quantity, purchase_price = :unit_cost WHERE id = :id');
            $update->execute([
                ':quantity' => $quantity,
                ':unit_cost' => $unitCost,
                ':id' => $productId,
            ]);

            if ($supplierId > 0 && $dueAmount > 0) {
                $pdo->prepare('UPDATE suppliers SET balance = balance + :due WHERE id = :id')->execute([
                    ':due' => $dueAmount,
                    ':id' => $supplierId,
                ]);
            }

            $message = 'ক্রয় তথ্য সংরক্ষণ হয়েছে।';
        }
    } else {
        $message = 'সব তথ্য ঠিকভাবে পূরণ করুন।';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_submit'])) {
    $supplierId = (int) ($_POST['payment_supplier_id'] ?? 0);
    $amount = (float) ($_POST['payment_amount'] ?? 0);
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
    $categoryId = (int) ($_POST['expense_category_id'] ?? 0);

    if ($supplierId > 0 && $amount > 0 && $categoryId > 0) {
        [$pdo] = db_connection();
        if ($pdo) {
            $stmt = $pdo->prepare('INSERT INTO expenses (expense_category_id, amount, expense_date, note) VALUES (:category_id, :amount, :expense_date, :note)');
            $stmt->execute([
                ':category_id' => $categoryId,
                ':amount' => $amount,
                ':expense_date' => $paymentDate,
                ':note' => 'সাপ্লায়ার বকেয়া পরিশোধ',
            ]);

            $pdo->prepare('UPDATE suppliers SET balance = GREATEST(balance - :amount, 0) WHERE id = :id')->execute([
                ':amount' => $amount,
                ':id' => $supplierId,
            ]);

            $paymentMessage = 'বকেয়া পেমেন্ট সংরক্ষণ হয়েছে।';
        }
    } else {
        $paymentMessage = 'সাপ্লায়ার, খাত এবং পরিমাণ দিন।';
    }
}

$purchases = fetch_all('SELECT pch.quantity, pch.unit_cost, pch.total_amount, pch.paid_amount, pch.due_amount, pch.payment_type, pch.purchase_date, pr.name AS product, s.name AS supplier
    FROM purchases pch
    LEFT JOIN products pr ON pr.id = pch.product_id
    LEFT JOIN suppliers s ON s.id = pch.supplier_id
    ORDER BY pch.purchase_date DESC, pch.id DESC
    LIMIT 10');
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h5 class="section-title">নতুন ক্রয় যুক্ত করুন</h5>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form class="vstack gap-3" method="post">
                <input type="hidden" name="purchase_submit" value="1">
                <div>
                    <label class="form-label">পণ্য</label>
                    <select class="form-select" name="product_id" required>
                        <option value="">পণ্য নির্বাচন করুন</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= (int) $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">সাপ্লায়ার</label>
                    <select class="form-select" name="supplier_id">
                        <option value="">সাপ্লায়ার নির্বাচন করুন</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">পরিমাণ</label>
                        <input class="form-control" type="number" name="quantity" min="1" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">ক্রয় মূল্য</label>
                        <input class="form-control" type="number" step="0.01" name="unit_cost" min="0.01" required>
                    </div>
                </div>
                <div>
                    <label class="form-label">পেমেন্ট ধরন</label>
                    <select class="form-select" name="payment_type">
                        <option value="cash">নগদ</option>
                        <option value="partial">আংশিক বাকিতে</option>
                        <option value="credit">বাকিতে</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">পরিশোধ</label>
                    <input class="form-control" type="number" step="0.01" name="paid_amount" placeholder="<?= $currency ?>">
                </div>
                <div>
                    <label class="form-label">তারিখ</label>
                    <input class="form-control" type="date" name="purchase_date" value="<?= date('Y-m-d') ?>">
                </div>
                <button class="btn btn-primary" type="submit">ক্রয় সংরক্ষণ</button>
            </form>
        </div>

        <div class="card p-4 mt-4">
            <h6 class="section-title">বকেয়া পেমেন্ট</h6>
            <?php if ($paymentMessage): ?>
                <div class="alert alert-info"><?= htmlspecialchars($paymentMessage) ?></div>
            <?php endif; ?>
            <form class="vstack gap-3" method="post">
                <input type="hidden" name="payment_submit" value="1">
                <div>
                    <label class="form-label">সাপ্লায়ার</label>
                    <select class="form-select" name="payment_supplier_id" required>
                        <option value="">সাপ্লায়ার নির্বাচন করুন</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>">
                                <?= htmlspecialchars($supplier['name']) ?> (বকেয়া: <?= format_currency($currency, $supplier['balance']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">খরচের খাত</label>
                    <select class="form-select" name="expense_category_id" required>
                        <option value="">খাত নির্বাচন করুন</option>
                        <?php foreach ($expenseCategories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">পরিমাণ</label>
                    <input class="form-control" type="number" step="0.01" name="payment_amount" required>
                </div>
                <div>
                    <label class="form-label">তারিখ</label>
                    <input class="form-control" type="date" name="payment_date" value="<?= date('Y-m-d') ?>">
                </div>
                <button class="btn btn-outline-primary" type="submit">পেমেন্ট সংরক্ষণ</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="section-title">সাম্প্রতিক ক্রয়</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>পণ্য</th>
                        <th>সাপ্লায়ার</th>
                        <th>পরিমাণ</th>
                        <th>মোট</th>
                        <th>পরিশোধ</th>
                        <th>বকেয়া</th>
                        <th>ধরন</th>
                        <th>তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($purchases)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">কোনো ক্রয় নেই।</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($purchases as $purchase): ?>
                            <tr>
                                <td><?= htmlspecialchars($purchase['product'] ?? '') ?></td>
                                <td><?= htmlspecialchars($purchase['supplier'] ?? 'নির্ধারিত নয়') ?></td>
                                <td><?= (int) $purchase['quantity'] ?></td>
                                <td><?= format_currency($currency, $purchase['total_amount']) ?></td>
                                <td><?= format_currency($currency, $purchase['paid_amount']) ?></td>
                                <td><?= format_currency($currency, $purchase['due_amount']) ?></td>
                                <td><?= htmlspecialchars($purchase['payment_type']) ?></td>
                                <td><?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
