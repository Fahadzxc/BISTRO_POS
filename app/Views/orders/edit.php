<?= view('layouts/_sidebar', ['currentPage' => 'orders']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Edit Order <?= esc($order['invoice_no']) ?></span>
        <div class="user-info">
            <a href="<?= site_url('orders/view/' . $order['id']) ?>" class="btn btn-outline-secondary btn-sm">Back to Order</a>
        </div>
    </header>

    <main class="content-area">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm" style="max-width: 560px;">
            <div class="card-body">
                <h6 class="mb-3">Order Details</h6>
                <form method="post" action="<?= site_url('orders/update/' . $order['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <?php $paymentMethod = old('payment_method', $order['payment_method'] ?? 'cash'); ?>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="cash" <?= $paymentMethod === 'cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="card" <?= $paymentMethod === 'card' ? 'selected' : '' ?>>Card</option>
                        </select>
                    </div>

                    <div class="mb-3" id="cash_group">
                        <label class="form-label">Cash Amount</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="cash" value="<?= esc((string) old('cash', (float) ($order['cash'] ?? 0))) ?>">
                        <small class="text-muted">Total amount: ₱<?= number_format((float) ($order['total'] ?? 0), 2) ?></small>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
(() => {
    const paymentMethod = document.getElementById('payment_method');
    const cashGroup = document.getElementById('cash_group');

    function toggleCash() {
        const isCash = paymentMethod.value === 'cash';
        cashGroup.style.display = isCash ? 'block' : 'none';
    }

    paymentMethod.addEventListener('change', toggleCash);
    toggleCash();
})();
</script>
