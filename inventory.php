<?php
$pageTitle = 'ইনভেন্টরি';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';

$categories = fetch_all('SELECT id, name FROM categories ORDER BY name ASC');
$suppliers = fetch_all('SELECT id, name FROM suppliers ORDER BY name ASC');

// Load all products without filtering (filtering will be done client-side)
$products = fetch_all("SELECT p.id, p.name, p.stock, p.purchase_price, p.selling_price, c.id AS category_id, c.name AS category, s.id AS supplier_id, s.name AS supplier
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN suppliers s ON s.id = p.supplier_id
    ORDER BY p.id DESC");
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0"><i class="bi bi-box-seam"></i> স্টক তালিকা</h5>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-secondary">মোট <?= count($products) ?> টি পণ্য</span>
            <a href="add_product.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> নতুন পণ্য যোগ করুন</a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="row g-3 mb-3 mt-2">
        <div class="col-md-6">
            <!-- <label class="form-label fw-semibold"><i class="bi bi-search"></i> পণ্য অনুসংধান</label> -->
            <input type="text" class="form-control" id="productSearch" placeholder="পণ্যের নাম লিখুন...">
        </div>

        <div class="col-md-3">
            <!-- <label class="form-label fw-semibold">ক্যাটেগরি</label> -->
            <select class="form-select" id="filterCategory">
                <option value="">সব ক্যাটেগরি</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <!-- <label class="form-label fw-semibold">সাপ্লায়ার</label> -->
            <select class="form-select" id="filterSupplier">
                <option value="">সব সাপ্লায়ার</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= (int) $supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Product Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>পণ্য</th>
                    <th>ক্যাটেগরি</th>
                    <th>সাপ্লায়ার</th>
                    <th class="text-center">স্টক</th>
                    <th class="text-end">ক্রয় মূল্য</th>
                    <th class="text-end">বিক্রয় মূল্য</th>
                    <th class="text-center">স্ট্যাটাস</th>
                    <th class="text-center">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody id="productsTableBody">
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">কোনো পণ্য নেই। <a href="add_product.php">নতুন পণ্য যোগ করুন</a>।</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $stockValue = (int) $product['stock'];
                        $status = 'ইন স্টক';
                        $badge = 'bg-success';
                        if ($stockValue <= 5) {
                            $status = 'রিস্টক প্রয়োজন';
                            $badge = 'bg-danger';
                        } elseif ($stockValue <= 10) {
                            $status = 'কম স্টক';
                            $badge = 'bg-warning text-dark';
                        }
                        ?>
                        <tr data-product-name="<?= htmlspecialchars($product['name']) ?>" data-category-id="<?= (int) ($product['category_id'] ?? 0) ?>" data-supplier-id="<?= (int) ($product['supplier_id'] ?? 0) ?>">
                            <td><a href="product_ledger.php?product_id=<?= (int) $product['id'] ?>" class="text-decoration-none fw-semibold"><?= htmlspecialchars($product['name']) ?></a></td>
                            <td><span><?= htmlspecialchars($product['category'] ?? '—') ?></span></td>
                            <td><?= htmlspecialchars($product['supplier'] ?? '—') ?></td>
                            <td class="text-center"><strong><?= $stockValue ?></strong></td>
                            <td class="text-end"><?= format_currency($currency, $product['purchase_price']) ?></td>
                            <td class="text-end"><?= format_currency($currency, $product['selling_price']) ?></td>
                            <td class="text-center"><span class="badge <?= $badge ?>"><?= $status ?></span></td>
                            <td class="text-center">
                                <a href="product_ledger.php?product_id=<?= (int) $product['id'] ?>" class="btn btn-sm btn-outline-secondary" title="লেজার দেখুন"><i class="bi bi-graph-up"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    jQuery(function($){
        // Client-side product filtering
        function filterProducts() {
            const searchText = $('#productSearch').val().toLowerCase();
            const categoryId = $('#filterCategory').val();
            const supplierId = $('#filterSupplier').val();

            let visibleCount = 0;

            // Only process rows that have product data attributes
            $('#productsTableBody tr[data-product-name]').each(function() {
                const $row = $(this);
                const productName = $row.data('product-name').toLowerCase();
                const rowCategoryId = String($row.data('category-id'));
                const rowSupplierId = String($row.data('supplier-id'));

                let matchesSearch = productName.includes(searchText);
                let matchesCategory = categoryId === '' || rowCategoryId === categoryId;
                let matchesSupplier = supplierId === '' || rowSupplierId === supplierId;

                if (matchesSearch && matchesCategory && matchesSupplier) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });

            // Show "no results" message if no products match
            if (visibleCount === 0) {
                if ($('#noResultsRow').length === 0) {
                    $('#productsTableBody').append('<tr id="noResultsRow"><td colspan="8" class="text-center text-muted py-4">কোনো পণ্য পাওয়া যায়নি।</td></tr>');
                }
            } else {
                $('#noResultsRow').remove();
            }
        }

        // Trigger filter on input change
        $('#productSearch').on('keyup', function() {
            filterProducts();
        });

        $('#filterCategory').on('change', function() {
            filterProducts();
        });

        $('#filterSupplier').on('change', function() {
            filterProducts();
        });
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
