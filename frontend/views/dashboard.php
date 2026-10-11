<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Dashboard - EGOTECH WORLD</title>
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="flex h-screen overflow-hidden text-slate-800 bg-[#F8FAF8] font-sans">

    <!-- REUSABLE SIDEBAR NAVIGATION -->
    <?= $this->include('includes/sidebar') ?>

    <!-- MAIN DASHBOARD WORKSPACE -->
    <main class="flex-1 flex flex-col h-full overflow-y-auto min-w-0 bg-[#F8FAF8]">
        
        <!-- Top Sub-Header & Breadcrumb -->
        <header class="bg-white/80 backdrop-blur-sm border-b border-[#bcd3b8]/60 px-8 py-3.5 flex justify-between items-center text-xs shrink-0 sticky top-0 z-20">
            <div class="flex items-center gap-2 font-medium text-slate-500">
                <span class="font-bold text-slate-700">EGOTECH WORLD</span>
                <span>/</span>
                <span class="text-[#2d6a4f] bg-[#eaf4ec] px-2 py-0.5 rounded font-semibold border border-[#a3c9a8]/40">Executive Dashboard</span>
            </div>

            <div class="flex items-center gap-4 text-slate-600 font-medium">
                <div class="flex items-center gap-1.5 bg-white border border-[#bcd3b8]/60 px-3 py-1.5 rounded-lg shadow-xs">
                    <i class="fa-solid fa-location-dot text-[#2d6a4f]"></i>
                    <span>Branch: <strong><?= htmlspecialchars($branch) ?></strong></span>
                </div>

                <div class="flex items-center gap-1.5 bg-white border border-[#bcd3b8]/60 px-3 py-1.5 rounded-lg shadow-xs">
                    <i class="fa-solid fa-user-check text-[#2d6a4f]"></i>
                    <span>Switch User: <strong><?= htmlspecialchars($username) ?></strong></span>
                </div>

                <span class="text-slate-300">|</span>
                <span class="text-slate-500 font-medium"><?= date('l, F d, Y') ?></span>
            </div>
        </header>

        <!-- Dashboard Body Container -->
        <div class="p-8 space-y-6">

            <!-- Title & Quick POS Launch Banner -->
            <div class="flex justify-between items-end">
                <div>
                    <div class="inline-flex items-center gap-2 bg-[#eaf4ec] border border-[#a3c9a8] text-[#2d6a4f] px-2.5 py-0.5 rounded-full text-[11px] font-bold mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#2d6a4f] animate-pulse"></span>
                        Live Store Terminal • <?= htmlspecialchars($branch) ?>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight page-title" style="color: #135e38 !important;">Supermarket Operations Command</h2>
                    <p class="text-xs text-slate-500">Real-time cashier velocity, counter transactions, shelf inventory health, and register settlement.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="location.reload()" class="px-3.5 py-2 bg-white border border-[#bcd3b8] text-slate-700 hover:bg-[#eaf4ec]/50 rounded-xl text-xs font-bold shadow-xs flex items-center gap-2 transition">
                        <i class="fa-solid fa-arrows-rotate text-[#2d6a4f]"></i> Refresh DB
                    </button>
                    <a href="pos.php" class="px-4 py-2 bg-[#2d6a4f] hover:bg-[#1b4332] text-white rounded-xl text-xs font-bold shadow-sm flex items-center gap-2 transition active:scale-95">
                        <i class="fa-solid fa-cash-register"></i> Launch POS Register
                    </a>
                </div>
            </div>

            <!-- Row 1: Real-Time Aggregate KPI Stat Cards -->
            <div class="grid grid-cols-4 gap-4">
                <!-- Card 1: TODAY'S REVENUE -->
                <div class="border border-[#bcd3b8] bg-white rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Today's Revenue</span>
                        <div class="w-8 h-8 rounded-xl bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-xs shadow-xs">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="my-2">
                        <div class="text-2xl font-black text-slate-800">Rs. <?= number_format($todayRevenue, 2) ?></div>
                    </div>
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#2d6a4f] bg-[#eaf4ec] px-2 py-0.5 rounded-md border border-[#a3c9a8]/40">
                            <i class="fa-solid fa-arrow-trend-up text-[10px]"></i> + Live DB Query
                        </span>
                        <span class="text-slate-400 text-[10px] font-semibold">Terminal #01</span>
                    </div>
                </div>

                <!-- Card 2: COUNTER CHECKOUTS -->
                <div class="border border-[#bcd3b8] bg-white rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Counter Checkouts</span>
                        <div class="w-8 h-8 rounded-xl bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-xs shadow-xs">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                    </div>
                    <div class="my-2">
                        <div class="text-2xl font-black text-slate-800"><?= number_format($todayBills) ?> <span class="text-base font-bold text-slate-500">Bills</span></div>
                    </div>
                    <div class="text-[11px]">
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#2d6a4f] bg-[#eaf4ec] px-2 py-0.5 rounded-md border border-[#a3c9a8]/40">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#2d6a4f] animate-pulse"></span> 100% database synchronized
                        </span>
                    </div>
                </div>

                <!-- Card 3: CASHIERS & STAFF -->
                <div class="border border-[#bcd3b8] bg-white rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Cashiers & Staff</span>
                        <div class="w-8 h-8 rounded-xl bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-xs shadow-xs">
                            <i class="fa-solid fa-user-group"></i>
                        </div>
                    </div>
                    <div class="my-2">
                        <div class="text-2xl font-black text-slate-800"><?= number_format($activeStaff) ?> <span class="text-base font-bold text-slate-500">Active</span></div>
                    </div>
                    <div class="text-[11px]">
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#2d6a4f] bg-[#eaf4ec] px-2 py-0.5 rounded-md border border-[#a3c9a8]/40">
                            <i class="fa-solid fa-shield-halved text-[10px]"></i> Active accounts in DB
                        </span>
                    </div>
                </div>

                <!-- Card 4: LOW STOCK WARNINGS -->
                <div class="border border-[#bcd3b8] bg-white rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Low Stock Warnings</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs shadow-xs border border-amber-200/60">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                    </div>
                    <div class="my-2">
                        <div class="text-2xl font-black text-rose-600"><?= number_format($lowStockCount) ?> <span class="text-base font-bold text-slate-500">SKUs</span></div>
                    </div>
                    <div class="text-[11px]">
                        <a href="products.php" class="text-rose-600 font-bold hover:underline inline-flex items-center gap-1 text-[11px]">
                            <i class="fa-solid fa-box-open text-[10px]"></i> Replenish inventory <i class="fa-solid fa-arrow-right text-[9px]"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Row 2: REGISTER DRAWER SETTLEMENT (Tender Breakdown) -->
            <div class="border border-[#bcd3b8] bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex justify-between items-center mb-3">
                    <div>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">REGISTER DRAWER SETTLEMENT (Tender Breakdown)</h3>
                        <p class="text-[11px] text-slate-400">Live sums and exact percentages calculated from today's bills</p>
                    </div>
                    <div class="text-xs font-bold text-slate-700">
                        Settlement Total: <span class="text-[#2d6a4f] font-black">Rs. <?= number_format($settlementTotal, 2) ?></span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <!-- Cash -->
                    <div class="p-3.5 rounded-xl bg-[#eaf4ec] border border-[#a3c9a8] flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#2d6a4f] text-white flex items-center justify-center text-base shadow-xs">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-extrabold uppercase tracking-wider text-[#1b4332]">Cash in Drawer</div>
                                <div class="text-base font-black text-slate-800">Rs. <?= number_format($cashTotal, 2) ?></div>
                            </div>
                        </div>
                        <span class="text-sm font-black text-[#2d6a4f]"><?= $cashPct ?>%</span>
                    </div>

                    <!-- Card -->
                    <div class="p-3.5 rounded-xl bg-[#eaf4ec]/70 border border-[#a3c9a8]/70 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#1b4332] text-white flex items-center justify-center text-base shadow-xs">
                                <i class="fa-solid fa-credit-card"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-extrabold uppercase tracking-wider text-[#1b4332]">Card Terminal</div>
                                <div class="text-base font-black text-slate-800">Rs. <?= number_format($cardTotal, 2) ?></div>
                            </div>
                        </div>
                        <span class="text-sm font-black text-[#1b4332]"><?= $cardPct ?>%</span>
                    </div>

                    <!-- LankaQR -->
                    <div class="p-3.5 rounded-xl bg-[#eaf4ec]/50 border border-[#a3c9a8]/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#14281e] text-white flex items-center justify-center text-base shadow-xs">
                                <i class="fa-solid fa-qrcode"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-extrabold uppercase tracking-wider text-[#14281e]">LankaQR & UPI</div>
                                <div class="text-base font-black text-slate-800">Rs. <?= number_format($lankaQrTotal, 2) ?></div>
                            </div>
                        </div>
                        <span class="text-sm font-black text-[#14281e]"><?= $qrPct ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Row 3: Hourly Sales Velocity & Top Revenue Categories -->
            <div class="grid grid-cols-3 gap-6">
                
                <!-- Left: Hourly Sales Velocity Chart (Database-Driven) -->
                <div class="col-span-2 border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Hourly Sales Velocity</h3>
                            <p class="text-[11px] text-slate-400">Checkout count grouped by hour for today</p>
                        </div>
                        <span class="text-[10px] font-bold bg-[#eaf4ec] text-[#2d6a4f] px-2.5 py-1 rounded-full border border-[#a3c9a8]"><?= htmlspecialchars($peakHourText) ?></span>
                    </div>

                    <!-- Dynamic CSS Bar Chart -->
                    <div class="h-36 flex items-end justify-between gap-2 pt-4 px-2 border-b border-slate-100">
                        <?php 
                        foreach ($hourlyData as $hr => $stat): 
                            $timeLabel = date('h A', mktime($hr, 0));
                            $count = $stat['count'];
                            // Calculate height relative to max bill count
                            $heightPct = ($maxBillCount > 0 && $count > 0) ? round(($count / $maxBillCount) * 100) : 5;
                        ?>
                            <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group" title="<?= $timeLabel ?>: <?= $count ?> bills (Rs. <?= number_format($stat['revenue'], 2) ?>)">
                                <div class="w-full bg-[#eaf4ec] rounded-t-lg group-hover:bg-[#a3c9a8]/40 transition relative flex items-end h-full">
                                    <div class="w-full bg-[#2d6a4f] rounded-t-lg transition-all duration-300 group-hover:bg-[#1b4332]" style="height: <?= $heightPct ?>%;"></div>
                                </div>
                                <span class="text-[10px] font-semibold text-slate-400 truncate w-full text-center"><?= $timeLabel ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Today's Fast-Moving Products -->
                    <div class="mt-4 pt-3">
                        <span class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider block mb-2">
                            <i class="fa-solid fa-fire text-amber-500"></i> Today's Fast-Moving Products
                        </span>
                        <div class="grid grid-cols-4 gap-2">
                            <?php if (!empty($fastMoving)): ?>
                                <?php foreach ($fastMoving as $item): ?>
                                    <div class="p-2 rounded-xl bg-[#F8FAF8] border border-[#bcd3b8]/60">
                                        <div class="text-[11px] font-bold text-slate-700 truncate"><?= htmlspecialchars($item['product_name']) ?></div>
                                        <div class="text-[10px] text-[#2d6a4f] font-extrabold mt-0.5"><?= (int)$item['units_sold'] ?> Units Sold</div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-span-4 text-center py-2 text-xs text-slate-400 font-medium">No sales recorded yet today.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right: Top Revenue Categories (Database-Driven) -->
                <div class="border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Top Revenue Categories</h3>
                        <p class="text-[11px] text-slate-400 mb-4">Real revenue contribution percentages from bill items</p>

                        <div class="space-y-4 text-xs font-bold">
                            <?php 
                            $barColors = ['bg-[#2d6a4f]', 'bg-[#1b4332]', 'bg-[#40916c]', 'bg-[#52b788]', 'bg-[#14281e]'];
                            if (!empty($topCategories)):
                                foreach ($topCategories as $idx => $cat):
                                    $catName = htmlspecialchars($cat['category_name']);
                                    $catRev = (float)$cat['cat_revenue'];
                                    $pct = $totalCatRevenue > 0 ? round(($catRev / $totalCatRevenue) * 100) : 0;
                                    $colorClass = $barColors[$idx % count($barColors)];
                            ?>
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-slate-700 truncate"><?= $catName ?></span>
                                        <span class="text-slate-500 font-extrabold"><?= $pct ?>%</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full <?= $colorClass ?> rounded-full transition-all duration-500" style="width: <?= max($pct, 2) ?>%;"></div>
                                    </div>
                                </div>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <div class="text-center py-8 text-xs text-slate-400">No category sales data found.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="pt-4 mt-2 border-t border-slate-100 text-center">
                        <a href="reports.php" class="text-xs font-bold text-[#2d6a4f] hover:text-[#1b4332] flex items-center justify-center gap-1">
                            View Detailed Analytics <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </main>

</body>
</html>