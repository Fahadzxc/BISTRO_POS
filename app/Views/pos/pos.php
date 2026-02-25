<?= view('layouts/_sidebar', ['currentPage' => 'pos']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">POS</span>
        <div class="user-info">
            <span class="text-muted small"><?= esc(session()->get('name')) ?></span>
            <span class="badge bg-warning text-dark role-badge"><?= esc(session()->get('role')) ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-left me-1"></i>Logout
            </a>
        </div>
    </header>

    <div class="pos-container">
        <div class="pos-products">
            <div class="pos-category-bar">
                <button type="button" class="btn btn-outline-primary pos-cat-btn active" data-category="">
                    All
                </button>
                <?php foreach ($categories as $cat): ?>
                <button type="button" class="btn btn-outline-primary pos-cat-btn" data-category="<?= $cat['id'] ?>">
                    <?= esc($cat['name']) ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="pos-product-grid" id="posProductGrid">
                <div class="text-center py-5 text-muted" id="posProductsLoading">
                    <div class="spinner-border" role="status"></div>
                    <p class="mt-2 mb-0">Loading products...</p>
                </div>
            </div>
        </div>

        <div class="pos-cart">
            <div class="pos-cart-header">
                <i class="bi bi-cart3 me-2"></i>Order Cart
            </div>
            <div class="pos-cart-items" id="posCartItems">
                <div class="pos-empty-cart">Cart is empty</div>
            </div>
            <div class="pos-cart-footer">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span id="posSubtotal">₱0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-3 pos-cart-total">
                    <span>Total</span>
                    <span id="posTotal">₱0.00</span>
                </div>
                <button type="button" class="btn btn-primary btn-checkout" id="posCheckoutBtn" disabled>
                    <i class="bi bi-credit-card me-2"></i>Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Total</label>
                    <input type="text" class="form-control form-control-lg" id="paymentTotal" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" id="paymentMethod">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                    </select>
                </div>
                <div class="mb-3" id="cashInputGroup">
                    <label class="form-label">Cash Received</label>
                    <input type="number" class="form-control form-control-lg" id="paymentCash" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="mb-0" id="changeGroup" style="display:none;">
                    <label class="form-label">Change</label>
                    <input type="text" class="form-control form-control-lg text-success fw-bold" id="paymentChange" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-lg" id="paymentConfirmBtn">
                    <i class="bi bi-check-lg me-1"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Order Complete!</h5>
                <p class="mb-0 text-muted" id="successInvoice"></p>
                <p class="mb-0 fw-bold" id="successTotal"></p>
                <p class="mb-0 small text-muted" id="successChange"></p>
            </div>
        </div>
    </div>
</div>

<script>
const POS = {
    cfg: {
        addToCart: '<?= $urlAddToCart ?? '' ?>',
        updateCart: '<?= $urlUpdateCart ?? '' ?>',
        removeFromCart: '<?= $urlRemoveFromCart ?? '' ?>',
        getCart: '<?= $urlGetCart ?? '' ?>',
        getProducts: '<?= $urlGetProducts ?? '' ?>',
        checkout: '<?= $urlCheckout ?? '' ?>',
        csrfToken: '<?= $csrfToken ?? '' ?>',
        csrfName: '<?= $csrfName ?? '' ?>'
    },
    async fetch(url, data = {}) {
        const body = new FormData();
        body.append(this.cfg.csrfName, this.cfg.csrfToken);
        for (const k in data) body.append(k, data[k]);
        const r = await fetch(url, { method: 'POST', body });
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Server returned non-JSON. URL:', url, 'Status:', r.status);
            return { success: false, message: 'Server error. Please refresh the page.' };
        }
    },
    async getProducts(catId = '') {
        const url = this.cfg.getProducts + (catId ? '?category_id=' + catId : '');
        const r = await fetch(url);
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('getProducts: Server returned non-JSON');
            return [];
        }
    },
    async addToCart(productId, qty = 1) {
        return this.fetch(this.cfg.addToCart, { product_id: productId, qty });
    },
    async updateCart(productId, qty) {
        return this.fetch(this.cfg.updateCart, { product_id: productId, qty });
    },
    async removeFromCart(productId, cartKey) {
        const data = cartKey ? { cart_key: cartKey } : { product_id: productId };
        return this.fetch(this.cfg.removeFromCart, data);
    },
    async getCart() {
        return this.fetch(this.cfg.getCart);
    },
    async checkout(paymentMethod, cash = 0) {
        return this.fetch(this.cfg.checkout, { payment_method: paymentMethod, cash });
    },
    formatPrice(n) {
        return '₱' + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },
    renderProducts(products) {
        const grid = document.getElementById('posProductGrid');
        if (!grid) return;
        grid.innerHTML = products.map(p => `
            <div class="pos-product-card" data-id="${p.id}" data-name="${p.name}" data-price="${p.price}" title="Click to add to cart">
                <div class="product-img">
                    ${p.image ? `<img src="${p.image}" style="width:100%;height:100%;object-fit:cover;border-radius:8px">` : '<i class="bi bi-box-seam"></i>'}
                </div>
                <div class="product-name">${p.name}</div>
                <div class="product-price">${this.formatPrice(p.price)}</div>
                <button type="button" class="btn btn-sm btn-primary pos-add-btn mt-1 w-100">
                    <i class="bi bi-plus-lg"></i> Add
                </button>
            </div>
        `).join('');
        grid.querySelectorAll('.pos-product-card').forEach(card => {
            card.addEventListener('click', (e) => {
                e.preventDefault();
                this.onAddProduct(card.dataset.id, card.dataset.name, card.dataset.price);
            });
        });
    },
    async onAddProduct(id, name, price) {
        const r = await this.addToCart(id, 1);
        if (r.success) this.renderCart(r.cart);
        else alert(r.message || 'Error');
    },
    renderCart(cart) {
        const itemsEl = document.getElementById('posCartItems');
        const subtotalEl = document.getElementById('posSubtotal');
        const totalEl = document.getElementById('posTotal');
        const btn = document.getElementById('posCheckoutBtn');

        if (!cart.items || cart.items.length === 0) {
            itemsEl.innerHTML = '<div class="pos-empty-cart">Cart is empty</div>';
            subtotalEl.textContent = this.formatPrice(0);
            totalEl.textContent = this.formatPrice(0);
            btn.disabled = true;
            return;
        }

        itemsEl.innerHTML = cart.items.map(item => {
            const isRoom = item._key && item._key.indexOf('room_') === 0;
            return `
            <div class="pos-cart-item" data-id="${item.product_id}" data-key="${item._key || ''}">
                <div class="item-name">${item.name}</div>
                <div class="item-qty">
                    ${isRoom ? `<span class="px-2">${item.qty}</span>` : `
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-qty qty-minus">−</button>
                    <span class="px-2">${item.qty}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-qty qty-plus">+</button>
                    `}
                </div>
                <div class="item-subtotal">${this.formatPrice(item.subtotal)}</div>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove" title="Remove"><i class="bi bi-trash"></i></button>
            </div>
        `;
        }).join('');

        subtotalEl.textContent = this.formatPrice(cart.subtotal);
        totalEl.textContent = this.formatPrice(cart.total);
        btn.disabled = false;

        itemsEl.querySelectorAll('.qty-minus').forEach(b => {
            b.addEventListener('click', () => this.onQtyChange(b.closest('.pos-cart-item').dataset.id, -1));
        });
        itemsEl.querySelectorAll('.qty-plus').forEach(b => {
            b.addEventListener('click', () => this.onQtyChange(b.closest('.pos-cart-item').dataset.id, 1));
        });
        itemsEl.querySelectorAll('.btn-remove').forEach(b => {
            const row = b.closest('.pos-cart-item');
            b.addEventListener('click', () => this.onRemove(row.dataset.id, row.dataset.key));
        });
    },
    async onQtyChange(productId, delta) {
        const item = document.querySelector(`.pos-cart-item[data-id="${productId}"]`);
        const qtyEl = item.querySelector('.item-qty span');
        let qty = parseInt(qtyEl.textContent) + delta;
        if (qty < 1) qty = 0;
        const r = await this.updateCart(productId, qty);
        if (r.success) this.renderCart(r.cart);
        else alert(r.message || 'Error');
    },
    async onRemove(productId, cartKey) {
        const r = await this.removeFromCart(productId, cartKey);
        if (r.success) this.renderCart(r.cart);
    },
    initPayment(total) {
        document.getElementById('paymentTotal').value = this.formatPrice(total);
        document.getElementById('paymentCash').value = '';
        document.getElementById('paymentChange').value = '';
        document.getElementById('cashInputGroup').style.display = document.getElementById('paymentMethod').value === 'cash' ? 'block' : 'none';
        document.getElementById('changeGroup').style.display = 'none';
    },
    async confirmPayment() {
        const method = document.getElementById('paymentMethod').value;
        const cash = parseFloat(document.getElementById('paymentCash').value) || 0;
        const total = parseFloat(document.getElementById('posTotal').textContent.replace(/[₱,]/g, ''));

        if (method === 'cash' && cash < total) {
            alert('Insufficient cash amount');
            return;
        }

        const r = await this.checkout(method, cash);
        if (r.success) {
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            document.getElementById('successInvoice').textContent = r.invoice_no;
            document.getElementById('successTotal').textContent = this.formatPrice(r.total);
            document.getElementById('successChange').textContent = r.change != null ? 'Change: ' + this.formatPrice(r.change) : '';
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            this.renderCart({ items: [], subtotal: 0, total: 0 });
            document.getElementById('successModal').addEventListener('hidden.bs.modal', () => successModal.dispose(), { once: true });
        } else {
            alert(r.message || 'Checkout failed');
        }
    }
};

