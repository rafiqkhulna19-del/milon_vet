<?php
$pageTitle = 'ইনভেন্টরি';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$products = fetch_all('SELECT p.name, p.stock, p.purchase_price, p.selling_price, c.name AS category
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.id DESC');
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">স্টক তালিকা</h5>
        <button class="btn btn-primary" type="button">নতুন পণ্য যোগ করুন</button>
    </div>
    <div class="table-responsive mt-3">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>পণ্য</th>
                    <th>ক্যাটেগরি</th>
                    <th>স্টক</th>
                    <th>ক্রয় মূল্য</th>
                    <th>বিক্রয় মূল্য</th>
                    <th>স্ট্যাটাস</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">কোনো পণ্য নেই।</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                            $stock = (int) $product['stock'];
                            $status = 'ইন স্টক';
                            $badge = 'bg-success';
                            if ($stock <= 5) {
                                $status = 'রিস্টক';
                                $badge = 'bg-danger';
                            } elseif ($stock <= 10) {
                                $status = 'লো স্টক';
                                $badge = 'bg-warning text-dark';
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category'] ?? 'অনির্ধারিত') ?></td>
                            <td><?= $stock ?></td>
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
<?php require __DIR__ . '/includes/footer.php'; ?>
