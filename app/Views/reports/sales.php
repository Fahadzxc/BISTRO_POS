<?= view('layouts/_sidebar', ['currentPage' => 'reports']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Sales Reports</span>
        <div class="user-info">
            <span class="text-muted small"><?= esc(session()->get('name')) ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-left me-1"></i>Logout</a>
        </div>
    </header>

    <main class="content-area">
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <a href="<?= site_url('reports/sales') ?>" class="btn btn-primary">
                <i class="bi bi-receipt me-1"></i>Sales
            </a>
            <a href="<?= site_url('reports/ktv') ?>" class="btn btn-outline-primary">
                <i class="bi bi-door-open me-1"></i>KTV Rooms
            </a>
            <a href="<?= site_url('reports/inventory') ?>" class="btn btn-outline-primary">
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
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value" id="sumAmount">₱0.00</div>
                    <div class="stat-label">Total Sales</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value" id="sumOrders">0</div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value" id="avgOrder">₱0.00</div>
                    <div class="stat-label">Average Order Value</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-2">Sales Trend</h6>
                <canvas id="salesTrend" height="120"></canvas>
            </div>
        </div>

        <div class="card border-0 shadow-sm no-print-padding">
            <div class="card-body">
                <h6 class="mb-2">Orders</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Invoice</th><th>Total</th><th>Payment</th><th>Date</th></tr>
                        </thead>
                        <tbody id="ordersBody">
                            <tr><td colspan="4" class="text-muted text-center py-3">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.js"></script>
<script>
(function() {
    const urlData = '<?= esc($urlData) ?>';
    const peso = (n) => '₱' + Number(n || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    let trendChart;
    function ensureChart() {
        if (trendChart) return;
        trendChart = new Chart(document.getElementById('salesTrend'), {
            type: 'line',
            data: { labels: [], datasets: [{ label: 'Sales', data: [], borderColor: 'rgb(26,54,93)', backgroundColor: 'rgba(26,54,93,0.12)', fill: true, tension: 0.25 }] },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    }
    function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function renderOrders(orders) {
        const body = document.getElementById('ordersBody');
        if (!orders || !orders.length) { body.innerHTML = '<tr><td colspan="4" class="text-muted text-center py-3">No data.</td></tr>'; return; }
        body.innerHTML = orders.map(o => '<tr><td><strong>' + escapeHtml(o.invoice_no || '') + '</strong></td><td>' + peso(o.total) + '</td><td>' + escapeHtml(o.payment_method || '') + '</td><td>' + escapeHtml(o.created_at || '') + '</td></tr>').join('');
    }
    async function load() {
        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;
        const url = new URL(urlData); url.searchParams.set('from', from); url.searchParams.set('to', to);
        const res = await fetch(url.toString()).then(r => r.json());
        if (!res.success) return;
        const total = res.summary.total_amount || 0;
        const orders = res.summary.total_orders || 0;
        document.getElementById('sumAmount').textContent = peso(total);
        document.getElementById('sumOrders').textContent = orders;
        document.getElementById('avgOrder').textContent = peso(orders ? (total / orders) : 0);
        ensureChart();
        trendChart.data.labels = (res.series || []).map(r => r.label);
        trendChart.data.datasets[0].data = (res.series || []).map(r => Number(r.value || 0));
        trendChart.update();
        renderOrders(res.orders || []);
    }
    document.getElementById('applyBtn').addEventListener('click', load);
    load();
})();
</script>
<style>@media print { .sidebar, .top-navbar, .btn, #applyBtn, .no-print-padding { display: none !important; } .main-wrapper { margin-left: 0 !important; } }</style>
