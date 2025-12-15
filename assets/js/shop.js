// Shop Page JavaScript - Complete Version
document.addEventListener('DOMContentLoaded', function () {
    console.log('Shop page loaded successfully');

    // ========== QUANTITY CONTROLS ==========
    // Handle quantity increase
    document.querySelectorAll('.qty-increase').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const input = this.parentElement.querySelector('.qty-input');
            let value = parseInt(input.value) || 1;
            const max = parseInt(input.getAttribute('max')) || 99;

            if (value < max) {
                value += 1;
                input.value = value;

                // Visual feedback
                this.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            }
        });
    });

    // Handle quantity decrease
    document.querySelectorAll('.qty-decrease').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const input = this.parentElement.querySelector('.qty-input');
            let value = parseInt(input.value) || 1;
            const min = parseInt(input.getAttribute('min')) || 1;

            if (value > min) {
                value -= 1;
                input.value = value;

                // Visual feedback
                this.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            }
        });
    });

    // ========== CATEGORY FILTER ==========
    const filterButtons = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const category = this.getAttribute('data-category');
            let visibleCount = 0;

            // Filter products
            productCards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'block';
                    visibleCount++;
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 10);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });

            // Update product count display
            updateProductCount(visibleCount, category);
        });
    });

    function updateProductCount(count, category) {
        const sectionHeader = document.querySelector('.section-header h2');
        const allCount = productCards.length;

        if (category === 'all') {
            sectionHeader.innerHTML = `<i class="fas fa-gamepad"></i> Available Products (${allCount})`;
        } else {
            const activeFilter = document.querySelector('.filter-btn.active');
            const categoryName = activeFilter.textContent.trim();
            sectionHeader.innerHTML = `<i class="fas fa-gamepad"></i> ${categoryName} (${count})`;
        }
    }

    // ========== SEARCH FUNCTIONALITY ==========
    function searchProducts() {
        const searchInput = document.getElementById('searchInput');
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        if (searchTerm === '') {
            // Reset to all products if search is empty
            filterButtons[0].click();
            return;
        }

        productCards.forEach(card => {
            const productName = card.querySelector('.product-name').textContent.toLowerCase();
            const productDesc = card.querySelector('.product-desc').textContent.toLowerCase();
            const category = card.querySelector('.category-badge').textContent.toLowerCase();

            if (productName.includes(searchTerm) ||
                productDesc.includes(searchTerm) ||
                category.includes(searchTerm)) {
                card.style.display = 'block';
                visibleCount++;
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 10);
            } else {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.display = 'none';
                }, 300);
            }
        });

        // Update section header with search results
        const sectionHeader = document.querySelector('.section-header h2');
        sectionHeader.innerHTML = `<i class="fas fa-search"></i> Search Results (${visibleCount})`;

        if (visibleCount === 0) {
            sectionHeader.innerHTML = `<i class="fas fa-search"></i> No Results Found`;
        }
    }

    // Attach search function to global scope
    window.searchProducts = searchProducts;

    // Add Enter key support for search
    document.getElementById('searchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            searchProducts();
        }
    });

    // Clear search when clicking on filter
    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('searchInput').value = '';
        });
    });

    // ========== ADD TO CART FORM HANDLING ==========
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const productId = this.querySelector('input[name="product_id"]').value;
            const productCard = this.closest('.product-card');
            const quantityInput = productCard.querySelector('.qty-input');
            const quantity = quantityInput ? quantityInput.value : 1;
            const productName = productCard.querySelector('.product-name').textContent;
            const button = this.querySelector('.add-cart-btn');
            const originalText = button.innerHTML;
            const originalBackground = button.style.background;

            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            button.disabled = true;

            // Create form data for AJAX request
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            // Make AJAX request to add_to_cart.php
            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update cart count from server response
                        const cartCount = document.querySelector('.cart-count');
                        cartCount.textContent = data.cart_count || 0;

                        // Add bounce animation
                        cartCount.style.animation = 'none';
                        setTimeout(() => {
                            cartCount.style.animation = 'bounce 0.5s';
                        }, 10);

                        // Show success message
                        button.innerHTML = '<i class="fas fa-check"></i> Added!';
                        button.style.background = '#10b981';

                        // Show notification with server message
                        showNotification(data.message || `${productName} (x${quantity}) added to cart!`, 'success');

                        // Reset button after 2 seconds
                        setTimeout(() => {
                            button.innerHTML = originalText;
                            button.style.background = originalBackground;
                            button.disabled = false;
                        }, 2000);

                        console.log('Successfully added to cart:', data);
                    } else {
                        // Show error message from server
                        button.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error';
                        button.style.background = '#ef4444';
                        showNotification(data.message || 'Failed to add to cart', 'error');

                        // Reset button after 2 seconds
                        setTimeout(() => {
                            button.innerHTML = originalText;
                            button.style.background = originalBackground;
                            button.disabled = false;
                        }, 2000);

                        console.error('Add to cart error:', data);
                    }
                })
                .catch(error => {
                    console.error('Network error:', error);
                    button.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error';
                    button.style.background = '#ef4444';
                    showNotification('Network error. Please try again.', 'error');

                    // Reset button after 2 seconds
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.style.background = originalBackground;
                        button.disabled = false;
                    }, 2000);
                });
        });
    });

    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;

        // Add to body
        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Add notification animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    // ========== ANIMATIONS ==========
    // Initial fade-in animation for products
    productCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';

        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + (index * 80));
    });

    // Update initial product count
    updateProductCount(productCards.length, 'all');

    // Function to update cart count on page load (optional)
    function updateCartCountOnLoad() {
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartCount = document.querySelector('.cart-count');
                    cartCount.textContent = data.cart_count || 0;
                }
            })
            .catch(error => {
                console.error('Error fetching cart count:', error);
            });
    }

    // Uncomment this if you create get_cart_count.php
    // updateCartCountOnLoad();
});