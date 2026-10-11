    <!-- Global Footer -->
    <footer class="bg-slate-800 text-slate-400 py-3 px-6 text-xs text-center border-t border-slate-700 mt-auto no-print">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
            <div>
                <span class="font-semibold text-slate-300">Supermarket POS & ERP System</span> &copy; <?= date('Y') ?> &bull; All Rights Reserved.
            </div>
            <div class="flex items-center space-x-4 text-slate-400">
                <span><i class="fa-solid fa-server text-emerald-400 mr-1"></i> Core PHP 8+ (PDO)</span>
                <span><i class="fa-solid fa-database text-blue-400 mr-1"></i> MySQL InnoDB</span>
                <span><i class="fa-solid fa-bolt text-amber-400 mr-1"></i> Real-time JS</span>
            </div>
        </div>
    </footer>

    <!-- Global Toast / Alert Notification Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col space-y-2 pointer-events-none no-print"></div>

    <script>
        // Global Notification Toast helper
        window.showToast = function(message, type = 'info', duration = 3500) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            const bgClass = type === 'success' ? 'bg-emerald-600 text-white' : (type === 'error' ? 'bg-red-600 text-white' : 'bg-slate-800 text-white');
            const icon = type === 'success' ? 'fa-circle-check' : (type === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-info');

            toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg shadow-xl text-sm font-medium border border-white/10 ${bgClass} transition transform translate-y-2 opacity-0 duration-200`;
            toast.innerHTML = `<i class="fa-solid ${icon} text-lg"></i> <span>${message}</span>`;
            
            container.appendChild(toast);

            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);

            // Animate out
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        };
    </script>
</body>
</html>
