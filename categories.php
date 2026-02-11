<?php
$pageTitle = 'ক্যাটেগরি ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        [$pdo] = db_connection();
        if ($pdo) {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
            $stmt->execute([':name' => $name]);
            $message = 'ক্যাটেগরি যোগ হয়েছে।';
        }
    } else {
        $message = 'ক্যাটেগরি নাম দিন।';
    }
}

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
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form class="vstack gap-3" method="post">
                <input class="form-control" type="text" name="name" placeholder="ক্যাটেগরি নাম" required>
                <button class="btn btn-primary" type="submit">সংরক্ষণ করুন</button>
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
