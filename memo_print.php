<?php
$pageTitle = 'মেমো প্রিন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$memoQuery = trim($_GET['memo'] ?? '');
$memo = null;
if ($memoQuery !== '') {
    $memo = fetch_one('SELECT s.memo_no, s.total, s.created_at, c.name AS customer
        FROM sales s
        LEFT JOIN customers c ON c.id = s.customer_id
        WHERE s.memo_no = :memo
        LIMIT 1', [
        ':memo' => $memoQuery,
    ]);
}
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">মেমো খুঁজুন</h5>
            <form class="vstack gap-3" method="get">
                <input class="form-control" type="text" name="memo" value="<?= htmlspecialchars($memoQuery) ?>" placeholder="মেমো নম্বর লিখুন">
                <button class="btn btn-primary" type="submit">প্রিভিউ</button>
            </form>
        </div>
        <div class="card p-4 mt-4">
            <h6 class="section-title">প্রিন্ট অপশন</h6>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" type="button">প্রিন্ট</button>
                <button class="btn btn-outline-secondary" type="button">PDF</button>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <h5 class="section-title">মেমো প্রিভিউ</h5>
            <?php if (!$memo && $memoQuery !== ''): ?>
                <div class="alert alert-warning">এই মেমো নম্বরের কোনো তথ্য নেই।</div>
            <?php endif; ?>
            <div class="border p-3 rounded bg-body-secondary">
                <p class="fw-bold mb-1"><?= htmlspecialchars($settings['app_name']) ?></p>
                <p class="mb-1">মেমো: <?= htmlspecialchars($memo['memo_no'] ?? '---') ?></p>
                <p class="mb-1">কাস্টমার: <?= htmlspecialchars($memo['customer'] ?? '---') ?></p>
                <p class="mb-1">তারিখ: <?= $memo ? date('d M Y', strtotime($memo['created_at'])) : '---' ?></p>
                <hr>
                <p>মোট: <?= $memo ? format_currency($currency, $memo['total']) : '---' ?></p>
                <p class="mb-0">ধন্যবাদ!</p>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
