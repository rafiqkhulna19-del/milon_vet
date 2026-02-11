<?php
$pageTitle = 'ইনভেন্টরি';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';

$categories = fetch_all('SELECT id, name FROM categories ORDER BY name ASC');
$suppliers = fetch_all('SELECT id, name FROM suppliers ORDER BY name ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $supplierId = (int) ($_POST['supplier_id'] ?? 0);
    $purchasePrice = (float) ($_POST['purchase_price'] ?? 0);
    $sellingPrice = (float) ($_POST['selling_price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);

    if ($name !== '' && $purchasePrice > 0 && $sellingPrice > 0) {
        [$pdo] = db_connection();
        if ($pdo) {
            $stmt = $pdo->prepare('INSERT INTO products (name, category_id, supplier_id, purchase_price, selling_price, stock) VALUES (:name, :category_id, :supplier_id, :purchase_price, :selling_price, :stock)');
            $stmt->execute([
                ':name' => $name,
                ':category_id' => $categoryId ?: null,
                ':supplier_id' => $supplierId ?: null,
                ':purchase_price' => $purchasePrice,
                ':selling_price' => $sellingPrice,
                ':stock' => $stock,
            ]);
            $message = 'নতুন পণ্য যোগ হয়েছে।';
        }
    } else {
        $message = 'পণ্যের নাম ও মূল্য দিন।';
    }
}

$products = fetch_all('SELECT p.name, p.stock, p.purchase_price, p.selling_price, c.name AS category, s.name AS supplier
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN suppliers s ON s.id = p.supplier_id
    ORDER BY p.id DESC');
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">স্টক তালিকা</h5>
        <span class="text-muted">মোট <?= count($products) ?> টি পণ্য</span>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="row g-3 mt-2">
        <div class="col-lg-4">
            <div class="card border-0 bg-body-secondary p-3">
                <h6 class="section-title">নতুন পণ্য যোগ করুন</h6>
                <form class="vstack gap-2" method="post">
                    <input class="form-control" type="text" name="name" placeholder="পণ্যের নাম" required>
                    <select class="form-select" name="category_id">
                        <option value="">ক্যাটেগরি নির্বাচন করুন</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="form-select" name="supplier_id">
                        <option value="">সাপ্লায়ার নির্বাচন করুন</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input class="form-control" type="number" step="0.01" name="purchase_price" placeholder="ক্রয় মূল্য" required>
                    <input class="form-control" type="number" step="0.01" name="selling_price" placeholder="বিক্রয় মূল্য" required>
                    <input class="form-control" type="number" name="stock" placeholder="ইনিশিয়াল স্টক">
                    <button class="btn btn-primary" type="submit">সেভ করুন</button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>পণ্য</th>
                            <th>ক্যাটেগরি</th>
                            <th>সাপ্লায়ার</th>
                            <th>স্টক</th>
                            <th>ক্রয় মূল্য</th>
                            <th>বিক্রয় মূল্য</th>
                            <th>স্ট্যাটাস</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">কোনো পণ্য নেই।</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <?php
                                    $stockValue = (int) $product['stock'];
                                    $status = 'ইন স্টক';
                                    $badge = 'bg-success';
                                    if ($stockValue <= 5) {
                                        $status = 'রিস্টক';
                                        $badge = 'bg-danger';
                                    } elseif ($stockValue <= 10) {
                                        $status = 'লো স্টক';
                                        $badge = 'bg-warning text-dark';
                                    }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= htmlspecialchars($product['category'] ?? 'অনির্ধারিত') ?></td>
                                    <td><?= htmlspecialchars($product['supplier'] ?? 'নির্ধারিত নয়') ?></td>
                                    <td><?= $stockValue ?></td>
                                    <td><?= format_currency($currency, $product['purchase_price']) ?></td>
                                    <td><?= format_currency($currency, $product['selling_price']) ?></td>
                                    <td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
