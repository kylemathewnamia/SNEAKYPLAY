// user_dashboard.js - Complete JavaScript
document.addEventListener('DOMContentLoaded', function () {
    console.log('SneakyPlay2 Dashboard loaded successfully!');

    // ====== USER DROPDOWN FUNCTIONALITY ======
    const userMenu = document.querySelector('.user-menu');
    if (userMenu) {
        userMenu.addEventListener('click', function (e) {
            e.stopPropagation();
            const dropdown = this.querySelector('.dropdown');
            if (dropdown) {
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function () {
            const dropdown = document.querySelector('.dropdown');
            if (dropdown) {
                dropdown.style.display = 'none';
            }
        });
    }

    // ====== SIDEBAR NAVIGATION ======
    const sidebarLinks = document.querySelectorAll('.sidebar a');
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';

    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'index.php')) {
            link.classList.add('active');
        }

        link.addEventListener('click', function (e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                // Remove active class from all links
                sidebarLinks.forEach(l => l.classList.remove('active'));
                // Add active class to clicked link
                this.classList.add('active');
            }
        });
    });

    // ====== ADD TO CART FUNCTIONALITY ======
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');

            // Show loading state
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;

            // Simulate API call
            setTimeout(() => {
                showNotification(`${productName} added to cart!`, 'success');

                // Update button state
                this.innerHTML = '<i class="fas fa-check"></i> Added';

                // Reset after 2 seconds
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                }, 2000);

                // Update cart count (simulated)
                updateCartCount(1);

            }, 800);
        });
    });

    // ====== VIEW PRODUCT FUNCTIONALITY ======
    const viewProductButtons = document.querySelectorAll('.view-product');
    viewProductButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');

            // Show loading notification
            showNotification('Loading product details...', 'info');

            // Simulate navigation
            setTimeout(() => {
                window.location.href = `product.php?id=${productId}`;
            }, 500);
        });
    });

    // ====== SEARCH FUNCTIONALITY ======
    const searchInput = document.querySelector('.search-bar input');
    const searchButton = document.querySelector('.search-bar button');

    if (searchInput && searchButton) {
        searchButton.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }

    function performSearch() {
        const query = searchInput.value.trim();
        if (!query) {
            showNotification('Please enter a search term', 'warning');
            searchInput.focus();
            return;
        }

        showNotification(`Searching for: "${query}"`, 'info');

        // In a real application, this would redirect to search results
        setTimeout(() => {
            // window.location.href = `search.php?q=${encodeURIComponent(query)}`;
            console.log(`Search query: ${query}`);
        }, 1000);
    }

    // ====== NOTIFICATION SYSTEM ======
    function showNotification(message, type = 'info') {
        // Create notification container if it doesn't exist
        let container = document.querySelector('.notification-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'notification-container';
            document.body.appendChild(container);

            // Add notification styles
            const styles = document.createElement('style');
            styles.textContent = `
                .notification-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                    max-width: 350px;
                }
                
                .notification {
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    padding: 1rem;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    transform: translateX(150%);
                    transition: transform 0.3s ease-out;
                    border-left: 4px solid;
                }
                
                .notification.show {
                    transform: translateX(0);
                }
                
                .notification.success {
                    border-left-color: var(--success-color);
                }
                
                .notification.error {
                    border-left-color: var(--danger-color);
                }
                
                .notification.warning {
                    border-left-color: var(--warning-color);
                }
                
                .notification.info {
                    border-left-color: var(--primary-color);
                }
                
                .notification-content {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                }
                
                .notification-content i {
                    font-size: 1.25rem;
                }
                
                .notification.success .notification-content i {
                    color: var(--success-color);
                }
                
                .notification.error .notification-content i {
                    color: var(--danger-color);
                }
                
                .notification.warning .notification-content i {
                    color: var(--warning-color);
                }
                
                .notification.info .notification-content i {
                    color: var(--primary-color);
                }
                
                .notification-close {
                    background: none;
                    border: none;
                    font-size: 1.25rem;
                    cursor: pointer;
                    color: var(--text-secondary);
                    padding: 0;
                    margin-left: 1rem;
                    opacity: 0.7;
                    transition: opacity 0.2s;
                }
                
                .notification-close:hover {
                    opacity: 1;
                }
            `;
            document.head.appendChild(styles);
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;

        // Get icon based on type
        let icon = 'fa-info-circle';
        switch (type) {
            case 'success': icon = 'fa-check-circle'; break;
            case 'error': icon = 'fa-exclamation-circle'; break;
            case 'warning': icon = 'fa-exclamation-triangle'; break;
        }

        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${icon}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">&times;</button>
        `;

        // Add notification to container
        container.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Auto remove after 5 seconds
        const autoRemove = setTimeout(() => {
            removeNotification(notification);
        }, 5000);

        // Manual close button
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', function () {
            clearTimeout(autoRemove);
            removeNotification(notification);
        });
    }

    function removeNotification(notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }

    // ====== HELPER FUNCTIONS ======
    function updateCartCount(change) {
        // This would typically come from an API response
        // For now, we'll simulate it
        const cartStat = document.querySelector('.stat-card:nth-child(3) h3');
        if (cartStat) {
            const currentCount = parseInt(cartStat.textContent) || 0;
            cartStat.textContent = currentCount + change;

            // Add animation
            cartStat.style.transform = 'scale(1.2)';
            setTimeout(() => {
                cartStat.style.transform = 'scale(1)';
            }, 300);
        }
    }

    // ====== ANIMATIONS ======
    // Add fade-in animation to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';

        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // ====== ORDER DETAILS MODAL (Optional) ======
    // If you want to add modal functionality for order details
    const orderDetailLinks = document.querySelectorAll('a[href*="order-details"]');
    orderDetailLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            // You could prevent default and show a modal here
            // For now, just log
            console.log('Viewing order details');
        });
    });

    // ====== PAGE LOAD ANIMATION ======
    // Add a slight fade-in to the entire content
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.style.opacity = '0';
        mainContent.style.transition = 'opacity 0.3s ease';

        setTimeout(() => {
            mainContent.style.opacity = '1';
        }, 100);
    }

    // ====== WELCOME MESSAGE ======
    // Show a welcome notification if it's the first visit today
    const lastVisit = localStorage.getItem('lastDashboardVisit');
    const today = new Date().toDateString();

    if (lastVisit !== today) {
        setTimeout(() => {
            showNotification('Welcome back to your dashboard!', 'success');
            localStorage.setItem('lastDashboardVisit', today);
        }, 1000);
    }
});