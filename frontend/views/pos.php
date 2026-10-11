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
      .cat-pill-active { background-color: #2d6a4f !important; color: #FFFFFF !important; font-weight: 700 !important; }
      @page { size: 80mm auto; margin: 0; }
        
        /* Force View Switcher & Action Buttons to Grocery Green (Override pos.js blue classes) */
        #view-grid-btn.bg-blue-600,
        #view-grid-btn[class*="bg-blue"],
        #view-compact-btn.bg-blue-600,
        #view-compact-btn[class*="bg-blue"] {
            background-color: #2d6a4f !important;
            color: #ffffff !important;
            border-color: #2d6a4f !important;
        }

        /* Compact View Layout Modifier */
        .pos-compact-view {
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)) !important;
            gap: 0.5rem !important;
        }
        .pos-compact-view .prod-thumb {
            display: none !important;
        }
        .pos-compact-view .product-card {
            padding: 0.6rem 0.75rem !important;
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
    <!-- html2pdf.js & JsBarcode CDNs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="flex h-screen overflow-hidden text-slate-800 bg-[#F8FAF8] font-sans">

    <!-- Reusable Sidebar Component -->
    <?= $this->include('includes/sidebar') ?>

    <!-- Main POS Workspace -->
    <div class="flex-1 flex flex-col h-full overflow-hidden bg-[#F8FAF8] min-w-0">
        
        <!-- Top Navigation / Breadcrumb Header -->
        <header class="bg-white/80 backdrop-blur-sm border-b border-[#bcd3b8]/60 px-6 py-3 flex justify-between items-center text-xs shrink-0 sticky top-0 z-20">
            <div class="flex items-center gap-2 font-medium text-slate-500">
                <span class="font-bold text-slate-700">EGOTECH WORLD</span>
                <span>/</span>
                <span class="text-[#2d6a4f] bg-[#eaf4ec] px-2 py-0.5 rounded font-semibold border border-[#a3c9a8]/40">Point of Sale (POS) Register</span>
            </div>

            <!-- Header Controls: Branch, Cashier Badge, Clock & Synced Indicator -->
            <div class="flex items-center gap-3 text-slate-600 font-medium">
                <div class="flex items-center gap-1.5 bg-[#eaf4ec] text-[#2d6a4f] border border-[#a3c9a8] px-2.5 py-1 rounded-lg text-[11px] font-bold">
                    <span class="w-2 h-2 rounded-full bg-[#2d6a4f] animate-pulse"></span>
                    <span>Live Synced</span>
                </div>

                <div class="flex items-center gap-1.5 bg-white border border-slate-200 px-3 py-1 rounded-lg shadow-sm">
                    <i class="fa-solid fa-location-dot text-[#2d6a4f]"></i>
                    <span>Branch: <strong><?= htmlspecialchars($branch) ?></strong></span>
                </div>

                <div class="flex items-center gap-1.5 bg-white border border-slate-200 px-3 py-1 rounded-lg shadow-sm">
                    <i class="fa-solid fa-user-check text-[#2d6a4f]"></i>
                    <span>Terminal #01 &bull; <strong><?= htmlspecialchars($username) ?></strong></span>
                </div>

                <span class="text-slate-300">|</span>
                <span id="live-clock" class="font-mono font-bold text-slate-700"></span>
            </div>
        </header>

        <!-- Main Cashier Terminal Container (Dual Panel Layout) -->
        <div id="pos-main-container" class="flex-1 flex flex-col md:flex-row overflow-hidden max-w-full">

            <!-- LEFT PANEL: Product Grid & Scanner Control (~65% width) -->
            <div class="w-full md:w-[65%] bg-slate-50/50 flex flex-col border-r border-slate-200 overflow-hidden">
                
                <!-- Top Control Bar (Scanner, Search, View Switcher, Calc, Reload) -->
                <div class="p-3 bg-white border-b border-slate-200 flex flex-col sm:flex-row gap-2.5 items-center shrink-0">
                    <!-- Barcode Input -->
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#2d6a4f]">
                            <i class="fa-solid fa-barcode text-lg"></i>
                        </div>
                        <input type="text" id="barcode-input" placeholder="Scan barcode (F2)..." autofocus autocomplete="off"
                            class="w-full pl-9 pr-20 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] font-mono text-xs font-bold text-slate-800 placeholder-slate-400">
                        <span class="absolute right-2.5 top-2.5 text-[9px] uppercase font-bold bg-[#eaf4ec] text-[#2d6a4f] border border-[#a3c9a8]/60 px-1.5 py-0.5 rounded">F2 Scanner</span>
                    </div>

                    <!-- Live Product Search Input -->
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#2d6a4f]">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </div>
                        <input type="text" id="search-input" placeholder="Search item or SKU..." autocomplete="off"
                            class="w-full pl-8 pr-3 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] text-xs text-slate-800 placeholder-slate-400">
                    </div>

                    <!-- View Mode Switcher Buttons (Grid vs Compact) -->
                    <div class="flex items-center bg-[#F8FAF8] p-0.5 rounded-xl border border-[#bcd3b8] shrink-0 text-xs font-bold">
                        <button type="button" id="view-grid-btn" onclick="setPosViewMode('grid')" class="px-3 py-1.5 rounded-lg transition bg-[#2d6a4f] text-white shadow-xs flex items-center gap-1.5" title="Thumbnail / Grid View">
                            <i class="fa-solid fa-border-all text-xs"></i>
                            <span class="hidden sm:inline">Grid</span>
                        </button>
                        <button type="button" id="view-compact-btn" onclick="setPosViewMode('compact')" class="px-3 py-1.5 rounded-lg transition text-slate-600 hover:text-[#2d6a4f] hover:bg-[#eaf4ec] flex items-center gap-1.5" title="Compact Minimal Text View">
                            <i class="fa-solid fa-list text-xs"></i>
                            <span class="hidden sm:inline">Compact</span>
                        </button>
                    </div>

                    <!-- Action Quick Buttons (Calc F3 & Reload) -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" id="calc-btn" onclick="toggleCalcModal()" class="px-3 py-2 bg-[#eaf4ec] hover:bg-[#d8ebd8] border border-[#bcd3b8] text-[#2d6a4f] font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-xs" title="Calculator (F3)">
                            <i class="fa-solid fa-calculator"></i>
                            <span class="hidden lg:inline">Calc (F3)</span>
                        </button>
                        <button type="button" onclick="location.reload()" class="p-2 bg-white hover:bg-slate-100 text-slate-600 border border-[#bcd3b8] rounded-xl text-xs transition shadow-xs" title="Reload Catalog">
                            <i class="fa-solid fa-rotate"></i>
                        </button>
                    </div>
                </div>

                <!-- Category Filter Pills Bar -->
                <div class="px-3 py-2 bg-white border-b border-slate-200 overflow-x-auto flex items-center gap-1.5 shrink-0 no-scrollbar">
                    <button type="button" onclick="filterCategory(0, this)" class="cat-pill cat-pill-active px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0 bg-[#eaf4ec] text-[#1b4332] hover:bg-[#2d6a4f] hover:text-white border border-[#a3c9a8]/40">
                        All Items (<?= $totalActiveCount ?>)
                    </button>
                    <?php foreach ($categories as $cat): ?>
                        <button type="button" onclick="filterCategory(<?= $cat['id'] ?>, this)" class="cat-pill px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 bg-[#eaf4ec] text-[#1b4332] hover:bg-[#2d6a4f] hover:text-white border border-[#a3c9a8]/40">
                            <?= htmlspecialchars($cat['name']) ?> (<?= $cat['product_count'] ?>)
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Live Product Cards Grid Area -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div id="product-cards-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        <?php if (empty($products)): ?>
                            <div class="col-span-full py-16 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-4xl mb-2"></i>
                                <p class="text-xs font-medium">No active products in catalog.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($products as $prod): ?>
                                <?php 
                                    $isLowStock = (int)$prod['stock'] <= (int)($prod['alert_quantity'] ?? 5);
                                    $thumbHtml = !empty($prod['image_url']) 
                                        ? '<img src="' . htmlspecialchars($prod['image_url']) . '" class="w-full h-24 object-cover rounded-xl mb-2 border border-slate-100">'
                                        : '<div class="w-full h-24 rounded-xl bg-[#eaf4ec] border border-[#a3c9a8]/50 text-[#2d6a4f] flex items-center justify-center text-3xl mb-2"><i class="fa-solid fa-basket-shopping"></i></div>';
                                ?>
                                <div class="product-card bg-white p-3 rounded-xl border border-[#bcd3b8] shadow-xs hover:border-[#2d6a4f] hover:shadow-sm transition cursor-pointer flex flex-col justify-between group relative overflow-hidden"
                                     data-id="<?= $prod['id'] ?>"
                                     data-category-id="<?= $prod['category_id'] ?>"
                                     data-name="<?= htmlspecialchars(strtolower($prod['name'])) ?>"
                                     data-barcode="<?= htmlspecialchars($prod['barcode']) ?>"
                                     data-sku="<?= htmlspecialchars(strtolower($prod['sku'])) ?>"
                                     data-stock="<?= $prod['stock'] ?>"
                                     onclick='quickAddProduct(<?= json_encode($prod) ?>)'>
                                    
                                    <!-- Top Stock Badge -->
                                    <div class="absolute top-2 right-2 z-10">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold font-mono <?= $isLowStock ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-[#eaf4ec] text-[#1b4332] border border-[#a3c9a8]' ?>">
                                            <?= $prod['stock'] ?> pcs
                                        </span>
                                    </div>

                                    <div>
                                        <div class="prod-thumb">
                                            <?= $thumbHtml ?>
                                        </div>
                                        <h4 class="font-bold text-slate-800 text-xs line-clamp-1 group-hover:text-[#2d6a4f] transition"><?= htmlspecialchars($prod['name']) ?></h4>
                                        <div class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($prod['sku']) ?></div>
                                    </div>

                                    <div class="flex justify-between items-center mt-2.5 pt-2 border-t border-slate-100">
                                        <span class="font-black text-[#1b4332] font-mono text-xs">Rs. <?= number_format((float)$prod['price'], 2) ?></span>
                                        <button type="button" class="w-6 h-6 rounded-lg bg-[#eaf4ec] text-[#2d6a4f] group-hover:bg-[#2d6a4f] group-hover:text-white flex items-center justify-center transition shadow-xs text-xs font-bold">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status Footer -->
                <div class="p-2.5 bg-white border-t border-[#bcd3b8] flex items-center justify-between text-[11px] text-slate-500 shrink-0">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-circle-info text-[#2d6a4f]"></i>
                        <span>Press <kbd class="px-1 py-0.5 bg-slate-100 border rounded font-mono text-[9px] font-bold">F2</kbd> Scanner focus &bull; <kbd class="px-1 py-0.5 bg-slate-100 border rounded font-mono text-[9px] font-bold">F3</kbd> Calculator</span>
                    </div>
                    <div class="font-mono text-slate-400 text-[10px]">
                        WebAudio BEEP &bull; Hardware Scanner Auto-Detect
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: Active Order Checkout (~35% width) -->
            <div class="w-full md:w-[35%] bg-white flex flex-col justify-between border-l border-[#bcd3b8] shadow-xl">
                
                <!-- Order Header & Customer -->
                <div class="p-4 border-b border-[#bcd3b8] bg-slate-50/50 shrink-0">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-[#14281e] text-white flex items-center justify-center text-xs font-bold shadow-sm">
                                <i class="fa-solid fa-cart-shopping"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-sm leading-tight page-title" style="color: #135e38 !important;">Active Order</h3>
                                <span class="text-[10px] text-slate-400 font-mono">Cashier: <?= htmlspecialchars($username) ?></span>
                            </div>
                        </div>
                        <span id="cart-item-badge" class="px-2.5 py-1 bg-[#eaf4ec] text-[#2d6a4f] font-extrabold text-xs rounded-full border border-[#a3c9a8]/50">0 Items</span>
                    </div>

                    <!-- Customer Selector Input -->
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs"><i class="fa-solid fa-user"></i></span>
                        <input type="text" id="customer-name-input" value="Walk-in Customer" placeholder="Customer Name..." 
                            class="w-full pl-8 pr-3 py-1.5 bg-white border border-[#bcd3b8] rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f]">
                    </div>
                </div>

                <!-- Cart Items List Container -->
                <div class="flex-1 overflow-y-auto p-3 relative">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-100 text-slate-500 uppercase tracking-wider font-extrabold sticky top-0 border-b border-[#bcd3b8] z-10">
                            <tr>
                                <th class="py-2.5 px-3">Item</th>
                                <th class="py-2.5 px-2 text-center">Qty</th>
                                <th class="py-2.5 px-3 text-right">Total</th>
                                <th class="py-2.5 px-2 text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="cart-tbody" class="divide-y divide-slate-100">
                            <!-- Dynamic Cart Items injected by pos.js -->
                        </tbody>
                    </table>

                    <!-- Empty Cart Placeholder -->
                    <div id="empty-cart-msg" class="flex flex-col items-center justify-center py-16 text-slate-400">
                        <div class="w-14 h-14 rounded-full bg-[#eaf4ec] flex items-center justify-center mb-3 text-[#2d6a4f]">
                            <i class="fa-solid fa-cart-arrow-down text-2xl"></i>
                        </div>
                        <h4 class="text-xs font-bold text-slate-600 mb-1">Cart is Empty</h4>
                        <p class="text-[11px] text-slate-400 text-center max-w-xs">Click any item card or scan barcode to add items to active bill.</p>
                    </div>
                </div>

                <!-- Financial Totals & Payment Section -->
                <div class="p-4 bg-white border border-[#bcd3b8] rounded-2xl m-3 shadow-sm shrink-0 space-y-3">
                    
                    <!-- Financial Calculation Breakdown -->
                    <div class="space-y-1.5 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span id="summary-subtotal" class="font-bold font-mono text-slate-800">Rs. 0.00</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Discount (Rs):</span>
                            <input type="number" id="discount-input" value="0.00" min="0" step="1.00"
                                class="w-24 text-right font-mono font-bold border border-slate-300 rounded px-2 py-0.5 text-xs focus:ring-1 focus:ring-[#2d6a4f] bg-white">
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-400">
                            <span>NBT / VAT Tax (0%):</span>
                            <span id="summary-tax" class="font-mono">Rs. 0.00</span>
                        </div>
                        <hr class="border-slate-200 my-1">
                        <div class="flex justify-between items-baseline pt-1">
                            <span class="text-xs font-extrabold uppercase text-slate-800">TOTAL DUE:</span>
                            <span id="summary-total" class="text-xl font-black font-mono text-[#14281e]">Rs. 0.00</span>
                        </div>
                    </div>

                    <!-- Payment Method Selectors -->
                    <div>
                        <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">Payment Method</label>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button type="button" class="pay-method-btn active py-2 border rounded-xl font-bold text-xs bg-[#2d6a4f] text-white shadow-xs transition flex items-center justify-center gap-1" data-method="Cash">
                                <i class="fa-solid fa-money-bill-wave"></i> Cash
                            </button>
                            <button type="button" class="pay-method-btn py-2 border rounded-xl font-bold text-xs bg-white text-slate-700 hover:bg-slate-100 transition flex items-center justify-center gap-1" data-method="Card">
                                <i class="fa-solid fa-credit-card"></i> Card
                            </button>
                            <button type="button" class="pay-method-btn py-2 border rounded-xl font-bold text-xs bg-white text-slate-700 hover:bg-slate-100 transition flex items-center justify-center gap-1" data-method="LankaQR">
                                <i class="fa-solid fa-qrcode"></i> QR
                            </button>
                        </div>
                    </div>

                    <!-- Quick Cash Tendered Preset Buttons -->
                    <div id="tender-container" class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Quick Cash Tendered</label>
                        <div class="grid grid-cols-5 gap-1 text-[11px] font-mono font-bold">
                            <button type="button" onclick="setTenderPreset('exact')" class="py-1 bg-[#eaf4ec] text-[#2d6a4f] border border-[#a3c9a8] rounded hover:bg-[#2d6a4f] hover:text-white transition">Exact</button>
                            <button type="button" onclick="setTenderPreset(500)" class="py-1 bg-white border border-slate-300 rounded hover:bg-slate-100 transition">500</button>
                            <button type="button" onclick="setTenderPreset(1000)" class="py-1 bg-white border border-slate-300 rounded hover:bg-slate-100 transition">1000</button>
                            <button type="button" onclick="setTenderPreset(2000)" class="py-1 bg-white border border-slate-300 rounded hover:bg-slate-100 transition">2000</button>
                            <button type="button" onclick="setTenderPreset(5000)" class="py-1 bg-white border border-slate-300 rounded hover:bg-slate-100 transition">5000</button>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400 font-mono text-xs font-bold">Rs</span>
                                <input type="number" id="tender-input" placeholder="0.00" min="0" step="10.00"
                                    class="w-full text-right text-base font-mono font-black border border-slate-300 rounded-lg pl-7 pr-2 py-1 bg-white focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[10px] font-bold text-slate-400 block uppercase">Change</span>
                                <span id="summary-change" class="text-sm font-black font-mono text-[#2d6a4f]">Rs. 0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout & Clear Actions -->
                    <div class="space-y-1.5 pt-1">
                        <button id="checkout-btn" class="w-full py-3 bg-[#2d6a4f] hover:bg-[#1b4332] text-white font-black rounded-xl shadow-sm transition duration-200 text-sm flex justify-center items-center gap-2 uppercase tracking-wide">
                            <i class="fa-solid fa-check-circle"></i> Complete Sale
                        </button>
                        <button id="clear-cart-btn" class="w-full py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg transition duration-200 text-xs flex justify-center items-center gap-1">
                            <i class="fa-solid fa-rotate-left"></i> Clear Order
                        </button>
                    </div>

                </div>
            </div>

        </div>

    </div>

    <!-- 80mm Standard Thermal Receipt Modal Wrapper -->
    <div id="receipt-modal-wrapper" class="hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div id="receipt-modal-container" class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full relative max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Top Actions (No Print) -->
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-200 no-print">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-print text-[#2d6a4f]"></i> Thermal Receipt Preview
                </h3>
                <div class="flex items-center space-x-2">
                    <button id="download-pdf-btn" onclick="exportReceiptPDF()" class="px-3 py-1.5 bg-[#2d6a4f] text-white text-xs font-bold rounded-lg hover:bg-[#1b4332] transition flex items-center gap-1">
                        <i class="fa-solid fa-file-pdf"></i> Download PDF
                    </button>
                    <button id="print-receipt-btn" onclick="window.print()" class="px-3 py-1.5 bg-[#14281e] text-white text-xs font-bold rounded-lg hover:bg-[#1b4332] transition flex items-center gap-1">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                    <button id="close-receipt-btn" class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
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
                    <span>Cashier: <strong id="receipt-cashier"><?= htmlspecialchars($username) ?></strong></span>
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
                        <span id="receipt-tax">Rs. 0.00</span>
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

    <!-- Calculator Modal (F3 Shortcut) -->
    <div id="calc-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-5 max-w-xs w-full relative">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100 mb-3">
                <h4 class="font-extrabold text-slate-800 text-xs flex items-center gap-1.5"><i class="fa-solid fa-calculator text-[#2d6a4f]"></i> Cashier Calculator (F3)</h4>
                <button type="button" onclick="toggleCalcModal()" class="text-slate-400 hover:text-slate-600 text-sm"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="space-y-3">
                <input type="text" id="calc-screen" readonly value="0" class="w-full text-right font-mono font-black text-xl py-2 px-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-800">
                <div class="grid grid-cols-4 gap-1.5 text-xs font-bold font-mono">
                    <button type="button" onclick="calcInput('C')" class="py-2.5 bg-rose-100 text-rose-700 rounded-lg">C</button>
                    <button type="button" onclick="calcInput('/')" class="py-2.5 bg-slate-200 text-slate-800 rounded-lg">/</button>
                    <button type="button" onclick="calcInput('*')" class="py-2.5 bg-slate-200 text-slate-800 rounded-lg">*</button>
                    <button type="button" onclick="calcInput('-')" class="py-2.5 bg-slate-200 text-slate-800 rounded-lg">-</button>
                    
                    <button type="button" onclick="calcInput('7')" class="py-2.5 bg-slate-50 text-slate-800 rounded-lg">7</button>
                    <button type="button" onclick="calcInput('8')" class="py-2.5 bg-slate-50 text-slate-800 rounded-lg">8</button>
                    <button type="button" onclick="calcInput('9')" class="py-2.5 bg-slate-50 text-slate-800 rounded-lg">9</button>
                    <button type="button" onclick="calcInput('+')" class="py-2.5 bg-slate-200 text-slate-800 rounded-lg">+</button>
                    
                    <button type="button" onclick="calcInput('4')" class="py-2.5 bg-slate-50 text-slate-800 rounded-lg">4</button>
                    <button type="button" onclick="calcInput('5')" class="py-2.5 bg-slate-50 text-slate-800 rounded-lg">5</button>
                    <button type="button" onclick="calcInput('6')" class="py-2.5 bg-slate-50 text-slate-800 rounded-lg">6</button>
                    <button type="button" onclick="calcInput('=')" class="row-span-2 py-2.5 bg-[#2d6a4f] text-white rounded-lg font-black text-sm flex items-center justify-center">=</button>
                    
                    <button type="button" onclick="calcInput('1')" class="py-2.5 bg-slate-50 text-slate-800 rounded-lg">1</button>
                    <button type="button" onclick="calcInput('2')" class="py-2.5 bg-slate-50 text-slate-800 rounded-lg">2</button>
                    <button type="button" onclick="calcInput('3')" class="py-2.5 bg-slate-50 text-slate-800 rounded-lg">3</button>
                    
                    <button type="button" onclick="calcInput('0')" class="col-span-2 py-2.5 bg-slate-50 text-slate-800 rounded-lg">0</button>
                    <button type="button" onclick="calcInput('.')" class="py-2.5 bg-slate-50 text-slate-800 rounded-lg">.</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Live clock & Calculator Script -->
    <script>
        setInterval(() => {
            const now = new Date();
            const el = document.getElementById('live-clock');
            if (el) el.innerText = now.toLocaleTimeString();
        }, 1000);

        function toggleCalcModal() {
            const modal = document.getElementById('calc-modal');
            if (modal) modal.classList.toggle('hidden');
        }

        let calcExpr = '';
        function calcInput(val) {
            const screen = document.getElementById('calc-screen');
            if (!screen) return;
            if (val === 'C') {
                calcExpr = '';
                screen.value = '0';
            } else if (val === '=') {
                try {
                    calcExpr = String(eval(calcExpr));
                    screen.value = calcExpr;
                } catch (e) {
                    screen.value = 'Error';
                    calcExpr = '';
                }
            } else {
                calcExpr += val;
                screen.value = calcExpr;
            }
        }
    </script>
    <script src="assets/js/pos.js"></script>
</body>
</html>