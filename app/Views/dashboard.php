<?= view('layouts/_sidebar', ['currentPage' => 'dashboard']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Dashboard</span>
        <div class="user-info">
            <span class="text-muted small"><?= esc(session()->get('name')) ?></span>
            <span class="badge bg-warning text-dark role-badge"><?= esc(session()->get('role')) ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-left me-1"></i>Logout
            </a>
        </div>
    </header>

    <main class="content-area">
        <div class="mb-4">
            <h4 class="mb-1">Welcome back, <?= esc(session()->get('name')) ?>!</h4>
            <p class="text-muted small mb-0">Here's what's happening with your business today.</p>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <div class="stat-value">₱0</div>
                        <div class="stat-label">Total Sales Today</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-music-note-beamed"></i>
                    </div>
                    <div>
                        <div class="stat-value">0</div>
                        <div class="stat-label">Active KTV Rooms</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="stat-value">0</div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="<?= site_url('inventory') ?>?status=low_stock" class="text-decoration-none text-dark">
                    <div class="stat-card d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= (int) ($low_stock_count ?? 0) ?></div>
                            <div class="stat-label">Low Stock Alerts</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="<?= site_url('inventory') ?>?status=out_of_stock" class="text-decoration-none text-dark">
                    <div class="stat-card d-flex align-items-center gap-3">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= (int) ($out_of_stock_count ?? 0) ?></div>
                            <div class="stat-label">Out of Stock</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <p class="section-title">Quick Actions</p>
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <a href="#" class="quick-action-btn text-decoration-none">
                    <i class="bi bi-plus-circle"></i>
                    <span class="fw-semibold small">New Order</span>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#" class="quick-action-btn text-decoration-none">
                    <i class="bi bi-cash-stack"></i>
                    <span class="fw-semibold small">Open POS</span>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#" class="quick-action-btn text-decoration-none">
                    <i class="bi bi-box-seam"></i>
                    <span class="fw-semibold small">Manage Products</span>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="#" class="quick-action-btn text-decoration-none">
                    <i class="bi bi-door-open"></i>
                    <span class="fw-semibold small">Manage Rooms</span>
                </a>
            </div>
        </div>

        <!-- Info card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title">Getting Started</h6>
                <p class="card-text text-muted small mb-0">
                    You are logged in as <strong><?= esc(session()->get('role')) ?></strong>.
                    Use the sidebar to navigate or the quick actions above to start working.
                    Connect your sales, orders, and inventory modules to see live data here.
                </p>
            </div>
        </div>
    </main>
</div>
