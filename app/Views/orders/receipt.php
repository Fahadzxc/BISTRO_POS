<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt <?= esc($order['invoice_no']) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 14px; line-height: 1.4; margin: 0; padding: 12px; max-width: 320px; margin: 0 auto; }
        .receipt-header { text-align: center; margin-bottom: 12px; font-weight: bold; font-size: 16px; }
        .receipt-line { border-bottom: 1px dashed #000; margin: 8px 0; }
        .receipt-items { margin: 8px 0; }
        .receipt-row { display: flex; justify-content: space-between; margin: 4px 0; }
        .receipt-row .qty { flex: 0 0 24px; text-align: right; margin-right: 8px; }
        .receipt-row .name { flex: 1; }
        .receipt-row .amt { text-align: right; }
        .receipt-footer { text-align: center; margin-top: 12px; font-size: 12px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="receipt-header">KTV Bistro POS</div>
    <div class="receipt-line"></div>
    <div class="receipt-row"><span>Invoice #:</span><span><?= esc($order['invoice_no']) ?></span></div>
    <div class="receipt-row"><span>Date:</span><span><?= esc($order['created_at']) ?></span></div>
    <div class="receipt-row"><span>Cashier:</span><span><?= esc($order['cashier_name'] ?? '-') ?></span></div>
    <div class="receipt-line"></div>
    <div class="receipt-items">
        <strong>Items:</strong>
        <?php foreach ($order['items'] as $item): ?>
        <div class="receipt-row">
            <span class="qty"><?= (int) $item['qty'] ?>x</span>
            <span class="name"><?= esc($item['product_name'] ?? 'Item') ?></span>
            <span class="amt"><?= number_format((float) ($item['subtotal'] ?? 0), 2) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="receipt-line"></div>
    <div class="receipt-row"><span>Total:</span><span>₱<?= number_format((float) ($order['total'] ?? 0), 2) ?></span></div>
    <?php if (! empty($order['cash']) && ($order['payment_method'] ?? '') === 'cash'): ?>
    <div class="receipt-row"><span>Cash:</span><span>₱<?= number_format((float) $order['cash'], 2) ?></span></div>
    <div class="receipt-row"><span>Change:</span><span>₱<?= number_format((float) ($order['change_amount'] ?? 0), 2) ?></span></div>
    <?php endif; ?>
    <div class="receipt-line"></div>
    <div class="receipt-footer">Thank you!</div>

    <p class="no-print" style="margin-top: 16px;">
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" onclick="window.close()">Close</button>
    </p>
    <script>
    if (window.location.search.indexOf('print=1') !== -1) {
        window.onload = function() { window.print(); };
    }
    </script>
</body>
</html>
