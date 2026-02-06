<?php
$pageTitle = 'মেমো প্রিন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$memoQuery = trim($_GET['memo'] ?? '');
$memo = null;
$items = [];
$business = fetch_one('SELECT * FROM business_info ORDER BY id DESC LIMIT 1');

if ($memoQuery !== '') {
    $memo = fetch_one('SELECT s.id, s.memo_no, s.total, s.paid, s.created_at, c.name AS customer, c.phone AS customer_phone
        FROM sales s
        LEFT JOIN customers c ON c.id = s.customer_id
        WHERE s.memo_no = :memo
        LIMIT 1', [
        ':memo' => $memoQuery,
    ]);
    if ($memo) {
        $items = fetch_all('SELECT si.quantity, si.price, p.name AS product
            FROM sale_items si
            LEFT JOIN products p ON p.id = si.product_id
            WHERE si.sale_id = :sale_id', [
            ':sale_id' => $memo['id'],
        ]);
    }
}

$due = 0;
if ($memo) {
    $due = max((float) $memo['total'] - (float) $memo['paid'], 0);
}
?>
<div class="row g-4">
    <div class="col-lg-5">
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
                <button class="btn btn-outline-primary" type="button" onclick="window.print()">প্রিন্ট</button>
                <button class="btn btn-outline-secondary" type="button">PDF</button>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4" id="memoPrintArea">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-uppercase text-muted small">Sales Invoice</div>
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($business['business_name'] ?? $settings['app_name']) ?></h4>
                    <div class="text-muted small"><?= htmlspecialchars($business['address'] ?? '') ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($business['phone'] ?? '') ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($business['email'] ?? '') ?></div>
                </div>
                <div class="text-end">
                    <div class="fw-semibold">মেমো নং: <?= htmlspecialchars($memo['memo_no'] ?? '---') ?></div>
                    <div class="text-muted small">তারিখ: <?= $memo ? date('d M Y', strtotime($memo['created_at'])) : '---' ?></div>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <div class="fw-semibold">কাস্টমার</div>
                    <div><?= htmlspecialchars($memo['customer'] ?? 'ওয়াক-ইন') ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($memo['customer_phone'] ?? '') ?></div>
                </div>
                <div class="invoice-summary">
                    <div>মোট: <?= $memo ? format_currency($currency, $memo['total']) : '---' ?></div>
                    <div>পরিশোধ: <?= $memo ? format_currency($currency, $memo['paid']) : '---' ?></div>
                    <div class="fw-semibold">বাকি: <?= $memo ? format_currency($currency, $due) : '---' ?></div>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>পণ্য</th>
                            <th>পরিমাণ</th>
                            <th>দর</th>
                            <th>মোট</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$memo): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">মেমো নির্বাচন করুন।</td>
                            </tr>
                        <?php elseif (empty($items)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">কোনো পণ্য নেই।</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <?php $lineTotal = (float) $item['quantity'] * (float) $item['price']; ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product'] ?? '') ?></td>
                                    <td><?= (int) $item['quantity'] ?></td>
                                    <td><?= format_currency($currency, $item['price']) ?></td>
                                    <td><?= format_currency($currency, $lineTotal) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">প্রস্তুতকারী: <?= htmlspecialchars($_SESSION['user']['name'] ?? 'ম্যানেজার') ?></div>
                <div class="text-end">
                    <div class="fw-semibold">নেট মোট: <?= $memo ? format_currency($currency, $memo['total']) : '---' ?></div>
                    <div class="text-muted">ধন্যবাদ।</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
