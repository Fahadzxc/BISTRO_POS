<?= view('layouts/_sidebar', ['currentPage' => 'orders']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Order <?= esc($order['invoice_no']) ?></span>
        <div class="user-info">
            <a href="<?= site_url('orders') ?>" class="btn btn-outline-secondary btn-sm me-2">Back to Orders</a>
            <a href="<?= site_url('orders/print/' . $order['id']) ?>?print=1" target="_blank" class="btn btn-primary btn-sm" id="btnPrint">
                <i class="bi bi-printer me-1"></i>Print Receipt
            </a>
        </div>
    </header>

    <main class="content-area">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Invoice Number</strong></div>
                    <div class="col-md-8"><?= esc($order['invoice_no']) ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Date</strong></div>
                    <div class="col-md-8"><?= esc($order['created_at']) ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Cashier</strong></div>
                    <div class="col-md-8"><?= esc($order['cashier_name'] ?? '-') ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Status</strong></div>
                    <div class="col-md-8">
                        <?php $currentStatus = strtolower((string) ($order['status'] ?? 'pending')); ?>
                        <span class="badge bg-<?= $currentStatus === 'completed' ? 'success' : ($currentStatus === 'processing' ? 'warning text-dark' : 'secondary') ?>">
                            <?= esc(ucfirst($currentStatus)) ?>
                        </span>
                    </div>
                </div>

                <?php if (strtolower((string) session()->get('role')) === 'admin'): ?>
                    <form method="post" action="<?= site_url('orders/update-status/' . $order['id']) ?>" class="row g-2 align-items-end mt-2">
                        <?= csrf_field() ?>
                        <div class="col-md-4">
                            <label class="form-label small mb-1"><strong>Update Status</strong></label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $currentStatus === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="completed" <?= $currentStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-sm btn-primary">Update Status</button>
                        </div>
                        <?php if ($currentStatus === 'pending'): ?>
                            <div class="col-md-auto">
                                <a href="<?= site_url('orders/edit/' . $order['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit Order">
                                    <i class="bi bi-pencil-square me-1"></i>Edit Order
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-3">Items</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td><?= esc($item['product_name'] ?? 'Item') ?></td>
                                <td class="text-end"><?= (int) $item['qty'] ?></td>
                                <td class="text-end">₱<?= number_format((float) ($item['price'] ?? 0), 2) ?></td>
                                <td class="text-end">₱<?= number_format((float) ($item['subtotal'] ?? 0), 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="max-width: 360px;">
            <div class="card-body">
                <h6 class="mb-3">Summary</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span>₱<?= number_format((float) ($order['total'] ?? 0), 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Discount</span>
                    <span>₱0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2 fw-bold">
                    <span>Total</span>
                    <span>₱<?= number_format((float) ($order['total'] ?? 0), 2) ?></span>
                </div>
                <?php if (($order['payment_method'] ?? '') === 'cash'): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Amount paid (cash)</span>
                    <span>₱<?= number_format((float) ($order['cash'] ?? 0), 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Change</span>
                    <span>₱<?= number_format((float) ($order['change_amount'] ?? 0), 2) ?></span>
                </div>
                <?php elseif (($order['payment_method'] ?? '') === 'card'): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Payment</span>
                    <span>Card</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-muted small">
                    <span>Amount charged</span>
                    <span>₱<?= number_format((float) ($order['total'] ?? 0), 2) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
