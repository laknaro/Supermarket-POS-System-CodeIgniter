<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not authenticated and page is not login
$currentPage = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user_id']) && $currentPage !== 'index.php') {
    header('Location: index.php');
    exit;
}

$currentUser = [
    'id' => $_SESSION['user_id'] ?? 1,
    'username' => $_SESSION['username'] ?? 'Admin',
    'role' => $_SESSION['role'] ?? 'Cashier',
    'employee_id' => $_SESSION['employee_id'] ?? 'EMP001',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Supermarket POS & ERP System' ?></title>
    <!-- Critical Inline Styles to Prevent FOUC & Page Flash -->
    <style>
      html, body { background-color: #F8FAF8 !important; opacity: 1 !important; }
      aside { background-color: #cfe3cb !important; }
      aside a, aside a *, aside span, aside i { color: #1e3a2b !important; }
      aside a[class*="bg-white"], aside a[class*="bg-white"] * { background-color: #ffffff !important; color: #14281e !important; }
      aside a:hover, aside a:active, aside a:focus { background-color: #b8d3b3 !important; color: #14281e !important; outline: none !important; }
    </style>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Custom & Thermal Print Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Header -->
    <header class="bg-indigo-900 text-white shadow-lg sticky top-0 z-30 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Brand & Logo -->
                <div class="flex items-center space-x-3">
                    <a href="pos.php" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 bg-indigo-600 group-hover:bg-indigo-500 text-white flex items-center justify-center rounded-xl shadow-md transition duration-200">
                            <i class="fa-solid fa-cart-shopping text-xl"></i>
                        </div>
                        <div>
                            <span class="text-xl font-extrabold tracking-tight text-white block">EgoTech POS</span>
                            <span class="text-xs text-indigo-300 font-medium block">Supermarket Management</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex space-x-1">
                    <a href="pos.php" class="px-3 py-2 rounded-lg text-sm font-semibold transition duration-150 flex items-center gap-2 <?= $currentPage === 'pos.php' ? 'bg-indigo-700 text-white shadow-inner' : 'text-indigo-100 hover:bg-indigo-800' ?>">
                        <i class="fa-solid fa-cash-register text-indigo-300"></i> Cashier Terminal
                    </a>
                    <a href="products.php" class="px-3 py-2 rounded-lg text-sm font-semibold transition duration-150 flex items-center gap-2 <?= $currentPage === 'products.php' ? 'bg-indigo-700 text-white shadow-inner' : 'text-indigo-100 hover:bg-indigo-800' ?>">
                        <i class="fa-solid fa-boxes-stacked text-indigo-300"></i> Inventory & Products
                    </a>
                    <a href="reports.php" class="px-3 py-2 rounded-lg text-sm font-semibold transition duration-150 flex items-center gap-2 <?= $currentPage === 'reports.php' ? 'bg-indigo-700 text-white shadow-inner' : 'text-indigo-100 hover:bg-indigo-800' ?>">
                        <i class="fa-solid fa-chart-line text-indigo-300"></i> Sales Reports
                    </a>
                </nav>

                <!-- User Profile & Status -->
                <div class="flex items-center space-x-4">
                    <div class="hidden lg:flex items-center space-x-2 text-xs bg-indigo-950/60 px-3 py-1.5 rounded-lg border border-indigo-700/50">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span id="header-clock" class="font-mono text-indigo-200 font-medium"></span>
                    </div>

                    <div class="flex items-center space-x-3 bg-indigo-800/80 px-3 py-1.5 rounded-lg border border-indigo-700">
                        <div class="w-7 h-7 bg-indigo-600 rounded-full flex items-center justify-center text-xs font-bold text-white uppercase">
                            <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                        </div>
                        <div class="text-xs text-left">
                            <span class="font-bold text-white block leading-none"><?= htmlspecialchars($currentUser['username']) ?></span>
                            <span class="text-indigo-300 block text-[10px] uppercase font-semibold leading-tight"><?= htmlspecialchars($currentUser['role']) ?></span>
                        </div>
                    </div>

                    <a href="index.php?action=logout" class="p-2 text-indigo-200 hover:text-white hover:bg-indigo-800 rounded-lg transition" title="Sign Out">
                        <i class="fa-solid fa-right-from-bracket text-lg"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Update header clock every second
        function updateClock() {
            const el = document.getElementById('header-clock');
            if (el) {
                const now = new Date();
                el.innerText = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
