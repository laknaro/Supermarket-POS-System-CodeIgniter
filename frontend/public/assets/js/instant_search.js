/**
 * EGOTECH World Supermart - Global Instant Live Search Engine
 * 120ms Debounced multi-column client-side filter with clear button & fallback states
 */
document.addEventListener('DOMContentLoaded', () => {
    initGlobalInstantSearch();
});

function initGlobalInstantSearch() {
    // Find all search inputs across forms, tables, and grids
    const searchInputs = document.querySelectorAll('input[name="search"], input#search-input, input[type="text"][placeholder*="Filter"], input[type="text"][placeholder*="Search"]');

    searchInputs.forEach(input => {
        if (input.dataset.instantSearchAttached) return;
        input.dataset.instantSearchAttached = 'true';

        // 1. Wrap container and attach clear 'X' button
        const parent = input.parentElement;
        if (parent) {
            const computedStyle = window.getComputedStyle(parent);
            if (computedStyle.position === 'static') {
                parent.style.position = 'relative';
            }

            // Adjust input padding to fit clear icon
            input.style.paddingRight = '2.5rem';

            let clearBtn = parent.querySelector('.instant-search-clear');
            if (!clearBtn) {
                clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'instant-search-clear hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition cursor-pointer z-10';
                clearBtn.innerHTML = '<i class="fa-solid fa-circle-xmark text-sm"></i>';
                clearBtn.title = 'Clear search';
                parent.appendChild(clearBtn);

                clearBtn.addEventListener('click', () => {
                    input.value = '';
                    performInstantSearch(input, '');
                    clearBtn.classList.add('hidden');
                    input.focus();
                });
            }

            input.addEventListener('input', () => {
                if (input.value.trim() !== '') {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }
            });
        }

        // 2. Prevent form submission / reload on Enter inside search bar
        const form = input.closest('form');
        if (form) {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    // Check if single match auto-add in POS terminal
                    const grid = document.getElementById('product-cards-grid');
                    if (grid) {
                        const visibleCards = Array.from(grid.querySelectorAll('.product-card')).filter(c => c.style.display !== 'none');
                        if (visibleCards.length === 1) {
                            e.preventDefault();
                            visibleCards[0].click();
                            input.value = '';
                            performInstantSearch(input, '');
                            return;
                        }
                    }
                    e.preventDefault();
                }
            });
        }

        // 3. 120ms Debouncing Instant Search Trigger
        let debounceTimer = null;
        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performInstantSearch(this, this.value.trim());
            }, 120);
        });
    });
}

function performInstantSearch(inputEl, rawQuery) {
    const query = rawQuery.toLowerCase();

    // A. POS Terminal Grid Cards Filter (pos.php)
    const productGrid = document.getElementById('product-cards-grid');
    if (productGrid) {
        const cards = productGrid.querySelectorAll('.product-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = (card.dataset.name || '').toLowerCase();
            const barcode = (card.dataset.barcode || '').toLowerCase();
            const sku = (card.dataset.sku || '').toLowerCase();
            const cardText = card.textContent.toLowerCase();

            if (query === '' || name.includes(query) || barcode.includes(query) || sku.includes(query) || cardText.includes(query)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        renderGridNoMatches(productGrid, visibleCount, rawQuery);
        return;
    }

    // B. Table Rows Filter (products.php, inventory.php, reports.php, bill_history.php, categories.php, users.php)
    const pageTables = document.querySelectorAll('table');
    pageTables.forEach(table => {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr:not(.no-search-match-row)');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowContent = row.textContent.toLowerCase().replace(/\s+/g, ' ');

            if (query === '' || rowContent.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        renderTableNoMatches(tbody, visibleCount, table, rawQuery);
    });
}

function renderGridNoMatches(gridEl, count, query) {
    let msgNode = gridEl.querySelector('.no-search-match-grid');
    if (count === 0 && query !== '') {
        if (!msgNode) {
            msgNode = document.createElement('div');
            msgNode.className = 'no-search-match-grid col-span-full py-16 text-center text-slate-400';
            gridEl.appendChild(msgNode);
        }
        msgNode.innerHTML = `
            <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-300">
                <i class="fa-solid fa-magnifying-glass text-2xl"></i>
            </div>
            <h4 class="text-xs font-bold text-slate-600 mb-1">No matching products found</h4>
            <p class="text-[11px] text-slate-400">No items in catalog match "${escapeHtmlText(query)}"</p>
        `;
        msgNode.style.display = 'block';
    } else if (msgNode) {
        msgNode.style.display = 'none';
    }
}

function renderTableNoMatches(tbodyEl, count, tableEl, query) {
    let matchRow = tbodyEl.querySelector('.no-search-match-row');
    if (count === 0 && query !== '') {
        const colSpan = tableEl.querySelectorAll('thead th').length || 8;
        if (!matchRow) {
            matchRow = document.createElement('tr');
            matchRow.className = 'no-search-match-row';
            tbodyEl.appendChild(matchRow);
        }
        matchRow.innerHTML = `
            <td colspan="${colSpan}" class="py-12 text-center text-slate-400">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-300">
                    <i class="fa-solid fa-folder-open text-xl"></i>
                </div>
                <p class="text-xs font-semibold text-slate-600 mb-0.5">No matching records found</p>
                <span class="text-[11px] text-slate-400">No records match "${escapeHtmlText(query)}"</span>
            </td>
        `;
        matchRow.style.display = '';
    } else if (matchRow) {
        matchRow.style.display = 'none';
    }
}

function escapeHtmlText(str) {
    return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
}
