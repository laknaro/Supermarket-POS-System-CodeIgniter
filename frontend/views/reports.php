<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Critical Inline Styles to Prevent FOUC & Page Flash -->
    <style>
      :root { color-scheme: light !important; }
      html, body {
        background-color: #F8FAF8 !important;
        color: #1e293b !important;
        margin: 0 !important;
        padding: 0 !important;
      }
      aside { background-color: #cfe3cb !important; }
      aside a, aside a *, aside span, aside i { color: #1e3a2b !important; }
      aside a[class*="bg-white"], aside a[class*="bg-white"] * { background-color: #ffffff !important; color: #14281e !important; }
      aside a:hover, aside a:active, aside a:focus { background-color: #b8d3b3 !important; color: #14281e !important; outline: none !important; }
      @page { size: 80mm auto; margin: 0; }
    </style>
    <!-- Tailwind CSS Config & CDN -->
    <script>
      (function() {
        const origWarn = console.warn;
        console.warn = function(...args) {
          if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com should not be used in production')) return;
          origWarn.apply(console, args);
        };
      })();
      window.tailwind = {
        darkMode: 'class'
      };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="flex h-screen overflow-hidden text-slate-800 bg-[#F8FAF8] font-sans">

    <!-- Reusable Sidebar Component -->
    <?= $this->include('includes/sidebar') ?>

    <!-- Main Workspace Area -->
    <main class="flex-1 flex flex-col h-full overflow-y-auto min-w-0">

        <!-- Top Navigation Sub-Header -->
        <header class="bg-white/80 backdrop-blur-sm border-b border-[#bcd3b8]/60 px-8 py-3.5 flex justify-between items-center text-xs shrink-0 sticky top-0 z-20">
            <div class="flex items-center gap-2 font-medium text-slate-500">
                <span class="font-bold text-slate-700">EGOTECH WORLD</span>
                <span>/</span>
                <span class="text-[#2d6a4f] bg-[#eaf4ec] px-2 py-0.5 rounded font-semibold border border-[#a3c9a8]/40">Reports & Analytics</span>
            </div>

            <div class="flex items-center gap-4 text-slate-600 font-medium">
                <div class="flex items-center gap-1.5 bg-white border border-[#bcd3b8]/60 px-3 py-1.5 rounded-lg shadow-xs">
                    <i class="fa-solid fa-location-dot text-[#2d6a4f]"></i>
                    <span>Branch: <strong><?= htmlspecialchars($branch) ?></strong></span>
                </div>
                <span class="text-slate-400">|</span>
                <span id="live-clock" class="font-mono font-bold text-slate-700"></span>
            </div>
        </header>

        <!-- Main Body Container -->
        <div class="p-8 space-y-6">

            <!-- Title & Period Filter Banner -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 bg-[#eaf4ec] border border-[#a3c9a8] text-[#2d6a4f] px-2.5 py-0.5 rounded-full text-[11px] font-semibold mb-1.5">
                        <i class="fa-solid fa-chart-line"></i> Real-time Bill History & Financial Reporting
                    </div>
                    <h2 class="text-2xl font-black tracking-tight page-title" style="color: #135e38 !important;">Financial Reports & Bill Receipts</h2>
                    <p class="text-xs text-slate-500">Monitor store revenue, customer checkouts, drawer breakdown, and reprint thermal receipts.</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Period Filter Tabs -->
                    <div class="inline-flex bg-white p-1 rounded-xl border border-slate-200/80 shadow-sm text-xs font-bold">
                        <a href="reports.php?period=all" class="px-3.5 py-1.5 rounded-lg transition <?= $filterPeriod === 'all' ? 'bg-[#2d6a4f] text-white shadow-xs' : 'text-slate-600 hover:bg-[#eaf4ec]' ?>">All Time</a>
                        <a href="reports.php?period=today" class="px-3.5 py-1.5 rounded-lg transition <?= $filterPeriod === 'today' ? 'bg-[#2d6a4f] text-white shadow-xs' : 'text-slate-600 hover:bg-[#eaf4ec]' ?>">Today</a>
                        <a href="reports.php?period=week" class="px-3.5 py-1.5 rounded-lg transition <?= $filterPeriod === 'week' ? 'bg-[#2d6a4f] text-white shadow-xs' : 'text-slate-600 hover:bg-[#eaf4ec]' ?>">This Week</a>
                        <a href="reports.php?period=month" class="px-3.5 py-1.5 rounded-lg transition <?= $filterPeriod === 'month' ? 'bg-[#2d6a4f] text-white shadow-xs' : 'text-slate-600 hover:bg-[#eaf4ec]' ?>">This Month</a>
                    </div>

                    <!-- EXPORT SALES TO CSV BUTTON -->
                    <a href="reports.php?action=export_csv&period=<?= htmlspecialchars($filterPeriod) ?>" class="px-4 py-2 bg-[#2d6a4f] hover:bg-[#1b4332] text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm transition active:scale-95">
                        <i class="fa-solid fa-file-csv text-base"></i>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-4 gap-4">
                <div class="border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Revenue</span>
                        <div class="w-8 h-8 rounded-xl bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-xs">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                    </div>
                    <div class="my-2">
                        <div class="text-2xl font-black text-slate-800">Rs. <?= number_format((float)$metrics['total_revenue'], 2) ?></div>
                    </div>
                    <div class="text-[11px] text-slate-400 font-medium">Aggregated gross income</div>
                </div>

                <div class="border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Completed Transactions</span>
                        <div class="w-8 h-8 rounded-xl bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-xs">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                    <div class="my-2">
                        <div class="text-2xl font-black text-slate-800"><?= number_format((int)$metrics['total_bills']) ?> <span class="text-base font-bold text-slate-500">Bills</span></div>
                    </div>
                    <div class="text-[11px] text-slate-400 font-medium">Avg Bill: <strong class="text-slate-700">Rs. <?= number_format((float)$metrics['avg_bill'], 2) ?></strong></div>
                </div>

                <div class="border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Cash Register Total</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="my-2">
                        <div class="text-2xl font-black text-slate-800">Rs. <?= number_format((float)$metrics['cash_total'], 2) ?></div>
                    </div>
                    <div class="text-[11px] text-slate-400 font-medium">Physical cash in drawer</div>
                </div>

                <div class="border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Digital Settlements</span>
                        <div class="w-8 h-8 rounded-xl bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-xs">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>
                    </div>
                    <div class="my-2">
                        <div class="text-2xl font-black text-[#1b4332]">Rs. <?= number_format((float)($metrics['card_total'] + $metrics['qr_total']), 2) ?></div>
                    </div>
                    <div class="text-[11px] text-slate-400 font-medium">Card: Rs. <?= number_format((float)$metrics['card_total'], 2) ?> &bull; QR: Rs. <?= number_format((float)$metrics['qr_total'], 2) ?></div>
                </div>
            </div>

            <!-- Transaction Audit Log Table Card -->
            <div class="border border-[#bcd3b8] bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 bg-[#F8FAFC] border-b border-slate-200 flex justify-between items-center">
                    <h3 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-[#2d6a4f]"></i> Transaction Receipts History (ORDER BY created_at DESC)
                    </h3>
                    <span class="text-[11px] text-slate-400 font-mono">Showing latest transactions</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-100 text-slate-500 uppercase tracking-wider font-extrabold border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Bill Number</th>
                                <th class="py-3.5 px-4">Date & Time</th>
                                <th class="py-3.5 px-4">Cashier Name</th>
                                <th class="py-3.5 px-4 text-center">Payment Method</th>
                                <th class="py-3.5 px-4 text-right">Subtotal</th>
                                <th class="py-3.5 px-4 text-right">Discount</th>
                                <th class="py-3.5 px-4 text-right">Total Amount</th>
                                <th class="py-3.5 px-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            <?php if (empty($bills)): ?>
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-file-invoice text-4xl mb-2"></i>
                                        <p class="text-xs font-semibold">No sales bills found for the selected period.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bills as $b): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-4 font-mono font-bold text-[#2d6a4f]">
                                            <?= htmlspecialchars((string)$b['bill_number']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 font-mono text-slate-500 text-[11px]">
                                            <?= htmlspecialchars((string)$b['created_at']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-800">
                                            <?= htmlspecialchars((string)($b['cashier_name'] ?? 'Admin')) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full <?= $b['payment_method'] === 'Cash' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-[#eaf4ec] text-[#2d6a4f] border border-[#a3c9a8]' ?>">
                                                <?= htmlspecialchars((string)$b['payment_method']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono text-slate-600">
                                            Rs. <?= number_format((float)$b['subtotal'], 2) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono text-slate-400 text-[11px]">
                                            Rs. <?= number_format((float)$b['discount_amount'], 2) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-black font-mono text-slate-900">
                                            Rs. <?= number_format((float)$b['total_amount'], 2) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <div class="inline-flex items-center gap-1.5">
                                                <button onclick="viewBillItems(<?= $b['id'] ?>, '<?= $b['bill_number'] ?>')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-bold transition flex items-center gap-1" title="View Line Items">
                                                    <i class="fa-solid fa-list-check"></i> Line Items
                                                </button>
                                                <button onclick="reprintThermalReceipt(<?= $b['id'] ?>)" class="px-2 py-1 text-[#2d6a4f] border border-[#a3c9a8] bg-[#eaf4ec] hover:bg-[#2d6a4f] hover:text-white rounded-lg text-[11px] font-bold transition flex items-center gap-1 shadow-xs" title="View Receipt / Reprint">
                                                    <i class="fa-solid fa-receipt"></i> View Receipt / Reprint
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- Modal: Bill Line Items Breakdown -->
    <div id="bill-items-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative">
            <div class="flex justify-between items-center pb-3 border-b border-slate-200 mb-4">
                <div>
                    <h3 class="font-extrabold text-slate-800 text-sm">Receipt Line Items</h3>
                    <span id="modal-bill-no" class="text-xs font-mono font-bold text-[#2d6a4f]"></span>
                </div>
                <button onclick="closeModal('bill-items-modal')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <div class="max-h-60 overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 font-bold text-slate-600 uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-2">Item</th>
                            <th class="p-2 text-center">Qty</th>
                            <th class="p-2 text-right">Price</th>
                            <th class="p-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="modal-items-tbody" class="divide-y divide-slate-100">
                        <!-- Dynamic items loaded via JS fetch -->
                    </tbody>
                </table>
            </div>

            <div class="pt-4 flex justify-end border-t border-slate-200 mt-4">
                <button onclick="closeModal('bill-items-modal')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs">Close</button>
            </div>
        </div>
    </div>

    <!-- Modal: 80mm Standard Thermal Receipt Reprint -->
    <div id="receipt-modal-wrapper" class="hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div id="receipt-modal-container" class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full relative max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Top Actions (No Print) -->
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-200 no-print">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-print text-[#2d6a4f]"></i> Thermal Receipt Preview
                </h3>
                <div class="flex items-center space-x-2">
                    <button onclick="exportReceiptPDF()" class="px-3 py-1.5 bg-[#2d6a4f] text-white text-xs font-bold rounded-lg hover:bg-[#1b4332] transition flex items-center gap-1">
                        <i class="fa-solid fa-file-pdf"></i> PDF
                    </button>
                    <button onclick="window.print()" class="px-3 py-1.5 bg-[#14281e] text-white text-xs font-bold rounded-lg hover:bg-[#1b4332] transition flex items-center gap-1">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                    <button onclick="closeModal('receipt-modal-wrapper')" class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Thermal Receipt Output Element (80mm) -->
            <div id="thermal-receipt" class="thermal-receipt border border-dashed border-slate-300 rounded p-3 font-mono text-xs bg-white text-black">
                <div style="text-align: center; margin-bottom: 6px;">
                    <div style="font-size: 15px; font-weight: 800; text-transform: uppercase;">EGOTECH SUPERMARKET</div>
                    <div style="font-size: 10px;">123 Main Street, Colombo 03</div>
                    <div style="font-size: 10px;">Tel: +94 11 234 5678 &bull; Branch: <?= htmlspecialchars($branch) ?></div>
                    <div style="font-size: 10px;">VAT Reg No: 123456789</div>
                </div>

                <div class="border-b border-dashed border-slate-400 my-2"></div>

                <div style="display: flex; justify-content: space-between; font-size: 10px;">
                    <span>Invoice #: <strong id="receipt-bill-no">INV-000000</strong></span>
                    <span id="receipt-date">--</span>
                </div>
                <div style="font-size: 10px; margin-top: 2px; display: flex; justify-content: space-between;">
                    <span>Cashier: <strong id="receipt-cashier">Admin</strong></span>
                    <span>Customer: <strong id="receipt-customer">Walk-in Customer</strong></span>
                </div>

                <div class="border-b border-dashed border-slate-400 my-2"></div>

                <table class="w-full text-left" style="font-size: 10px;">
                    <thead>
                        <tr class="border-b border-slate-300">
                            <th>Item description</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="receipt-items-tbody">
                        <!-- Dynamic Receipt Line Items -->
                    </tbody>
                </table>

                <div class="border-b border-dashed border-slate-400 my-2"></div>

                <div style="font-size: 11px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Subtotal:</span>
                        <span id="receipt-subtotal">Rs. 0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Discount:</span>
                        <span id="receipt-discount">Rs. 0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>VAT / Tax (0%):</span>
                        <span>Rs. 0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 12px; margin-top: 4px; border-top: 1px dashed #000; padding-top: 4px;">
                        <span>NET TOTAL:</span>
                        <span id="receipt-total">Rs. 0.00</span>
                    </div>
                </div>

                <div class="border-b border-dashed border-slate-400 my-2"></div>

                <div style="font-size: 10px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Payment Mode:</span>
                        <span id="receipt-payment-method" style="font-weight: bold;">Cash</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Tendered:</span>
                        <span id="receipt-tender">Rs. 0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-weight: bold;">
                        <span>Change Due:</span>
                        <span id="receipt-change">Rs. 0.00</span>
                    </div>
                </div>

                <div class="border-b border-dashed border-slate-400 my-2"></div>

                <div style="text-align: center; margin-top: 6px;">
                    <svg id="receipt-barcode-svg" style="margin: 0 auto; width: 100%; max-height: 45px;"></svg>
                    <div style="font-size: 10px; margin-top: 4px; font-weight: bold;">*** THANK YOU FOR SHOPPING WITH US! ***</div>
                    <div style="font-size: 9px; color: #444;">Please keep receipt for returns within 7 days.</div>
                </div>
            </div>

        </div>
    </div>

    <script>
        async function viewBillItems(billId, billNumber) {
            document.getElementById('modal-bill-no').innerText = billNumber;
            const tbody = document.getElementById('modal-items-tbody');
            tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-slate-400"><i class="fa-solid fa-spinner animate-spin"></i> Loading line items...</td></tr>';
            
            document.getElementById('bill-items-modal').classList.remove('hidden');

            try {
                const res = await fetch(`reports.php?fetch_bill_id=${billId}`);
                const items = await res.json();
                
                tbody.innerHTML = '';
                items.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="p-2 font-semibold text-slate-800">${item.product_name}</td>
                        <td class="p-2 text-center font-mono font-bold">${item.quantity}</td>
                        <td class="p-2 text-right font-mono">Rs. ${parseFloat(item.price).toFixed(2)}</td>
                        <td class="p-2 text-right font-mono font-bold text-slate-900">Rs. ${parseFloat(item.line_total).toFixed(2)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (err) {
                tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-rose-500 font-semibold">Failed to load line items</td></tr>';
            }
        }

        async function reprintThermalReceipt(billId) {
            try {
                const res = await fetch(`reports.php?fetch_full_bill_id=${billId}`);
                const result = await res.json();
                
                if (result.status === 'success' && result.data) {
                    const bill = result.data;
                    document.getElementById('receipt-bill-no').innerText = bill.bill_number;
                    document.getElementById('receipt-date').innerText = bill.created_at;
                    document.getElementById('receipt-cashier').innerText = bill.cashier_name || 'Admin';
                    document.getElementById('receipt-customer').innerText = bill.customer_name || 'Walk-in Customer';

                    const tbody = document.getElementById('receipt-items-tbody');
                    tbody.innerHTML = '';
                    (bill.items || []).forEach(item => {
                        const tr = document.createElement('tr');
                        const priceVal = parseFloat(item.price || 0);
                        const totalVal = parseFloat(item.line_total || (priceVal * item.quantity));
                        tr.innerHTML = `
                            <td style="padding: 2px 0;"><div>${item.product_name}</div></td>
                            <td style="text-align: center; font-weight: bold;">${item.quantity}</td>
                            <td style="text-align: right;">${priceVal.toFixed(2)}</td>
                            <td style="text-align: right; font-weight: bold;">${totalVal.toFixed(2)}</td>
                        `;
                        tbody.appendChild(tr);
                    });

                    document.getElementById('receipt-subtotal').innerText = `Rs. ${parseFloat(bill.subtotal).toFixed(2)}`;
                    document.getElementById('receipt-discount').innerText = `Rs. ${parseFloat(bill.discount_amount).toFixed(2)}`;
                    document.getElementById('receipt-total').innerText = `Rs. ${parseFloat(bill.total_amount).toFixed(2)}`;
                    document.getElementById('receipt-payment-method').innerText = bill.payment_method;
                    document.getElementById('receipt-tender').innerText = `Rs. ${parseFloat(bill.tender_amount || bill.total_amount).toFixed(2)}`;
                    document.getElementById('receipt-change').innerText = `Rs. ${parseFloat(bill.change_amount || 0).toFixed(2)}`;

                    if (window.JsBarcode && bill.bill_number) {
                        try {
                            JsBarcode("#receipt-barcode-svg", bill.bill_number, {
                                format: "CODE128",
                                width: 1.4,
                                height: 38,
                                displayValue: true,
                                fontSize: 10,
                                margin: 2
                            });
                        } catch (e) {}
                    }

                    document.getElementById('receipt-modal-wrapper').classList.remove('hidden');
                } else {
                    alert('Failed to load bill receipt details.');
                }
            } catch (err) {
                alert('Error loading receipt data.');
            }
        }

        window.exportReceiptPDF = function () {
            const element = document.getElementById('thermal-receipt');
            const billNo = document.getElementById('receipt-bill-no')?.innerText || 'INV-000000';

            if (window.html2pdf) {
                const opt = {
                    margin:       [2, 2, 2, 2],
                    filename:     `Bill_${billNo}.pdf`,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true },
                    jsPDF:        { unit: 'mm', format: [80, 220], orientation: 'portrait' }
                };
                html2pdf().set(opt).from(element).save();
            } else {
                window.print();
            }
        };

        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

        setInterval(() => {
            const now = new Date();
            const el = document.getElementById('live-clock');
            if (el) el.innerText = now.toLocaleTimeString();
        }, 1000);
    </script>
</body>
</html>