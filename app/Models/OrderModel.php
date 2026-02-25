<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'invoice_no',
        'total',
        'payment_method',
        'cash',
        'change_amount',
        'created_at',
        'cashier_id',
    ];
    protected $useTimestamps    = false;

    public function generateInvoiceNo(): string
    {
        $prefix = 'INV' . date('Ymd');
        $last   = $this->like('invoice_no', $prefix, 'after')->orderBy('id', 'DESC')->first();
        $seq    = 1;
        if ($last) {
            $parts = explode('-', $last['invoice_no']);
            $seq   = (int) end($parts) + 1;
        }
        return $prefix . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function deductStock(int $productId, int $qty): bool
    {
        $productModel = new ProductModel();
        $product      = $productModel->find($productId);
        if (! $product || $product['stock'] < $qty) {
            return false;
        }
        return $productModel->update($productId, [
            'stock' => $product['stock'] - $qty,
        ]);
    }
}
