<?php
$pageTitle = 'আয়-ব্যয়';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$today = date('Y-m-d');
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? $today;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$pdo] = db_connection();
    if (!$pdo) {
        $error = 'ডাটাবেস সংযোগ পাওয়া যায়নি।';
    } else {
        if (isset($_POST['add_category'])) {
            $name = trim($_POST['category_name'] ?? '');
            $type = trim($_POST['category_type'] ?? '');
            if ($name !== '' && in_array($type, ['income', 'expense'], true)) {
                $pdo->prepare('INSERT INTO transaction_categories (name, type) VALUES (:name, :type)')
                    ->execute([':name' => $name, ':type' => $type]);
                $message = 'খাত যোগ হয়েছে।';
            } else {
                $error = 'খাতের নাম ও ধরন দিন।';
            }
        }

        if (isset($_POST['add_account'])) {
            $name = trim($_POST['account_name'] ?? '');
            $type = trim($_POST['account_type'] ?? '');
            if ($name !== '' && $type !== '') {
                $pdo->prepare('INSERT INTO accounts (name, type) VALUES (:name, :type)')
                    ->execute([':name' => $name, ':type' => $type]);
                $message = 'অ্যাকাউন্ট যোগ হয়েছে।';
            } else {
                $error = 'অ্যাকাউন্টের নাম ও ধরন দিন।';
            }
        }

        if (isset($_POST['add_transaction'])) {
            $type = trim($_POST['txn_type'] ?? '');
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $accountId = (int) ($_POST['account_id'] ?? 0);
            $amount = (float) ($_POST['amount'] ?? 0);
            $txnDate = $_POST['txn_date'] ?? $today;
            $note = trim($_POST['note'] ?? '');

            if (in_array($type, ['income', 'expense'], true) && $categoryId > 0 && $accountId > 0 && $amount > 0) {
                $pdo->prepare('INSERT INTO transactions (type, category_id, account_id, amount, txn_date, note) VALUES (:type, :category_id, :account_id, :amount, :txn_date, :note)')
                    ->execute([
                        ':type' => $type,
                        ':category_id' => $categoryId,
                        ':account_id' => $accountId,
                        ':amount' => $amount,
                        ':txn_date' => $txnDate,
                        ':note' => $note,
                    ]);
                $message = 'লেনদেন যোগ হয়েছে।';
            } else {
                $error = 'লেনদেনের সব তথ্য দিন।';
            }
        }

        if (isset($_POST['add_transfer'])) {
            $fromId = (int) ($_POST['from_account_id'] ?? 0);
            $toId = (int) ($_POST['to_account_id'] ?? 0);
            $amount = (float) ($_POST['transfer_amount'] ?? 0);
            $transferDate = $_POST['transfer_date'] ?? $today;
            $note = trim($_POST['transfer_note'] ?? '');

            if ($fromId > 0 && $toId > 0 && $fromId !== $toId && $amount > 0) {
                $pdo->prepare('INSERT INTO account_transfers (from_account_id, to_account_id, amount, transfer_date, note) VALUES (:from_id, :to_id, :amount, :transfer_date, :note)')
                    ->execute([
                        ':from_id' => $fromId,
                        ':to_id' => $toId,
                        ':amount' => $amount,
                        ':transfer_date' => $transferDate,
                        ':note' => $note,
                    ]);
                $message = 'ফান্ড ট্রান্সফার হয়েছে।';
            } else {
                $error = 'ট্রান্সফার তথ্য সঠিকভাবে দিন।';
            }
        }
    }
}

$accounts = fetch_all('SELECT id, name, type FROM accounts WHERE is_active = 1 ORDER BY type, name');
$incomeCategories = fetch_all('SELECT id, name FROM transaction_categories WHERE type = "income" ORDER BY name');
$expenseCategories = fetch_all('SELECT id, name FROM transaction_categories WHERE type = "expense" ORDER BY name');

$balances = fetch_all('SELECT a.id, a.name, a.type,
    COALESCE((SELECT SUM(CASE WHEN t.type = "income" THEN t.amount ELSE -t.amount END) FROM transactions t WHERE t.account_id = a.id), 0) AS txn_total,
    COALESCE((SELECT SUM(CASE WHEN tr.to_account_id = a.id THEN tr.amount WHEN tr.from_account_id = a.id THEN -tr.amount END) FROM account_transfers tr WHERE tr.to_account_id = a.id OR tr.from_account_id = a.id), 0) AS transfer_total
    FROM accounts a
    WHERE a.is_active = 1
    ORDER BY a.type, a.name');

$accountSummary = [];
$totalBalance = 0;
foreach ($balances as $balance) {
    $current = (float) $balance['txn_total'] + (float) $balance['transfer_total'];
    $accountSummary[] = [
        'name' => $balance['name'],
        'type' => $balance['type'],
        'balance' => $current,
    ];
    $totalBalance += $current;
}

$incomeByCategory = fetch_all('SELECT c.name, COALESCE(SUM(t.amount), 0) AS total
    FROM transaction_categories c
    LEFT JOIN transactions t ON t.category_id = c.id AND t.type = "income" AND t.txn_date BETWEEN :start AND :end
    WHERE c.type = "income"
    GROUP BY c.id
    ORDER BY total DESC, c.name ASC', [
    ':start' => $start,
    ':end' => $end,
]);

