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
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Show toast with global function
            showToast(data.message);

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
            }
        } else {
            showToast(data.message || 'Failed to update wishlist', null);
        }
    } catch (error) {
        console.error('Wishlist error:', error);
        showToast('An error occurred. Please try again.', null);
    }
}
