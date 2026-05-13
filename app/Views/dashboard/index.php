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
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-purple bg-opacity-10 text-purple" style="background:rgba(128,0,128,0.1);color:purple;"><i class="bi bi-mic-fill"></i></div>
                    <div>
                        <div class="stat-value" id="wKtvSales">₱0</div>
                        <div class="stat-label">KTV Sales Today</div>
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

        <!-- Active KTV Rooms Section -->
        <div class="card border-0 shadow-sm mb-4 mt-3">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-music-note-beamed me-2"></i>Active KTV Rooms</h6>
                <div id="activeRoomsContainer">
                    <p class="text-muted mb-0">No active rooms.</p>
                </div>
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
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-trophy-fill me-2 text-warning"></i>Top 10 Products</h6>
                        <a href="<?= site_url('products') ?>" class="btn btn-sm btn-outline-secondary">View all</a>
                    </div>
                    <div class="card-body pt-0" id="topProductsList">
                        <p class="text-muted text-center py-4 mb-0">Loading…</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-mic-fill me-2" style="color:#7c3aed;"></i>KTV Room Usage</h6>
                        <a href="<?= site_url('ktv-rooms') ?>" class="btn btn-sm btn-outline-primary">Rooms</a>
                    </div>
                    <div class="card-body pt-0">
                        <p class="text-muted small mb-3">Revenue from ended sessions in the last 30 days.</p>
                        <div id="ktvUsagePanel"></div>
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
    const baseUrl = '<?= rtrim(base_url(), '/') ?>/';
    const peso = (n) => '₱' + Number(n || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    let dailyChart, monthlyChart;

    function escHtml(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    }

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
    }

    function renderTopProducts(products) {
        const el = document.getElementById('topProductsList');
        if (!products || !products.length) {
            el.innerHTML = '<p class="text-muted text-center py-4 mb-0">No sales yet.</p>';
            return;
        }
        const medals = ['🥇', '🥈', '🥉'];
        el.innerHTML = products.map((p, i) => {
            let imgUrl = '';
            if (p.image) {
                const s = String(p.image).replace(/^\//, '');
                if (s.startsWith('http://') || s.startsWith('https://')) {
                    imgUrl = s;
                } else {
                    const path = s.startsWith('uploads/') ? s : ('uploads/products/' + s);
                    imgUrl = baseUrl + path;
                }
            }
            const thumb = imgUrl
                ? `<img src="${escHtml(imgUrl)}" alt="" class="rounded-3 flex-shrink-0" width="52" height="52" style="object-fit:cover;border:1px solid #e5e7eb;">`
                : `<div class="rounded-3 flex-shrink-0 d-flex align-items-center justify-content-center bg-light border" style="width:52px;height:52px;"><i class="bi bi-image text-muted"></i></div>`;
            const rank = i < 3 ? `<span class="fs-5">${medals[i]}</span>` : `<span class="badge rounded-pill bg-light text-secondary border">${i + 1}</span>`;
            return `
            <div class="d-flex align-items-center gap-3 py-2 ${i < products.length - 1 ? 'border-bottom border-light' : ''}">
                <div style="min-width:36px;text-align:center;">${rank}</div>
                ${thumb}
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate">${escHtml(p.name)}</div>
                    <div class="small text-muted">${peso(p.price)} · <span class="text-dark fw-medium">${p.total_qty} sold</span></div>
                </div>
            </div>`;
        }).join('');
    }

    function renderKtvUsage(ktv) {
        const el = document.getElementById('ktvUsagePanel');
        const labels = ktv?.labels || [];
        const data = ktv?.data || [];
        const sessions = ktv?.sessions || [];
        if (!labels.length) {
            el.innerHTML = '<p class="text-muted text-center py-3 mb-0">No rooms configured.</p>';
            return;
        }
        const totalRev = data.reduce((a, b) => a + Number(b || 0), 0);
        const totalSess = sessions.reduce((a, b) => a + Number(b || 0), 0);
        const colors = ['#5b21b6', '#2563eb', '#ea580c', '#059669', '#dc2626'];
        const hasAny = totalRev > 0 || totalSess > 0;

        let html = `
        <div class="rounded-3 p-3 mb-3 text-white" style="background:linear-gradient(135deg,#5b21b6 0%,#7c3aed 45%,#2563eb 100%);">
            <div class="row g-2 text-center">
                <div class="col-6">
                    <div class="small opacity-75">Total revenue</div>
                    <div class="fs-5 fw-bold">${peso(totalRev)}</div>
                </div>
                <div class="col-6">
                    <div class="small opacity-75">Sessions ended</div>
                    <div class="fs-5 fw-bold">${totalSess}</div>
                </div>
            </div>
            ${totalSess > 0 ? `<div class="small text-center mt-2 opacity-75">Avg ${peso(totalRev / totalSess)} per session</div>` : ''}
        </div>`;

        if (!hasAny) {
            html += '<p class="text-muted small text-center mb-3">No ended sessions in the last 30 days. Open <a href="<?= site_url('ktv-rooms') ?>">KTV Rooms</a> to start one.</p>';
        }

        html += labels.map((label, i) => {
            const rev = Number(data[i] || 0);
            const sess = Number(sessions[i] || 0);
            const share = totalRev > 0 ? Math.round((rev / totalRev) * 100) : 0;
            const c = colors[i % colors.length];
            return `
            <div class="mb-3 p-3 rounded-3" style="background:#fafafa;border:1px solid #eee;">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <span class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;background:${c}18;color:${c};">
                            <i class="bi bi-door-open fs-5"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">${escHtml(label)}</div>
                            <div class="small text-muted">${sess} session${sess === 1 ? '' : 's'}</div>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-bold" style="color:${c};">${peso(rev)}</div>
                        ${totalRev > 0 ? `<div class="small text-muted">${share}% of total</div>` : ''}
                    </div>
                </div>
                <div class="progress" style="height:8px;border-radius:6px;background:#e5e7eb;">
                    <div class="progress-bar" role="progressbar" style="width:${totalRev > 0 ? share : 0}%;background:${c};border-radius:6px;"></div>
                </div>
            </div>`;
        }).join('');

        html += `<a href="<?= site_url('reports/ktv') ?>" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-graph-up-arrow me-1"></i>Full KTV report</a>`;
        el.innerHTML = html;
    }

    function applyWidgets(w) {
        document.getElementById('wSalesToday').textContent = peso(w.totalSalesToday);
        document.getElementById('wOrdersToday').textContent = w.totalOrdersToday ?? 0;
        document.getElementById('wCustomersToday').textContent = w.totalCustomersToday ?? 0;
        document.getElementById('wActiveRooms').textContent = w.activeKtvRooms ?? 0;
        document.getElementById('wKtvSales').textContent = peso(w.ktvSalesToday);
        document.getElementById('wLowStock').textContent = w.lowStockAlerts ?? 0;
        document.getElementById('wOutStock').textContent = w.outOfStockAlerts ?? 0;
    }

    function formatTimer(seconds) {
        const s = Math.max(0, Math.floor(Number(seconds)));
        const h = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        return [h, m].map(v => String(v).padStart(2, '0')).join(':');
    }

    function renderActiveRooms(rooms) {
        const container = document.getElementById('activeRoomsContainer');
        if (!rooms || rooms.length === 0) {
            container.innerHTML = '<p class="text-muted mb-0">No active rooms.</p>';
            return;
        }
        container.innerHTML = '<div class="row g-2">' + rooms.map(room => {
            const isLow = room.remaining <= 300;
            return `
                <div class="col-sm-6 col-md-4">
                    <div class="border rounded p-3 ${isLow ? 'border-danger' : 'border-success'}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>${room.room_name}</strong>
                            <span class="badge bg-danger">Occupied</span>
                        </div>
                        <div class="fs-4 fw-bold ${isLow ? 'text-danger' : ''}">${formatTimer(room.remaining)} <small class="text-muted fw-normal">remaining</small></div>
                        <div class="small text-muted mt-1">
                            <i class="bi bi-people-fill me-1"></i>Max ${room.capacity} persons &bull; ${peso(room.hourly_rate)}/hr
                        </div>
                    </div>
                </div>
            `;
        }).join('') + '</div>';
    }

    function applyCharts(c) {
        ensureCharts();
        dailyChart.data.labels = c.dailySales?.labels || [];
        dailyChart.data.datasets[0].data = c.dailySales?.data || [];
        dailyChart.update();
        monthlyChart.data.labels = c.monthlySales?.labels || [];
        monthlyChart.data.datasets[0].data = c.monthlySales?.data || [];
        monthlyChart.update();
        renderTopProducts(Array.isArray(c.topProducts) ? c.topProducts : []);
        renderKtvUsage(c.ktvUsage || {});
    }

    async function refresh() {
        try {
            const r = await fetch(urlStats);
            const res = await r.json();
            if (res.success) {
                applyWidgets(res.widgets);
                applyCharts(res.charts);
                renderActiveRooms(res.activeRooms || []);
            }
        } catch (e) {}
    }

    document.getElementById('dashRefreshBtn').addEventListener('click', refresh);
    refresh();
    setInterval(refresh, 30000);
})();
</script>
