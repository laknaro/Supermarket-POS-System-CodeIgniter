<style>
    /* Sidebar Navigation Links */
    #main-app-sidebar nav a {
        color: #1e3a2b;
        -webkit-tap-highlight-color: transparent !important;
        transition: all 0.2s ease-in-out;
    }
    #main-app-sidebar nav a i {
        color: #1e3a2b;
        transition: color 0.2s ease-in-out;
    }

    /* Active Page Link State (Exact Soft Green Theme) */
    #main-app-sidebar nav a.is-nav-active {
        background-color: #b8d3b3 !important;
        color: #14281e !important;
        font-weight: 800 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
    }
    #main-app-sidebar nav a.is-nav-active i {
        color: #14281e !important;
    }

    /* Active button එක ඇතුළේ stock badge එකක් තිබුණොත් */
    #main-app-sidebar nav a.is-nav-active .inventory-count-badge {
        background-color: #14281e !important;
        color: #ffffff !important;
    }

    /* Hover & Focus State */
    #main-app-sidebar nav a:not(.is-nav-active):hover,
    #main-app-sidebar nav a:not(.is-nav-active):focus,
    #main-app-sidebar nav a:not(.is-nav-active):active {
        background-color: #b8d3b3 !important;
        color: #14281e !important;
        outline: none !important;
    }

    /* Logo Text Inside Box */
    .brand-logo-badge {
        background-color: #14281e !important;
        color: #cfe3cb !important;
    }

    /* Inventory Stock Count Badge */
    .inventory-count-badge {
        background-color: #14281e !important;
        color: #ffffff !important;
    }
</style>

<!-- Reusable Modern Supermarket Sage Sidebar Component -->
<aside id="main-app-sidebar" class="w-64 flex flex-col justify-between p-4 shadow-xl shrink-0 select-none h-full" style="background-color: #cfe3cb !important;">
    <div>
        <!-- Brand Logo Header -->
        <a href="<?= $isCashier ? 'pos.php' : ($isInventory ? 'products.php' : 'dashboard.php') ?>" class="flex items-center gap-3 px-3 py-4 mb-4">
            <div class="brand-logo-badge w-10 h-10 font-black rounded-xl flex items-center justify-center text-lg shadow-md">
                ET
            </div>
            <div>
                <h1 class="font-extrabold text-sm tracking-wide uppercase leading-none text-[#14281e]">EGOTECH</h1>
                <span class="text-[10px] text-[#1e3a2b] font-bold tracking-widest uppercase">World Supermart</span>
            </div>
        </a>

        <!-- Main Nav Links -->
        <nav class="space-y-1 text-sm font-semibold">
            <span class="text-[11px] font-extrabold tracking-wider text-[#1e3a2b]/80 px-3 uppercase mb-2 block">Main Menu</span>
            
            <!-- 1. Dashboard (Admin & Manager Only) -->
            <?php if ($isAdminOrManager): ?>
                <a href="dashboard.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= ($currentPage === 'dashboard.php') ? 'is-nav-active' : '' ?>">
                    <i class="fa-solid fa-table-columns text-base"></i>
                    <span>Dashboard</span>
                </a>
            <?php endif; ?>

            <!-- 2. Billing & Payment (Admin, Manager, Cashier) -->
            <?php if ($isAdminOrManager || $isCashier): ?>
                <a href="pos.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= ($currentPage === 'pos.php') ? 'is-nav-active' : '' ?>">
                    <i class="fa-solid fa-cash-register text-base"></i>
                    <span>Billing & Payment</span>
                </a>
            <?php endif; ?>

            <!-- 3. Bill History (Admin, Manager, Cashier) -->
            <?php if ($isAdminOrManager || $isCashier): ?>
                <a href="bill_history.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= ($currentPage === 'bill_history.php') ? 'is-nav-active' : '' ?>">
                    <i class="fa-solid fa-clock-rotate-left text-base"></i>
                    <span>Bill History</span>
                </a>
            <?php endif; ?>

            <!-- 4. Products (Admin, Manager, Inventory) -->
            <?php if ($isAdminOrManager || $isInventory): ?>
                <a href="products.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= ($currentPage === 'products.php') ? 'is-nav-active' : '' ?>">
                    <i class="fa-solid fa-cube text-base"></i>
                    <span>Products</span>
                </a>
            <?php endif; ?>

            <!-- 5. Categories (Admin, Manager, Inventory) -->
            <?php if ($isAdminOrManager || $isInventory): ?>
                <a href="categories.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= ($currentPage === 'categories.php') ? 'is-nav-active' : '' ?>">
                    <i class="fa-solid fa-tags text-base"></i>
                    <span>Categories</span>
                </a>
            <?php endif; ?>

            <!-- 6. Inventory & Stock Operations (Admin, Manager, Inventory) -->
            <?php if ($isAdminOrManager || $isInventory): ?>
                <a href="inventory.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?= ($currentPage === 'inventory.php') ? 'is-nav-active' : '' ?>">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-boxes-stacked text-base"></i>
                        <span>Inventory</span>
                    </div>
                    <?php if ($lowStockBadgeCount > 0): ?>
                        <span class="inventory-count-badge w-5 h-5 text-[10px] font-black rounded-full flex items-center justify-center shadow-xs">
                            <?= $lowStockBadgeCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <!-- 7. User Management (Admin & Manager Only) -->
            <?php if ($isAdminOrManager): ?>
                <a href="users.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= ($currentPage === 'users.php') ? 'is-nav-active' : '' ?>">
                    <i class="fa-solid fa-users-gear text-base"></i>
                    <span>User Management</span>
                </a>
            <?php endif; ?>

            <!-- 8. Reports & Analytics (Admin, Manager, Inventory) -->
            <?php if ($isAdminOrManager || $isInventory): ?>
                <a href="reports.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= ($currentPage === 'reports.php') ? 'is-nav-active' : '' ?>">
                    <i class="fa-solid fa-chart-line text-base"></i>
                    <span>Reports & Analytics</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Sidebar Bottom Profile / Action Block -->
    <div class="space-y-3">
        <button onclick="location.reload()" class="w-full py-2 bg-[#b8d3b3] hover:bg-[#a5c6a0] text-[#14281e] text-xs font-bold rounded-xl flex items-center justify-center gap-2 border border-[#a8c7a3] shadow-xs">
            <i class="fa-solid fa-rotate-left"></i> Refresh System
        </button>

        <div class="bg-white/60 rounded-2xl p-3 flex items-center justify-between border border-[#b8d3b3]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-[#14281e] text-[#cfe3cb] font-black text-xs flex items-center justify-center uppercase shadow-inner">
                    <?= htmlspecialchars(substr($userDisplay, 0, 1)) ?>
                </div>
                <div>
                    <div class="text-xs font-extrabold leading-tight text-[#14281e]"><?= htmlspecialchars($userDisplay) ?></div>
                    <div class="text-[10px] text-[#1e3a2b] font-semibold"><?= htmlspecialchars($roleDisplay) ?> • Terminal</div>
                </div>
            </div>
            <i class="fa-regular fa-bell text-[#1e3a2b] text-sm"></i>
        </div>

        <a href="index.php?action=logout" class="w-full py-2.5 bg-[#EF4444] hover:bg-red-600 text-white text-xs font-bold rounded-xl flex items-center justify-center gap-2 shadow active:scale-95">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out
        </a>
    </div>
</aside>