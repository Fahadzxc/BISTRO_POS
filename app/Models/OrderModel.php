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

    /**
     * Get order with items and cashier name (for detail/receipt).
     */
    public function getOrderWithItems(int $orderId): ?array
    {
        $order = $this->db->table('orders o')
            ->select('o.*, u.name as cashier_name')
            ->join('users u', 'u.user_id = o.cashier_id', 'left')
            ->where('o.id', $orderId)
            ->get()
            ->getRowArray();
        if (! $order) {
            return null;
        }
        $items = $this->db->table('order_items oi')
            ->select('oi.*, p.name as product_name')
            ->join('products p', 'p.id = oi.product_id', 'left')
            ->where('oi.order_id', $orderId)
            ->get()
            ->getResultArray();
        $order['items'] = $items;
        return $order;
    }

    /**
     * Get orders list with cashier name, search and date filter, pagination.
     */
    public function getOrdersList(?string $search, ?string $dateFrom, ?string $dateTo, int $perPage = 20, int $page = 1): array
    {
        $builder = $this->db->table('orders o')
            ->select('o.id, o.invoice_no, o.total, o.payment_method, o.cash, o.change_amount, o.created_at, o.cashier_id, u.name as cashier_name')
            ->join('users u', 'u.user_id = o.cashier_id', 'left');
        if ($search !== null && $search !== '') {
            $builder->like('o.invoice_no', $search);
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $builder->where('DATE(o.created_at) >=', $dateFrom);
        }
        if ($dateTo !== null && $dateTo !== '') {
            $builder->where('DATE(o.created_at) <=', $dateTo);
        }
        $total = $builder->countAllResults(false);
        $rows  = $builder->orderBy('o.id', 'DESC')
            ->limit($perPage, (int) (($page - 1) * $perPage))
            ->get()
            ->getResultArray();
        return [
            'orders' => $rows,
            'total'  => $total,
            'pager'  => [
                'per_page' => $perPage,
                'page'     => $page,
                'total'    => $total,
            ],
        ];
    }
}
