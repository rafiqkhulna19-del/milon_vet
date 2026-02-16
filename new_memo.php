<?php
$pageTitle = 'নতুন মেমো';

// Handle AJAX POST before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    
    require __DIR__ . '/config.php';
    require __DIR__ . '/includes/functions.php';
    
    $customerId = (int) ($_POST['customer_id'] ?? 0);
    $memoNo = trim($_POST['memo_no'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $paid = (float) ($_POST['paid'] ?? 0);
    $discount = (float) ($_POST['discount'] ?? 0);

    $productIds = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['price'] ?? [];

    $items = [];
    $usedProducts = [];
    $hasDuplicate = false;
    
    foreach ($productIds as $index => $productId) {
        $productId = (int) $productId;
        $quantity = (int) ($quantities[$index] ?? 0);
        $price = (float) ($prices[$index] ?? 0);
        
        if ($productId > 0) {
            if (in_array($productId, $usedProducts, true)) {
                $hasDuplicate = true;
                break;
            }
            $usedProducts[] = $productId;
        }
        
        if ($productId > 0 && $quantity > 0 && $price > 0) {
            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
            ];
        }
    }

    if ($hasDuplicate) {
        echo json_encode([
            'success' => false,
            'message' => 'এই পণ্যটি ইতিমধ্যে যুক্ত করা হয়েছে।'
        ]);
        exit;
    }
    
    if ($memoNo === '' || empty($items)) {
        echo json_encode([
            'success' => false,
            'message' => 'সব তথ্য ঠিকভাবে পূরণ করুন।'
        ]);
        exit;
    }
    
    $result = save_memo($customerId, $memoNo, $items, $discount, $paid, $paymentMethod);
    echo json_encode($result);
    exit;
}

// Page load - include header
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';

