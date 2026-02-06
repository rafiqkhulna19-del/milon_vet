<?php
$pageTitle = 'পণ্য ক্রয়';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';

$products = fetch_all('SELECT id, name FROM products ORDER BY name ASC');
$suppliers = fetch_all('SELECT id, name FROM suppliers ORDER BY name ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $supplierId = (int) ($_POST['supplier_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $unitCost = (float) ($_POST['unit_cost'] ?? 0);
    $purchaseDate = $_POST['purchase_date'] ?? date('Y-m-d');

    if ($productId > 0 && $quantity > 0 && $unitCost > 0) {
        [$pdo] = db_connection();
        if ($pdo) {
            $stmt = $pdo->prepare('INSERT INTO purchases (product_id, supplier_id, quantity, unit_cost, purchase_date) VALUES (:product_id, :supplier_id, :quantity, :unit_cost, :purchase_date)');
            $stmt->execute([
                ':product_id' => $productId,
                ':supplier_id' => $supplierId ?: null,
                ':quantity' => $quantity,
                ':unit_cost' => $unitCost,
                ':purchase_date' => $purchaseDate,
            ]);

            $update = $pdo->prepare('UPDATE products SET stock = stock + :quantity, purchase_price = :unit_cost WHERE id = :id');
            $update->execute([
                ':quantity' => $quantity,
                ':unit_cost' => $unitCost,
                ':id' => $productId,
            ]);

            $message = 'ক্রয় তথ্য সংরক্ষণ হয়েছে।';
        }
    } else {
        $message = 'সব তথ্য ঠিকভাবে পূরণ করুন।';
    }
}

$purchases = fetch_all('SELECT pch.quantity, pch.unit_cost, pch.purchase_date, pr.name AS product, s.name AS supplier
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
                    <label class="form-label">তারিখ</label>
                    <input class="form-control" type="date" name="purchase_date" value="<?= date('Y-m-d') ?>">
                </div>
                <button class="btn btn-primary" type="submit">ক্রয় সংরক্ষণ</button>
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
                        <th>মূল্য</th>
                        <th>তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($purchases)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">কোনো ক্রয় নেই।</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($purchases as $purchase): ?>
                            <tr>
                                <td><?= htmlspecialchars($purchase['product'] ?? '') ?></td>
                                <td><?= htmlspecialchars($purchase['supplier'] ?? 'নির্ধারিত নয়') ?></td>
                                <td><?= (int) $purchase['quantity'] ?></td>
                                <td><?= format_currency($currency, $purchase['unit_cost']) ?></td>
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
