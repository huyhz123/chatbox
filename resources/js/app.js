import './bootstrap';
import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import Swal from 'sweetalert2';
import { Chart } from 'chart.js/auto';

window.Alpine = Alpine;
window.Sortable = Sortable;
window.Swal = Swal;
window.Chart = Chart;

Alpine.start();

// Cart functionality
window.addToCart = function(productId, quantity = 1) {
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ product_id: productId, quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Product added to cart', 'success');
            updateCartCount();
        }
    });
};

// Update cart count in navbar
window.updateCartCount = function() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = data.count;
            });
        });
};

// Initialize drag and drop for admin tables
document.addEventListener('DOMContentLoaded', function() {
    const sortableTables = document.querySelectorAll('.sortable-table tbody');
    sortableTables.forEach(table => {
        new Sortable(table, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function(evt) {
                const order = Array.from(table.children).map((row, index) => ({
                    id: row.dataset.id,
                    order: index
                }));

                fetch('/admin/update-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ order })
                });
            }
        });
    });
});
