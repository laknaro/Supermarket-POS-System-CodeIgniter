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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="flex h-screen overflow-hidden text-slate-800 bg-[#F8FAF8] font-sans">

    <!-- Reusable Sidebar Component -->
    <?= $this->include('includes/sidebar') ?>

    <!-- Main Workspace Area -->
    <main class="flex-1 flex flex-col h-full overflow-y-auto">

        <!-- Top Navigation Sub-Header -->
        <header class="bg-white/80 backdrop-blur-sm border-b border-[#bcd3b8]/60 px-8 py-3.5 flex justify-between items-center text-xs shrink-0 sticky top-0 z-20">
            <div class="flex items-center gap-2 font-medium text-slate-500">
                <span class="font-bold text-slate-700">EGOTECH WORLD</span>
                <span>/</span>
                <span class="text-[#2d6a4f] bg-[#eaf4ec] px-2 py-0.5 rounded font-semibold border border-[#a3c9a8]/40">Products & Inventory</span>
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

            <!-- Title & Quick Add Banner -->
            <div class="flex justify-between items-end">
                <div>
                    <div class="inline-flex items-center gap-2 bg-[#eaf4ec] border border-[#a3c9a8] text-[#2d6a4f] px-2.5 py-0.5 rounded-full text-[11px] font-semibold mb-1.5">
                        <i class="fa-solid fa-boxes-stacked"></i> Catalog & Shelf Management
                    </div>
                    <h2 class="text-2xl font-black tracking-tight page-title" style="color: #135e38 !important;">Products & Stock Inventory</h2>
                    <p class="text-xs text-slate-500">Manage supermart items, barcode mapping, prices, cost margins, and automated stock alerts.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="openAddModal()" class="px-4 py-2.5 bg-[#2d6a4f] hover:bg-[#1b4332] text-white rounded-xl text-xs font-bold shadow-sm flex items-center gap-2 transition">
                        <i class="fa-solid fa-plus text-sm"></i> Add New Product
                    </button>
                </div>
            </div>

            <!-- Flash Alert Message -->
            <?php if ($message): ?>
                <div class="p-4 rounded-xl text-xs font-semibold flex items-center justify-between <?= $messageType === 'success' ? 'bg-[#eaf4ec] text-[#1b4332] border border-[#a3c9a8]' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid <?= $messageType === 'success' ? 'fa-circle-check text-[#2d6a4f]' : 'fa-circle-exclamation text-rose-500' ?> text-sm"></i>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- Inventory Executive KPI Cards -->
            <div class="grid grid-cols-3 gap-4">
                <div class="border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Active SKUs Catalog</span>
                        <div class="text-2xl font-black text-slate-800 font-mono mt-1"><?= number_format($totalProducts) ?></div>
                        <span class="text-[10px] text-slate-400 font-medium">Ready for live POS checkout</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-lg">
                        <i class="fa-solid fa-box"></i>
                    </div>
                </div>

                <div class="border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Low Stock Warnings</span>
                        <div class="text-2xl font-black text-rose-600 font-mono mt-1"><?= number_format($lowStockCount) ?></div>
                        <span class="text-[10px] text-rose-500 font-bold">Needs shelf replenishment</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>

                <div class="border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Inventory Valuation</span>
                        <div class="text-2xl font-black text-[#2d6a4f] font-mono mt-1">LKR <?= number_format($totalStockValue, 2) ?></div>
                        <span class="text-[10px] text-slate-400 font-medium">At current retail prices</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-lg">
                        <i class="fa-solid fa-vault"></i>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Controls -->
            <div class="border border-[#bcd3b8] bg-white p-4 rounded-2xl shadow-sm">
                <form method="GET" action="products.php" class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Filter by product name, barcode, or SKU..." 
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] text-xs text-slate-800 placeholder-slate-400">
                    </div>

                    <div class="w-full sm:w-56">
                        <select name="category_id" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] text-xs font-semibold text-slate-700">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $catFilter === (int)$cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-[#2d6a4f] hover:bg-[#1b4332] text-white font-bold rounded-xl text-xs transition shadow-sm">
                        Filter Results
                    </button>

                    <?php if ($search !== '' || $catFilter): ?>
                        <a href="products.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition text-center flex items-center">
                            Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Products Table Card -->
            <div class="border border-[#bcd3b8] bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-[#F8FAFC] text-slate-500 uppercase tracking-wider font-extrabold border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Item Details</th>
                                <th class="py-3.5 px-4">Category</th>
                                <th class="py-3.5 px-4">Barcode / SKU</th>
                                <th class="py-3.5 px-4 text-right">Selling Price</th>
                                <th class="py-3.5 px-4 text-center">Stock Level</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-box-open text-4xl mb-2"></i>
                                        <p class="text-xs font-semibold">No active products found matching your filter criteria.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $p): ?>
                                    <?php 
                                        $isLowStock = (int)$p['stock'] <= (int)$p['alert_quantity'];
                                        $thumb = !empty($p['image_url']) 
                                            ? '<img src="' . htmlspecialchars($p['image_url']) . '" class="w-9 h-9 object-cover rounded-lg border border-slate-200 shrink-0">'
                                            : '<div class="w-9 h-9 rounded-lg bg-[#eaf4ec] border border-[#a3c9a8]/50 text-[#2d6a4f] flex items-center justify-center text-sm shrink-0"><i class="fa-solid fa-basket-shopping"></i></div>';
                                    ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-3">
                                                <?= $thumb ?>
                                                <div>
                                                    <div class="font-bold text-slate-800"><?= htmlspecialchars($p['name']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 font-semibold rounded-md border border-slate-200 text-[10px]">
                                                <?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 font-mono text-[11px]">
                                            <div class="font-bold text-slate-800"><?= htmlspecialchars($p['barcode']) ?></div>
                                            <div class="text-[10px] text-slate-400"><?= htmlspecialchars($p['sku']) ?></div>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-black font-mono text-slate-900">
                                            LKR <?= number_format((float)$p['price'], 2) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-extrabold font-mono <?= $isLowStock ? 'bg-rose-100 text-rose-700 border border-rose-200 animate-pulse' : 'bg-[#eaf4ec] text-[#1b4332] border border-[#a3c9a8]' ?>">
                                                <?= $p['stock'] ?> <?= $isLowStock ? '(LOW)' : '' ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full <?= $p['status'] === 'Active' ? 'bg-[#eaf4ec] text-[#1b4332]' : 'bg-slate-100 text-slate-500' ?>">
                                                <?= htmlspecialchars($p['status']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <div class="inline-flex items-center gap-1">
                                                <button onclick='openRestockModal(<?= json_encode($p) ?>)' class="p-1.5 text-[#2d6a4f] hover:bg-[#eaf4ec] rounded-lg transition" title="Add Stock">
                                                    <i class="fa-solid fa-plus-circle text-sm"></i>
                                                </button>
                                                <button onclick='openEditModal(<?= json_encode($p) ?>)' class="p-1.5 text-[#2d6a4f] hover:bg-[#eaf4ec] rounded-lg transition" title="Edit Item">
                                                    <i class="fa-solid fa-pen-to-square text-sm"></i>
                                                </button>
                                                <a href="products.php?action=delete&id=<?= $p['id'] ?>" 
                                                   onclick="return confirm('Are you sure you want to permanently delete this product?')" 
                                                   class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition inline-flex items-center justify-center" 
                                                   title="Permanently Delete Product">
                                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                                </a>
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

    <!-- Modal: Add New Product -->
    <div id="add-product-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-6 relative overflow-hidden">
            
            <!-- Modal Header -->
            <div class="flex justify-between items-center pb-4 border-b border-slate-100 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center font-bold text-lg shrink-0">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-800 text-base leading-tight">Add New Product</h3>
                        <p class="text-xs text-slate-400">Fill in all required fields</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('add-product-modal')" class="w-8 h-8 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Product Form -->
            <form method="POST" action="products.php" enctype="multipart/form-data" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="add_product">

                <!-- Product Image Upload / URL Selector Section -->
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80 flex items-center gap-4">
                    <!-- Left: Square Image Preview Box -->
                    <div class="w-24 h-24 rounded-2xl bg-white border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-400 shrink-0 overflow-hidden relative" id="add-img-preview-box">
                        <i class="fa-regular fa-image text-2xl mb-1 text-slate-300" id="add-img-icon"></i>
                        <span class="text-[10px] font-semibold text-slate-400" id="add-img-text">No image</span>
                        <img id="add-img-element" src="" class="hidden w-full h-full object-cover absolute inset-0">
                    </div>

                    <!-- Right: Tab Switcher (Image URL vs Upload File) -->
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200 text-[11px] font-bold">
                            <button type="button" id="add-tab-url-btn" onclick="switchAddImgTab('url')" class="flex-1 py-1.5 rounded-lg text-center transition bg-[#2d6a4f] text-white shadow-xs">
                                Image URL
                            </button>
                            <button type="button" id="add-tab-file-btn" onclick="switchAddImgTab('file')" class="flex-1 py-1.5 rounded-lg text-center transition text-slate-500 hover:text-slate-800">
                                Upload File
                            </button>
                        </div>

                        <!-- Tab 1: Image URL Input -->
                        <div id="add-img-url-container">
                            <input type="url" name="image_url" id="add-image-url-input" placeholder="Paste image web URL (e.g. https://...)" 
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] text-xs text-slate-800">
                        </div>

                        <!-- Tab 2: Upload File Input -->
                        <div id="add-img-file-container" class="hidden">
                            <label class="w-full py-2 px-3 bg-white border border-slate-300 rounded-xl flex items-center justify-center gap-2 cursor-pointer hover:bg-slate-100 text-slate-700 font-bold text-xs transition">
                                <i class="fa-solid fa-upload text-[#2d6a4f]"></i>
                                <span id="add-file-label-text">Choose Image File</span>
                                <input type="file" name="image_file" id="add-image-file-input" accept="image/*" class="hidden" onchange="handleFileChoose(this)">
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Product Name -->
                <div>
                    <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">PRODUCT NAME *</label>
                    <input type="text" name="name" required placeholder="e.g. Basmati Rice 5kg" 
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] text-xs font-semibold text-slate-800">
                </div>

                <!-- Barcode with Inline Generate Button -->
                <div>
                    <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">BARCODE *</label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-mono"><i class="fa-solid fa-barcode"></i></span>
                            <input type="text" name="barcode" id="add-barcode-input" required placeholder="Scan or enter 13-digit barcode" 
                                class="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] font-mono font-bold text-xs text-slate-800">
                        </div>
                        <button type="button" onclick="generateRandomBarcode('add-barcode-input')" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl flex items-center gap-1.5 transition text-xs shrink-0 border border-slate-200">
                            <i class="fa-solid fa-arrows-rotate text-[#2d6a4f]"></i> Generate
                        </button>
                    </div>
                </div>

                <!-- Category & Status Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">CATEGORY</label>
                        <select name="category_id" class="w-full px-3 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] text-xs font-semibold text-slate-700">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">STATUS</label>
                        <select name="status" class="w-full px-3 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] text-xs font-semibold text-slate-700">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Price, Stock, SKU & Cost Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">UNIT PRICE (LKR) *</label>
                        <input type="number" step="0.01" name="price" required placeholder="LKR 0.00" 
                            class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] font-mono font-bold text-xs text-slate-800">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">INITIAL STOCK *</label>
                        <input type="number" name="stock" value="1" required min="0" 
                            class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] font-mono font-bold text-xs text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">COST PRICE (LKR)</label>
                        <input type="number" step="0.01" name="cost_price" value="0.00" placeholder="0.00" 
                            class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] font-mono text-xs text-slate-800">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">SKU CODE (OPTIONAL)</label>
                        <input type="text" name="sku" placeholder="Auto-generated if empty" 
                            class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] font-mono text-xs text-slate-800">
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-4 flex justify-end items-center gap-3 border-t border-slate-100 mt-2">
                    <button type="button" onclick="closeModal('add-product-modal')" class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-[#2d6a4f] hover:bg-[#1b4332] text-white font-bold rounded-xl text-xs shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Product -->
    <div id="edit-product-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-6 relative">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-[#2d6a4f]"></i> Edit Product Details
                </h3>
                <button type="button" onclick="closeModal('edit-product-modal')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form method="POST" action="products.php" enctype="multipart/form-data" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" id="edit-id" name="id">

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Product Name *</label>
                    <input type="text" id="edit-name" name="name" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-[#2d6a4f]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Category</label>
                        <select id="edit-category" name="category_id" class="w-full px-3 py-2 border border-slate-300 rounded-xl">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Barcode Number *</label>
                        <input type="text" id="edit-barcode" name="barcode" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">SKU Code *</label>
                        <input type="text" id="edit-sku" name="sku" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Selling Price (LKR) *</label>
                        <input type="number" step="0.01" id="edit-price" name="price" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl font-mono font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Cost Price (LKR)</label>
                        <input type="number" step="0.01" id="edit-cost-price" name="cost_price" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Stock Level</label>
                        <input type="number" id="edit-stock" name="stock" class="w-full px-3 py-2 border border-slate-300 rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Status</label>
                        <select id="edit-status" name="status" class="w-full px-3 py-2 border border-slate-300 rounded-xl">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-2 border-t border-slate-100">
                    <button type="button" onclick="closeModal('edit-product-modal')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#2d6a4f] text-white font-bold rounded-xl hover:bg-[#1b4332]">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Quick Restock -->
    <div id="restock-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 relative">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-[#2d6a4f]"></i> Quick Restock
                </h3>
                <button type="button" onclick="closeModal('restock-modal')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form method="POST" action="products.php" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="restock">
                <input type="hidden" id="restock-id" name="id">

                <div>
                    <span class="text-slate-500 font-semibold block">Target Product:</span>
                    <span id="restock-name" class="font-bold text-slate-800 text-sm block"></span>
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Quantity to Add *</label>
                    <input type="number" name="add_stock" min="1" value="10" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl font-mono text-base font-bold">
                </div>

                <div class="pt-4 flex justify-end space-x-2 border-t border-slate-100">
                    <button type="button" onclick="closeModal('restock-modal')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#2d6a4f] text-white font-bold rounded-xl hover:bg-[#1b4332]">Add Stock</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        
        function openAddModal() {
            const barcodeInput = document.getElementById('add-barcode-input');
            if (barcodeInput && !barcodeInput.value) {
                generateRandomBarcode('add-barcode-input');
            }
            openModal('add-product-modal');
        }

        function generateRandomBarcode(inputId) {
            let barcode = '479';
            for (let i = 0; i < 10; i++) {
                barcode += Math.floor(Math.random() * 10);
            }
            const input = document.getElementById(inputId);
            if (input) {
                input.value = barcode;
            }
        }

        function switchAddImgTab(tab) {
            const urlBtn = document.getElementById('add-tab-url-btn');
            const fileBtn = document.getElementById('add-tab-file-btn');
            const urlContainer = document.getElementById('add-img-url-container');
            const fileContainer = document.getElementById('add-img-file-container');

            if (tab === 'url') {
                urlBtn.className = 'flex-1 py-1.5 rounded-lg text-center transition bg-[#2d6a4f] text-white shadow-xs';
                fileBtn.className = 'flex-1 py-1.5 rounded-lg text-center transition text-slate-500 hover:text-slate-800';
                urlContainer.classList.remove('hidden');
                fileContainer.classList.add('hidden');
            } else {
                fileBtn.className = 'flex-1 py-1.5 rounded-lg text-center transition bg-[#2d6a4f] text-white shadow-xs';
                urlBtn.className = 'flex-1 py-1.5 rounded-lg text-center transition text-slate-500 hover:text-slate-800';
                fileContainer.classList.remove('hidden');
                urlContainer.classList.add('hidden');
            }
        }

        function handleFileChoose(input) {
            const labelText = document.getElementById('add-file-label-text');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (labelText) labelText.innerText = file.name;

                const reader = new FileReader();
                reader.onload = function (e) {
                    showPreviewImage(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        }

        const addUrlInput = document.getElementById('add-image-url-input');
        if (addUrlInput) {
            addUrlInput.addEventListener('input', function () {
                const url = this.value.trim();
                if (url) {
                    showPreviewImage(url);
                } else {
                    resetPreviewImage();
                }
            });
        }

        function showPreviewImage(src) {
            const imgEl = document.getElementById('add-img-element');
            const icon = document.getElementById('add-img-icon');
            const text = document.getElementById('add-img-text');

            if (imgEl) {
                imgEl.src = src;
                imgEl.classList.remove('hidden');
            }
            if (icon) icon.classList.add('hidden');
            if (text) text.classList.add('hidden');
        }

        function resetPreviewImage() {
            const imgEl = document.getElementById('add-img-element');
            const icon = document.getElementById('add-img-icon');
            const text = document.getElementById('add-img-text');

            if (imgEl) imgEl.classList.add('hidden');
            if (icon) icon.classList.remove('hidden');
            if (text) text.classList.remove('hidden');
        }

        function openModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        }

        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        }

        function openEditModal(prod) {
            const editId = document.getElementById('edit-id');
            const editName = document.getElementById('edit-name');
            const editCat = document.getElementById('edit-category');
            const editBarcode = document.getElementById('edit-barcode');
            const editSku = document.getElementById('edit-sku');
            const editPrice = document.getElementById('edit-price');
            const editCost = document.getElementById('edit-cost-price');
            const editStock = document.getElementById('edit-stock');
            const editStatus = document.getElementById('edit-status');

            if (editId) editId.value = prod.id;
            if (editName) editName.value = prod.name;
            if (editCat) editCat.value = prod.category_id || '';
            if (editBarcode) editBarcode.value = prod.barcode;
            if (editSku) editSku.value = prod.sku;
            if (editPrice) editPrice.value = prod.price;
            if (editCost) editCost.value = prod.cost_price;
            if (editStock) editStock.value = prod.stock;
            if (editStatus) editStatus.value = prod.status || 'Active';
            openModal('edit-product-modal');
        }

        function openRestockModal(prod) {
            const rId = document.getElementById('restock-id');
            const rName = document.getElementById('restock-name');
            if (rId) rId.value = prod.id;
            if (rName) rName.innerText = prod.name + ' (Current Stock: ' + prod.stock + ')';
            openModal('restock-modal');
        }

        setInterval(() => {
            const now = new Date();
            const el = document.getElementById('live-clock');
            if (el) el.innerText = now.toLocaleTimeString();
        }, 1000);
    </script>
</body>
</html>
