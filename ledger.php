<?php
// Handle AJAX request FIRST before any output
if (!empty($_GET['ajax']) && !empty($_GET['product_id'])) {
    require __DIR__ . '/includes/header.php';
    
    $selectedProductId = (int)$_GET['product_id'];
    header('Content-Type: application/json; charset=utf-8');
    
    $entries = get_product_ledger_entries($selectedProductId);
    
    echo json_encode([
        'success' => true,
        'entries' => $entries
    ]);
    exit;
}

// Regular page load
$pageTitle = 'ইনভেন্টরি লেজার';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';

$products = fetch_all('SELECT id, name FROM products ORDER BY name ASC');

// For page load, initialize empty
$allEntries = [];
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">ইনভেন্টরি লেজার</h5>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <label class="form-label">পণ্য</label>
            <select class="form-select" name="product_id" id="productFilter">
                <option value="">সব পণ্য</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= (int) $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">তারিখ রেঞ্জ</label>
            <input type="text" class="form-control" id="dateRangePicker" />
        </div>
    </div>

    <div class="table-responsive mt-4">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>তারিখ</th>
                    <th>পণ্য</th>
                    <th>টাইপ</th>
                    <th>রেফারেন্স</th>
                    <th class="text-end">পরিমাণ</th>
                    <th class="text-end">ব্যালেন্স</th>
                    <th>নোট</th>
                </tr>
            </thead>
            <tbody id="ledgerTableBody">
                <tr class="empty-row">
                    <td colspan="7" class="text-center text-muted">পণ্য নির্বাচন করুন</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    jQuery(function($){
        const $productSelect = $('#productFilter');
        const $dateRangePicker = $('#dateRangePicker');
        const $tbody = $('#ledgerTableBody');
        let allEntries = [];

        // Convert Bangla numbers to English
        function convertBanglaToEnglish(str) {
            const banglaNumbers = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
            const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            let result = str;
            for (let i = 0; i < 10; i++) {
                result = result.replace(new RegExp(banglaNumbers[i], 'g'), englishNumbers[i]);
            }
            return result;
        }

        // Render entries
        function renderEntries(entries) {
            if (entries.length === 0) {
                $tbody.html('<tr class="empty-row"><td colspan="7" class="text-center text-muted">কোনো লেনদেন নেই।</td></tr>');
                return;
            }

            let html = '';
            entries.forEach(row => {
                const typeLabel = row.entry_type.charAt(0).toUpperCase() + row.entry_type.slice(1);
                let badge = 'bg-secondary';
                if (row.entry_type === 'purchase') badge = 'bg-info';
                else if (row.entry_type === 'sale') badge = 'bg-warning text-dark';
                else if (row.entry_type === 'initial') badge = 'bg-success';

                const txnDate = new Date(row.txn_date);
                const formattedDate = String(txnDate.getDate()).padStart(2, '0') + '/' + 
                                     String(txnDate.getMonth() + 1).padStart(2, '0') + '/' + 
                                     txnDate.getFullYear();

                html += `<tr data-product-id="${row.product_id}" data-txn-date="${row.txn_date}">
                    <td>${formattedDate}</td>
                    <td>${row.product_name || '—'}</td>
                    <td><span class="badge ${badge}">${typeLabel}</span></td>
                    <td>${row.reference || ''}</td>
                    <td class="text-end">${row.quantity}</td>
                    <td class="text-end fw-semibold">${row.balance}</td>
                    <td>${row.note || ''}</td>
                </tr>`;
            });

            $tbody.html(html);
        }

        // Filter entries by date range
        function filterByDateRange(entries) {
            const dateRangeVal = $dateRangePicker.val();
            let startDate = '';
            let endDate = '';
            
            if (dateRangeVal && dateRangeVal.trim() !== '') {
                const parts = dateRangeVal.split(' - ');
                if (parts.length === 2) {
                    startDate = convertBanglaToEnglish(parts[0].trim());
                    endDate = convertBanglaToEnglish(parts[1].trim());
                }
            }
            
            let filtered = entries;
            if (startDate && endDate) {
                filtered = entries.filter(row => {
                    const txnDate = row.txn_date;
                    return txnDate >= startDate && txnDate <= endDate;
                });
            }
            
            return filtered;
        }

        // Load data for selected product
        $productSelect.on('change', function() {
            const productId = $(this).val();
            
            if (!productId) {
                allEntries = [];
                renderEntries([]);
                return;
            }
            
            // Load from server via AJAX
            $.ajax({
                url: 'ledger.php',
                type: 'GET',
                data: {
                    product_id: productId,
                    ajax: 1
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        allEntries = response.entries;
                        const filtered = filterByDateRange(allEntries);
                        renderEntries(filtered);
                    }
                },
                error: function() {
                    alert('ডেটা লোড করতে সমস্যা হয়েছে');
                }
            });
        });

        // Initialize select2
        $productSelect.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'পণ্য নির্বাচন করুন',
            allowClear: true
        });

        // Initialize daterangepicker
        $dateRangePicker.daterangepicker({
            opens: 'left',
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: 'প্রয়োগ',
                cancelLabel: 'বাতিল',
                daysOfWeek: ['রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহ', 'শুক্র', 'শনি'],
                monthNames: ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর']
            }
        });

        // On date range apply, filter and render
        $dateRangePicker.on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            const filtered = filterByDateRange(allEntries);
            renderEntries(filtered);
        });

        // On cancel, clear filter
        $dateRangePicker.on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            renderEntries(allEntries);
        });
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
