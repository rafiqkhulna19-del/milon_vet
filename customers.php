<?php
$pageTitle = 'কাস্টমার ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $due = (float) ($_POST['due_balance'] ?? 0);

    if ($name !== '') {
        [$pdo] = db_connection();
        if ($pdo) {
            $stmt = $pdo->prepare('INSERT INTO customers (name, phone, address, due_balance) VALUES (:name, :phone, :address, :due_balance)');
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':address' => $address,
                ':due_balance' => $due,
            ]);
            $message = 'নতুন কাস্টমার যোগ হয়েছে।';
        }
    } else {
        $message = 'কাস্টমারের নাম দিন।';
    }
}

$customers = fetch_all('SELECT name, phone, address, due_balance FROM customers ORDER BY id DESC');
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">কাস্টমার তালিকা</h5>
        <span class="text-muted">মোট <?= count($customers) ?> জন</span>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="row g-3 mt-2">
        <div class="col-lg-4">
            <div class="card border-0 bg-body-secondary p-3">
                <h6 class="section-title">নতুন কাস্টমার</h6>
                <form class="vstack gap-2" method="post">
                    <input class="form-control" type="text" name="name" placeholder="নাম" required>
                    <input class="form-control" type="text" name="phone" placeholder="মোবাইল">
                    <input class="form-control" type="text" name="address" placeholder="ঠিকানা">
                    <input class="form-control" type="number" step="0.01" name="due_balance" placeholder="ডিউ (ঐচ্ছিক)">
                    <button class="btn btn-primary" type="submit">সংরক্ষণ করুন</button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>নাম</th>
                            <th>মোবাইল</th>
                            <th>এলাকা</th>
                            <th>ডিউ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">কোনো কাস্টমার নেই।</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?= htmlspecialchars($customer['name']) ?></td>
                                    <td><?= htmlspecialchars($customer['phone'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($customer['address'] ?? '') ?></td>
                                    <td><?= format_currency($currency, $customer['due_balance'] ?? 0) ?></td>
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
