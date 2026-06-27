import './bootstrap';

import Alpine from 'alpinejs';

const isFilamentPanel = () => document.documentElement.classList.contains('fi')
    || document.body?.classList.contains('fi-body')
    || typeof window.filamentData !== 'undefined';

function showNotification(message, type = 'success') {
    const id = 'notif-' + Date.now();
    const colors = type === 'success'
        ? 'bg-green-50 border-green-200 text-green-700'
        : 'bg-red-50 border-red-200 text-red-700';

    const el = document.createElement('div');
    el.id = id;
    el.className = `fixed top-4 right-4 z-50 px-6 py-3.5 rounded-lg shadow-lg border ${colors} text-sm font-medium transition-all duration-300 transform translate-x-0`;
    el.textContent = message;
    document.body.appendChild(el);

    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transform = 'translateX(100%)';
        setTimeout(() => el.remove(), 300);
    }, 3000);
}

function registerStorefrontStores(alpine) {
    alpine.store('cart', {
        open: false,
        count: 0,
        items: [],
        total: 0,

        init() {
            this.count = window.cartCount || 0;
            this.items = window.cartItems || [];
            this.calculateTotal();
        },

        calculateTotal() {
            this.total = this.items.reduce((sum, item) => sum + (item.price || 0) * (item.quantity || 0), 0);
        },

        async add(productId, quantity = 1) {
            try {
                const response = await fetch(window.routes.cartAdd, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ product_id: productId, quantity })
                });

                const data = await response.json();

                if (data.success) {
                    this.count = data.cartCount;
                    this.items = data.cartItems;
                    this.calculateTotal();
                    this.open = true;
                    this.updateCartCountElements();
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Failed to add item to cart.', 'error');
            }
        },

        async update(productId, quantity) {
            if (quantity < 1) return;

            try {
                const response = await fetch(window.routes.cartUpdate.replace('**ID**', productId), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ quantity })
                });

                const data = await response.json();

                if (data.success) {
                    this.count = data.cartCount;
                    this.items = data.cartItems;
                    this.calculateTotal();
                    this.updateCartCountElements();
                    showNotification('Cart updated!', 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Failed to update cart.', 'error');
            }
        },

        async remove(productId) {
            try {
                const response = await fetch(window.routes.cartRemove.replace('**ID**', productId), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.count = data.cartCount;
                    this.items = data.cartItems;
                    this.calculateTotal();
                    this.updateCartCountElements();
                    showNotification('Item removed from cart.', 'success');
                }
            } catch (error) {
                showNotification('Failed to remove item.', 'error');
            }
        },

        updateCartCountElements() {
            document.querySelectorAll('[data-cart-count]').forEach((el) => {
                el.textContent = this.count;
            });
        }
    });

    alpine.store('wishlist', {
        open: false
    });
}

if (!isFilamentPanel()) {
    const shouldStartAlpine = typeof window.Alpine === 'undefined';
    const alpine = window.Alpine ?? Alpine;

    registerStorefrontStores(alpine);

    if (shouldStartAlpine) {
        window.Alpine = alpine;
        alpine.start();
    }
}
