<?php
$pageTitle = 'ক্যাটেগরি ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$categories = fetch_all('SELECT c.id, c.name, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id, c.name
    ORDER BY c.id DESC');
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h5 class="section-title">নতুন ক্যাটেগরি</h5>
            <form class="vstack gap-3">
                <input class="form-control" type="text" placeholder="ক্যাটেগরি নাম">
                <button class="btn btn-primary" type="button">সংরক্ষণ করুন</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="section-title">ক্যাটেগরি তালিকা</h5>
            <ul class="list-group list-group-flush">
                <?php if (empty($categories)): ?>
                    <li class="list-group-item text-center text-muted">কোনো ক্যাটেগরি নেই।</li>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= htmlspecialchars($category['name']) ?></span>
                            <span class="badge bg-secondary"><?= (int) $category['product_count'] ?> আইটেম</span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
