<?php
$pageTitle = 'নতুন পণ্য যোগ করুন';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';
$messageType = 'info';

$categories = fetch_all('SELECT id, name FROM categories ORDER BY name ASC');
$suppliers = fetch_all('SELECT id, name FROM suppliers ORDER BY name ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $supplierId = (int) ($_POST['supplier_id'] ?? 0);
    $purchasePrice = (float) ($_POST['purchase_price'] ?? 0);
    $sellingPrice = (float) ($_POST['selling_price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);

    if ($name !== '' && $purchasePrice > 0 && $sellingPrice > 0) {
        [$pdo] = db_connection();
        if ($pdo) {
            try {
                // Insert product
                $stmt = $pdo->prepare('INSERT INTO products (name, category_id, supplier_id, purchase_price, selling_price, stock) VALUES (:name, :category_id, :supplier_id, :purchase_price, :selling_price, :stock)');
                $stmt->execute([
                    ':name' => $name,
                    ':category_id' => $categoryId ?: null,
                    ':supplier_id' => $supplierId ?: null,
                    ':purchase_price' => $purchasePrice,
                    ':selling_price' => $sellingPrice,
                    ':stock' => $stock,
                ]);
                $productId = $pdo->lastInsertId();
                
                // Create initial inventory_ledger entry if stock > 0
                if ($stock > 0) {
                    $ledgerStmt = $pdo->prepare('INSERT INTO inventory_ledger (product_id, entry_type, quantity, txn_date, reference, note) VALUES (:product_id, :entry_type, :quantity, :txn_date, :reference, :note)');
                    $ledgerStmt->execute([
                        ':product_id' => $productId,
                        ':entry_type' => 'initial',
                        ':quantity' => $stock,
                        ':txn_date' => date('Y-m-d'),
                        ':reference' => 'Initial Stock',
                        ':note' => 'Initial stock entry for ' . $name,
                    ]);
                }
                
                $message = 'পণ্য সফলভাবে যোগ হয়েছে। ১ সেকেন্ডে ফিরিয়ে নেওয়া হবে...';
                $messageType = 'success';
                // Redirect after 1 second
                echo '<script>setTimeout(() => { window.location.href = "inventory.php"; }, 1000);</script>';
            } catch (Exception $e) {
                $message = 'ত্রুটি: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    } else {
        $message = 'ত্রুটি: পণ্যের নাম এবং উভয় মূল্য প্রয়োজন (ক্রয় মূল্য > ০, বিক্রয় মূল্য > ০)।';
        $messageType = 'danger';
    }
}
?>
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="section-title mb-1"><i class="bi bi-plus-lg"></i> নতুন পণ্য যোগ করুন</h5>
            <small class="text-secondary">সমস্ত প্রয়োজনীয় তথ্য পূরণ করুন এবং সংরক্ষণ করুন</small>
        </div>
        <a href="inventory.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> ফিরে যান</a>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Product Form -->
    <form method="post" class="needs-validation" novalidate>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">পণ্যের নাম *</label>
                <input class="form-control" type="text" name="name" placeholder="উদাহরণ: প্যারাসিটামল ৫০০ মিগ্রা" required>
                <small class="form-text text-secondary">পণ্যের সম্পূর্ণ নাম এবং স্পেসিফিকেশন</small>
                <div class="invalid-feedback">পণ্যের নাম প্রয়োজন।</div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label fw-semibold">ক্যাটেগরি</label>
                <select class="form-select select2-category" name="category_id">
                    <option value="">ক্যাটেগরি নির্বাচন করুন</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-secondary">পণ্যটি কোন ক্যাটেগরিতে পড়ে</small>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">সাপ্লায়ার</label>
                <select class="form-select select2-supplier" name="supplier_id">
                    <option value="">সাপ্লায়ার নির্বাচন করুন</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= (int) $supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-secondary">পণ্যের প্রধান সরবরাহকারী</small>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">ক্রয় মূল্য *</label>
                <div class="input-group">
                    <input class="form-control" type="number" step="0.01" name="purchase_price" placeholder="0.00" min="0.01" required>
                    <span class="input-group-text"><?= htmlspecialchars($currency) ?></span>
                </div>
                <small class="form-text text-secondary">সরবরাহকারীর কাছ থেকে ক্রয়ের মূল্য</small>
                <div class="invalid-feedback">ক্রয় মূল্য প্রয়োজন এবং ০ এর চেয়ে বেশি হতে হবে।</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">বিক্রয় মূল্য *</label>
                <div class="input-group">
                    <input class="form-control" type="number" step="0.01" name="selling_price" placeholder="0.00" min="0.01" required>
                    <span class="input-group-text"><?= htmlspecialchars($currency) ?></span>
                </div>
                <small class="form-text text-secondary">গ্রাহকদের কাছে বিক্রয়ের মূল্য</small>
                <div class="invalid-feedback">বিক্রয় মূল্য প্রয়োজন এবং ০ এর চেয়ে বেশি হতে হবে।</div>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">ইনিশিয়াল স্টক</label>
                <input class="form-control" type="number" name="stock" placeholder="0" min="0" value="0">
                <small class="form-text text-secondary">পণ্য যোগ করার সময় প্রাথমিক স্টক পরিমাণ (পরে পরিবর্তন করা যাবে)</small>
            </div>

            <div class="col-12">
                <hr class="my-3">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check"></i> পণ্য সংরক্ষণ করুন
                    </button>
                    <a href="inventory.php" class="btn btn-outline-secondary">বাতিল</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    jQuery(function($){
        // Initialize select2 for form
        $('.select2-category').select2({
            theme: 'bootstrap-5',
            placeholder: 'ক্যাটেগরি নির্বাচন করুন',
            allowClear: true,
            width: '100%'
        });

        $('.select2-supplier').select2({
            theme: 'bootstrap-5',
            placeholder: 'সাপ্লায়ার নির্বাচন করুন',
            allowClear: true,
            width: '100%'
        });

        // Enable Bootstrap form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach((form) => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
