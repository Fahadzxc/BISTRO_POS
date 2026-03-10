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
                <?php if (! empty($order['cash']) && $order['payment_method'] === 'cash'): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Cash</span>
                    <span>₱<?= number_format((float) $order['cash'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Change</span>
                    <span>₱<?= number_format((float) ($order['change_amount'] ?? 0), 2) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
