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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="flex h-screen overflow-hidden text-slate-800 bg-[#F8FAF8] font-sans">

    <!-- Persistent Master Sidebar Component -->
    <?= $this->include('includes/sidebar') ?>

    <!-- Main Workspace Container -->
    <main class="flex-1 flex flex-col h-full overflow-y-auto">

        <!-- Top Header Navigation -->
        <header class="bg-white/80 backdrop-blur-sm border-b border-[#bcd3b8]/60 px-8 py-3.5 flex justify-between items-center text-xs shrink-0 sticky top-0 z-20">
            <div class="flex items-center gap-2 font-medium text-slate-500">
                <span class="font-bold text-slate-700">EGOTECH WORLD</span>
                <span>/</span>
                <span class="text-[#2d6a4f] bg-[#eaf4ec] px-2 py-0.5 rounded font-semibold border border-[#a3c9a8]/40">Inventory & Stock Control</span>
            </div>

            <div class="flex items-center gap-4 text-slate-600 font-medium">
                <div class="flex items-center gap-1.5 bg-white border border-[#bcd3b8]/60 px-3 py-1.5 rounded-lg shadow-xs">
                    <i class="fa-solid fa-location-dot text-[#2d6a4f]"></i>
                    <span>Branch: <strong><?= htmlspecialchars($branch) ?></strong></span>
                </div>
                <div class="flex items-center gap-1.5 bg-white border border-[#bcd3b8]/60 px-3 py-1.5 rounded-lg shadow-xs">
                    <i class="fa-solid fa-user-check text-[#2d6a4f]"></i>
                    <span>User: <strong><?= htmlspecialchars($username) ?> (<?= htmlspecialchars($userRole) ?>)</strong></span>
                </div>
                <span class="text-slate-400">|</span>
                <span id="live-clock" class="font-mono font-bold text-slate-700"></span>
            </div>
        </header>

        <!-- Main Body Content -->
        <div class="p-8 space-y-6">

            <!-- Title & Quick Stats Banner -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-black tracking-tight page-title" style="color: #135e38 !important;">Inventory & Stock Operations</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Monitor real-time warehouse shelves, manage low-stock replenishment, and record purchase deliveries.</p>
                </div>

                <div class="flex items-center gap-3">
                    <button onclick="location.reload()" class="p-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-600 text-xs shadow-sm transition" title="Refresh Shelves">
                        <i class="fa-solid fa-rotate-right text-[#2d6a4f]"></i>
                    </button>
                    <div class="bg-white border border-slate-200/80 px-4 py-2 rounded-xl shadow-sm flex items-center gap-3 text-xs">
                        <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Low Stock SKUs</span>
                        <span class="bg-rose-50 text-rose-600 font-extrabold px-2 py-0.5 rounded-lg border border-rose-100">
                            <?= $lowStockCount ?> Items
                        </span>
                    </div>
                </div>
            </div>

            <!-- Attention Required Alert Banner (Shown if any low stock exists) -->
            <?php if ($lowStockCount > 0): ?>
                <div class="bg-amber-50/90 border border-amber-200/90 rounded-2xl p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-base"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-amber-900 uppercase tracking-wide">
                                Attention Required: <?= $lowStockCount ?> Product<?= $lowStockCount > 1 ? 's' : '' ?> Below Reorder Point
                            </h4>
                            <p class="text-[11px] text-amber-700">The following products need immediate supplier purchase orders to prevent shelf stock-outs:</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <?php foreach (array_slice($lowStockItems, 0, 3) as $alertItem): ?>
                            <button onclick="openRestockModal(<?= $alertItem['id'] ?>, '<?= htmlspecialchars(addslashes($alertItem['name'])) ?>', <?= (int)$alertItem['stock'] ?>)" class="px-3 py-1.5 bg-white hover:bg-amber-100/50 border border-amber-300 rounded-xl text-xs font-bold text-amber-900 flex items-center gap-2 shadow-xs transition">
                                <span><?= htmlspecialchars($alertItem['name']) ?></span>
                                <span class="text-rose-600 text-[10px] font-extrabold"><?= (int)$alertItem['stock'] ?> left (Min: 10)</span>
                                <i class="fa-solid fa-arrow-right text-[10px] text-amber-500"></i>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Search & Live Filter Bar -->
            <div class="border border-[#bcd3b8] bg-white p-3.5 rounded-2xl shadow-sm flex justify-between items-center gap-4">
                <form method="GET" action="inventory.php" class="relative max-w-md w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search inventory items by name, category, SKU..." class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-700 outline-none focus:border-[#2d6a4f] focus:ring-1 focus:ring-[#2d6a4f] shadow-xs transition">
                </form>
                <div class="text-[11px] font-bold text-slate-400">
                    Showing <?= count($inventoryItems) ?> tracked items
                </div>
            </div>

            <!-- Inventory Data Table Card -->
            <div class="border border-[#bcd3b8] bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50/90 text-slate-400 uppercase tracking-wider font-extrabold border-b border-slate-200/80">
                            <tr>
                                <th class="py-3.5 px-4">SKU & ITEM NAME</th>
                                <th class="py-3.5 px-4">CATEGORY</th>
                                <th class="py-3.5 px-4">SUPPLIER</th>
                                <th class="py-3.5 px-4 text-center">MIN THRESHOLD</th>
                                <th class="py-3.5 px-4 text-center">CURRENT STOCK</th>
                                <th class="py-3.5 px-4">HEALTH STATUS</th>
                                <th class="py-3.5 px-4 text-center">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            <?php if (empty($inventoryItems)): ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-boxes-stacked text-3xl mb-2"></i>
                                        <p class="text-xs font-semibold">No stock inventory items found.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($inventoryItems as $item): ?>
                                    <?php 
                                        $stock = (int)$item['stock'];
                                        $isLow = $stock <= $minThresholdDefault;
                                        $healthPercent = min(100, (int)round(($stock / 50) * 100));
                                        if ($stock === 0) $healthPercent = 0;
                                    ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <!-- SKU & Item Name -->
                                        <td class="py-3.5 px-4 flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200 flex items-center justify-center">
                                                <?php if (!empty($item['image_url'])): ?>
                                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="thumbnail" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <i class="fa-solid fa-basket-shopping text-slate-400 text-sm"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800 leading-snug"><?= htmlspecialchars($item['name']) ?></div>
                                                <div class="text-[10px] font-mono text-slate-400">SKU: <?= htmlspecialchars($item['sku'] ?? $item['barcode']) ?></div>
                                            </div>
                                        </td>

                                        <!-- Category -->
                                        <td class="py-3.5 px-4">
                                            <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg text-[10px] font-bold border border-slate-200/80">
                                                <?= htmlspecialchars($item['category_name']) ?>
                                            </span>
                                        </td>

                                        <!-- Supplier -->
                                        <td class="py-3.5 px-4 text-slate-500 font-normal">
                                            General Supplier
                                        </td>

                                        <!-- Min Threshold -->
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-500">
                                            <?= $minThresholdDefault ?> pcs
                                        </td>

                                        <!-- Current Stock -->
                                        <td class="py-3.5 px-4 text-center font-mono font-black <?= $isLow ? 'text-rose-600' : 'text-slate-800' ?>">
                                            <?= $stock ?> pcs
                                        </td>

                                        <!-- Health Status Bar -->
                                        <td class="py-3.5 px-4 w-44">
                                            <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                                <span class="<?= $isLow ? 'text-rose-600' : 'text-[#2d6a4f]' ?>">
                                                    <?= $isLow ? 'Critical' : 'Adequate' ?>
                                                </span>
                                                <span class="text-slate-400"><?= $healthPercent ?>%</span>
                                            </div>
                                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full <?= $isLow ? 'bg-rose-500' : 'bg-[#2d6a4f]' ?>" style="width: <?= $healthPercent ?>%"></div>
                                            </div>
                                        </td>

                                        <!-- Action: Restock -->
                                        <td class="py-3.5 px-4 text-center">
                                            <button onclick="openRestockModal(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>', <?= $stock ?>)" class="px-3 py-1.5 text-[#2d6a4f] border border-[#a3c9a8] bg-[#eaf4ec] hover:bg-[#2d6a4f] hover:text-white rounded-xl font-bold text-xs transition flex items-center gap-1.5 mx-auto">
                                                <i class="fa-solid fa-boxes-packing text-xs"></i>
                                                <span>Restock</span>
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

    <!-- Modal: Quick Restock Quantity -->
    <div id="restock-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 relative">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-xs">
                        <i class="fa-solid fa-plus font-bold"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-800 text-sm">Replenish Shelf Stock</h3>
                </div>
                <button onclick="closeRestockModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form method="POST" action="inventory.php" class="space-y-4">
                <input type="hidden" name="action" value="restock">
                <input type="hidden" name="product_id" id="modal-product-id">

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Product Title</label>
                    <div id="modal-product-name" class="font-bold text-slate-800 text-sm"></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Current Stock: <span id="modal-current-stock" class="font-mono font-bold text-slate-700"></span> pcs</div>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Quantity to Add (Pcs) *</label>
                    <input type="number" name="restock_qty" min="1" value="25" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-800 outline-none focus:border-[#2d6a4f] focus:ring-1 focus:ring-[#2d6a4f]">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeRestockModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-[#2d6a4f] hover:bg-[#1b4332] text-white font-bold rounded-xl text-xs shadow-sm transition">
                        ✓ Confirm Delivery
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        }

        function openRestockModal(id, name, currentStock) {
            const pid = document.getElementById('modal-product-id');
            const pname = document.getElementById('modal-product-name');
            const pstock = document.getElementById('modal-current-stock');
            const modal = document.getElementById('restock-modal');
            if (pid) pid.value = id;
            if (pname) pname.innerText = name;
            if (pstock) pstock.innerText = currentStock;
            if (modal) modal.classList.remove('hidden');
        }

        function closeRestockModal() {
            const modal = document.getElementById('restock-modal');
            if (modal) modal.classList.add('hidden');
        }

        setInterval(() => {
            const now = new Date();
            const el = document.getElementById('live-clock');
            if (el) el.innerText = now.toLocaleTimeString();
        }, 1000);
    </script>
</body>
</html>