<?php
$pageTitle = 'নতুন মেমো';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';

$customers = fetch_all('SELECT id, name FROM customers ORDER BY name ASC');
$products = fetch_all('SELECT id, name, selling_price, stock FROM products ORDER BY name ASC');

$memoSeedRow = fetch_one('SELECT COUNT(*) AS total FROM sales');
$memoSeed = (int) ($memoSeedRow['total'] ?? 0) + 1;
$generatedMemo = 'MV-' . date('Ymd') . '-' . str_pad((string) $memoSeed, 4, '0', STR_PAD_LEFT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = (int) ($_POST['customer_id'] ?? 0);
    $memoNo = trim($_POST['memo_no'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $paid = (float) ($_POST['paid'] ?? 0);

    $productIds = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['price'] ?? [];

    $items = [];
    foreach ($productIds as $index => $productId) {
        $productId = (int) $productId;
        $quantity = (int) ($quantities[$index] ?? 0);
        $price = (float) ($prices[$index] ?? 0);
        if ($productId > 0 && $quantity > 0 && $price > 0) {
            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'line_total' => $quantity * $price,
            ];
        }
    }

    if ($memoNo !== '' && !empty($items)) {
        $total = array_sum(array_column($items, 'line_total'));
        if ($paid > $total) {
            $paid = $total;
        }

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
                $stockStmt = $pdo->prepare('UPDATE products SET stock = GREATEST(stock - :quantity, 0) WHERE id = :id');

                foreach ($items as $item) {
                    $itemStmt->execute([
                        ':sale_id' => $saleId,
                        ':product_id' => $item['product_id'],
                        ':quantity' => $item['quantity'],
                        ':price' => $item['price'],
                    ]);
                    $stockStmt->execute([
                        ':quantity' => $item['quantity'],
                        ':id' => $item['product_id'],
                    ]);
                }

                $pdo->commit();
                $message = 'মেমো সংরক্ষণ হয়েছে।';
                $generatedMemo = '';
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
    <div class="col-lg-9">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="section-title">নতুন মেমো</h5>
                <span class="text-muted">মেমো নং: <strong id="memoPreview"><?= htmlspecialchars($generatedMemo) ?></strong></span>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form class="row g-3" method="post" id="memoForm">
                <input type="hidden" name="memo_no" value="<?= htmlspecialchars($generatedMemo) ?>" id="memoNoField">
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
                    <label class="form-label">পেমেন্ট পদ্ধতি</label>
                    <select class="form-select" name="payment_method">
                        <option value="ক্যাশ">ক্যাশ</option>
                        <option value="ব্যাংক">ব্যাংক</option>
                        <option value="মোবাইল ব্যাংকিং">মোবাইল ব্যাংকিং</option>
                    </select>
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="memoItems">
                            <thead>
                                <tr>
                                    <th>পণ্য</th>
                                    <th>স্টক</th>
                                    <th style="width: 120px;">পরিমাণ</th>
                                    <th style="width: 140px;">দাম</th>
                                    <th style="width: 140px;">মোট</th>
                                    <th style="width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select class="form-select product-select" name="product_id[]" required>
                                            <option value="">পণ্য নির্বাচন করুন</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?= (int) $product['id'] ?>" data-price="<?= htmlspecialchars($product['selling_price']) ?>" data-stock="<?= (int) $product['stock'] ?>">
                                                    <?= htmlspecialchars($product['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="stock-cell">0</td>
                                    <td><input class="form-control qty-input" type="number" name="quantity[]" min="1" value="1" required></td>
                                    <td><input class="form-control price-input" type="number" step="0.01" name="price[]" required></td>
                                    <td class="line-total">0</td>
                                    <td>
                                        <button class="btn btn-outline-danger btn-sm remove-row" type="button">×</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" type="button" id="addRow">আরও পণ্য যোগ করুন</button>
                </div>

                <div class="col-md-4">
                    <label class="form-label">মোট</label>
                    <input class="form-control" type="text" id="grandTotal" value="0" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">পরিশোধ</label>
                    <input class="form-control" type="number" step="0.01" name="paid" id="paidAmount" placeholder="<?= $currency ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">বাকি</label>
                    <input class="form-control" type="text" id="dueAmount" value="0" readonly>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">মেমো সংরক্ষণ</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card p-4">
            <h6 class="section-title">নির্দেশনা</h6>
            <ul class="small text-muted mb-0">
                <li>একাধিক পণ্য যোগ করা যাবে।</li>
                <li>বাকি থাকলে ডিউ রিপোর্টে দেখাবে।</li>
                <li>স্টক কমলে ইনভেন্টরি থেকে আপডেট করুন।</li>
            </ul>
        </div>
    </div>
</div>
<script>
    const memoItems = document.getElementById('memoItems');
    const addRowBtn = document.getElementById('addRow');
    const grandTotal = document.getElementById('grandTotal');
    const paidAmount = document.getElementById('paidAmount');
    const dueAmount = document.getElementById('dueAmount');

    const updateTotals = () => {
        let total = 0;
        memoItems.querySelectorAll('tbody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value || 0);
            const price = parseFloat(row.querySelector('.price-input').value || 0);
            const lineTotal = qty * price;
            row.querySelector('.line-total').textContent = lineTotal.toFixed(2);
            total += lineTotal;
        });
        grandTotal.value = total.toFixed(2);
        const paid = parseFloat(paidAmount.value || 0);
        dueAmount.value = Math.max(total - paid, 0).toFixed(2);
    };

    const bindRow = (row) => {
        const productSelect = row.querySelector('.product-select');
        const priceInput = row.querySelector('.price-input');
        const stockCell = row.querySelector('.stock-cell');
        const qtyInput = row.querySelector('.qty-input');
        const removeBtn = row.querySelector('.remove-row');

        productSelect.addEventListener('change', () => {
            const selected = productSelect.options[productSelect.selectedIndex];
            const price = selected?.dataset?.price || 0;
            const stock = selected?.dataset?.stock || 0;
            priceInput.value = price;
            stockCell.textContent = stock;
            updateTotals();
        });

        [priceInput, qtyInput].forEach(input => input.addEventListener('input', updateTotals));
        removeBtn.addEventListener('click', () => {
            if (memoItems.querySelectorAll('tbody tr').length > 1) {
                row.remove();
                updateTotals();
            }
        });
    };

    memoItems.querySelectorAll('tbody tr').forEach(bindRow);
    paidAmount.addEventListener('input', updateTotals);

    addRowBtn.addEventListener('click', () => {
        const firstRow = memoItems.querySelector('tbody tr');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('.product-select').value = '';
        newRow.querySelector('.price-input').value = '';
        newRow.querySelector('.qty-input').value = 1;
        newRow.querySelector('.stock-cell').textContent = '0';
        newRow.querySelector('.line-total').textContent = '0';
        memoItems.querySelector('tbody').appendChild(newRow);
        bindRow(newRow);
        updateTotals();
    });

    updateTotals();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
