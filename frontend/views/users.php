<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Critical Inline Styles to Prevent FOUC & Fix Dark Inputs -->
    <style>
      :root { color-scheme: light !important; }
      html, body {
        background-color: #F8FAF8 !important;
        color: #1e293b !important;
        margin: 0 !important;
        padding: 0 !important;
      }
      aside { background-color: #cfe3cb !important; }
      /* Clean white background and legible slate text for inputs across all themes */
      input, textarea, select {
        background-color: #ffffff !important;
        color: #1e293b !important;
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
                <span class="text-[#2d6a4f] bg-[#eaf4ec] px-2 py-0.5 rounded font-semibold border border-[#a3c9a8]/40">User Management</span>
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
                        <i class="fa-solid fa-users-gear"></i> System Credentials & Access Control
                    </div>
                    <h2 class="text-2xl font-black tracking-tight page-title" style="color: #135e38 !important;">Staff & User Management</h2>
                    <p class="text-xs text-slate-500">Manage cashier accounts, system permissions, employee IDs, and login security credentials.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="openModal('add-user-modal')" class="px-4 py-2.5 bg-[#2d6a4f] hover:bg-[#1b4332] text-white rounded-xl text-xs font-bold shadow-sm flex items-center gap-2 transition">
                        <i class="fa-solid fa-user-plus text-sm"></i> Add New Employee
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

            <!-- Executive KPI Summary Cards -->
            <div class="grid grid-cols-3 gap-4">
                <div class="border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total User Accounts</span>
                        <div class="text-2xl font-black text-slate-800 font-mono mt-1"><?= number_format($totalUsers) ?></div>
                        <span class="text-[10px] text-slate-400 font-medium">Registered system accounts</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-lg">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                </div>

                <div class="border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Active Cashiers</span>
                        <div class="text-2xl font-black text-[#2d6a4f] font-mono mt-1"><?= number_format($cashiersCount) ?></div>
                        <span class="text-[10px] text-slate-400 font-medium">Checkout counter staff</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-lg">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                </div>

                <div class="border border-[#bcd3b8] bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Active Accounts Ratio</span>
                        <div class="text-2xl font-black text-[#1b4332] font-mono mt-1"><?= number_format($activeUsers) ?> / <?= $totalUsers ?></div>
                        <span class="text-[10px] text-slate-400 font-medium">System status enabled</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[#eaf4ec] text-[#2d6a4f] flex items-center justify-center text-lg">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Controls -->
            <div class="border border-[#bcd3b8] bg-white p-4 rounded-2xl shadow-sm">
                <form method="GET" action="users.php" class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Employee ID, Username, or Email..." 
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] text-xs text-slate-800 placeholder-slate-400">
                    </div>

                    <div class="w-full sm:w-48">
                        <select name="role" class="w-full py-2 px-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] text-xs font-semibold text-slate-700">
                            <option value="">All Roles</option>
                            <option value="Admin" <?= $roleFilter === 'Admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="Manager" <?= $roleFilter === 'Manager' ? 'selected' : '' ?>>Manager</option>
                            <option value="Cashier" <?= $roleFilter === 'Cashier' ? 'selected' : '' ?>>Cashier</option>
                            <option value="Inventory Staff" <?= $roleFilter === 'Inventory Staff' ? 'selected' : '' ?>>Inventory Staff</option>
                        </select>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-[#2d6a4f] hover:bg-[#1b4332] text-white font-bold rounded-xl text-xs transition shadow-sm">
                        Filter Users
                    </button>

                    <?php if ($search !== '' || $roleFilter !== ''): ?>
                        <a href="users.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition text-center flex items-center">
                            Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Users Table Card -->
            <div class="border border-[#bcd3b8] bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-[#F8FAFC] text-slate-500 uppercase tracking-wider font-extrabold border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Employee ID</th>
                                <th class="py-3.5 px-4">Staff Details</th>
                                <th class="py-3.5 px-4">Role</th>
                                <th class="py-3.5 px-4">Branch</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4">Created Date</th>
                                <th class="py-3.5 px-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-users-slash text-4xl mb-2"></i>
                                        <p class="text-xs font-semibold">No user accounts found.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-4 font-mono font-bold text-[#2d6a4f]">
                                            <?= htmlspecialchars($u['employee_id']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 font-bold text-slate-800">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-[#14281e] text-white font-black text-xs flex items-center justify-center uppercase">
                                                    <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800"><?= htmlspecialchars($u['username']) ?></div>
                                                    <div class="text-[10px] text-slate-400 font-mono font-normal"><?= htmlspecialchars($u['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full <?= $u['role'] === 'Admin' ? 'bg-purple-100 text-purple-800 border border-purple-200' : ($u['role'] === 'Cashier' ? 'bg-[#eaf4ec] text-[#2d6a4f] border border-[#a3c9a8]' : 'bg-slate-100 text-slate-800 border border-slate-200') ?>">
                                                <?= htmlspecialchars($u['role']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600 text-xs">
                                            <?= htmlspecialchars($u['branch']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full <?= $u['status'] === 'Active' ? 'bg-[#eaf4ec] text-[#1b4332] border border-[#a3c9a8]' : 'bg-rose-100 text-rose-800 border border-rose-200' ?>">
                                                <?= htmlspecialchars($u['status']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 font-mono text-slate-500 text-[11px]">
                                            <?= htmlspecialchars($u['created_at']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <div class="inline-flex items-center gap-1">
                                                <button type="button" onclick='openEditModal(<?= json_encode($u) ?>)' class="p-1.5 text-[#2d6a4f] hover:bg-[#eaf4ec] rounded-lg transition" title="Edit Staff Account">
                                                    <i class="fa-solid fa-user-pen text-sm"></i>
                                                </button>
                                                <?php if ((int)$u['id'] !== $currentUserId): ?>
                                                    <form method="POST" action="users.php" onsubmit="return confirm('Delete user account \'<?= htmlspecialchars($u['username']) ?>\'?')" class="inline">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                        <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Delete User">
                                                            <i class="fa-solid fa-user-minus text-sm"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-[10px] text-slate-400 font-semibold px-2">(Current)</span>
                                                <?php endif; ?>
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

    <!-- Modal: Add New Employee -->
    <div id="add-user-modal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 relative border border-slate-100">
            <div class="flex justify-between items-center pb-3 border-b border-slate-200 mb-4">
                <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-[#2d6a4f]"></i> Add New Employee Account
                </h3>
                <button type="button" onclick="closeModal('add-user-modal')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="users.php" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="add_user">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Employee ID *</label>
                        <input type="text" name="employee_id" required placeholder="EMP003" 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Username *</label>
                        <input type="text" name="username" required placeholder="cashier2" 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Email Address *</label>
                        <input type="email" name="email" required placeholder="staff@supermart.com" 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Password *</label>
                        <input type="password" name="password" required placeholder="••••••••" 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Role</label>
                        <select name="role" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                            <option value="Cashier">Cashier</option>
                            <option value="Admin">Admin</option>
                            <option value="Manager">Manager</option>
                            <option value="Inventory Staff">Inventory Staff</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Branch</label>
                        <input type="text" name="branch" value="Colombo – Head Office" 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-2 border-t border-slate-200">
                    <button type="button" onclick="closeModal('add-user-modal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#2d6a4f] hover:bg-[#1b4332] text-white font-bold rounded-xl transition shadow-sm">Save Employee</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Employee -->
    <div id="edit-user-modal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 relative border border-slate-100">
            <div class="flex justify-between items-center pb-3 border-b border-slate-200 mb-4">
                <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-[#2d6a4f]"></i> Edit Staff Account
                </h3>
                <button type="button" onclick="closeModal('edit-user-modal')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="users.php" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" id="edit-id" name="id">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Employee ID *</label>
                        <input type="text" id="edit-employee-id" name="employee_id" required 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Username *</label>
                        <input type="text" id="edit-username" name="username" required 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Email Address *</label>
                        <input type="email" id="edit-email" name="email" required 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">New Password (Optional)</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current" 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Role</label>
                        <select id="edit-role" name="role" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                            <option value="Cashier">Cashier</option>
                            <option value="Admin">Admin</option>
                            <option value="Manager">Manager</option>
                            <option value="Inventory Staff">Inventory Staff</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Branch</label>
                        <input type="text" id="edit-branch" name="branch" 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Status</label>
                        <select id="edit-status" name="status" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-2 border-t border-slate-200">
                    <button type="button" onclick="closeModal('edit-user-modal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#2d6a4f] hover:bg-[#1b4332] text-white font-bold rounded-xl transition shadow-sm">Update Employee</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            var modal = document.getElementById(id);
            if (modal) modal.classList.remove('hidden');
        }
        function closeModal(id) {
            var modal = document.getElementById(id);
            if (modal) modal.classList.add('hidden');
        }
        
        function openEditModal(u) {
            document.getElementById('edit-id').value = u.id;
            document.getElementById('edit-employee-id').value = u.employee_id;
            document.getElementById('edit-username').value = u.username;
            document.getElementById('edit-email').value = u.email;
            document.getElementById('edit-role').value = u.role;
            document.getElementById('edit-branch').value = u.branch || 'Colombo – Head Office';
            document.getElementById('edit-status').value = u.status;
            openModal('edit-user-modal');
        }

        setInterval(() => {
            const now = new Date();
            const el = document.getElementById('live-clock');
            if (el) el.innerText = now.toLocaleTimeString();
        }, 1000);
    </script>
</body>
</html>