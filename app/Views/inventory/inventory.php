<?= view('layouts/_sidebar', ['currentPage' => 'inventory']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Inventory</span>
        <div class="user-info">
            <span class="text-muted small"><?= esc(session()->get('name')) ?></span>
            <span class="badge bg-warning text-dark role-badge"><?= esc(session()->get('role')) ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-left me-1"></i>Logout
            </a>
        </div>
    </header>

    <main class="content-area">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h4 class="mb-0">Stock Overview</h4>
            <div class="d-flex gap-2">
                <span class="badge bg-warning text-dark inv-badge">Low: <?= (int) $lowStockCount ?></span>
                <span class="badge bg-danger inv-badge">Out: <?= (int) $outOfStockCount ?></span>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" id="invSearch" class="form-control" placeholder="Search product or category...">
                    </div>
                    <div class="col-md-3">
                        <select id="invCategory" class="form-select">
                            <option value="">All categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>"><?= esc($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="invStatus" class="form-select">
                            <option value="">All statuses</option>
                            <option value="in_stock">In Stock</option>
                            <option value="low_stock">Low Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-primary w-100" id="invRefresh">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Min Stock</th>
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="invTableBody">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm"></div>
                                    <p class="mt-2 mb-0">Loading...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Stock adjustment modal -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Stock — <span id="adjustProductName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="adjustProductId">
                <div class="mb-3">
                    <label class="form-label">Direction</label>
                    <select id="adjustDirection" class="form-select">
                        <option value="in">Stock In</option>
                        <option value="out">Stock Out</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" id="adjustQty" class="form-control" min="1" value="1">
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <select id="adjustReason" class="form-select">
                        <option value="Supplier delivery">Supplier delivery</option>
                        <option value="Damage">Damage</option>
                        <option value="Expired">Expired</option>
                        <option value="Manual correction">Manual correction</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="adjustSubmit">
                    <i class="bi bi-check-lg me-1"></i>Apply
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Set min stock modal -->
<div class="modal fade" id="minStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Minimum Stock — <span id="minStockProductName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="minStockProductId">
                <div class="mb-3">
                    <label class="form-label">Minimum stock (alert when at or below)</label>
                    <input type="number" id="minStockValue" class="form-control" min="0" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="minStockSubmit">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const urlList = '<?= $urlList ?>';
    const urlAdjust = '<?= $urlAdjust ?>';
    const urlUpdateMinStock = '<?= site_url('inventory/update-min-stock') ?>';
    const csrfName = '<?= $csrfName ?>';
    const csrfToken = '<?= $csrfToken ?>';

    function getStatusBadge(status) {
        if (status === 'in_stock') return '<span class="badge bg-success">In Stock</span>';
        if (status === 'low_stock') return '<span class="badge bg-warning text-dark">Low Stock</span>';
        return '<span class="badge bg-danger">Out of Stock</span>';
    }

    function getStatusRowClass(status) {
        if (status === 'low_stock') return 'table-warning';
        if (status === 'out_of_stock') return 'table-danger';
        return '';
    }

    function loadList() {
        const search = document.getElementById('invSearch').value.trim();
        const categoryId = document.getElementById('invCategory').value;
        const status = document.getElementById('invStatus').value;
        const url = new URL(urlList);
        if (search) url.searchParams.set('search', search);
        if (categoryId) url.searchParams.set('category_id', categoryId);
        if (status) url.searchParams.set('status', status);

        const tbody = document.getElementById('invTableBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div><p class="mt-2 mb-0">Loading...</p></td></tr>';

        fetch(url.toString())
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.data || !res.data.length) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No products found.</td></tr>';
                    return;
                }
                tbody.innerHTML = res.data.map(p => `
                    <tr class="${getStatusRowClass(p.stock_status)}">
                        <td><strong>${escapeHtml(p.name)}</strong></td>
                        <td>${escapeHtml(p.category_name || '-')}</td>
                        <td>${p.stock}</td>
                        <td>${p.min_stock ?? 0}</td>
                        <td>${getStatusBadge(p.stock_status)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary inv-btn-adjust" data-id="${p.id}" data-name="${escapeAttr(p.name)}" title="Adjust stock">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary inv-btn-min" data-id="${p.id}" data-name="${escapeAttr(p.name)}" data-min="${p.min_stock ?? 0}" title="Set min stock">
                                <i class="bi bi-sliders"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');

                tbody.querySelectorAll('.inv-btn-adjust').forEach(btn => {
                    btn.addEventListener('click', () => openAdjustModal(btn.dataset.id, btn.dataset.name));
                });
                tbody.querySelectorAll('.inv-btn-min').forEach(btn => {
                    btn.addEventListener('click', () => openMinStockModal(btn.dataset.id, btn.dataset.name, btn.dataset.min));
                });
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load.</td></tr>';
            });
    }

    function escapeHtml(s) {
        const div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }
    function escapeAttr(s) {
        return String(s).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function openAdjustModal(id, name) {
        document.getElementById('adjustProductId').value = id;
        document.getElementById('adjustProductName').textContent = name;
        document.getElementById('adjustQty').value = 1;
        new bootstrap.Modal(document.getElementById('adjustModal')).show();
    }

    function openMinStockModal(id, name, min) {
        document.getElementById('minStockProductId').value = id;
        document.getElementById('minStockProductName').textContent = name;
        document.getElementById('minStockValue').value = min || 0;
        new bootstrap.Modal(document.getElementById('minStockModal')).show();
    }

    function postFormData(url, data) {
        const fd = new FormData();
        fd.append(csrfName, csrfToken);
        for (const k in data) fd.append(k, data[k]);
        return fetch(url, { method: 'POST', body: fd }).then(r => r.json());
    }

    document.getElementById('invRefresh').addEventListener('click', loadList);
    document.getElementById('invSearch').addEventListener('input', debounce(loadList, 350));
    document.getElementById('invCategory').addEventListener('change', loadList);
    document.getElementById('invStatus').addEventListener('change', loadList);

    document.getElementById('adjustSubmit').addEventListener('click', function() {
        const productId = document.getElementById('adjustProductId').value;
        const direction = document.getElementById('adjustDirection').value;
        const qty = parseInt(document.getElementById('adjustQty').value, 10) || 0;
        const reason = document.getElementById('adjustReason').value;
        if (qty <= 0) { alert('Enter a valid quantity.'); return; }
        this.disabled = true;
        postFormData(urlAdjust, { product_id: productId, direction, qty, reason })
            .then(res => {
                this.disabled = false;
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('adjustModal')).hide();
                    loadList();
                } else {
                    alert(res.message || 'Failed to adjust stock.');
                }
            })
            .catch(() => { this.disabled = false; alert('Request failed.'); });
    });

    document.getElementById('minStockSubmit').addEventListener('click', function() {
        const productId = document.getElementById('minStockProductId').value;
        const minStock = Math.max(0, parseInt(document.getElementById('minStockValue').value, 10) || 0);
        this.disabled = true;
        postFormData(urlUpdateMinStock, { product_id: productId, min_stock: minStock })
            .then(res => {
                this.disabled = false;
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('minStockModal')).hide();
                    loadList();
                } else {
                    alert(res.message || 'Failed to update.');
                }
            })
            .catch(() => { this.disabled = false; alert('Request failed.'); });
    });

    function debounce(fn, ms) {
        let t;
        return function() { clearTimeout(t); t = setTimeout(() => fn.apply(this, arguments), ms); };
    }

    // Pre-select status from query string
    const params = new URLSearchParams(window.location.search);
    const statusParam = params.get('status');
    if (statusParam) {
        const sel = document.getElementById('invStatus');
        if (sel.querySelector('option[value="' + statusParam + '"]')) sel.value = statusParam;
    }

    loadList();
})();
</script>
