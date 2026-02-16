<?php
$pageTitle = 'মেমো ম্যানেজমেন্ট';
require __DIR__ . '/includes/header.php';

$currency = $settings['currency'] ?? '৳';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    if ($deleteId > 0) {
        [$pdo] = db_connection();
        if ($pdo) {
            $pdo->prepare('DELETE FROM sale_items WHERE sale_id = :sale_id')->execute([':sale_id' => $deleteId]);
            $pdo->prepare('DELETE FROM sales WHERE id = :id')->execute([':id' => $deleteId]);
            $message = 'মেমো ডিলিট করা হয়েছে।';
        }
    }
}

// Get filter parameters
$today = date('Y-m-d');
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$customerId = (int) ($_GET['customer_id'] ?? 0);
$paymentStatus = $_GET['payment_status'] ?? '';
$memoSearchQuery = trim($_GET['memo_search'] ?? '');

// Get customers list for filter dropdown
$allCustomers = fetch_all('SELECT id, name FROM customers ORDER BY name ASC');

// Build dynamic SQL query based on filters
$queryConditions = ['1=1'];
$params = [];

// Date range filter
if ($startDate && $endDate) {
    $queryConditions[] = 's.created_at >= :start_date AND s.created_at <= :end_date';
    $params[':start_date'] = $startDate . ' 00:00:00';
    $params[':end_date'] = $endDate . ' 23:59:59';
}

// Customer filter
if ($customerId > 0) {
    $queryConditions[] = 's.customer_id = :customer_id';
    $params[':customer_id'] = $customerId;
}

// Payment status filter
if ($paymentStatus === 'paid') {
    $queryConditions[] = 's.total <= s.paid';
} elseif ($paymentStatus === 'unpaid') {
    $queryConditions[] = 's.paid = 0';
} elseif ($paymentStatus === 'partial') {
    $queryConditions[] = 's.paid > 0 AND s.total > s.paid';
}

// Memo number search
if ($memoSearchQuery !== '') {
    $queryConditions[] = 's.memo_no LIKE :memo_search';
    $params[':memo_search'] = '%' . $memoSearchQuery . '%';
}

$whereClause = implode(' AND ', $queryConditions);

