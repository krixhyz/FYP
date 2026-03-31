/**
 * Cart AJAX Handler
 * Handles add-to-cart, update-quantity, and remove-from-cart via AJAX
 * Shows toast notifications on success/error
 */

document.addEventListener('DOMContentLoaded', () => {
    // Store add-to-cart forms
    document.querySelectorAll('form[data-cart-action="add"]').forEach(form => {
        form.addEventListener('submit', handleAddToCartSubmit);
    });

    // Cart index update forms
    document.querySelectorAll('form[data-cart-action="update"]').forEach(form => {
        form.addEventListener('submit', handleCartUpdateSubmit);
    });

    // Cart index remove forms
    document.querySelectorAll('form[data-cart-action="remove"]').forEach(form => {
        form.addEventListener('submit', handleCartRemoveSubmit);
    });

    // Cart checkout update forms
    document.querySelectorAll('form[data-cart-action="checkout-update"]').forEach(form => {
        form.addEventListener('submit', handleCheckoutUpdateSubmit);
    });
});

/**
 * Handle add-to-cart form submission
 */
async function handleAddToCartSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    // Add Accept header to get JSON response
    try {
        const response = await fetch(form.action, {
            method: form.method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();

        if (response.ok && data.success) {
            showCartSuccessToast(data.message);
        } else {
            showCartErrorToast(data.message || 'Failed to add to cart');
        }
    } catch (error) {
        console.error('Cart error:', error);
        showCartErrorToast('An error occurred. Please try again.');
    }
}

/**
 * Handle cart item update (quantity change)
 */
async function handleCartUpdateSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(formData),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            showCartSuccessToast(data.message);
        } else {
            showCartErrorToast(data.message || 'Failed to update cart');
        }
    } catch (error) {
        console.error('Cart error:', error);
        showCartErrorToast('An error occurred. Please try again.');
    }
}

/**
 * Handle cart item removal
 */
async function handleCartRemoveSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-HTTP-Method-Override': 'DELETE',
            },
            body: new URLSearchParams(formData),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            showCartSuccessToast(data.message);
            // Remove the item row from DOM
            const itemRow = form.closest('[data-cart-item]');
            if (itemRow) {
                itemRow.style.opacity = '0';
                itemRow.style.transform = 'translateX(100px)';
                setTimeout(() => itemRow.remove(), 300);
            }
        } else {
            showCartErrorToast(data.message || 'Failed to remove from cart');
        }
    } catch (error) {
        console.error('Cart error:', error);
        showCartErrorToast('An error occurred. Please try again.');
    }
}

/**
 * Handle checkout page quantity updates (pure form submission, no AJAX)
 */
async function handleCheckoutUpdateSubmit(e) {
    // Keep this as regular form submission since checkout page handles it server-side
    // Just show toast on redirect (handled by form action)
    return true;
}