$expenseByCategory = fetch_all('SELECT c.name, COALESCE(SUM(t.amount), 0) AS total
    FROM transaction_categories c
    LEFT JOIN transactions t ON t.category_id = c.id AND t.type = "expense" AND t.txn_date BETWEEN :start AND :end
    WHERE c.type = "expense"
    GROUP BY c.id
    ORDER BY total DESC, c.name ASC', [
    ':start' => $start,
    ':end' => $end,
]);

$recentTransactions = fetch_all('SELECT t.type, t.amount, t.txn_date, t.note, c.name AS category, a.name AS account
    FROM transactions t
    LEFT JOIN transaction_categories c ON c.id = t.category_id
    LEFT JOIN accounts a ON a.id = t.account_id
    ORDER BY t.txn_date DESC, t.id DESC
    LIMIT 10');

$recentTransfers = fetch_all('SELECT tr.amount, tr.transfer_date, tr.note,
    fa.name AS from_account, ta.name AS to_account
    FROM account_transfers tr
    LEFT JOIN accounts fa ON fa.id = tr.from_account_id
    LEFT JOIN accounts ta ON ta.id = tr.to_account_id
    ORDER BY tr.transfer_date DESC, tr.id DESC
    LIMIT 5');
?>
<?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h5 class="section-title">অ্যাকাউন্ট ব্যালান্স</h5>
            <div class="text-muted small mb-2">সর্বশেষ ব্যালান্স</div>
            <div class="list-group list-group-flush">
                <?php if (empty($accountSummary)): ?>
                    <div class="text-muted text-center py-3">কোনো অ্যাকাউন্ট নেই।</div>
                <?php else: ?>
                    <?php foreach ($accountSummary as $account): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($account['name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars(ucfirst($account['type'])) ?></div>
                            </div>
                            <div class="fw-semibold"><?= format_currency($currency, $account['balance']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="d-flex justify-content-between mt-3 fw-bold">
                <span>মোট ব্যালান্স</span>
                <span><?= format_currency($currency, $totalBalance) ?></span>
            </div>
        </div>
        <div class="card p-4 mt-4">
            <h6 class="section-title">অ্যাকাউন্ট যোগ করুন</h6>
            <form class="vstack gap-2" method="post">
                <input type="hidden" name="add_account" value="1">
                <input class="form-control" type="text" name="account_name" placeholder="অ্যাকাউন্ট নাম" required>
                <select class="form-select" name="account_type" required>
                    <option value="">ধরন নির্বাচন করুন</option>
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="bkash">bKash</option>
                    <option value="other">Other</option>
                </select>
                <button class="btn btn-outline-primary" type="submit">যোগ করুন</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="section-title mb-0">আয়-ব্যয়ের সারাংশ</h5>
                <form class="d-flex align-items-center gap-2" method="get">
                    <input class="form-control form-control-sm" type="date" name="start" value="<?= htmlspecialchars($start) ?>">
                    <input class="form-control form-control-sm" type="date" name="end" value="<?= htmlspecialchars($end) ?>">
                    <button class="btn btn-sm btn-outline-primary" type="submit">ফিল্টার</button>
                </form>
            </div>
            <div class="row mt-3 g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-semibold mb-2">খাতভিত্তিক আয়</div>
                        <?php if (empty($incomeByCategory)): ?>
                            <div class="text-muted small">কোনো আয় নেই।</div>
                        <?php else: ?>
                            <?php foreach ($incomeByCategory as $row): ?>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><?= htmlspecialchars($row['name']) ?></span>
                                    <span><?= format_currency($currency, $row['total']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-semibold mb-2">খাতভিত্তিক ব্যয়</div>
                        <?php if (empty($expenseByCategory)): ?>
                            <div class="text-muted small">কোনো ব্যয় নেই।</div>
                        <?php else: ?>
                            <?php foreach ($expenseByCategory as $row): ?>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><?= htmlspecialchars($row['name']) ?></span>
                                    <span><?= format_currency($currency, $row['total']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="card p-4 mt-4">
            <h5 class="section-title">নতুন আয়/ব্যয় যোগ করুন</h5>
            <form class="row g-3" method="post">
                <input type="hidden" name="add_transaction" value="1">
                <div class="col-md-3">
                    <label class="form-label">ধরন</label>
                    <select class="form-select" name="txn_type" id="txnType" required>
                        <option value="">নির্বাচন করুন</option>
                        <option value="income">আয়</option>
                        <option value="expense">ব্যয়</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">খাত</label>
                    <select class="form-select category-select" name="category_id" id="incomeCategory" disabled style="display:none;">
                        <option value="">আয়ের খাত</option>
                        <?php foreach ($incomeCategories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="form-select category-select" name="category_id" id="expenseCategory" disabled style="display:none;">
                        <option value="">ব্যয়ের খাত</option>
                        <?php foreach ($expenseCategories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">অ্যাকাউন্ট</label>
                    <select class="form-select" name="account_id" required>
                        <option value="">অ্যাকাউন্ট নির্বাচন</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= (int) $account['id'] ?>"><?= htmlspecialchars($account['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">পরিমাণ</label>
                    <input class="form-control" type="number" step="0.01" name="amount" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">তারিখ</label>
                    <input class="form-control" type="date" name="txn_date" value="<?= $today ?>">
                </div>
                <div class="col-md-9">
                    <label class="form-label">নোট</label>
                    <input class="form-control" type="text" name="note" placeholder="ঐচ্ছিক">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">সেভ করুন</button>
                </div>
            </form>
        </div>
        <div class="card p-4 mt-4">
            <h5 class="section-title">ফান্ড ট্রান্সফার</h5>
            <form class="row g-3" method="post">
                <input type="hidden" name="add_transfer" value="1">
                <div class="col-md-4">
                    <label class="form-label">From</label>
                    <select class="form-select" name="from_account_id" required>
                        <option value="">অ্যাকাউন্ট নির্বাচন</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= (int) $account['id'] ?>"><?= htmlspecialchars($account['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">To</label>
                    <select class="form-select" name="to_account_id" required>
                        <option value="">অ্যাকাউন্ট নির্বাচন</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= (int) $account['id'] ?>"><?= htmlspecialchars($account['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">পরিমাণ</label>
                    <input class="form-control" type="number" step="0.01" name="transfer_amount" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">তারিখ</label>
                    <input class="form-control" type="date" name="transfer_date" value="<?= $today ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">নোট</label>
                    <input class="form-control" type="text" name="transfer_note" placeholder="ঐচ্ছিক">
                </div>
                <div class="col-12">
                    <button class="btn btn-outline-primary" type="submit">ট্রান্সফার করুন</button>
                </div>
            </form>
        </div>
        <div class="card p-4 mt-4">
            <h5 class="section-title">সাম্প্রতিক লেনদেন</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ধরন</th>
                            <th>খাত</th>
                            <th>অ্যাকাউন্ট</th>
                            <th>পরিমাণ</th>
                            <th>তারিখ</th>
                            <th>নোট</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentTransactions)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">কোনো লেনদেন নেই।</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentTransactions as $row): ?>
                                <tr>
                                    <td><?= $row['type'] === 'income' ? 'আয়' : 'ব্যয়' ?></td>
                                    <td><?= htmlspecialchars($row['category'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['account'] ?? '-') ?></td>
                                    <td><?= format_currency($currency, $row['amount']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['txn_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['note'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card p-4 mt-4">
            <h5 class="section-title">সাম্প্রতিক ট্রান্সফার</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>To</th>
                            <th>পরিমাণ</th>
                            <th>তারিখ</th>
                            <th>নোট</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentTransfers)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">কোনো ট্রান্সফার নেই।</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentTransfers as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['from_account'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['to_account'] ?? '-') ?></td>
                                    <td><?= format_currency($currency, $row['amount']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['transfer_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['note'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card p-4 mt-4">
            <h5 class="section-title">খাত যোগ করুন</h5>
            <form class="row g-3" method="post">
                <input type="hidden" name="add_category" value="1">
                <div class="col-md-5">
                    <label class="form-label">খাতের নাম</label>
                    <input class="form-control" type="text" name="category_name" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ধরন</label>
                    <select class="form-select" name="category_type" required>
                        <option value="">নির্বাচন করুন</option>
                        <option value="income">আয়</option>
                        <option value="expense">ব্যয়</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-outline-primary w-100" type="submit">খাত যোগ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    (function () {
        const typeSelect = document.getElementById('txnType');
        const incomeCategory = document.getElementById('incomeCategory');
        const expenseCategory = document.getElementById('expenseCategory');

        const syncCategory = () => {
            const type = typeSelect.value;
            if (type === 'income') {
                incomeCategory.disabled = false;
                incomeCategory.name = 'category_id';
                incomeCategory.style.display = 'block';
                expenseCategory.disabled = true;
                expenseCategory.name = '';
                expenseCategory.style.display = 'none';
            } else if (type === 'expense') {
                expenseCategory.disabled = false;
                expenseCategory.name = 'category_id';
                expenseCategory.style.display = 'block';
                incomeCategory.disabled = true;
                incomeCategory.name = '';
                incomeCategory.style.display = 'none';
            } else {
                incomeCategory.disabled = true;
                incomeCategory.name = '';
                incomeCategory.style.display = 'none';
                expenseCategory.disabled = true;
                expenseCategory.name = '';
                expenseCategory.style.display = 'none';
            }
        };

        typeSelect.addEventListener('change', syncCategory);
        syncCategory();
    })();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