document.addEventListener('DOMContentLoaded', async () => {
    const grid = document.getElementById('posProductGrid');
    const loadProducts = async (catId = '') => {
        grid.innerHTML = '<div class="text-center py-5"><div class="spinner-border"></div><p class="mt-2">Loading...</p></div>';
        const products = await POS.getProducts(catId);
        if (Array.isArray(products)) POS.renderProducts(products);
    };

    document.querySelectorAll('.pos-cat-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.pos-cat-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadProducts(btn.dataset.category);
        });
    });

    await loadProducts();
    const cartRes = await POS.getCart();
    if (cartRes.success && cartRes.cart) POS.renderCart(cartRes.cart);

    document.getElementById('posCheckoutBtn').addEventListener('click', () => {
        const total = parseFloat(document.getElementById('posTotal').textContent.replace(/[₱,]/g, ''));
        if (total <= 0) return;
        POS.initPayment(total);
        new bootstrap.Modal(document.getElementById('paymentModal')).show();
    });

    document.getElementById('paymentMethod').addEventListener('change', function() {
        document.getElementById('cashInputGroup').style.display = this.value === 'cash' ? 'block' : 'none';
        document.getElementById('changeGroup').style.display = 'none';
    });

    document.getElementById('paymentCash').addEventListener('input', function() {
        const total = parseFloat(document.getElementById('posTotal').textContent.replace(/[₱,]/g, ''));
        const cash = parseFloat(this.value) || 0;
        const changeEl = document.getElementById('paymentChange');
        const changeGroup = document.getElementById('changeGroup');
        if (cash >= total) {
            changeEl.value = POS.formatPrice(cash - total);
            changeGroup.style.display = 'block';
        } else {
            changeGroup.style.display = 'none';
        }
    });

    document.getElementById('paymentConfirmBtn').addEventListener('click', () => POS.confirmPayment());
});
</script>
