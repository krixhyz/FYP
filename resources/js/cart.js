/**
 * Cart Management
 * Handles AJAX cart operations with real-time badge updates
 */

// Make functions globally accessible
window.cartModule = {
    updateBadge: function(count) {
        // Ensure count is a number
        const numCount = parseInt(count, 10);
        
        const badge = document.getElementById('cart-count');
        if (!badge) return;
        
        if (numCount > 0) {
            badge.textContent = numCount > 99 ? '99+' : numCount;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
            badge.textContent = '';
        }
    },
    
    fetchCount: async function() {
        try {
            const response = await fetch('/cart/count', {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            
            if (response.ok) {
                const data = await response.json();
                this.updateBadge(data.count);
            }
        } catch (error) {
            console.error('[Cart] Fetch count error:', error);
        }
    },

    formatCurrency: function(amount) {
        const value = Number.isFinite(amount) ? amount : 0;
        return `Rs. ${value.toFixed(2)}`;
    },

    updateRowLineTotal: function(form, quantity) {
        const row = form.closest('[data-cart-item], [data-checkout-item]');
        if (!row) return;

        const unitPrice = parseFloat(row.getAttribute('data-unit-price') || '0');
        const lineTotal = unitPrice * quantity;
        row.setAttribute('data-line-total', String(lineTotal));

        const lineTotalEl = row.querySelector('[data-line-total-display]');
        if (lineTotalEl) {
            lineTotalEl.textContent = this.formatCurrency(lineTotal);
        }

        const inlineSummaryEl = row.querySelector('[data-line-summary]');
        if (inlineSummaryEl) {
            inlineSummaryEl.textContent = `Unit: ${this.formatCurrency(unitPrice)} | Line Total: ${this.formatCurrency(lineTotal)}`;
        }
    },

    recalculateTotals: function() {
        const cartRows = Array.from(document.querySelectorAll('[data-cart-item]'));
        if (cartRows.length > 0) {
            const total = cartRows.reduce((sum, row) => {
                return sum + parseFloat(row.getAttribute('data-line-total') || '0');
            }, 0);

            const totalEl = document.querySelector('[data-cart-grand-total]');
            if (totalEl) {
                totalEl.textContent = this.formatCurrency(total);
            }
        }

        const checkoutRows = Array.from(document.querySelectorAll('[data-checkout-item]'));
        if (checkoutRows.length > 0) {
            const total = checkoutRows.reduce((sum, row) => {
                return sum + parseFloat(row.getAttribute('data-line-total') || '0');
            }, 0);

            const totalEl = document.querySelector('[data-checkout-total]');
            if (totalEl) {
                totalEl.textContent = this.formatCurrency(total);
            }
        }
    }
};

// Setup handlers on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Initial load on page load
    window.cartModule.fetchCount();
    
    // Setup all cart forms
    setupFormHandlers();
});

function showToastMessage(message, type = 'info') {
    if (window.toastr && typeof window.toastr[type] === 'function') {
        window.toastr[type](message);
        return;
    }

    if (typeof window.showToast === 'function') {
        window.showToast(message, null, null, type);
        return;
    }

    if (type === 'error') {
        console.error(message);
    } else {
        console.log(message);
    }
}

function getApiErrorMessage(data, fallback) {
    if (data && typeof data.message === 'string' && data.message.trim() !== '') {
        return data.message;
    }

    if (data && data.errors && typeof data.errors === 'object') {
        const firstError = Object.values(data.errors)[0];
        if (Array.isArray(firstError) && firstError.length > 0) {
            return String(firstError[0]);
        }
    }

    return fallback;
}

function normalizeQuantityInput(input) {
    if (!input) return;

    const min = parseInt(input.min || '1', 10);
    const max = parseInt(input.max || String(min), 10);
    const value = parseInt(input.value, 10);

    if (Number.isNaN(value)) {
        input.value = min;
        return;
    }

    input.value = Math.min(max, Math.max(min, value));
}

function setupFormHandlers() {
    // Add to cart
    document.querySelectorAll('form[data-cart-action="add"]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new FormData(form),
                });

                // Middleware may redirect with HTML when auth/session is stale.
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }

                let data = {};
                const isJson = (response.headers.get('content-type') || '').includes('application/json');
                if (isJson) {
                    data = await response.json();
                }

                if (response.ok && data.success && data.cartCount !== undefined) {
                    showToastMessage('Item added to cart', 'success');
                    window.cartModule.updateBadge(data.cartCount);
                } else {
                    const message = getApiErrorMessage(data, response.status === 419
                        ? 'Session expired. Please refresh and try again.'
                        : 'Unable to add item to cart.');
                    showToastMessage(message, 'error');
                }
            } catch (error) {
                console.error('[Cart] Add error:', error);
                showToastMessage('Error adding to cart', 'error');
            }
        });
    });
    
    // Update quantity (cart + checkout)
    document.querySelectorAll('form[data-cart-action="update"], form[data-cart-action="checkout-update"]').forEach(form => {
        const input = form.querySelector('input[name="quantity"]');
        if (input) {
            input.addEventListener('input', () => normalizeQuantityInput(input));
            input.addEventListener('blur', () => normalizeQuantityInput(input));
            normalizeQuantityInput(input);
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const qtyInput = form.querySelector('input[name="quantity"]');
            normalizeQuantityInput(qtyInput);
            const nextQty = qtyInput ? parseInt(qtyInput.value || '1', 10) : 1;
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(new FormData(form)),
                });
 
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }

                const isJson = (response.headers.get('content-type') || '').includes('application/json');
                const data = isJson ? await response.json() : {};
                
                if (data.success && data.cartCount !== undefined) {
                    const safeQty = Number.isNaN(nextQty) ? 1 : nextQty;
                    window.cartModule.updateRowLineTotal(form, safeQty);
                    window.cartModule.recalculateTotals();
                    showToastMessage(data.message || 'Cart updated', 'success');
                    window.cartModule.updateBadge(data.cartCount);
                } else {
                    showToastMessage(getApiErrorMessage(data, 'Error updating cart'), 'error');
                }
            } catch (error) {
                console.error('[Cart] Update error:', error);
                showToastMessage('Error updating cart', 'error');
            }
        });
    });
    
    // Remove from cart
    document.querySelectorAll('form[data-cart-action="remove"]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-HTTP-Method-Override': 'DELETE',
                    },
                    body: new URLSearchParams(new FormData(form)),
                });
 
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }

                const isJson = (response.headers.get('content-type') || '').includes('application/json');
                const data = isJson ? await response.json() : {};
                
                if (data.success && data.cartCount !== undefined) {
                    showToastMessage(data.message || 'Item removed from cart', 'success');
                    window.cartModule.updateBadge(data.cartCount);
                    
                    // Remove row if present
                    const row = form.closest('[data-cart-item]');
                    if (row) {
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            window.cartModule.recalculateTotals();
                        }, 300);
                    } else {
                        window.cartModule.recalculateTotals();
                    }
                } else {
                    showToastMessage(getApiErrorMessage(data, 'Error removing from cart'), 'error');
                }
            } catch (error) {
                console.error('[Cart] Remove error:', error);
                showToastMessage('Error removing from cart', 'error');
            }
        });
    });
}
