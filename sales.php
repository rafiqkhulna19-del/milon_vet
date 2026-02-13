<?php
$pageTitle = 'মেমো ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    if ($deleteId > 0) {
        [$pdo] = db_connection();
        if ($pdo) {
            $pdo->prepare('DELETE FROM sale_items WHERE sale_id = :sale_id')->execute([':sale_id' => $deleteId]);
            $pdo->prepare('DELETE FROM sales WHERE id = :id')->execute([':id' => $deleteId]);
            $message = 'মেমো ডিলিট করা হয়েছে।';
        }
    }
}

$sales = fetch_all('SELECT s.id, s.memo_no, s.total, s.discount, s.paid, s.created_at, c.name AS customer
    FROM sales s
    LEFT JOIN customers c ON c.id = s.customer_id
    ORDER BY s.created_at DESC');
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">মেমো তালিকা</h5>
        <a class="btn btn-primary" href="new_memo.php">নতুন মেমো</a>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="table-responsive mt-3">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>মেমো নং</th>
                    <th>কাস্টমার</th>
                    <th>মোট</th>
                    <th>ডিসকাউন্ট</th>
                    <th>পরিশোধ</th>
                    <th>তারিখ</th>
                    <th class="text-end">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">কোনো মেমো নেই।</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?= htmlspecialchars($sale['memo_no']) ?></td>
                            <td><?= htmlspecialchars($sale['customer'] ?? 'ওয়াক-ইন') ?></td>
                            <td><?= format_currency($currency, $sale['total']) ?></td>
                            <td><?= format_currency($currency, $sale['discount'] ?? 0) ?></td>
                            <td><?= format_currency($currency, $sale['paid']) ?></td>
                            <td><?= date('d/m/Y', strtotime($sale['created_at'])) ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="memo_print.php?memo=<?= urlencode($sale['memo_no']) ?>">ভিউ/প্রিন্ট</a>
                                <form method="post" class="d-inline" onsubmit="return confirm('মেমো ডিলিট করবেন?');">
                                    <input type="hidden" name="delete_id" value="<?= (int) $sale['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">ডিলিট</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
