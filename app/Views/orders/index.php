<?= view('layouts/_sidebar', ['currentPage' => 'orders']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Order History</span>
        <div class="user-info">
            <span class="text-muted small"><?= esc(session()->get('name')) ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-left me-1"></i>Logout</a>
        </div>
    </header>

    <main class="content-area">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= esc(session()->getFlashdata('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= esc(session()->getFlashdata('error')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="get" action="<?= site_url('orders') ?>" class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Invoice</label>
                        <input type="text" name="search" class="form-control form-control-sm" value="<?= esc($search ?? '') ?>" placeholder="Search invoice">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($dateFrom ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($dateTo ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice Number</th>
                                <th>Date</th>
                                <th>Cashier</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No orders found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td><strong><?= esc($o['invoice_no']) ?></strong></td>
                                    <td><?= esc($o['created_at']) ?></td>
                                    <td><?= esc($o['cashier_name'] ?? '-') ?></td>
                                    <td>₱<?= number_format((float) ($o['total'] ?? 0), 2) ?></td>
                                    <td><?= esc($o['payment_method'] ?? '') ?></td>
                                    <td>
                                        <a href="<?= site_url('orders/view/' . $o['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php
        $total = $pager['total'] ?? 0;
        $perPage = $pager['per_page'] ?? 20;
        $page = $pager['page'] ?? 1;
        $totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
        ?>
        <?php if ($totalPages > 1): ?>
        <nav class="mt-3 d-flex justify-content-between align-items-center">
            <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?> (<?= $total ?> orders)</small>
            <ul class="pagination pagination-sm mb-0">
                <?php $baseUrl = site_url('orders') . '?' . http_build_query(array_filter(['search' => $search ?? '', 'date_from' => $dateFrom ?? '', 'date_to' => $dateTo ?? ''])); ?>
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $page <= 1 ? '#' : $baseUrl . '&page=' . ($page - 1) ?>">Previous</a>
                </li>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $page >= $totalPages ? '#' : $baseUrl . '&page=' . ($page + 1) ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </main>
</div>
