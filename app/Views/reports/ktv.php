<?= view('layouts/_sidebar', ['currentPage' => 'reports']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">KTV Room Usage Reports</span>
        <div class="user-info">
            <span class="text-muted small"><?= esc(session()->get('name')) ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-left me-1"></i>Logout
            </a>
        </div>
    </header>

    <main class="content-area">
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <a href="<?= site_url('reports/sales') ?>" class="btn btn-outline-primary">
                <i class="bi bi-receipt me-1"></i>Sales
            </a>
            <a href="<?= site_url('reports/ktv') ?>" class="btn btn-primary">
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
                        <button type="button" class="btn btn-primary" id="applyBtn">
                            <i class="bi bi-funnel me-1"></i>Apply
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i>Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" id="totalSessions">0</div>
                    <div class="stat-label">Total Sessions</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" id="totalHours">0</div>
                    <div class="stat-label">Total Hours Used</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" id="totalRevenue">₱0</div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" id="mostUsedRoom">—</div>
                    <div class="stat-label">Most Used Room</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-2">Revenue per Room</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Room</th>
                                <th>Sessions</th>
                                <th>Minutes</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody id="byRoomBody">
                            <tr>
                                <td colspan="4" class="text-muted text-center py-3">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
(function() {
    var urlData = '<?= esc($urlData) ?>';
    function peso(n) {
        return '₱' + Number(n || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }
    function renderByRoom(rows) {
        var body = document.getElementById('byRoomBody');
        if (!rows || !rows.length) {
            body.innerHTML = '<tr><td colspan="4" class="text-muted text-center py-3">No data.</td></tr>';
            return;
        }
        body.innerHTML = rows.map(function(r) {
            return '<tr><td>' + escapeHtml(r.room_name || '') + '</td><td>' + (r.total_sessions || 0) + '</td><td>' + (r.total_minutes || 0) + '</td><td>' + peso(r.total_revenue) + '</td></tr>';
        }).join('');
    }
    function load() {
        var from = document.getElementById('fromDate').value;
        var to = document.getElementById('toDate').value;
        var url = new URL(urlData);
        url.searchParams.set('from', from);
        url.searchParams.set('to', to);
        fetch(url.toString()).then(function(r) { return r.json(); }).then(function(res) {
            if (!res.success) return;
            var s = res.summary || {};
            document.getElementById('totalSessions').textContent = s.total_sessions ?? 0;
            document.getElementById('totalHours').textContent = Math.round((s.total_minutes || 0) / 60 * 10) / 10;
            document.getElementById('totalRevenue').textContent = peso(s.total_revenue);
            document.getElementById('mostUsedRoom').textContent = s.most_used_room ? escapeHtml(s.most_used_room) : '—';
            renderByRoom(res.byRoom || []);
        });
    }
    document.getElementById('applyBtn').addEventListener('click', load);
    load();
})();
</script>
<style>
@media print {
    .sidebar, .top-navbar, .btn { display: none !important; }
    .main-wrapper { margin-left: 0 !important; }
}
</style>
