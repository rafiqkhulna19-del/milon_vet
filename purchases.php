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
    $supplierId = (int) ($_POST['supplier_id'] ?? 0);
    $paymentType = $_POST['payment_type'] ?? 'cash';
    $paidAmount = (float) ($_POST['paid_amount'] ?? 0);
    $discount = (float) ($_POST['discount'] ?? 0);
    $purchaseDate = $_POST['purchase_date'] ?? date('Y-m-d');

    $productIds = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $unitCosts = $_POST['unit_cost'] ?? [];

    $items = [];
    foreach ($productIds as $index => $productId) {
        $productId = (int) $productId;
        $quantity = (int) ($quantities[$index] ?? 0);
        $unitCost = (float) ($unitCosts[$index] ?? 0);
        if ($productId > 0 && $quantity > 0 && $unitCost > 0) {
            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_total' => $quantity * $unitCost,
            ];
        }
    }

    if (!empty($items)) {
        $totalAmount = array_sum(array_column($items, 'line_total'));
        if ($discount < 0) {
            $discount = 0;
        }
        if ($discount > $totalAmount) {
            $discount = $totalAmount;
        }
        $netAmount = $totalAmount - $discount;

        if ($paymentType === 'cash') {
            $paidAmount = $netAmount;
        } elseif ($paymentType === 'credit') {
            $paidAmount = 0;
        } else {
            $paidAmount = min($paidAmount, $netAmount);
        }
        $dueAmount = $netAmount - $paidAmount;

        [$pdo] = db_connection();
        if ($pdo) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('INSERT INTO purchases (supplier_id, total_amount, discount, net_amount, paid_amount, due_amount, payment_type, purchase_date) VALUES (:supplier_id, :total_amount, :discount, :net_amount, :paid_amount, :due_amount, :payment_type, :purchase_date)');
                $stmt->execute([
                    ':supplier_id' => $supplierId ?: null,
                    ':total_amount' => $totalAmount,
                    ':discount' => $discount,
                    ':net_amount' => $netAmount,
                    ':paid_amount' => $paidAmount,
                    ':due_amount' => $dueAmount,
                    ':payment_type' => $paymentType,
                    ':purchase_date' => $purchaseDate,
                ]);
                $purchaseId = (int) $pdo->lastInsertId();

                $itemStmt = $pdo->prepare('INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_cost, line_total) VALUES (:purchase_id, :product_id, :quantity, :unit_cost, :line_total)');
                $stockStmt = $pdo->prepare('UPDATE products SET stock = stock + :quantity, purchase_price = :unit_cost WHERE id = :id');

                foreach ($items as $item) {
                    $itemStmt->execute([
                        ':purchase_id' => $purchaseId,
                        ':product_id' => $item['product_id'],
                        ':quantity' => $item['quantity'],
                        ':unit_cost' => $item['unit_cost'],
                        ':line_total' => $item['line_total'],
                    ]);
                    $stockStmt->execute([
                        ':quantity' => $item['quantity'],
                        ':unit_cost' => $item['unit_cost'],
                        ':id' => $item['product_id'],
                    ]);
                }

                if ($supplierId > 0 && $dueAmount > 0) {
                    $pdo->prepare('UPDATE suppliers SET balance = balance + :due WHERE id = :id')->execute([
                        ':due' => $dueAmount,
                        ':id' => $supplierId,
                    ]);
                }

                if ($paidAmount > 0) {
                    $accountId = get_account_id_by_type_or_name('cash');
                    $categoryId = ensure_transaction_category('Purchase', 'expense');
                    if ($accountId && $categoryId) {
                        create_transaction('expense', $categoryId, $accountId, (float) $paidAmount, $purchaseDate, 'Purchase #' . $purchaseId);
                    }
                }

                $pdo->commit();
                $message = 'ক্রয় তথ্য সংরক্ষণ হয়েছে।';
            } catch (Throwable $error) {
                $pdo->rollBack();
                $message = 'ক্রয় তথ্য সংরক্ষণ ব্যর্থ হয়েছে।';
            }
        }
    } else {
        $message = 'পণ্য যোগ করুন।';
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

            $expenseCategory = fetch_one('SELECT name FROM expense_categories WHERE id = :id', [
                ':id' => $categoryId,
            ]);
            $categoryName = $expenseCategory['name'] ?? 'Supplier Payment';
            $txnCategoryId = ensure_transaction_category($categoryName, 'expense');
            $accountId = get_account_id_by_type_or_name('cash');
            if ($txnCategoryId && $accountId) {
                create_transaction('expense', $txnCategoryId, $accountId, (float) $amount, $paymentDate, 'Supplier payment');
            }

            $paymentMessage = 'বকেয়া পেমেন্ট সংরক্ষণ হয়েছে।';
        }
    } else {
        $paymentMessage = 'সাপ্লায়ার, খাত এবং পরিমাণ দিন।';
    }
}

