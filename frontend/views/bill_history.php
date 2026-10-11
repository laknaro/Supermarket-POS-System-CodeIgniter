<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Critical Inline Styles & Dedicated Print Stylesheet -->
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
      
      /* Dedicated 80mm Thermal Receipt Print Stylesheet */
      @media print {
        @page {
          size: 80mm auto;
          margin: 0mm;
        }
        html, body {
          background: #ffffff !important;
          color: #000000 !important;
          margin: 0 !important;
          padding: 0 !important;
          width: 80mm !important;
          height: auto !important;
          overflow: visible !important;
        }
        /* Hide all page layout elements during print */
        aside,
        header,
        main,
        .no-print,
        #main-app-sidebar,
        #toast-container {
          display: none !important;
        }
        /* Isolate and display ONLY the thermal receipt modal wrapper */
        #receipt-modal-wrapper {
          display: block !important;
          position: absolute !important;
          left: 0 !important;
          top: 0 !important;
          width: 80mm !important;
          height: auto !important;
          background: #ffffff !important;
          padding: 0 !important;
          margin: 0 auto !important;
          box-shadow: none !important;
          backdrop-filter: none !important;
          -webkit-backdrop-filter: none !important;
        }
        #receipt-modal-container {
          display: block !important;
          box-shadow: none !important;
          border: none !important;
          padding: 0 !important;
          margin: 0 auto !important;
          width: 80mm !important;
          max-width: 80mm !important;
          background: #ffffff !important;
        }
        #thermal-receipt, .thermal-receipt {
          display: block !important;
          width: 80mm !important;
          max-width: 80mm !important;
          margin: 0 auto !important;
          padding: 8px 10px !important;
          border: none !important;
          box-shadow: none !important;
          background: #ffffff !important;
          color: #000000 !important;
          font-family: 'Courier New', Courier, monospace !important;
          font-size: 11px !important;
          line-height: 1.3 !important;
          box-sizing: border-box !important;
        }
      }
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
                <span class="text-[#2d6a4f] bg-[#eaf4ec] px-2 py-0.5 rounded font-semibold border border-[#a3c9a8]/40">Bill History & Receipts</span>
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

            <!-- Title & Actions Banner -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 bg-[#eaf4ec] border border-[#a3c9a8] text-[#2d6a4f] px-2.5 py-0.5 rounded-full text-[11px] font-semibold mb-1.5">
                        <i class="fa-solid fa-clock-rotate-left"></i> Transaction Ledger & Thermal Reprinting
                    </div>
                    <h2 class="text-2xl font-black tracking-tight page-title" style="color: #135e38 !important;">Bill History</h2>
                    <p class="text-xs text-slate-500">Search past register checkouts, verify tenders, and reprint 80mm standard thermal receipts.</p>
                </div>

                <a href="pos.php" class="px-4 py-2.5 bg-[#2d6a4f] hover:bg-[#1b4332] text-white rounded-xl text-xs font-bold shadow-sm flex items-center gap-2 transition">
                    <i class="fa-solid fa-cash-register"></i> New Transaction
                </a>
            </div>

            <!-- Search & Filter Controls -->
            <div class="border border-[#bcd3b8] bg-white p-4 rounded-2xl shadow-sm">
                <form method="GET" action="bill_history.php" class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Bill Number, Customer name, or Cashier..." 
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] text-xs text-slate-800 placeholder-slate-400">
                    </div>

                    <div class="w-full sm:w-48">
                        <select name="method" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] text-xs font-semibold text-slate-700">
                            <option value="">All Payment Methods</option>
                            <option value="Cash" <?= $methodFilter === 'Cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="Card" <?= $methodFilter === 'Card' ? 'selected' : '' ?>>Card</option>
                            <option value="LankaQR" <?= $methodFilter === 'LankaQR' ? 'selected' : '' ?>>LankaQR</option>
                        </select>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-[#2d6a4f] hover:bg-[#1b4332] text-white font-bold rounded-xl text-xs transition shadow-sm">
                        Search Bills
                    </button>

                    <?php if ($search !== '' || $methodFilter !== ''): ?>
                        <a href="bill_history.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition text-center flex items-center">
                            Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Bills History Table -->
            <div class="border border-[#bcd3b8] bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-[#F8FAFC] text-slate-500 uppercase tracking-wider font-extrabold border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4 whitespace-nowrap">Bill Number</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">Date & Time</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">Customer</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">Cashier</th>
                                <th class="py-3.5 px-4 text-center whitespace-nowrap">Payment Mode</th>
                                <th class="py-3.5 px-4 text-right whitespace-nowrap">Subtotal</th>
                                <th class="py-3.5 px-4 text-right whitespace-nowrap">Discount</th>
                                <th class="py-3.5 px-4 text-right whitespace-nowrap">Net Total</th>
                                <th class="py-3.5 px-4 text-center whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            <?php if (empty($bills)): ?>
                                <tr>
                                    <td colspan="9" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-clock-rotate-left text-4xl mb-2"></i>
                                        <p class="text-xs font-semibold">No bill transaction history found.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bills as $b): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-4 font-mono font-bold text-[#2d6a4f] whitespace-nowrap">
                                            <?= htmlspecialchars((string)$b['bill_number']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 font-mono text-slate-500 text-[11px] whitespace-nowrap">
                                            <?= htmlspecialchars((string)$b['created_at']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-800 whitespace-nowrap">
                                            <?= htmlspecialchars((string)($b['customer_name'] ?? 'Walk-in Customer')) ?>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-700 whitespace-nowrap">
                                            <?= htmlspecialchars((string)($b['cashier_name'] ?? 'Admin')) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <?php 
                                                $pm = (string)($b['payment_method'] ?? 'Cash');
                                                $badgeClass = ($pm === 'Cash') 
                                                    ? 'bg-[#eaf4ec] text-[#2d6a4f] border border-[#a3c9a8]' 
                                                    : (($pm === 'Card') 
                                                        ? 'bg-blue-50 text-blue-700 border border-blue-200' 
                                                        : 'bg-purple-50 text-purple-700 border border-purple-200');
                                            ?>
                                            <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full <?= $badgeClass ?>">
                                                <?= htmlspecialchars($pm) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono text-slate-600 whitespace-nowrap">
                                            Rs. <?= number_format((float)$b['subtotal'], 2) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono text-slate-400 text-[11px] whitespace-nowrap">
                                            Rs. <?= number_format((float)$b['discount_amount'], 2) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-black font-mono text-slate-900 whitespace-nowrap">
                                            Rs. <?= number_format((float)$b['total_amount'], 2) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <button onclick="reprintThermalReceipt(<?= $b['id'] ?>)" class="px-2.5 py-1 text-[#2d6a4f] border border-[#a3c9a8] bg-[#eaf4ec] hover:bg-[#2d6a4f] hover:text-white rounded-lg text-xs font-bold transition flex items-center justify-center gap-1 mx-auto shadow-xs">
                                                <i class="fa-solid fa-receipt"></i> View Receipt / Reprint
                                            </button>
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

    <!-- 80mm Standard Thermal Receipt Modal Wrapper -->
    <div id="receipt-modal-wrapper" class="hidden fixed inset-0 bg-slate-900/75 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div id="receipt-modal-container" class="bg-white rounded-3xl shadow-2xl p-6 max-w-sm w-full relative max-h-[90vh] overflow-y-auto border border-slate-100">
            
            <!-- Modal Top Actions Header (Hidden During Print) -->
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-200 no-print">
                <h3 class="font-extrabold text-sm leading-tight page-title" style="color: #135e38 !important;">
                    <i class="fa-solid fa-receipt text-[#2d6a4f]"></i> Thermal Receipt Preview
                </h3>
                <div class="flex items-center space-x-2">
                    <button onclick="window.print()" class="px-3.5 py-1.5 bg-[#2d6a4f] hover:bg-[#1b4332] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i class="fa-solid fa-print"></i> Print Bill
                    </button>
                    <button onclick="exportReceiptPDF()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                        <i class="fa-solid fa-file-pdf text-rose-600"></i> PDF
                    </button>
                    <button onclick="closeModal('receipt-modal-wrapper')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Thermal Receipt Output Element (80mm) -->
            <div id="thermal-receipt" class="thermal-receipt border border-dashed border-slate-300 rounded-xl p-3 bg-white text-black" style="font-family: 'Courier New', Courier, monospace; color: #000000;">
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
        async function reprintThermalReceipt(billId) {
            try {
                const res = await fetch(`bill_history.php?fetch_full_bill_id=${billId}`);
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

        function openModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        }

        setInterval(() => {
            const now = new Date();
            const el = document.getElementById('live-clock');
            if (el) el.innerText = now.toLocaleTimeString();
        }, 1000);
    </script>
</body>
</html>
