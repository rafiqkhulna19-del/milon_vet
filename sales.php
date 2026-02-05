<?php
$pageTitle = 'সেলস ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$sales = fetch_all('SELECT s.memo_no, s.total, c.name AS customer
    FROM sales s
    LEFT JOIN customers c ON c.id = s.customer_id
    ORDER BY s.created_at DESC
    LIMIT 5');
?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="section-title">সেলস এন্ট্রি</h5>
            <form class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">কাস্টমার</label>
                    <input class="form-control" type="text" placeholder="কাস্টমার নাম">
                </div>
                <div class="col-md-6">
                    <label class="form-label">মেমো নং</label>
                    <input class="form-control" type="text" placeholder="#MV-0001">
                </div>
                <div class="col-md-6">
                    <label class="form-label">পেমেন্ট পদ্ধতি</label>
                    <select class="form-select">
                        <option>ক্যাশ</option>
                        <option>ব্যাংক</option>
                        <option>মোবাইল ব্যাংকিং</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">মোট পরিমাণ</label>
                    <input class="form-control" type="number" placeholder="<?= $currency ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">নোট</label>
                    <textarea class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="button">সেলস সংরক্ষণ</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4">
            <h5 class="section-title">সাম্প্রতিক সেলস</h5>
            <ul class="list-group list-group-flush">
                <?php if (empty($sales)): ?>
                    <li class="list-group-item text-center text-muted">কোনো সেলস নেই।</li>
                <?php else: ?>
                    <?php foreach ($sales as $sale): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= htmlspecialchars($sale['memo_no']) ?></span>
                            <span><?= format_currency($currency, $sale['total']) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
