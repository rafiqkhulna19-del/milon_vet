<?php
$pageTitle = 'মেমো প্রিন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$memoQuery = trim($_GET['memo'] ?? '');
$memo = null;
$items = [];
$business = fetch_one('SELECT * FROM business_info ORDER BY id DESC LIMIT 1');

if ($memoQuery !== '') {
    $memo = fetch_one('SELECT s.id, s.memo_no, s.subtotal, s.discount, s.rounding, s.total, s.paid, s.created_at, c.name AS customer, c.phone AS customer_phone, c.address AS customer_address
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
<div class="card p-4" id="memoPrintArea">
    <div class="invoice-header d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($business['logo_url'])): ?>
                <img src="<?= htmlspecialchars($business['logo_url']) ?>" alt="<?= htmlspecialchars($business['business_name'] ?? '') ?>" style="height: 64px; width: 64px; object-fit: contain;">
            <?php else: ?>
                <div class="rounded-circle bg-body-secondary d-flex align-items-center justify-content-center" style="height: 64px; width: 64px;">
                    <span class="fw-bold">MV</span>
                </div>
            <?php endif; ?>
            <div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($business['business_name'] ?? $settings['app_name']) ?></h4>
                <div class="text-muted small"><?= htmlspecialchars($business['address'] ?? '') ?></div>
                <div class="text-muted small"><?= htmlspecialchars($business['phone'] ?? '') ?></div>
                <div class="text-muted small"><?= htmlspecialchars($business['email'] ?? '') ?></div>
            </div>
        </div>
        <div class="text-end invoice-meta">
            <div class="invoice-badge">Sales Invoice</div>
            <div class="fw-semibold">মেমো নং: <?= htmlspecialchars($memo['memo_no'] ?? '---') ?></div>
            <div class="text-muted small">তারিখ: <?= $memo ? date('d M Y', strtotime($memo['created_at'])) : '---' ?></div>
        </div>
    </div>
    <hr>
    <div class="invoice-body d-flex justify-content-between flex-wrap gap-3">
        <div class="invoice-block customer-block">
            <div class="customer-row customer-row-top">
                <span class="label text-muted">নাম</span>
                <span class="value"><?= htmlspecialchars($memo['customer'] ?? 'ওয়াক-ইন') ?></span>
                <span class="label phone-value">মোবাইল</span>
                <span class="value "><?= htmlspecialchars($memo['customer_phone'] ?? '-') ?></span>
            </div>
            <div class="customer-row">
                <span class="label text-muted">ঠিকানা</span>
                <span class="value"><?= htmlspecialchars($memo['customer_address'] ?? '-') ?></span>
            </div>
        </div>
    </div>
    <div class="table-responsive mt-3">
        <table class="table table-bordered invoice-table">
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
                        <td colspan="4" class="text-center text-muted">মেমো পাওয়া যায়নি।</td>
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
    <div class="invoice-summary-wrap d-flex justify-content-between flex-wrap mt-3">
        <div class="invoice-notes">
            <div class="invoice-words fw-semibold">নীট টাকা কথায়: <?= $memo ? format_bangla_amount_in_words((float) $memo['total']) : '---' ?></div>
            <div class="invoice-note-details">
                <div class="text-muted small">প্রস্তুতকারী: <?= htmlspecialchars($_SESSION['user']['name'] ?? 'ম্যানেজার') ?></div>
                <!-- <div class="text-muted small">সামান্য পরিবর্তন/ফেরত নীতি প্রযোজ্য</div> -->
            </div>
        </div>
        <div class="invoice-summary d-flex gap-3">
            <table class="table table-sm invoice-summary-table">
                <tbody>
                    <tr>
                        <th>সাব টোটাল</th>
                        <td class="text-end"><?= $memo ? format_currency($currency, $memo['subtotal'] ?? 0) : '---' ?></td>
                    </tr>
                    <tr>
                        <th>ডিসকাউন্ট</th>
                        <td class="text-end"><?= $memo ? format_currency($currency, $memo['discount'] ?? 0) : '---' ?></td>
                    </tr>
                    <tr>
                        <th>রাউন্ডিং</th>
                        <td class="text-end"><?= $memo ? format_currency($currency, $memo['rounding'] ?? 0) : '---' ?></td>
                    </tr>
                    <tr class="fw-semibold">
                        <th>নীট মোট</th>
                        <td class="text-end"><?= $memo ? format_currency($currency, $memo['total']) : '---' ?></td>
                    </tr>
                </tbody>
            </table>
            <table class="table table-sm invoice-summary-table">
                <tbody>
                    <tr class="fw-semibold">
                        <th>নীট মোট</th>
                        <td class="text-end"><?= $memo ? format_currency($currency, $memo['total']) : '---' ?></td>
                    </tr>
                    <tr>
                        <th>পরিশোধ</th>
                        <td class="text-end"><?= $memo ? format_currency($currency, $memo['paid']) : '---' ?></td>
                    </tr>
                    <tr class="fw-semibold">
                        <th>বাকি</th>
                        <td class="text-end"><?= $memo ? format_currency($currency, $due) : '---' ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="invoice-signatures d-flex justify-content-between mt-4">
        <div class="signature-block text-center">
            <div class="signature-line"></div>
            <div class="text-muted small">ক্রেতার স্বাক্ষর</div>
        </div>
        <div class="signature-block text-center">
            <div class="signature-line"></div>
            <div class="text-muted small">বিক্রেতার স্বাক্ষর</div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
