<?= view('layouts/_sidebar', ['currentPage' => 'dashboard']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Dashboard Analytics</span>
        <div class="user-info">
            <span class="text-muted small"><?= esc(session()->get('name')) ?></span>
            <span class="badge bg-warning text-dark role-badge"><?= esc(session()->get('role')) ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-left me-1"></i>Logout
            </a>
        </div>
    </header>

    <main class="content-area">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-1">Real-time Analytics</h4>
                <p class="text-muted small mb-0">Auto-refresh every 30 seconds.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= site_url('reports/sales') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-receipt me-1"></i>Sales</a>
                <a href="<?= site_url('reports/ktv') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-door-open me-1"></i>KTV</a>
                <a href="<?= site_url('reports/inventory') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-archive me-1"></i>Inventory</a>
                <button type="button" class="btn btn-primary btn-sm" id="dashRefreshBtn"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-currency-dollar"></i></div>
                    <div>
                        <div class="stat-value" id="wSalesToday">₱0</div>
                        <div class="stat-label">Total Sales Today</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-bag-check"></i></div>
                    <div>
                        <div class="stat-value" id="wOrdersToday">0</div>
                        <div class="stat-label">Total Orders Today</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="stat-value" id="wCustomersToday">0</div>
                        <div class="stat-label">Total Customers Today</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-music-note-beamed"></i></div>
                    <div>
                        <div class="stat-value" id="wActiveRooms">0</div>
                        <div class="stat-label">Active KTV Rooms</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="<?= site_url('inventory') ?>?status=low_stock" class="text-decoration-none text-dark">
                    <div class="stat-card d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-exclamation-triangle"></i></div>
                        <div>
                            <div class="stat-value" id="wLowStock">0</div>
                            <div class="stat-label">Low Stock Alerts</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="<?= site_url('inventory') ?>?status=out_of_stock" class="text-decoration-none text-dark">
                    <div class="stat-card d-flex align-items-center gap-3">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-circle"></i></div>
                        <div>
                            <div class="stat-value" id="wOutStock">0</div>
                            <div class="stat-label">Out of Stock</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="mb-2">Daily Sales (Last 7 Days)</h6>
                        <canvas id="chartDailySales" height="130"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="mb-2">Monthly Sales</h6>
                        <canvas id="chartMonthlySales" height="130"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="mb-2">Top 10 Products (by qty)</h6>
                        <canvas id="chartTopProducts" height="150"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="mb-2">KTV Room Usage (Revenue)</h6>
                        <canvas id="chartKtvUsage" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.js"></script>
<script>
(function() {
    const urlStats = '<?= esc($urlStats) ?>';
    const peso = (n) => '₱' + Number(n || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    let dailyChart, monthlyChart, topChart, ktvChart;

    function ensureCharts() {
        if (!dailyChart) {
            dailyChart = new Chart(document.getElementById('chartDailySales'), {
                type: 'line',
                data: { labels: [], datasets: [{ label: 'Sales', data: [], borderColor: 'rgb(26,54,93)', backgroundColor: 'rgba(26,54,93,0.12)', fill: true, tension: 0.25 }] },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return '₱' + v; } } } } }
            });
        }
        if (!monthlyChart) {
            monthlyChart = new Chart(document.getElementById('chartMonthlySales'), {
                type: 'bar',
                data: { labels: [], datasets: [{ label: 'Sales', data: [], backgroundColor: 'rgba(44,82,130,0.65)' }] },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return '₱' + v; } } } } }
            });
        }
        if (!topChart) {
            topChart = new Chart(document.getElementById('chartTopProducts'), {
                type: 'bar',
                data: { labels: [], datasets: [{ label: 'Qty Sold', data: [], backgroundColor: 'rgba(237,137,54,0.75)' }] },
                options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
            });
        }
        if (!ktvChart) {
            ktvChart = new Chart(document.getElementById('chartKtvUsage'), {
                type: 'pie',
                data: { labels: [], datasets: [{ data: [], backgroundColor: ['#1a365d','#2c5282','#ed8936','#38a169','#dc2626','#0ea5e9','#a855f7','#f59e0b','#64748b','#14b8a6'] }] },
                options: { responsive: true }
            });
        }
    }

    function applyWidgets(w) {
        document.getElementById('wSalesToday').textContent = peso(w.totalSalesToday);
        document.getElementById('wOrdersToday').textContent = w.totalOrdersToday ?? 0;
        document.getElementById('wCustomersToday').textContent = w.totalCustomersToday ?? 0;
        document.getElementById('wActiveRooms').textContent = w.activeKtvRooms ?? 0;
        document.getElementById('wLowStock').textContent = w.lowStockAlerts ?? 0;
        document.getElementById('wOutStock').textContent = w.outOfStockAlerts ?? 0;
    }

    function applyCharts(c) {
        ensureCharts();
        dailyChart.data.labels = c.dailySales?.labels || []; dailyChart.data.datasets[0].data = c.dailySales?.data || []; dailyChart.update();
        monthlyChart.data.labels = c.monthlySales?.labels || []; monthlyChart.data.datasets[0].data = c.monthlySales?.data || []; monthlyChart.update();
        topChart.data.labels = c.topProducts?.labels || []; topChart.data.datasets[0].data = c.topProducts?.data || []; topChart.update();
        ktvChart.data.labels = c.ktvUsage?.labels || [];
        ktvChart.data.datasets[0].data = (c.ktvUsage?.data || []).map(function(v) { return Math.max(0.01, v); });
        ktvChart.update();
    }

    async function refresh() {
        try {
            const r = await fetch(urlStats);
            const res = await r.json();
            if (res.success) { applyWidgets(res.widgets); applyCharts(res.charts); }
        } catch (e) {}
    }

    document.getElementById('dashRefreshBtn').addEventListener('click', refresh);
    refresh();
    setInterval(refresh, 30000);
})();
</script>