$customers = fetch_all('SELECT id, name, phone, address FROM customers ORDER BY name ASC');
$products = fetch_all('SELECT id, name, selling_price, stock FROM products ORDER BY name ASC');
$topProducts = fetch_all("
    SELECT p.id, p.name, p.selling_price, p.stock, COALESCE(SUM(si.quantity), 0) AS sold_qty
    FROM products p
    LEFT JOIN sale_items si ON si.product_id = p.id
    GROUP BY p.id, p.name, p.selling_price, p.stock
    ORDER BY sold_qty DESC, p.name ASC
    LIMIT 6
");

$memoSeedRow = fetch_one('SELECT COUNT(*) AS total FROM sales');
$memoSeed = (int) ($memoSeedRow['total'] ?? 0) + 1;
$generatedMemo = 'MV-' . date('Ymd') . '-' . str_pad((string) $memoSeed, 4, '0', STR_PAD_LEFT);
?>
<div class="row g-4">
    <div class="col-lg-9">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="section-title">নতুন মেমো</h5>
                <span class="text-muted">মেমো নং: <strong id="memoPreview"><?= htmlspecialchars($generatedMemo) ?></strong></span>
            </div>
            <form class="row g-3" method="post" id="memoForm">
                <input type="hidden" name="memo_no" value="<?= htmlspecialchars($generatedMemo) ?>" id="memoNoField">
                <div class="col-md-6">
                    <label class="form-label">কাস্টমার</label>
                    <select class="form-select" name="customer_id" id="customerSelect">
                        <option value="">ওয়াক-ইন</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= (int) $customer['id'] ?>"
                                data-phone="<?= htmlspecialchars($customer['phone'] ?? '') ?>"
                                data-address="<?= htmlspecialchars($customer['address'] ?? '') ?>">
                                <?= htmlspecialchars($customer['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">পেমেন্ট পদ্ধতি</label>
                    <select class="form-select" name="payment_method">
                        <option value="ক্যাশ">ক্যাশ</option>
                        <option value="ব্যাংক">ব্যাংক</option>
                        <option value="মোবাইল ব্যাংকিং">মোবাইল ব্যাংকিং</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="card border-0 bg-body-tertiary p-3 mb-3">
                        <div class="row g-3 align-items-end" id="addItemForm">
                            <div class="col-md-5">
                                <label class="form-label">পণ্য</label>
                                <select class="form-select" id="addProductSelect">
                                    <option value="">পণ্য সিলেক্ট করুন</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= (int) $product['id'] ?>"
                                            data-price="<?= htmlspecialchars($product['selling_price']) ?>"
                                            data-stock="<?= (int) $product['stock'] ?>">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">স্টক</label>
                                <input class="form-control" type="text" id="addStock" value="0" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">পরিমাণ</label>
                                <input class="form-control" type="number" id="addQty" min="1" value="1">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">দাম</label>
                                <input class="form-control" type="number" step="1" id="addPrice">
                            </div>
                            <div class="col-md-1 d-grid">
                                <button class="btn btn-primary" type="button" id="addItemBtn">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="memoItems">
                            <thead>
                                <tr>
                                    <th>পণ্য</th>
                                    <th>স্টক</th>
                                    <th style="width: 120px;">পরিমাণ</th>
                                    <th style="width: 140px;">দাম</th>
                                    <th style="width: 140px;">মোট</th>
                                    <th style="width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="empty-row">
                                    <td colspan="6" class="text-center text-muted">পণ্য যোগ করুন</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">সাব টোটাল</label>
                    <input class="form-control" type="text" id="subTotal" value="0" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ডিসকাউন্ট</label>
                    <input class="form-control" type="number" step="0.01" name="discount" id="discountAmount" value="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">রাউন্ডিং</label>
                    <input class="form-control" type="text" id="roundingAmount" value="0" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">নীট মোট</label>
                    <input class="form-control" type="text" id="grandTotal" value="0" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">পরিশোধ</label>
                    <input class="form-control" type="number" step="0.01" name="paid" id="paidAmount" placeholder="<?= $currency ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">বাকি</label>
                    <input class="form-control" type="text" id="dueAmount" value="0" readonly>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">মেমো সংরক্ষণ</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card p-4">
            <h6 class="section-title">টপ সেলিং</h6>
            <?php if (!empty($topProducts)): ?>
                <div class="d-grid gap-2">
                    <?php foreach ($topProducts as $product): ?>
                        <button class="btn btn-outline-secondary btn-sm text-start top-product" type="button"
                            data-id="<?= (int) $product['id'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>"
                            data-price="<?= htmlspecialchars($product['selling_price']) ?>"
                            data-stock="<?= (int) $product['stock'] ?>">
                            <?= htmlspecialchars($product['name']) ?> । <?= htmlspecialchars($product['selling_price']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="small text-muted mb-0">ডাটা নেই</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    jQuery(function ($) {
        const $memoForm = $('#memoForm');
        const $memoItems = $('#memoItems');
        const $addItemForm = $('#addItemForm');
        const $addProductSelect = $('#addProductSelect');
        const $addQty = $('#addQty');
        const $addPrice = $('#addPrice');
        const $addStock = $('#addStock');
        const $addItemBtn = $('#addItemBtn');
        const $subTotal = $('#subTotal');
        const $discountAmount = $('#discountAmount');
        const $roundingAmount = $('#roundingAmount');
        const $grandTotal = $('#grandTotal');
        const $paidAmount = $('#paidAmount');
        const $dueAmount = $('#dueAmount');
        const $topProductButtons = $('.top-product');
        const $customerSelect = $('#customerSelect');

        const formatCustomerOption = (customer) => {
            if (!customer.id) return customer.text;
            const phone = $(customer.element).data('phone') || '';
            const address = $(customer.element).data('address') || '';
            return $(
                `<div class="d-flex flex-column">
                    <span class="fw-semibold">${customer.text}</span>
                    <span class="text-muted small">${phone}${address ? ' • ' + address : ''}</span>
                </div>`
            );
        };

        const formatCustomerSelection = (customer) => {
            if (!customer.id) return customer.text;
            const address = $(customer.element).data('address') || '';
            return $(
                `<span>${customer.text}${address ? `<span class="text-muted small">\n${address}</span>` : ''}</span>`
            );
        };

        $customerSelect.select2({
            width: '100%',
            templateResult: formatCustomerOption,
            templateSelection: formatCustomerSelection,
        });

        $addProductSelect.select2({
            width: '100%'
        });

        const updateTotals = () => {
            let subtotal = 0;
            $memoItems.find('tbody tr').each(function () {
                const $row = $(this);
                if ($row.hasClass('empty-row')) return;
                const qty = parseFloat($row.find('.qty-input').val() || 0);
                const price = parseFloat($row.find('.price-input').val() || 0);
                const lineTotal = qty * price;
                $row.find('.line-total').text(lineTotal.toFixed(2));
                subtotal += lineTotal;
            });
            $subTotal.val(subtotal.toFixed(2));
            let discount = parseFloat($discountAmount.val() || 0);
            if (discount < 0) discount = 0;
            if (discount > subtotal) discount = subtotal;
            if (discount !== parseFloat($discountAmount.val() || 0)) {
                $discountAmount.val(discount.toFixed(2));
            }
            const net = subtotal - discount;
            const rounding = Math.round(net) - net;
            const total = net + rounding;
            $roundingAmount.val(rounding.toFixed(2));
            $grandTotal.val(total.toFixed(2));
            const paid = parseFloat($paidAmount.val() || 0);
            $dueAmount.val(Math.max(total - paid, 0).toFixed(2));
        };

        const isDuplicateProduct = (selectedValue, $currentRow) => {
            let isDuplicate = false;
            $memoItems.find('input[name="product_id[]"]').each(function () {
                if ($currentRow && this === $currentRow.find('input[name="product_id[]"]')[0]) return;
                if (this.value === selectedValue && selectedValue !== '') {
                    isDuplicate = true;
                    return false;
                }
            });
            return isDuplicate;
        };

        const bindRow = ($row) => {
            const $priceInput = $row.find('.price-input');
            const $qtyInput = $row.find('.qty-input');
            const $removeBtn = $row.find('.remove-row');

            $priceInput.add($qtyInput).on('input', updateTotals);
            $removeBtn.on('click', () => {
                if ($memoItems.find('tbody tr').length > 1) {
                    $row.remove();
                    updateTotals();
                    toggleEmptyRow();
                }
            });
        };

        const toggleEmptyRow = () => {
            const $body = $memoItems.find('tbody');
            const hasRows = $body.find('tr:not(.empty-row)').length > 0;
            $body.find('.empty-row').toggleClass('d-none', hasRows);
        };

        const addRow = (productId, name, stock, price, qty = 1) => {
            if (isDuplicateProduct(String(productId))) {
                Swal.fire('দুঃখিত', 'এই পণ্য ইতিমধ্যে যুক্ত করা হয়েছে।', 'warning');
                return;
            }
            const $row = $(
                `<tr>
                    <td>
                        ${name}
                        <input type="hidden" name="product_id[]" value="${productId}">
                    </td>
                    <td>${stock}</td>
                    <td><input class="form-control qty-input" type="number" name="quantity[]" min="1" value="${qty}"></td>
                    <td><input class="form-control price-input" type="number" step="1" name="price[]" value="${price}"></td>
                    <td class="line-total">0</td>
                    <td><button class="btn btn-outline-danger btn-sm remove-row" type="button">×</button></td>
                </tr>`
            );
            $memoItems.find('tbody').append($row);
            bindRow($row);
            toggleEmptyRow();
            updateTotals();
        };

        $addProductSelect.on('change', function () {
            const option = this.options[this.selectedIndex];
            if (!option || !option.value) {
                $addStock.val(0);
                $addPrice.val('');
                return;
            }
            $addStock.val(option.dataset.stock || 0);
            $addPrice.val(option.dataset.price || '');
        });

        $addItemBtn.on('click', () => {
            const option = $addProductSelect[0].options[$addProductSelect[0].selectedIndex];
            const productId = option?.value;
            if (!productId) {
                Swal.fire('পণ্য সিলেক্ট করুন', 'যোগ করার আগে পণ্য সিলেক্ট করুন।', 'warning');
                return;
            }
            const qty = parseFloat($addQty.val() || 0);
            const price = parseFloat($addPrice.val() || 0);
            if (qty <= 0 || price <= 0) {
                Swal.fire('ভুল ইনপুট', 'পরিমাণ ও দাম সঠিকভাবে দিন।', 'warning');
                return;
            }
            addRow(productId, option.text, $addStock.val(), price, qty);
            $addProductSelect.val('').trigger('change');
            $addQty.val(1);
            $addPrice.val('');
            $addStock.val(0);
            $addProductSelect.select2('open');
        });

        $topProductButtons.on('click', function () {
            const productId = $(this).data('id');
            const name = $(this).data('name');
            const price = $(this).data('price');
            const stock = $(this).data('stock');
            addRow(productId, name, stock, price);
        });

        $paidAmount.on('input', updateTotals);
        $discountAmount.on('input', updateTotals);
        updateTotals();

        // Handle form submission for memo save
        $memoForm.on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            $.ajax({
                url: window.location.pathname,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'সফল!',
                            html: `মেমো <strong>${response.memo_no}</strong> সংরক্ষণ হয়েছে।`,
                            icon: 'success',
                            confirmButtonText: 'ঠিক আছে',
                            showDenyButton: true,
                            denyButtonText: 'প্রিন্ট করুন',
                        }).then((result) => {
                            if (result.isDenied) {
                                window.location.href = 'memo_print.php?memo=' + encodeURIComponent(response.memo_no);
                            } else {
                                // Reload the page to reset the form and show the new memo number
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('ত্রুটি', response.message || 'কোনো সমস্যা হয়েছে।', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', {status: xhr.status, statusText: xhr.statusText, responseText: xhr.responseText, error: error});
                    let errorMsg = 'সার্ভার সংযোগ ব্যর্থ।';
                    if (xhr.responseText) {
                        try {
                            const resp = JSON.parse(xhr.responseText);
                            errorMsg = resp.message || errorMsg;
                        } catch (e) {
                            errorMsg = `Error (${xhr.status}): ${xhr.responseText.substring(0, 100)}`;
                        }
                    }
                    Swal.fire('ত্রুটি', errorMsg, 'error');
                }
            });
        });
    });
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
