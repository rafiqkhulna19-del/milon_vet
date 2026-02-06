<?php
$pageTitle = 'আয়-ব্যায়';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$today = date('Y-m-d');

$incomeRows = fetch_all('SELECT source, amount FROM incomes WHERE income_date = :today', [
    ':today' => $today,
]);
$incomeTotalRow = fetch_one('SELECT COALESCE(SUM(amount), 0) AS total FROM incomes WHERE income_date = :today', [
    ':today' => $today,
]);
$incomeTotal = $incomeTotalRow['total'] ?? 0;

$expenseRows = fetch_all('SELECT category, amount FROM expenses WHERE expense_date = :today', [
    ':today' => $today,
]);
$expenseTotalRow = fetch_one('SELECT COALESCE(SUM(amount), 0) AS total FROM expenses WHERE expense_date = :today', [
    ':today' => $today,
]);
$expenseTotal = $expenseTotalRow['total'] ?? 0;
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">আজকের আয়</h5>
            <ul class="list-group list-group-flush">
                <?php if (empty($incomeRows)): ?>
                    <li class="list-group-item text-muted text-center">আজকের কোন আয় নেই।</li>
                <?php else: ?>
                    <?php foreach ($incomeRows as $income): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= htmlspecialchars($income['source']) ?></span>
                            <span><?= format_currency($currency, $income['amount']) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                <li class="list-group-item d-flex justify-content-between fw-bold">
                    <span>মোট</span>
                    <span><?= format_currency($currency, $incomeTotal) ?></span>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">আজকের ব্যয়</h5>
            <ul class="list-group list-group-flush">
                <?php if (empty($expenseRows)): ?>
                    <li class="list-group-item text-muted text-center">আজকের কোন ব্যয় নেই।</li>
                <?php else: ?>
                    <?php foreach ($expenseRows as $expense): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= htmlspecialchars($expense['category']) ?></span>
                            <span><?= format_currency($currency, $expense['amount']) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                <li class="list-group-item d-flex justify-content-between fw-bold">
                    <span>মোট</span>
                    <span><?= format_currency($currency, $expenseTotal) ?></span>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="card p-4 mt-4">
    <h5 class="section-title">দৈনিক হিসাব যোগ করুন</h5>
    <form class="row g-3">
        <div class="col-md-4">
            <label class="form-label">ধরন</label>
            <select class="form-select">
                <option>আয়</option>
                <option>ব্যয়</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">খাত</label>
            <input class="form-control" type="text" placeholder="খাত লিখুন">
        </div>
        <div class="col-md-4">
            <label class="form-label">পরিমাণ</label>
            <input class="form-control" type="number" placeholder="<?= $currency ?>">
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="button">সেভ করুন</button>
        </div>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
