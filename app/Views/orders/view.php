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
                    <?php if ($currentStatus === 'completed'): ?>
                        <div class="alert alert-info mt-2 mb-0">
                            <i class="bi bi-info-circle me-2"></i>Order Completed
                        </div>
                    <?php else: ?>
                    <form id="statusUpdateForm" method="post" action="<?= site_url('orders/update-status/' . $order['id']) ?>" class="row g-2 align-items-end mt-2">
                        <?= csrf_field() ?>
                        <div class="col-md-4">
                            <label class="form-label small mb-1"><strong>Update Status</strong></label>
                            <select id="statusSelect" name="status" class="form-select form-select-sm">
                                <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?> <?= $currentStatus === 'processing' ? 'disabled' : '' ?>>Pending</option>
                                <option value="processing" <?= $currentStatus === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="completed" <?= $currentStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="button" class="btn btn-sm btn-primary" id="statusUpdateBtn">Update Status</button>
                        </div>
                        <?php if ($currentStatus === 'pending'): ?>
                            <div class="col-md-auto">
                                <button type="button" class="btn btn-sm btn-success" id="paymentBtn" data-toggle="payment">
                                    <i class="bi bi-credit-card me-1"></i>Payment
                                </button>
                            </div>
                            <div class="col-md-auto">
                                <a href="<?= site_url('orders/edit/' . $order['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit Order">
                                    <i class="bi bi-pencil-square me-1"></i>Edit Order
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                    
                    <!-- Hidden form for payment processing -->
                    <form id="paymentForm" method="post" action="<?= site_url('orders/update/' . $order['id']) ?>" style="display:none;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="payment_method" id="hiddenPaymentMethod" value="">
                        <input type="hidden" name="cash" id="hiddenCash" value="">
                    </form>
                    <?php endif; ?>
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

<!-- Payment Modal for Orders -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Total Amount</label>
                    <input type="text" class="form-control form-control-lg" id="paymentTotal" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" id="paymentMethod">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                    </select>
                </div>
                <div class="mb-3" id="cashInputGroup">
                    <label class="form-label">Cash Received</label>
                    <input type="number" class="form-control form-control-lg" id="paymentCash" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="mb-0" id="changeGroup" style="display:none;">
                    <label class="form-label">Change</label>
                    <input type="text" class="form-control form-control-lg text-success fw-bold" id="paymentChange" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-lg" id="paymentConfirmBtn">
                    <i class="bi bi-check-lg me-1"></i>Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderId = <?= $order['id'] ?>;
    const orderTotal = <?= (float) ($order['total'] ?? 0) ?>;
    const csrfName = '<?= csrf_token() ?>';
    const csrfHash = document.querySelector('input[name="<?= csrf_token() ?>"]')?.value || '<?= csrf_hash() ?>';

    const statusUpdateForm = document.getElementById('statusUpdateForm');
    const statusSelect = document.getElementById('statusSelect');
    const statusUpdateBtn = document.getElementById('statusUpdateBtn');
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    const paymentBtn = document.getElementById('paymentBtn');
    let paymentMode = 'update-status'; // 'update-status' or 'payment-only'

    function formatPrice(n) {
        return '₱' + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
   paymentBtn?.addEventListener('click', function(e) {
       e.preventDefault();
       paymentMode = 'payment-only';
       document.getElementById('paymentTotal').value = formatPrice(orderTotal);
       document.getElementById('paymentCash').value = '';
       document.getElementById('paymentChange').value = '';
       document.getElementById('paymentMethod').value = 'cash';
       document.getElementById('cashInputGroup').style.display = 'block';
       document.getElementById('changeGroup').style.display = 'none';
       paymentModal.show();
   });


    statusUpdateBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const newStatus = statusSelect.value;
        const currentStatus = '<?= $currentStatus ?>';

        // If changing to "processing" or "completed", check if payment is needed
        if ((newStatus === 'processing' || newStatus === 'completed') && ('<?= ($order['payment_method'] ?? '') ?>' === '')) {
            // Payment not yet processed, show payment modal
            paymentMode = 'update-status';
            document.getElementById('paymentTotal').value = formatPrice(orderTotal);
            document.getElementById('paymentCash').value = '';
            document.getElementById('paymentChange').value = '';
            document.getElementById('paymentMethod').value = 'cash';
            document.getElementById('cashInputGroup').style.display = 'block';
            document.getElementById('changeGroup').style.display = 'none';
            paymentModal.show();
        } else {
            // Just update status normally
            statusUpdateForm.submit();
        }
    });

    document.getElementById('paymentMethod').addEventListener('change', function() {
        document.getElementById('cashInputGroup').style.display = this.value === 'cash' ? 'block' : 'none';
        document.getElementById('changeGroup').style.display = 'none';
    });

    document.getElementById('paymentCash').addEventListener('input', function() {
        const cash = parseFloat(this.value) || 0;
        const changeEl = document.getElementById('paymentChange');
        const changeGroup = document.getElementById('changeGroup');
        if (cash >= orderTotal) {
            changeEl.value = formatPrice(cash - orderTotal);
            changeGroup.style.display = 'block';
        } else {
            changeGroup.style.display = 'none';
        }
    });

    document.getElementById('paymentConfirmBtn').addEventListener('click', async function() {
        const method = document.getElementById('paymentMethod').value;
        const cash = parseFloat(document.getElementById('paymentCash').value) || 0;

        if (method === 'cash' && cash < orderTotal) {
            alert('Insufficient cash amount');
            return;
        }

        // Set the hidden form values
        document.getElementById('hiddenPaymentMethod').value = method;
        document.getElementById('hiddenCash').value = cash;
        
        // Submit the payment form
        document.getElementById('paymentForm').submit();
    });
});
</script>
