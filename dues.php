<?php
$pageTitle = 'ডিউ ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';

$filterCustomerId = (int) ($_GET['customer_id'] ?? 0);
$customers = fetch_all('SELECT id, name FROM customers ORDER BY name ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['collect_due'])) {
    $saleId = (int) ($_POST['sale_id'] ?? 0);
    $amount = (float) ($_POST['amount'] ?? 0);
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');

    if ($saleId > 0 && $amount > 0) {
        [$pdo] = db_connection();
        if ($pdo) {
            $sale = fetch_one('SELECT id, memo_no, paid, total, customer_id FROM sales WHERE id = :id', [':id' => $saleId]);
            if ($sale) {
                $newPaid = min((float) $sale['paid'] + $amount, (float) $sale['total']);
                $pdo->prepare('UPDATE sales SET paid = :paid WHERE id = :id')->execute([
                    ':paid' => $newPaid,
                    ':id' => $saleId,
                ]);
                if (!empty($sale['customer_id'])) {
                        // record payment in customer ledger instead of updating customers.due_balance
                        create_customer_ledger_entry((int) $sale['customer_id'], 'payment', (float) $amount, $paymentDate, 'Sale ' . $sale['memo_no'], 'Due collection for sale ' . $sale['memo_no']);
                }
                $pdo->prepare('INSERT INTO incomes (source, amount, income_date, note) VALUES (:source, :amount, :income_date, :note)')->execute([
                    ':source' => 'ডিউ পেমেন্ট',
                    ':amount' => $amount,
                    ':income_date' => $paymentDate,
                    ':note' => 'কাস্টমার বকেয়া পরিশোধ',
                ]);
                $message = 'ডিউ পরিশোধ আপডেট হয়েছে।';
            }
        }
    } else {
        $message = 'সঠিক তথ্য দিন।';
    }
}

$params = [];
$sql = 'SELECT s.id, s.memo_no, s.total, s.paid, s.created_at, c.name AS customer, c.id AS customer_id
    FROM sales s
    LEFT JOIN customers c ON c.id = s.customer_id
    WHERE s.total > s.paid';
if ($filterCustomerId > 0) {
    $sql .= ' AND s.customer_id = :customer_id';
    $params[':customer_id'] = $filterCustomerId;
}
$sql .= ' ORDER BY s.created_at DESC';
$dues = fetch_all($sql, $params);

// compute total outstanding for the displayed list
$totalOutstanding = 0.0;
foreach ($dues as $d) {
    $totalOutstanding += ((float) $d['total'] - (float) $d['paid']);
}
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="section-title mb-0">বকেয়া তালিকা</h5>
                <div class="badge bg-warning text-dark p-2">মোট বকেয়া: <strong id="totalOutstandingBadge"><?= format_currency($currency, $totalOutstanding) ?></strong></div>
            </div>
            <form class="row g-2 mb-3" method="get" id="duesFilterForm">
                <div class="col-auto">
                    <select class="form-select select2-customer" name="customer_id" id="customerFilter">
                        <option value="">সব কাস্টমার</option>
                        <?php foreach ($customers as $cust): ?>
                            <option value="<?= (int) $cust['id'] ?>" <?= $filterCustomerId === (int) $cust['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cust['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <script>
                jQuery(function($){
                    const $sel = $('.select2-customer');
                    const $tbody = $('table tbody');
                    const $badgeTotal = $('#totalOutstandingBadge');
                    const $footerTotal = $('#tableFooterTotal');
                    const $saleSelect = $('select[name="sale_id"]');
                    const currencySymbol = '<?= htmlspecialchars($currency) ?>';

                    function formatCurrency(amount) {
                        return currencySymbol + ' ' + parseFloat(amount).toFixed(2);
                    }

                    function loadDuesForCustomer(customerId) {
                        const params = {};
                        if (customerId) params.customer_id = customerId;
                        
                        $.ajax({
                            url: window.location.pathname,
                            type: 'GET',
                            data: params,
                            dataType: 'html',
                            success: function(html) {
                                // parse the returned HTML to extract table and total
                                const $temp = $('<div>').html(html);
                                const newTbody = $temp.find('table tbody')[0] ? $temp.find('table tbody')[0].innerHTML : '<tr><td colspan="4" class="text-center text-muted">কোনো বকেয়া নেই।</td></tr>';
                                const newSelect = $temp.find('select[name="sale_id"]')[0] ? $temp.find('select[name="sale_id"]')[0].innerHTML : '<option value="">মেমো নির্বাচন করুন</option>';
                                const newTotal = $temp.find('#tableFooterTotal').text() || currencySymbol + ' 0.00';
                                
                                // update table body
                                $tbody.html(newTbody);
                                // update badge total
                                $badgeTotal.text(newTotal);
                                // update footer total
                                $footerTotal.text(newTotal);
                                // update sale_id select
                                $saleSelect.html(newSelect);
                            }
                        });
                    }

                    $sel.select2({
                        theme: 'bootstrap-5',
                        width: '250px',
                        placeholder: 'সব কাস্টমার',
                        allowClear: true
                    });
                    
                    $sel.on('change', function(){
                        const id = $(this).val();
                        loadDuesForCustomer(id);
                    });
                });
            </script>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>কাস্টমার</th>
                            <th>মেমো</th>
                            <th>বকেয়া</th>
                            <th>তারিখ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dues)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">কোনো বকেয়া নেই।</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dues as $due): ?>
                                <?php $amount = (float) $due['total'] - (float) $due['paid']; ?>
                                <tr>
                                    <td><?= htmlspecialchars($due['customer'] ?? 'ওয়াক-ইন') ?></td>
                                    <td><?= htmlspecialchars($due['memo_no']) ?></td>
                                    <td><?= format_currency($currency, $amount) ?></td>
                                    <td><?= date('d/m/Y', strtotime($due['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-active fw-bold">
                            <td colspan="2" class="text-end">মোট =</td>
                            <td id="tableFooterTotal"><?= format_currency($currency, $totalOutstanding) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h5 class="section-title">ডিউ সংগ্রহ</h5>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form class="vstack gap-3" method="post">
                <input type="hidden" name="collect_due" value="1">
                <select class="form-select" name="sale_id" required>
                    <option value="">মেমো নির্বাচন করুন</option>
                    <?php foreach ($dues as $due): ?>
                        <?php $amount = (float) $due['total'] - (float) $due['paid']; ?>
                        <option value="<?= (int) $due['id'] ?>">
                            <?= htmlspecialchars($due['memo_no']) ?> - <?= htmlspecialchars($due['customer'] ?? 'ওয়াক-ইন') ?> (<?= format_currency($currency, $amount) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input class="form-control" type="number" step="0.01" name="amount" placeholder="পরিমাণ" required>
                <input class="form-control" type="date" name="payment_date" value="<?= date('Y-m-d') ?>">
                <button class="btn btn-primary" type="submit">সংরক্ষণ করুন</button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
