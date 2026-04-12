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
    }
};

// Setup handlers on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Initial load on page load
    window.cartModule.fetchCount();
    
    // Setup all cart forms
    setupFormHandlers();
});

function setupFormHandlers() {
    // Add to cart
    document.querySelectorAll('form[data-cart-action="add"]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: new FormData(form),
                });
                
                const data = await response.json();
                
                if (data.success && data.cartCount !== undefined) {
                    if (window.toastr) window.toastr.success(data.message);
                    window.cartModule.updateBadge(data.cartCount);
                } else {
                    if (window.toastr) window.toastr.error(data.message || 'Error');
                }
            } catch (error) {
                console.error('[Cart] Add error:', error);
                if (window.toastr) window.toastr.error('Error adding to cart');
            }
        });
    });
    
    // Update quantity
    document.querySelectorAll('form[data-cart-action="update"]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(new FormData(form)),
                });
                
                const data = await response.json();
                
                if (data.success && data.cartCount !== undefined) {
                    if (window.toastr) window.toastr.success(data.message);
                    window.cartModule.updateBadge(data.cartCount);
                } else {
                    if (window.toastr) window.toastr.error(data.message || 'Error');
                }
            } catch (error) {
                console.error('[Cart] Update error:', error);
                if (window.toastr) window.toastr.error('Error updating cart');
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
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-HTTP-Method-Override': 'DELETE',
                    },
                    body: new URLSearchParams(new FormData(form)),
                });
                
                const data = await response.json();
                
                if (data.success && data.cartCount !== undefined) {
                    if (window.toastr) window.toastr.success(data.message);
                    window.cartModule.updateBadge(data.cartCount);
                    
                    // Remove row if present
                    const row = form.closest('[data-cart-item]');
                    if (row) {
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }
                } else {
                    if (window.toastr) window.toastr.error(data.message || 'Error');
                }
            } catch (error) {
                console.error('[Cart] Remove error:', error);
                if (window.toastr) window.toastr.error('Error removing from cart');
            }
        });
    });
}
