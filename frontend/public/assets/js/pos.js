// POS Terminal Dual-Panel Engine, Hardware Scanner Integration & View Mode Control
document.addEventListener('DOMContentLoaded', () => {
    let cart = [];
    let paymentMethod = 'Cash';

    // -------------------------------------------------------------
    // 1. WEB AUDIO API SYNTHESIZER FOR CASHIER FEEDBACK TONES
    // -------------------------------------------------------------
    function playBeepSound(type = 'scan') {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            if (type === 'scan') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime); // High pitch crisp scan tone
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.08);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.08);
            } else if (type === 'qty') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(520, ctx.currentTime); // Soft adjustment tone
                gain.gain.setValueAtTime(0.1, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.05);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.05);
            } else if (type === 'clear') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(320, ctx.currentTime); // Reset tone
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.12);
            }
        } catch (e) { }
    }

    // DOM Elements
    const barcodeInput = document.getElementById('barcode-input');
    const searchInput = document.getElementById('search-input');
    const customerNameInput = document.getElementById('customer-name-input');
    const cartTbody = document.getElementById('cart-tbody');
    const emptyCartMsg = document.getElementById('empty-cart-msg');
    const cartItemBadge = document.getElementById('cart-item-badge');

    // Financial Elements
    const subtotalEl = document.getElementById('summary-subtotal');
    const discountInput = document.getElementById('discount-input');
    const taxEl = document.getElementById('summary-tax');
    const totalEl = document.getElementById('summary-total');
    const tenderInput = document.getElementById('tender-input');
    const changeEl = document.getElementById('summary-change');
    const tenderContainer = document.getElementById('tender-container');

    // Action Buttons & Switchers
    const checkoutBtn = document.getElementById('checkout-btn');
    const clearCartBtn = document.getElementById('clear-cart-btn');
    const payMethodBtns = document.querySelectorAll('.pay-method-btn');

    // Receipt Modal Elements
    const receiptModalWrapper = document.getElementById('receipt-modal-wrapper');
    const closeReceiptBtn = document.getElementById('close-receipt-btn');
    const printReceiptBtn = document.getElementById('print-receipt-btn');

    // Focus Barcode Scanner on Initial Load
    if (barcodeInput) barcodeInput.focus();

    // -------------------------------------------------------------
    // 2. VIEW MODE TOGGLE (GRID vs COMPACT VIEW WITH LOCALSTORAGE)
    // -------------------------------------------------------------
    window.setPosViewMode = function (mode) {
        const gridEl = document.getElementById('product-cards-grid');
        const gridBtn = document.getElementById('view-grid-btn');
        const compactBtn = document.getElementById('view-compact-btn');

        if (!gridEl) return;

        if (mode === 'compact') {
            gridEl.classList.add('pos-compact-view');
            if (gridBtn) {
                gridBtn.className = 'px-2.5 py-1.5 rounded-lg transition text-slate-600 hover:text-blue-600 flex items-center gap-1';
            }
            if (compactBtn) {
                compactBtn.className = 'px-2.5 py-1.5 rounded-lg transition bg-blue-600 text-white shadow-sm flex items-center gap-1';
            }
            localStorage.setItem('posViewMode', 'compact');
        } else {
            gridEl.classList.remove('pos-compact-view');
            if (gridBtn) {
                gridBtn.className = 'px-2.5 py-1.5 rounded-lg transition bg-blue-600 text-white shadow-sm flex items-center gap-1';
            }
            if (compactBtn) {
                compactBtn.className = 'px-2.5 py-1.5 rounded-lg transition text-slate-600 hover:text-blue-600 flex items-center gap-1';
            }
            localStorage.setItem('posViewMode', 'grid');
        }
    };

    // Load saved layout preference
    const savedViewMode = localStorage.getItem('posViewMode') || 'grid';
    window.setPosViewMode(savedViewMode);

    // -------------------------------------------------------------
    // 3. SEAMLESS HARDWARE BARCODE READER SUPPORT (GLOBAL LISTENER)
    // -------------------------------------------------------------
    let scannerBuffer = '';
    let lastKeyTime = 0;

    document.addEventListener('keydown', (e) => {
        const now = Date.now();

        // F2: Manual Scanner Focus
        if (e.key === 'F2') {
            e.preventDefault();
            if (barcodeInput) {
                barcodeInput.value = '';
                barcodeInput.focus();
            }
            return;
        }

        // F3: Calculator Modal Toggle
        if (e.key === 'F3') {
            e.preventDefault();
            if (window.toggleCalcModal) window.toggleCalcModal();
            return;
        }

        // ESC: Close Modals
        if (e.key === 'Escape') {
            if (receiptModalWrapper && !receiptModalWrapper.classList.contains('hidden')) {
                receiptModalWrapper.classList.add('hidden');
                if (barcodeInput) barcodeInput.focus();
            }
            const calcModal = document.getElementById('calc-modal');
            if (calcModal && !calcModal.classList.contains('hidden')) {
                calcModal.classList.add('hidden');
                if (barcodeInput) barcodeInput.focus();
            }
            return;
        }

        // Fast Keystroke Detection for Handheld Hardware Scanners (~50ms)
        if (now - lastKeyTime > 100) {
            scannerBuffer = '';
        }
        lastKeyTime = now;

        if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
            scannerBuffer += e.key;
        }

        if (e.key === 'Enter') {
            const scannedCode = scannerBuffer.trim();
            // If barcode buffer contains a scanner payload (>= 3 chars)
            if (scannedCode.length >= 3) {
                const activeEl = document.activeElement;
                const isOtherInput = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA') && activeEl.id !== 'barcode-input' && activeEl.id !== 'search-input';

                if (!isOtherInput) {
                    e.preventDefault();
                    lookupAndAddScannedProduct(scannedCode);
                    scannerBuffer = '';
                }
            }
        }
    });

    // Dedicated Barcode Input Enter Key Handler
    if (barcodeInput) {
        barcodeInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const code = this.value.trim();
                if (code) {
                    lookupAndAddScannedProduct(code);
                    this.value = '';
                }
            }
        });
    }

    // Lookup Scanned Barcode in Grid Cards or Fallback to API
    async function lookupAndAddScannedProduct(code) {
        const codeClean = code.toLowerCase().trim();

        // 1. Try matching visible DOM product cards first
        const cards = Array.from(document.querySelectorAll('.product-card'));
        const matchedCard = cards.find(card => {
            const b = (card.dataset.barcode || '').toLowerCase();
            const s = (card.dataset.sku || '').toLowerCase();
            return b === codeClean || s === codeClean;
        });

        if (matchedCard) {
            const pId = parseInt(matchedCard.dataset.id);
            const pStock = parseInt(matchedCard.dataset.stock || 0);
            const pName = matchedCard.querySelector('h4')?.innerText || matchedCard.dataset.name;
            const pBarcode = matchedCard.dataset.barcode;
            const pPrice = parseFloat(matchedCard.querySelector('.font-indigo-700')?.innerText.replace(/[^0-9.]/g, '') || 0);

            window.quickAddProduct({
                id: pId,
                barcode: pBarcode,
                name: pName,
                price: pPrice,
                stock: pStock
            });

            if (barcodeInput) barcodeInput.value = '';
            return;
        }

        // 2. Fallback to API endpoint lookup
        try {
            const res = await fetch(`api/get_products.php?barcode=${encodeURIComponent(codeClean)}`);
            const result = await res.json();

            if (result.status === 'success' && result.data) {
                window.quickAddProduct(result.data);
                if (barcodeInput) barcodeInput.value = '';
            } else {
                alert(`Product not found for scanned barcode: ${code}`);
                if (barcodeInput) barcodeInput.select();
            }
        } catch (err) {
            console.error('Scan API error:', err);
            alert('Server connection error during barcode scan.');
        }
    }

    // -------------------------------------------------------------
    // 4. HIGH-PERFORMANCE INSTANT SEARCH (150ms DEBOUNCE & AUTO-ADD)
    // -------------------------------------------------------------
    let searchDebounceTimer = null;

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                const query = this.value.trim().toLowerCase();
                const cards = document.querySelectorAll('.product-card');

                cards.forEach(card => {
                    const name = card.dataset.name || '';
                    const barcode = card.dataset.barcode || '';
                    const sku = card.dataset.sku || '';

                    if (name.includes(query) || barcode.includes(query) || sku.includes(query)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }, 150);
        });

        // Single Match Auto-Add on Enter Key Press
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const visibleCards = Array.from(document.querySelectorAll('.product-card')).filter(c => c.style.display !== 'none');

                if (visibleCards.length === 1) {
                    visibleCards[0].click(); // Trigger quick add click
                    this.value = '';
                    // Reset grid view filter
                    document.querySelectorAll('.product-card').forEach(c => c.style.display = 'flex');
                }
            }
        });
    }

    // Category Filter Pills Handler
    window.filterCategory = function (catId, btn) {
        document.querySelectorAll('.cat-pill').forEach(b => b.classList.remove('cat-pill-active'));
        if (btn) btn.classList.add('cat-pill-active');

        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const cId = parseInt(card.dataset.categoryId || 0);
            if (catId === 0 || cId === catId) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    };

    // -------------------------------------------------------------
    // 5. QUICK ADD PRODUCT TO CART & RENDER
    // -------------------------------------------------------------
    window.quickAddProduct = function (product) {
        const pId = parseInt(product.id);
        const card = document.querySelector(`.product-card[data-id="${pId}"]`);
        const currentCardStock = card && card.dataset.stock !== undefined ? parseInt(card.dataset.stock) : parseInt(product.stock);
        const pPrice = parseFloat(product.price);

        if (currentCardStock <= 0) {
            alert(`Out of Stock: '${product.name}' has 0 items remaining!`);
            return;
        }

        const existing = cart.find(item => item.id === pId);
        if (existing) {
            if (existing.qty < currentCardStock) {
                existing.qty++;
            } else {
                alert(`Stock Limit Reached: Cannot add more than available stock (${currentCardStock}) for '${product.name}'`);
                return;
            }
        } else {
            cart.push({
                id: pId,
                barcode: product.barcode || '',
                name: product.name,
                price: pPrice,
                stock: currentCardStock,
                qty: 1
            });
        }

        playBeepSound('scan');
        renderCart();
    };

    // Render Cart Items Table
    function renderCart() {
        if (!cartTbody) return;
        cartTbody.innerHTML = '';

        const totalQtyCount = cart.reduce((sum, item) => sum + item.qty, 0);
        if (cartItemBadge) cartItemBadge.innerText = `${totalQtyCount} Items`;

        if (cart.length === 0) {
            if (emptyCartMsg) emptyCartMsg.classList.remove('hidden');
        } else {
            if (emptyCartMsg) emptyCartMsg.classList.add('hidden');
        }

        cart.forEach((item, index) => {
            const lineTotal = (item.price * item.qty).toFixed(2);
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition border-b border-slate-100';

            tr.innerHTML = `
                <td class="py-2 px-3">
                    <div class="font-bold text-slate-800 text-xs">${item.name}</div>
                    <div class="text-[10px] text-slate-400 font-mono">${item.barcode}</div>
                </td>
                <td class="py-2 px-2 text-center">
                    <div class="inline-flex items-center border border-slate-300 rounded-lg overflow-hidden bg-white shadow-sm">
                        <button class="px-2 py-0.5 text-slate-600 hover:bg-slate-100 font-bold text-xs transition" onclick="updateQty(${index}, -1)">-</button>
                        <span class="px-2 py-0.5 font-black text-indigo-900 text-xs font-mono">${item.qty}</span>
                        <button class="px-2 py-0.5 text-slate-600 hover:bg-slate-100 font-bold text-xs transition" onclick="updateQty(${index}, 1)">+</button>
                    </div>
                </td>
                <td class="py-2 px-3 text-right font-black text-slate-900 font-mono text-xs">Rs. ${lineTotal}</td>
                <td class="py-2 px-2 text-center">
                    <button class="w-6 h-6 rounded-full text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition inline-flex items-center justify-center" onclick="removeItem(${index})" title="Remove Item">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                </td>
            `;
            cartTbody.appendChild(tr);
        });

        calculateTotals();
    }

    // Inline Quantity & Delete Handlers
    window.updateQty = function (index, change) {
        const item = cart[index];
        if (!item) return;

        if (change > 0 && item.qty >= item.stock) {
            alert(`Stock limit reached! Available stock: ${item.stock}`);
            return;
        }

        item.qty += change;
        playBeepSound('qty');

        if (item.qty <= 0) {
            cart.splice(index, 1);
        }
        renderCart();
    };

    window.removeItem = function (index) {
        cart.splice(index, 1);
        playBeepSound('qty');
        renderCart();
    };

    // -------------------------------------------------------------
    // 6. FINANCIAL CALCULATIONS & QUICK CASH PRESETS
    // -------------------------------------------------------------
    function calculateTotals() {
        const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
        const discount = parseFloat(discountInput ? discountInput.value : 0) || 0;
        const netTotal = Math.max(0, subtotal - discount);

        if (subtotalEl) subtotalEl.innerText = `Rs. ${subtotal.toFixed(2)}`;
        if (totalEl) totalEl.innerText = `Rs. ${netTotal.toFixed(2)}`;

        updateChange(netTotal);
    }

    function updateChange(netTotal) {
        if (!tenderInput || !changeEl) return;
        const tender = parseFloat(tenderInput.value) || 0;
        const change = tender > netTotal ? tender - netTotal : 0;
        changeEl.innerText = `Rs. ${change.toFixed(2)}`;

        if (paymentMethod === 'Cash' && tender < netTotal && netTotal > 0) {
            changeEl.className = 'text-xs font-black font-mono text-rose-600';
        } else {
            changeEl.className = 'text-xs font-black font-mono text-emerald-600';
        }
    }

    // Quick Cash Tendered Preset Button Handler
    window.setTenderPreset = function (val) {
        const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
        const discount = parseFloat(discountInput ? discountInput.value : 0) || 0;
        const netTotal = Math.max(0, subtotal - discount);

        if (val === 'exact') {
            tenderInput.value = netTotal.toFixed(2);
        } else {
            tenderInput.value = parseFloat(val).toFixed(2);
        }

        updateChange(netTotal);
    };

    if (discountInput) {
        discountInput.addEventListener('input', calculateTotals);
    }

    if (tenderInput) {
        tenderInput.addEventListener('input', () => {
            const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            const discount = parseFloat(discountInput ? discountInput.value : 0) || 0;
            const netTotal = Math.max(0, subtotal - discount);
            updateChange(netTotal);
        });
    }

    // Payment Method Switcher
    payMethodBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            payMethodBtns.forEach(b => {
                b.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
                b.classList.add('bg-white', 'text-slate-700', 'hover:bg-slate-100');
            });
            this.classList.remove('bg-white', 'text-slate-700', 'hover:bg-slate-100');
            this.classList.add('bg-blue-600', 'text-white', 'shadow-sm');

            paymentMethod = this.dataset.method;

            if (tenderContainer) {
                if (paymentMethod === 'Cash') {
                    tenderContainer.classList.remove('opacity-50', 'pointer-events-none');
                } else {
                    tenderContainer.classList.add('opacity-50', 'pointer-events-none');
                    if (tenderInput) tenderInput.value = '';
                    if (changeEl) changeEl.innerText = 'Rs. 0.00';
                }
            }
        });
    });

    // Clear Terminal Order
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', () => {
            if (cart.length === 0) return;
            if (confirm('Clear current active order?')) {
                playBeepSound('clear');
                cart = [];
                if (tenderInput) tenderInput.value = '';
                if (discountInput) discountInput.value = '0.00';
                renderCart();
                if (barcodeInput) barcodeInput.focus();
            }
        });
    }

    // -------------------------------------------------------------
    // 7. COMPLETE SALE & SAVE BILL AJAX REQUEST
    // -------------------------------------------------------------
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', async () => {
            if (cart.length === 0) {
                alert('Cart is empty! Add items before completing sale.');
                if (barcodeInput) barcodeInput.focus();
                return;
            }

            const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            const discount = parseFloat(discountInput ? discountInput.value : 0) || 0;
            const netTotal = Math.max(0, subtotal - discount);
            let tender = parseFloat(tenderInput ? tenderInput.value : 0) || 0;
            const custName = customerNameInput ? (customerNameInput.value.trim() || 'Walk-in Customer') : 'Walk-in Customer';

            if (paymentMethod === 'Cash' && tender < netTotal) {
                alert(`Insufficient Cash Tendered!\nTendered: Rs. ${tender.toFixed(2)}\nNet Total: Rs. ${netTotal.toFixed(2)}`);
                if (tenderInput) tenderInput.focus();
                return;
            }

            // Disable button during processing
            checkoutBtn.disabled = true;
            checkoutBtn.innerHTML = `<i class="fa-solid fa-spinner animate-spin"></i> Processing...`;

            const payload = {
                customer_name: custName,
                subtotal: subtotal,
                discount: discount,
                tax: 0.00,
                total: netTotal,
                tender_amount: paymentMethod === 'Cash' ? tender : netTotal,
                change_amount: paymentMethod === 'Cash' ? Math.max(0, tender - netTotal) : 0.00,
                payment_method: paymentMethod,
                items: cart
            };

            try {
                const res = await fetch('api/save_bill.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success || data.status === 'success') {
                    const billObj = data.data || data;

                    // Update UI Product Card Stock Badges dynamically without full page reload
                    if (Array.isArray(billObj.items)) {
                        billObj.items.forEach(item => {
                            const card = document.querySelector(`.product-card[data-id="${item.id}"]`);
                            if (card) {
                                const newStock = item.remaining_stock !== undefined ? item.remaining_stock : Math.max(0, (parseInt(card.dataset.stock || 0) - item.qty));
                                card.dataset.stock = newStock;
                                const badge = card.querySelector('.absolute span');
                                if (badge) {
                                    badge.innerText = `${newStock} pcs`;
                                    if (newStock <= 5) {
                                        badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold font-mono bg-rose-100 text-rose-700 border border-rose-200';
                                    }
                                }
                            }
                        });
                    }

                    // Render & trigger thermal receipt modal
                    populateThermalReceipt(billObj);
                    openReceiptModal();

                    // Reset cart & state cleanly
                    cart = [];
                    if (tenderInput) tenderInput.value = '';
                    if (discountInput) discountInput.value = '0.00';
                    renderCart();

                } else {
                    const errorMsg = data.error || data.message || 'Unknown transaction error.';
                    alert('Error Completing Sale: ' + errorMsg);
                }
            } catch (err) {
                console.error('Checkout API error:', err);
                alert('Connection error occurred while saving the bill.');
            } finally {
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = `<i class="fa-solid fa-check-circle"></i> Complete Sale`;
            }
        });
    }

    // -------------------------------------------------------------
    // 8. THERMAL RECEIPT PREVIEW & PDF EXPORT
    // -------------------------------------------------------------
    function populateThermalReceipt(bill) {
        const elBillNo = document.getElementById('receipt-bill-no');
        const elDate = document.getElementById('receipt-date');
        const elCashier = document.getElementById('receipt-cashier');
        const elCustomer = document.getElementById('receipt-customer');
        const elItemsTbody = document.getElementById('receipt-items-tbody');
        const elSubtotal = document.getElementById('receipt-subtotal');
        const elDiscount = document.getElementById('receipt-discount');
        const elTotal = document.getElementById('receipt-total');
        const elPaymentMethod = document.getElementById('receipt-payment-method');
        const elTender = document.getElementById('receipt-tender');
        const elChange = document.getElementById('receipt-change');

        const billNo = bill.bill_no || bill.bill_number || 'INV-000000';
        const billDate = bill.date || bill.created_at || new Date().toLocaleString();
        const cashierName = bill.cashier || bill.cashier_name || 'Admin';
        const customerName = bill.customer || bill.customer_name || 'Walk-in Customer';

        if (elBillNo) elBillNo.innerText = billNo;
        if (elDate) elDate.innerText = billDate;
        if (elCashier) elCashier.innerText = cashierName;
        if (elCustomer) elCustomer.innerText = customerName;

        if (elItemsTbody) {
            elItemsTbody.innerHTML = '';
            const itemsList = bill.items || [];
            itemsList.forEach(item => {
                const tr = document.createElement('tr');
                const itemName = item.name || item.product_name || '';
                const itemQty = item.qty || item.quantity || 1;
                const itemPrice = parseFloat(item.price || item.unit_price || 0);
                const itemTotal = parseFloat(item.total || item.line_total || (itemPrice * itemQty));

                tr.innerHTML = `
                    <td style="padding: 2px 0;">
                        <div>${itemName}</div>
                    </td>
                    <td style="text-align: center; font-weight: bold;">${itemQty}</td>
                    <td style="text-align: right;">${itemPrice.toFixed(2)}</td>
                    <td style="text-align: right; font-weight: bold;">${itemTotal.toFixed(2)}</td>
                `;
                elItemsTbody.appendChild(tr);
            });
        }

        const subtotal = parseFloat(bill.subtotal || 0);
        const discount = parseFloat(bill.discount || bill.discount_amount || 0);
        const total = parseFloat(bill.total || bill.total_amount || 0);
        const tender = parseFloat(bill.tender || bill.tender_amount || 0);
        const change = parseFloat(bill.change || bill.change_amount || 0);

        if (elSubtotal) elSubtotal.innerText = `Rs. ${subtotal.toFixed(2)}`;
        if (elDiscount) elDiscount.innerText = `Rs. ${discount.toFixed(2)}`;
        if (elTotal) elTotal.innerText = `Rs. ${total.toFixed(2)}`;
        if (elPaymentMethod) elPaymentMethod.innerText = bill.payment_method || 'Cash';
        if (elTender) elTender.innerText = `Rs. ${tender.toFixed(2)}`;
        if (elChange) elChange.innerText = `Rs. ${change.toFixed(2)}`;

        // Render Barcode SVG via JsBarcode
        if (window.JsBarcode && billNo) {
            try {
                JsBarcode("#receipt-barcode-svg", billNo, {
                    format: "CODE128",
                    width: 1.4,
                    height: 38,
                    displayValue: true,
                    fontSize: 10,
                    margin: 2
                });
            } catch (e) {
                console.error('Barcode render error:', e);
            }
        }
    }

    // Export thermal receipt as PDF file using html2pdf.js
    window.exportReceiptPDF = function () {
        const element = document.getElementById('thermal-receipt');
        const billNo = document.getElementById('receipt-bill-no')?.innerText || 'INV-000000';

        if (window.html2pdf) {
            const opt = {
                margin: [2, 2, 2, 2],
                filename: `Bill_${billNo}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: [80, 220], orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        } else {
            window.print();
        }
    };

    function openReceiptModal() {
        if (receiptModalWrapper) {
            receiptModalWrapper.classList.remove('hidden');
        }
    }

    if (closeReceiptBtn) {
        closeReceiptBtn.addEventListener('click', () => {
            if (receiptModalWrapper) receiptModalWrapper.classList.add('hidden');
            if (barcodeInput) barcodeInput.focus();
        });
    }

    if (printReceiptBtn) {
        printReceiptBtn.addEventListener('click', () => {
            window.print();
        });
    }
});