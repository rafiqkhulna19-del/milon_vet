<?php
$pageTitle = 'খরচ ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';
$categoryMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_submit'])) {
    $categoryName = trim($_POST['category_name'] ?? '');
    if ($categoryName !== '') {
        [$pdo] = db_connection();
        if ($pdo) {
            $pdo->prepare('INSERT INTO expense_categories (name) VALUES (:name)')->execute([':name' => $categoryName]);
            $categoryMessage = 'খরচের খাত যোগ হয়েছে।';
        }
    } else {
        $categoryMessage = 'খরচের খাত লিখুন।';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expense_submit'])) {
    $categoryId = (int) ($_POST['expense_category_id'] ?? 0);
    $amount = (float) ($_POST['amount'] ?? 0);
    $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
    $note = trim($_POST['note'] ?? '');

    if ($categoryId > 0 && $amount > 0) {
        [$pdo] = db_connection();
        if ($pdo) {
            $stmt = $pdo->prepare('INSERT INTO expenses (expense_category_id, amount, expense_date, note) VALUES (:category_id, :amount, :expense_date, :note)');
            $stmt->execute([
                ':category_id' => $categoryId,
                ':amount' => $amount,
                ':expense_date' => $expenseDate,
                ':note' => $note,
            ]);
            $message = 'খরচ যোগ হয়েছে।';
        }
    } else {
        $message = 'খরচের খাত ও পরিমাণ দিন।';
    }
}

$categories = fetch_all('SELECT id, name FROM expense_categories ORDER BY name ASC');
$expenses = fetch_all('SELECT e.amount, e.expense_date, e.note, c.name AS category
    FROM expenses e
    LEFT JOIN expense_categories c ON c.id = e.expense_category_id
    ORDER BY e.expense_date DESC, e.id DESC
    LIMIT 10');
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h5 class="section-title">খরচ যোগ করুন</h5>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form class="vstack gap-3" method="post">
                <input type="hidden" name="expense_submit" value="1">
                <select class="form-select" name="expense_category_id" required>
                    <option value="">খরচের খাত নির্বাচন করুন</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input class="form-control" type="number" step="0.01" name="amount" placeholder="পরিমাণ" required>
                <input class="form-control" type="date" name="expense_date" value="<?= date('Y-m-d') ?>">
                <input class="form-control" type="text" name="note" placeholder="নোট (ঐচ্ছিক)">
                <button class="btn btn-primary" type="submit">সেভ করুন</button>
            </form>
        </div>

        <div class="card p-4 mt-4">
            <h6 class="section-title">খরচের খাত যোগ করুন</h6>
            <?php if ($categoryMessage): ?>
                <div class="alert alert-info"><?= htmlspecialchars($categoryMessage) ?></div>
            <?php endif; ?>
            <form class="vstack gap-2" method="post">
                <input type="hidden" name="category_submit" value="1">
                <input class="form-control" type="text" name="category_name" placeholder="খাতের নাম" required>
                <button class="btn btn-outline-primary" type="submit">যোগ করুন</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="section-title">সাম্প্রতিক খরচ</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>খাত</th>
                        <th>পরিমাণ</th>
                        <th>তারিখ</th>
                        <th>নোট</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">কোনো খরচ নেই।</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?= htmlspecialchars($expense['category'] ?? 'অনির্ধারিত') ?></td>
                                <td><?= format_currency($currency, $expense['amount']) ?></td>
                                <td><?= date('d/m/Y', strtotime($expense['expense_date'])) ?></td>
                                <td><?= htmlspecialchars($expense['note'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
