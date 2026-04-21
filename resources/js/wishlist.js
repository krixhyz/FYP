/**
 * Wishlist AJAX Handler
 * Handles wishlist toggle via AJAX
 * Shows toast notifications on success/error
 */

document.addEventListener('DOMContentLoaded', () => {
    // Attach wishlist toggle handlers to all wishlist forms
    document.querySelectorAll('form[data-wishlist-action]').forEach(form => {
        form.addEventListener('submit', handleWishlistToggleSubmit);
    });
});

function showWishlistToast(message, type = 'info') {
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

/**
 * Handle wishlist toggle form submission
 */
async function handleWishlistToggleSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const productId = form.dataset.productId;

    try {
        const response = await fetch(form.action, {
            method: form.method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        if (response.redirected) {
            window.location.href = response.url;
            return;
        }

        const isJson = (response.headers.get('content-type') || '').includes('application/json');
        const data = isJson ? await response.json() : {};

        if (response.ok && data.success) {
            showWishlistToast(data.saved ? 'Added to wishlist' : 'Removed from wishlist', data.saved ? 'success' : 'info');

            // Update the wishlist button icon
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                const svg = button.querySelector('svg');
                if (svg) {
                    // Toggle filled/unfilled heart based on saved status
                    if (data.saved) {
                        svg.setAttribute('fill', 'currentColor');
                    } else {
                        svg.setAttribute('fill', 'none');
                    }
                }
            }

            // Update button title
            if (button) {
                button.title = data.saved ? 'Remove from wishlist' : 'Save to wishlist';

                const textEl = button.querySelector('span');
                if (textEl) {
                    textEl.textContent = data.saved ? 'Remove from Wishlist' : 'Add to Wishlist';
                }
            }
        } else {
            showWishlistToast(data.message || 'Failed to update wishlist', 'error');
        }
    } catch (error) {
        console.error('Wishlist error:', error);
        showWishlistToast('An error occurred. Please try again.', 'error');
    }
}
