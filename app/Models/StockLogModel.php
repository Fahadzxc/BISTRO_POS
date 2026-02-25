<?php

namespace App\Models;

use CodeIgniter\Model;

class StockLogModel extends Model
{
    protected $table            = 'stock_logs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'product_id',
        'qty_before',
        'qty_change',
        'qty_after',
        'action_type',
        'remarks',
        'created_at',
        'user_id',
    ];
    protected $useTimestamps = false;

    public function log(int $productId, int $qtyBefore, int $qtyChange, string $actionType, ?string $remarks = null): bool
    {
        $userId = session()->get('user_id');
        return $this->insert([
            'product_id'  => $productId,
            'qty_before'   => $qtyBefore,
            'qty_change'   => $qtyChange,
            'qty_after'    => $qtyBefore + $qtyChange,
            'action_type'  => $actionType,
            'remarks'      => $remarks,
            'created_at'   => date('Y-m-d H:i:s'),
            'user_id'      => $userId,
        ]) !== false;
    }

    /**
     * Adjust product stock and log. Use inside existing transaction for POS; otherwise runs its own transaction.
     * Prevents negative stock. Returns ['success' => bool, 'message' => string].
     */
    public function adjustStock(int $productId, int $qtyChange, string $actionType, ?string $remarks = null, bool $inTransaction = false): array
    {
        $productModel = new ProductModel();
        $product = $productModel->find($productId);
        if (! $product) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        $qtyBefore = (int) ($product['stock'] ?? 0);
        $qtyAfter = $qtyBefore + $qtyChange;
        if ($qtyAfter < 0) {
            return ['success' => false, 'message' => 'Insufficient stock. Would result in negative stock.'];
        }

        $db = \Config\Database::connect();
        if (! $inTransaction) {
            $db->transStart();
        }

        $productModel->update($productId, ['stock' => $qtyAfter]);
        $this->log($productId, $qtyBefore, $qtyChange, $actionType, $remarks);

        if (! $inTransaction) {
            $db->transComplete();
            if ($db->transStatus() === false) {
                return ['success' => false, 'message' => 'Transaction failed'];
            }
        }
        return ['success' => true, 'message' => 'Stock updated', 'qty_after' => $qtyAfter];
    }

    public function getByProduct(int $productId, int $limit = 50)
    {
        return $this->where('product_id', $productId)
            ->orderBy('id', 'DESC')
            ->findAll($limit);
    }
}
