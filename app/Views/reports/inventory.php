<?= view('layouts/_sidebar', ['currentPage' => 'reports']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Inventory Reports</span>
        <div class="user-info">
            <span class="text-muted small"><?= esc(session()->get('name')) ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-left me-1"></i>Logout</a>
        </div>
    </header>

    <main class="content-area">
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <a href="<?= site_url('reports/sales') ?>" class="btn btn-outline-primary">
                <i class="bi bi-receipt me-1"></i>Sales
            </a>
            <a href="<?= site_url('reports/ktv') ?>" class="btn btn-outline-primary">
                <i class="bi bi-door-open me-1"></i>KTV Rooms
            </a>
            <a href="<?= site_url('reports/inventory') ?>" class="btn btn-primary">
                <i class="bi bi-archive me-1"></i>Inventory
            </a>
        </div>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">From</label>
                        <input type="date" id="fromDate" class="form-control" value="<?= esc($from) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">To</label>
                        <input type="date" id="toDate" class="form-control" value="<?= esc($to) ?>">
                    </div>
                    <div class="col-md-6 d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="applyBtn"><i class="bi bi-funnel me-1"></i>Apply</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12">
                <h6 class="mb-2">Stock Movement Summary (by action type)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr><th>Action</th><th>Movements</th><th>Total Qty Change</th></tr>
                        </thead>
                        <tbody id="summaryBody">
                            <tr><td colspan="3" class="text-muted text-center py-2">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-2">Low Stock List</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Product</th><th>Stock</th><th>Min Stock</th></tr>
                        </thead>
                        <tbody id="lowStockBody">
                            <tr><td colspan="3" class="text-muted text-center py-2">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-2">Damage / Expired Logs</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Product</th><th>Qty Change</th><th>Remarks</th></tr>
                        </thead>
                        <tbody id="issuesBody">
                            <tr><td colspan="4" class="text-muted text-center py-2">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-2">Stock Movement History</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Product</th><th>Before</th><th>Change</th><th>After</th><th>Type</th><th>Remarks</th></tr>
                        </thead>
                        <tbody id="logsBody">
                            <tr><td colspan="7" class="text-muted text-center py-2">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
(function() {
    const urlData = '<?= esc($urlData) ?>';
    function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function renderSummary(rows) {
        const body = document.getElementById('summaryBody');
        if (!rows || !rows.length) { body.innerHTML = '<tr><td colspan="3" class="text-muted text-center py-2">No data.</td></tr>'; return; }
        body.innerHTML = rows.map(r => '<tr><td>' + escapeHtml(r.label || '') + '</td><td>' + (r.movements || 0) + '</td><td>' + (r.total_qty_change || 0) + '</td></tr>').join('');
    }
    function renderLowStock(rows) {
        const body = document.getElementById('lowStockBody');
        if (!rows || !rows.length) { body.innerHTML = '<tr><td colspan="3" class="text-muted text-center py-2">None.</td></tr>'; return; }
        body.innerHTML = rows.map(r => '<tr><td>' + escapeHtml(r.name || '') + '</td><td>' + (r.stock ?? 0) + '</td><td>' + (r.min_stock ?? 0) + '</td></tr>').join('');
    }
    function renderIssues(rows) {
        const body = document.getElementById('issuesBody');
        if (!rows || !rows.length) { body.innerHTML = '<tr><td colspan="4" class="text-muted text-center py-2">None.</td></tr>'; return; }
        body.innerHTML = rows.map(r => '<tr><td>' + escapeHtml(r.created_at || '') + '</td><td>' + escapeHtml(r.product_name || '') + '</td><td>' + (r.qty_change || 0) + '</td><td>' + escapeHtml(r.remarks || '') + '</td></tr>').join('');
    }
    function renderLogs(rows) {
        const body = document.getElementById('logsBody');
        if (!rows || !rows.length) { body.innerHTML = '<tr><td colspan="7" class="text-muted text-center py-2">No data.</td></tr>'; return; }
        body.innerHTML = rows.map(r => '<tr><td>' + escapeHtml(r.created_at || '') + '</td><td>' + escapeHtml(r.product_name || '') + '</td><td>' + (r.qty_before ?? 0) + '</td><td>' + (r.qty_change ?? 0) + '</td><td>' + (r.qty_after ?? 0) + '</td><td>' + escapeHtml(r.action_type || '') + '</td><td>' + escapeHtml(r.remarks || '') + '</td></tr>').join('');
    }
    async function load() {
        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;
        const url = new URL(urlData); url.searchParams.set('from', from); url.searchParams.set('to', to);
        const res = await fetch(url.toString()).then(r => r.json());
        if (!res.success) return;
        renderSummary(res.summary || []);
        renderLowStock(res.lowStock || []);
        renderIssues(res.issues || []);
        renderLogs(res.logs || []);
    }
    document.getElementById('applyBtn').addEventListener('click', load);
    load();
})();
</script>
<style>@media print { .sidebar, .top-navbar, .btn { display: none !important; } .main-wrapper { margin-left: 0 !important; } }</style>