$sales = fetch_all("SELECT s.id, s.memo_no, s.total, s.discount, s.paid, s.created_at, s.customer_id, c.name AS customer
    FROM sales s
    LEFT JOIN customers c ON c.id = s.customer_id
    WHERE $whereClause
    ORDER BY s.created_at DESC", $params);
?>
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h5 class="section-title mb-3 mb-md-0">মেমো তালিকা</h5>
        <a class="btn btn-primary" href="new_memo.php">নতুন মেমো</a>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="card border-0 bg-body-secondary p-3 mt-3">
        <form method="get" class="row g-3" id="filterForm">
            <div class="col-md-6 col-lg-4">
                <div class="d-flex gap-2">
                    <input type="text" class="form-control flex-grow-1" id="dateRangePicker" name="date_range"
                        placeholder="তারিখ সিলেক্ট করুন">
                    <button type="button" class="btn btn-sm btn-outline-secondary clear-filter"
                        data-target="dateRangePicker" title="ক্লিয়ার"><i class="bi bi-x"></i></button>
                </div>
            </div>
            <div class="col-md-6 col-lg-5">
                <div class="d-flex gap-2">
                    <select class="form-select" name="customer_id" id="customer_filter">
                        <option value=""></option>
                        <?php foreach ($allCustomers as $cust): ?>
                            <option value="<?= (int) $cust['id'] ?>" <?= $customerId === (int) $cust['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cust['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="d-flex gap-2">
                    <select class="form-select" name="payment_status" id="payment_status_filter">
                        <option value=""></option>
                        <option value="paid" <?= $paymentStatus === 'paid' ? 'selected' : '' ?>>পরিশোধিত</option>
                        <option value="unpaid" <?= $paymentStatus === 'unpaid' ? 'selected' : '' ?>>অপরিশোধিত</option>
                        <option value="partial" <?= $paymentStatus === 'partial' ? 'selected' : '' ?>>আংশিক পরিশোধিত
                        </option>
                    </select>
                </div>
            </div>
            <!-- <div class="col-12"></div> -->
        </form>
    </div>

    <!-- Results Summary -->
    <?php if (!empty($sales)): ?>
        <?php
        $totalSales = array_sum(array_column($sales, 'total'));
        $totalDiscount = array_sum(array_column($sales, 'discount'));
        $totalPaid = array_sum(array_column($sales, 'paid'));
        $totalDue = $totalSales - $totalPaid;
        ?>
        <div class="row g-3 mt-3">
            <div class="col-6 col-md-3">
                <div class="card p-2 bg-body-secondary text-center">
                    <div class="small text-muted">মোট বিক্রয়</div>
                    <div class="fw-bold"><?= format_currency($currency, $totalSales) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-2 bg-body-secondary text-center">
                    <div class="small text-muted">মোট ডিসকাউন্ট</div>
                    <div class="fw-bold"><?= format_currency($currency, $totalDiscount) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-2 bg-body-secondary text-center">
                    <div class="small text-muted">মোট পরিশোধ</div>
                    <div class="fw-bold"><?= format_currency($currency, $totalPaid) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-2 bg-body-secondary text-center">
                    <div class="small text-muted">মোট বকেয়া</div>
                    <div class="fw-bold text-danger"><?= format_currency($currency, $totalDue) ?></div>
                </div>
            </div>
        </div>
        <div class="text-muted small mt-2">মোট <?= count($sales) ?> টি মেমো</div>
    <?php endif; ?>
    <div class="table-responsive mt-3">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
            <div class="input-group w-50">
                <input type="text" id="tableSearch" class="form-control"
                    placeholder="টেবিলে খুঁজুন (মেমো বা কাস্টমার) ...">
                <button type="button" id="clearTableSearch" class="btn btn-outline-secondary">×</button>
            </div>
            <div class="text-muted small" id="visibleCount">মোট <span
                    id="visibleCountNumber"><?= count($sales) ?></span> টি মেমো দেখা যাচ্ছে</div>
        </div>
        <table id="salesTable" class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>মেমো নং</th>
                    <th>কাস্টমার</th>
                    <th>মোট</th>
                    <th>ডিসকাউন্ট</th>
                    <th>পরিশোধ</th>
                    <th>বকেয়া</th>
                    <th>অবস্থা</th>
                    <th>তারিখ</th>
                    <th class="text-end">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">কোনো মেমো নেই।</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sales as $sale): ?>
                        <?php $due = max((float) $sale['total'] - (float) $sale['paid'], 0); ?>
                        <tr data-memo="<?= htmlspecialchars($sale['memo_no']) ?>"
                            data-customer="<?= htmlspecialchars($sale['customer'] ?? 'ওয়াক-ইন') ?>"
                            data-customer-id="<?= (int) ($sale['customer_id'] ?? 0) ?>"
                            data-date="<?= htmlspecialchars(substr($sale['created_at'], 0, 10)) ?>"
                            data-paid="<?= (float) $sale['paid'] ?>" data-total="<?= (float) $sale['total'] ?>"
                            data-due="<?= $due ?>">
                            <td><?= htmlspecialchars($sale['memo_no']) ?></td>
                            <td><?= htmlspecialchars($sale['customer'] ?? 'ওয়াক-ইন') ?></td>
                            <td><?= format_currency($currency, $sale['total']) ?></td>
                            <td><?= format_currency($currency, $sale['discount'] ?? 0) ?></td>
                            <td><?= format_currency($currency, $sale['paid']) ?></td>
                            <td><?= format_currency($currency, $due) ?></td>
                            <td>
                                <?php if ($due == 0): ?>
                                    <span class="badge bg-success">পরিশোধিত</span>
                                <?php elseif ((float) $sale['paid'] == 0): ?>
                                    <span class="badge bg-danger">অপরিশোধিত</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">আংশিক</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($sale['created_at'])) ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary"
                                    href="memo_print.php?memo=<?= urlencode($sale['memo_no']) ?>">ভিউ/প্রিন্ট</a>
                                <form method="post" class="d-inline" onsubmit="return confirm('মেমো ডিলিট করবেন?');">
                                    <input type="hidden" name="delete_id" value="<?= (int) $sale['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">ডিলিট</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function () {
        function applyFilters() {
            var tableSearch = ($('#tableSearch').val() || '').toLowerCase().trim();
            var memoSearch = ($('#memo_search').val() || '').toLowerCase().trim();
            var customerFilter = $('#customer_filter').val();
            var paymentFilter = $('#payment_status_filter').val();
            var visible = 0;
            var start = '';
            var end = '';
            var dr = $('#dateRangePicker').data('daterangepicker');
            if ($('#dateRangePicker').val() && dr) {
                start = dr.startDate.format('YYYY-MM-DD');
                end = dr.endDate.format('YYYY-MM-DD');
            }

            $('#salesTable tbody tr').each(function () {
                var $tr = $(this);
                var memo = ($tr.data('memo') || '').toString().toLowerCase();
                var customer = ($tr.data('customer') || '').toString().toLowerCase();
                var date = ($tr.data('date') || '').toString().substr(0, 10);
                var paid = parseFloat($tr.data('paid') || 0);
                var total = parseFloat($tr.data('total') || 0);
                var due = parseFloat($tr.data('due') || 0);
                var custId = $tr.data('customerId') || '';
                var ok = true;

                if (tableSearch) {
                    if (memo.indexOf(tableSearch) === -1 && customer.indexOf(tableSearch) === -1) ok = false;
                }
                if (memoSearch) {
                    if (memo.indexOf(memoSearch) === -1) ok = false;
                }
                if (customerFilter && customerFilter !== '') {
                    if (parseInt(customerFilter) !== parseInt(custId)) ok = false;
                }
                if (paymentFilter && paymentFilter !== '') {
                    if (paymentFilter === 'paid' && due != 0) ok = false;
                    if (paymentFilter === 'unpaid' && paid != 0) ok = false;
                    if (paymentFilter === 'partial' && !(paid > 0 && total > paid)) ok = false;
                }
                if (start && end) {
                    if (!(date >= start && date <= end)) ok = false;
                }

                if (ok) { $tr.show(); visible++; } else { $tr.hide(); }
            });

            $('#visibleCountNumber').text(visible);
        }

        // Initialize date range picker (no default selection; input stays empty until user picks)
        $('#dateRangePicker').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'প্রয়োগ করুন',
                cancelLabel: 'বাতিল করুন',
                fromLabel: 'থেকে',
                toLabel: 'পর্যন্ত',
                daysOfWeek: ['রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহ', 'শুক্র', 'শনি'],
                monthNames: ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'],
                firstDay: 0
            },
            ranges: {
                'আজ': [moment(), moment()],
                'গত ৭ দিন': [moment().subtract(6, 'days'), moment()],
                'গত ৩০ দিন': [moment().subtract(29, 'days'), moment()],
                'এই মাসে': [moment().startOf('month'), moment().endOf('month')],
                'গত মাসে': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        // When user applies a range, update input and filter
        $('#dateRangePicker').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            applyFilters();
        });

        // When user cancels/clears the picker, clear input and filter
        $('#dateRangePicker').on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('');
            applyFilters();
        });

        // Initialize Select2 for live-searchable selects (if Select2 is available)
        if ($.fn.select2) {
            $('#customer_filter').select2({
                placeholder: 'কাস্টমার সিলেক্ট',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });
            $('#payment_status_filter').select2({
                placeholder: 'পেমেন্ট স্ট্যাটাস',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });
        }

        // Events
        $('#tableSearch').on('input', applyFilters);
        $('#clearTableSearch').on('click', function () { $('#tableSearch').val(''); applyFilters(); });
        $('#memo_search').on('input', applyFilters);
        $('#customer_filter, #payment_status_filter').on('change', applyFilters);
        $('.clear-filter').on('click', function () {
            var target = $(this).data('target');
            if (target === 'dateRangePicker') {
                $('#start_date_hidden').val('');
                $('#end_date_hidden').val('');
                $('#dateRangePicker').val('');
                var dr = $('#dateRangePicker').data('daterangepicker');
                if (dr) { dr.setStartDate(moment()); dr.setEndDate(moment()); }
            } else {
                var $el = $('#' + target);
                if ($el.length) {
                    if ($el.hasClass('select2-hidden-accessible')) { $el.val(null).trigger('change'); }
                    else if ($el.is('select')) { $el.val('').trigger('change'); }
                    else { $el.val(''); }
                }
            }
            applyFilters();
        });

        // initial apply
        applyFilters();
    });
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>