$purchases = fetch_all('SELECT pch.id, pch.total_amount, pch.discount, pch.net_amount, pch.paid_amount, pch.due_amount, pch.payment_type, pch.purchase_date, s.name AS supplier
    FROM purchases pch
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
            <form class="vstack gap-3" method="post" id="purchaseForm">
                <input type="hidden" name="purchase_submit" value="1">
                <div>
                    <label class="form-label">সাপ্লায়ার</label>
                    <select class="form-select" name="supplier_id">
                        <option value="">সাপ্লায়ার নির্বাচন করুন</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="purchaseItems">
                        <thead>
                            <tr>
                                <th>পণ্য</th>
                                <th style="width: 120px;">পরিমাণ</th>
                                <th style="width: 140px;">ক্রয় মূল্য</th>
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
                                            <option value="<?= (int) $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input class="form-control qty-input" type="number" name="quantity[]" min="1" value="1" required></td>
                                <td><input class="form-control cost-input" type="number" step="0.01" name="unit_cost[]" required></td>
                                <td class="line-total">0</td>
                                <td>
                                    <button class="btn btn-outline-danger btn-sm remove-row" type="button">×</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-outline-secondary btn-sm" type="button" id="addPurchaseRow">আরও পণ্য যোগ করুন</button>

                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <label class="form-label">ডিসকাউন্ট</label>
                        <input class="form-control" type="number" step="0.01" name="discount" id="purchaseDiscount" value="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label">মোট</label>
                        <input class="form-control" type="text" id="purchaseTotal" value="0" readonly>
                    </div>
                    <div class="col-6">
                        <label class="form-label">পরিশোধ</label>
                        <input class="form-control" type="number" step="0.01" name="paid_amount" id="purchasePaid" value="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label">বকেয়া</label>
                        <input class="form-control" type="text" id="purchaseDue" value="0" readonly>
                    </div>
                </div>

                <div>
                    <label class="form-label">পেমেন্ট ধরন</label>
                    <select class="form-select" name="payment_type" id="purchasePaymentType">
                        <option value="cash">নগদ</option>
                        <option value="partial">আংশিক বাকিতে</option>
                        <option value="credit">বাকিতে</option>
                    </select>
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
                        <th>সাপ্লায়ার</th>
                        <th>মোট</th>
                        <th>ডিসকাউন্ট</th>
                        <th>নেট</th>
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
                                <td><?= htmlspecialchars($purchase['supplier'] ?? 'নির্ধারিত নয়') ?></td>
                                <td><?= format_currency($currency, $purchase['total_amount']) ?></td>
                                <td><?= format_currency($currency, $purchase['discount']) ?></td>
                                <td><?= format_currency($currency, $purchase['net_amount']) ?></td>
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
<script>
    const purchaseItems = document.getElementById('purchaseItems');
    const addPurchaseRow = document.getElementById('addPurchaseRow');
    const purchaseTotal = document.getElementById('purchaseTotal');
    const purchaseDiscount = document.getElementById('purchaseDiscount');
    const purchasePaid = document.getElementById('purchasePaid');
    const purchaseDue = document.getElementById('purchaseDue');
    const purchasePaymentType = document.getElementById('purchasePaymentType');

    const updatePurchaseTotals = () => {
        let total = 0;
        purchaseItems.querySelectorAll('tbody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value || 0);
            const cost = parseFloat(row.querySelector('.cost-input').value || 0);
            const lineTotal = qty * cost;
            row.querySelector('.line-total').textContent = lineTotal.toFixed(2);
            total += lineTotal;
        });
        const discount = parseFloat(purchaseDiscount.value || 0);
        const net = Math.max(total - discount, 0);
        purchaseTotal.value = net.toFixed(2);
        const paid = parseFloat(purchasePaid.value || 0);
        purchaseDue.value = Math.max(net - paid, 0).toFixed(2);
    };

    const bindPurchaseRow = (row) => {
        row.querySelector('.qty-input').addEventListener('input', updatePurchaseTotals);
        row.querySelector('.cost-input').addEventListener('input', updatePurchaseTotals);
        row.querySelector('.remove-row').addEventListener('click', () => {
            if (purchaseItems.querySelectorAll('tbody tr').length > 1) {
                row.remove();
                updatePurchaseTotals();
            }
        });
    };

    purchaseItems.querySelectorAll('tbody tr').forEach(bindPurchaseRow);
    purchaseDiscount.addEventListener('input', updatePurchaseTotals);
    purchasePaid.addEventListener('input', updatePurchaseTotals);
    purchasePaymentType.addEventListener('change', () => {
        if (purchasePaymentType.value === 'cash') {
            purchasePaid.value = purchaseTotal.value;
        } else if (purchasePaymentType.value === 'credit') {
            purchasePaid.value = 0;
        }
        updatePurchaseTotals();
    });

    addPurchaseRow.addEventListener('click', () => {
        const firstRow = purchaseItems.querySelector('tbody tr');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('.product-select').value = '';
        newRow.querySelector('.qty-input').value = 1;
        newRow.querySelector('.cost-input').value = '';
        newRow.querySelector('.line-total').textContent = '0';
        purchaseItems.querySelector('tbody').appendChild(newRow);
        bindPurchaseRow(newRow);
        updatePurchaseTotals();
    });

    updatePurchaseTotals();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
