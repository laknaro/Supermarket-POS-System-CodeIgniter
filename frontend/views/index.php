<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - EGOTECH WORLD</title>
    <!-- Critical Inline Styles & Custom Branding -->
    <style>
      :root { color-scheme: light !important; }
      html, body {
        margin: 0 !important;
        padding: 0 !important;
        min-height: 100vh;
      }
      .login-bg {
        background-image: linear-gradient(rgba(20, 50, 35, 0.55), rgba(15, 35, 25, 0.65)), 
                          url('https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1920&q=80'), 
                          url('assets/images/supermarket.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
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
</head>
<body class="relative min-h-screen flex items-center justify-center p-4 font-sans text-slate-800 overflow-x-hidden antialiased">

    <!-- 1. Full-Screen Fresh Organic Supermarket Background Image with Forest Green Gradient Overlay -->
    <div class="login-bg fixed inset-0 z-0 transform scale-105 transition-transform duration-1000"></div>

    <!-- 2. Dark Translucent Backdrop Overlay with Blur (Glassmorphism Effect) -->
    <div class="fixed inset-0 bg-[#0a1b12]/40 backdrop-blur-md z-0"></div>

    <!-- 3. Centered Translucent Login Card (Glassmorphism + Clean Shadows) -->
    <div class="relative z-10 max-w-[440px] w-full bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl shadow-black/40 border border-white/60 p-8 space-y-6">
        
        <!-- Top Branding Section -->
        <div class="text-center">
            <div class="w-14 h-14 bg-gradient-to-br from-[#1e3a2b] to-[#2d6a4f] text-[#cfe3cb] font-black rounded-2xl flex items-center justify-center text-2xl shadow-lg mb-3 mx-auto border border-[#387c5c]/40 ring-4 ring-[#1e3a2b]/10">
                ET
            </div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight leading-none uppercase">EGOTECH WORLD</h1>
            <p class="text-xs text-slate-500 font-semibold mt-1">Supermart Point of Sale & ERP System</p>
        </div>

        <!-- Flash Error Message -->
        <?php if (!empty($error)): ?>
            <div class="bg-rose-50/90 backdrop-blur-sm border border-rose-200/80 text-rose-700 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center gap-2.5 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm shrink-0"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="index.php" class="space-y-4">
            
            <!-- Username / Employee ID Field -->
            <div>
                <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Username or Employee ID</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i class="fa-solid fa-user text-sm"></i>
                    </span>
                    <input type="text" id="username" name="username" required autofocus placeholder="e.g. admin or EMP001"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50/80 border border-slate-200/90 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] focus:bg-white transition duration-150">
                </div>
            </div>

            <!-- Password Field with Toggle Eye Icon -->
            <div>
                <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input type="password" id="password" name="password" required placeholder="••••••••"
                        class="w-full pl-10 pr-10 py-2.5 bg-slate-50/80 border border-slate-200/90 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f] focus:border-[#2d6a4f] focus:bg-white transition duration-150">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                        <i id="toggle-eye" class="fa-solid fa-eye text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-[#2d6a4f] to-[#1b4332] hover:from-[#1b4332] hover:to-[#081c15] text-white font-bold rounded-2xl shadow-md hover:shadow-lg transition duration-200 text-xs uppercase tracking-wider flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket text-sm"></i> Sign In to System
            </button>
        </form>

        <!-- Quick-Fill Demo Credentials (2x2 Grid) -->
        <div class="pt-4 border-t border-slate-200/70">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1">
                    <i class="fa-solid fa-bolt text-amber-500"></i> Quick-Fill Demo Credentials
                </span>
                <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">Pass: admin123</span>
            </div>
            
            <div class="grid grid-cols-2 gap-2">
                <!-- Admin Demo Button -->
                <button type="button" onclick="fillDemo('admin')" class="p-2.5 bg-slate-50/80 hover:bg-[#eaf4ec] hover:border-[#a3c9a8] border border-slate-200/90 rounded-xl text-left transition duration-150 group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-[#2d6a4f] flex items-center justify-between">
                        <span>Admin</span>
                        <i class="fa-solid fa-user-shield text-[10px] text-[#2d6a4f] opacity-70"></i>
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">User: admin</div>
                </button>

                <!-- Cashier (Heshani) Demo Button -->
                <button type="button" onclick="fillDemo('heshani')" class="p-2.5 bg-slate-50/80 hover:bg-[#eaf4ec] hover:border-[#a3c9a8] border border-slate-200/90 rounded-xl text-left transition duration-150 group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-[#2d6a4f] flex items-center justify-between">
                        <span>Cashier (Heshani)</span>
                        <i class="fa-solid fa-cash-register text-[10px] text-[#2d6a4f] opacity-70"></i>
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">User: heshani</div>
                </button>

                <!-- Manager Demo Button -->
                <button type="button" onclick="fillDemo('manager')" class="p-2.5 bg-slate-50/80 hover:bg-[#eaf4ec] hover:border-[#a3c9a8] border border-slate-200/90 rounded-xl text-left transition duration-150 group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-[#2d6a4f] flex items-center justify-between">
                        <span>Manager</span>
                        <i class="fa-solid fa-briefcase text-[10px] text-[#2d6a4f] opacity-70"></i>
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">User: manager</div>
                </button>

                <!-- Inventory Demo Button -->
                <button type="button" onclick="fillDemo('inventory')" class="p-2.5 bg-slate-50/80 hover:bg-[#eaf4ec] hover:border-[#a3c9a8] border border-slate-200/90 rounded-xl text-left transition duration-150 group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-[#2d6a4f] flex items-center justify-between">
                        <span>Inventory</span>
                        <i class="fa-solid fa-boxes-stacked text-[10px] text-[#2d6a4f] opacity-70"></i>
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">User: inventory</div>
                </button>
            </div>
        </div>
    </div>

    <!-- JavaScript Interactive Handlers -->
    <script>
        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            const eyeIcon = document.getElementById('toggle-eye');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                pwdInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        function fillDemo(username) {
            const userInput = document.getElementById('username');
            const pwdInput = document.getElementById('password');
            if (userInput && pwdInput) {
                userInput.value = username;
                pwdInput.value = 'admin123';
                userInput.focus();
            }
        }
    </script>
</body>
</html>