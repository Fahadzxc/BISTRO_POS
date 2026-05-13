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
        <div class="d-flex gap-2 mb-3 flex-wrap no-print">
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

        <div class="sales-print-header d-none d-print-block text-center mb-3 pb-2 border-bottom">
            <div class="fw-bold fs-5">Bistro POS</div>
            <div class="small text-muted">Sales summary</div>
            <div class="small" id="printDateRange"></div>
        </div>

        <div class="card border-0 shadow-sm mb-3 no-print">
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
                        <button type="button" class="btn btn-outline-secondary" id="printBtn"><i class="bi bi-printer me-1"></i>Print</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3 sales-print-summary">
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

        <div class="card border-0 shadow-sm mb-3 no-print">
            <div class="card-body">
                <h6 class="mb-2">Sales Trend</h6>
                <canvas id="salesTrend" height="120"></canvas>
            </div>
        </div>

        <div class="card border-0 shadow-sm sales-print-orders">
            <div class="card-body">
                <h6 class="mb-2 no-print">Orders</h6>
                <h6 class="mb-2 d-none d-print-block small fw-bold">Order list</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 sales-orders-table">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th style="min-width:200px;">Items ordered</th>
                                <th class="text-end">Total</th>
                                <th>Payment</th>
                                <th class="text-end">Amount paid</th>
                                <th class="text-end">Change</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="ordersBody">
                            <tr><td colspan="7" class="text-muted text-center py-3">Loading...</td></tr>
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
        if (!orders || !orders.length) { body.innerHTML = '<tr><td colspan="7" class="text-muted text-center py-3">No data.</td></tr>'; return; }
        body.innerHTML = orders.map(o => {
            const items = (o.items || []).map(it =>
                '<div class="sales-order-line">' + escapeHtml(String(it.qty)) + '× ' + escapeHtml(it.product_name || 'Item')
                + ' <span class="text-muted">@' + peso(it.price) + '</span> → ' + peso(it.subtotal) + '</div>'
            ).join('');
            const pm = (o.payment_method || '').toLowerCase();
            let amountPaid = '—';
            let changeStr = '—';
            if (pm === 'cash') {
                amountPaid = o.cash != null ? peso(o.cash) : '—';
                changeStr = o.change_amount != null ? peso(o.change_amount) : '—';
            } else if (pm === 'card') {
                amountPaid = peso(o.total);
                changeStr = '—';
            }
            return '<tr><td><strong>' + escapeHtml(o.invoice_no || '') + '</strong></td>'
                + '<td class="small sales-order-items">' + (items || '<span class="text-muted">—</span>') + '</td>'
                + '<td class="text-end fw-medium">' + peso(o.total) + '</td>'
                + '<td>' + escapeHtml(o.payment_method || '') + '</td>'
                + '<td class="text-end">' + amountPaid + '</td>'
                + '<td class="text-end">' + changeStr + '</td>'
                + '<td class="text-nowrap small">' + escapeHtml(o.created_at || '') + '</td></tr>';
        }).join('');
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
    document.getElementById('printBtn').addEventListener('click', () => window.print());
    function syncPrintDateRange() {
        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;
        const el = document.getElementById('printDateRange');
        if (el) el.textContent = from && to ? from + ' to ' + to : '';
    }
    window.addEventListener('beforeprint', syncPrintDateRange);
    load();
})();
</script>
<style>
@media print {
    @page { size: auto; margin: 12mm; }
    body { background: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .sidebar,
    .top-navbar,
    .no-print { display: none !important; }
    .main-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
    }
    .content-area { padding: 0 !important; }
    .sales-print-summary .stat-card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
        padding: 0.75rem !important;
    }
    .sales-print-orders { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    .sales-print-orders table { font-size: 10px; }
    .sales-order-line { line-height: 1.35; margin-bottom: 2px; }
    canvas#salesTrend { display: none !important; }
}
.sales-order-items { vertical-align: top; }
</style>
