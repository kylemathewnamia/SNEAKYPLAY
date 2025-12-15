// Orders Page JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Close notification - ONLY if notification exists
    const notificationCloseBtns = document.querySelectorAll('.notification-close');
    if (notificationCloseBtns.length > 0) {
        notificationCloseBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const notification = this.closest('.notification');
                if (notification) {
                    notification.classList.add('hide');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }
            });
        });

        // Auto-remove notification after 5 seconds
        const notification = document.querySelector('.notification');
        if (notification) {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.classList.add('hide');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }
            }, 5000);
        }
    }

    // Filter tabs functionality - ONLY if tabs exist
    const filterTabs = document.querySelectorAll('.filter-tab');
    const orderCards = document.querySelectorAll('.order-card');

    if (filterTabs.length > 0 && orderCards.length > 0) {
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function () {
                // Remove active class from all tabs
                filterTabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');

                const filterValue = this.getAttribute('data-filter');

                // Filter order cards
                orderCards.forEach(card => {
                    if (filterValue === 'all') {
                        card.style.display = 'block';
                    } else {
                        const status = card.getAttribute('data-status');
                        card.style.display = status === filterValue ? 'block' : 'none';
                    }
                });
            });
        });
    }

    // Date filter functionality - ONLY if element exists
    const applyDateFilter = document.getElementById('applyDateFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');

    if (applyDateFilter && dateFrom && dateTo && orderCards.length > 0) {
        applyDateFilter.addEventListener('click', function () {
            const fromDate = dateFrom.value ? new Date(dateFrom.value) : null;
            const toDate = dateTo.value ? new Date(dateTo.value) : null;

            orderCards.forEach(card => {
                const orderDate = new Date(card.getAttribute('data-date'));
                let shouldShow = true;

                if (fromDate && orderDate < fromDate) {
                    shouldShow = false;
                }

                if (toDate && orderDate > toDate) {
                    shouldShow = false;
                }

                card.style.display = shouldShow ? 'block' : 'none';
            });
        });
    }

    // Sort functionality - ONLY if element exists
    const sortSelect = document.getElementById('sortOrders');
    const ordersContainer = document.getElementById('ordersContainer');

    if (sortSelect && ordersContainer && orderCards.length > 0) {
        sortSelect.addEventListener('change', function () {
            const sortValue = this.value;
            const ordersArray = Array.from(orderCards);

            ordersArray.sort((a, b) => {
                switch (sortValue) {
                    case 'newest':
                        return new Date(b.getAttribute('data-date')) - new Date(a.getAttribute('data-date'));
                    case 'oldest':
                        return new Date(a.getAttribute('data-date')) - new Date(b.getAttribute('data-date'));
                    case 'price-high':
                        return parseFloat(b.getAttribute('data-amount')) - parseFloat(a.getAttribute('data-amount'));
                    case 'price-low':
                        return parseFloat(a.getAttribute('data-amount')) - parseFloat(b.getAttribute('data-amount'));
                    default:
                        return 0;
                }
            });

            // Reorder cards in container
            ordersArray.forEach(card => {
                ordersContainer.appendChild(card);
            });
        });
    }

    // Reorder functionality - ONLY if buttons exist
    const reorderBtns = document.querySelectorAll('.reorder');
    if (reorderBtns.length > 0) {
        reorderBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const orderId = this.getAttribute('data-order-id');
                if (confirm('Reorder all items from this order?')) {
                    // Add loading state
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding to cart...';
                    this.disabled = true;

                    // Simulate AJAX request
                    setTimeout(() => {
                        alert('All items from this order have been added to your cart!');
                        this.innerHTML = '<i class="fas fa-redo"></i> Reorder';
                        this.disabled = false;
                    }, 1500);
                }
            });
        });
    }

    // Track order functionality - ONLY if buttons exist
    const trackOrderBtns = document.querySelectorAll('.track-order');
    if (trackOrderBtns.length > 0) {
        trackOrderBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const orderId = this.getAttribute('data-order-id');
                alert(`Tracking for Order #${orderId}\n\nThis feature would typically redirect to a tracking page or show tracking details.`);
            });
        });
    }
});

// ================================================
// CANCEL FUNCTION - MUST BE DECLARED OUTSIDE DOMContentLoaded
// ================================================

function confirmCancel(orderId) {
    // Show confirmation dialog
    if (confirm('Are you sure you want to cancel this order?\n\nThis action cannot be undone.')) {
        // Get the clicked button
        const btn = event.target.closest('.cancel-order') || event.target;

        // Show loading state
        if (btn) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
            btn.disabled = true;
        }

        // Redirect to cancellation URL
        window.location.href = 'orders.php?cancel_order=1&order_id=' + orderId;
        return true;
    }
    return false;
}

// Make sure function is available globally
window.confirmCancel = confirmCancel;