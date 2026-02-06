<?php
$pageTitle = 'সাপ্লায়ার ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';
$paymentMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_supplier'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $balance = (float) ($_POST['balance'] ?? 0);

    if ($name !== '') {
        [$pdo] = db_connection();
        if ($pdo) {
            $stmt = $pdo->prepare('INSERT INTO suppliers (name, phone, address, balance) VALUES (:name, :phone, :address, :balance)');
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':address' => $address,
                ':balance' => $balance,
            ]);
            $message = 'নতুন সাপ্লায়ার যোগ হয়েছে।';
        }
    } else {
        $message = 'সাপ্লায়ারের নাম দিন।';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supplier_payment'])) {
    $supplierId = (int) ($_POST['supplier_id'] ?? 0);
    $amount = (float) ($_POST['amount'] ?? 0);
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
    $categoryId = (int) ($_POST['expense_category_id'] ?? 0);

    if ($supplierId > 0 && $amount > 0 && $categoryId > 0) {
        [$pdo] = db_connection();
        if ($pdo) {
            $pdo->prepare('INSERT INTO expenses (expense_category_id, amount, expense_date, note) VALUES (:category_id, :amount, :expense_date, :note)')->execute([
                ':category_id' => $categoryId,
                ':amount' => $amount,
                ':expense_date' => $paymentDate,
                ':note' => 'সাপ্লায়ার পেমেন্ট',
            ]);
            $pdo->prepare('UPDATE suppliers SET balance = GREATEST(balance - :amount, 0) WHERE id = :id')->execute([
                ':amount' => $amount,
                ':id' => $supplierId,
            ]);
            $paymentMessage = 'পেমেন্ট সংরক্ষণ হয়েছে।';
        }
    } else {
        $paymentMessage = 'সাপ্লায়ার, খাত এবং পরিমাণ দিন।';
    }
}

$suppliers = fetch_all('SELECT id, name, phone, address, balance FROM suppliers ORDER BY id DESC');
$expenseCategories = fetch_all('SELECT id, name FROM expense_categories ORDER BY name ASC');
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">সাপ্লায়ার তালিকা</h5>
        <span class="text-muted">মোট <?= count($suppliers) ?> জন</span>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="row g-3 mt-2">
        <div class="col-lg-4">
            <div class="card border-0 bg-body-secondary p-3">
                <h6 class="section-title">নতুন সাপ্লায়ার</h6>
                <form class="vstack gap-2" method="post">
                    <input type="hidden" name="create_supplier" value="1">
                    <input class="form-control" type="text" name="name" placeholder="নাম" required>
                    <input class="form-control" type="text" name="phone" placeholder="মোবাইল">
                    <input class="form-control" type="text" name="address" placeholder="ঠিকানা">
                    <input class="form-control" type="number" step="0.01" name="balance" placeholder="বকেয়া">
                    <button class="btn btn-primary" type="submit">সংরক্ষণ করুন</button>
                </form>
            </div>

            <div class="card border-0 bg-body-secondary p-3 mt-3">
                <h6 class="section-title">বকেয়া পেমেন্ট</h6>
                <?php if ($paymentMessage): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($paymentMessage) ?></div>
                <?php endif; ?>
                <form class="vstack gap-2" method="post">
                    <input type="hidden" name="supplier_payment" value="1">
                    <select class="form-select" name="supplier_id" required>
                        <option value="">সাপ্লায়ার নির্বাচন করুন</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>">
                                <?= htmlspecialchars($supplier['name']) ?> (<?= format_currency($currency, $supplier['balance']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="form-select" name="expense_category_id" required>
                        <option value="">খরচের খাত</option>
                        <?php foreach ($expenseCategories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input class="form-control" type="number" step="0.01" name="amount" placeholder="পরিমাণ" required>
                    <input class="form-control" type="date" name="payment_date" value="<?= date('Y-m-d') ?>">
                    <button class="btn btn-outline-primary" type="submit">পেমেন্ট যোগ করুন</button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>নাম</th>
                            <th>মোবাইল</th>
                            <th>ঠিকানা</th>
                            <th>বকেয়া</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">কোনো সাপ্লায়ার নেই।</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td><?= htmlspecialchars($supplier['name']) ?></td>
                                    <td><?= htmlspecialchars($supplier['phone'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($supplier['address'] ?? '') ?></td>
                                    <td><?= format_currency($currency, $supplier['balance'] ?? 0) ?></td>
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
