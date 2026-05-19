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
    window.flasher[type](message);
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

            // If on wishlist page and item was removed, fade out and remove the card from DOM
            if (!data.saved && window.location.pathname.includes('wishlist')) {
                const card = form.closest('div[class*="bg-white"][class*="rounded-lg"]');
                if (card) {
                    // Animate fade and scale out
                    card.style.transition = 'all 300ms ease-out';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';

                    // Remove from DOM after animation completes
                    setTimeout(() => {
                        card.remove();

                        // Check if grid is now empty
                        const grid = document.querySelector('[class*="grid"][class*="grid-cols"]');
                        if (grid && grid.children.length === 0) {
                            // Reload page to show empty state
                            location.reload();
                        }
                    }, 300);
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
