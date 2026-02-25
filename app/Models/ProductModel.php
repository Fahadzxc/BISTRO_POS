<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['name', 'price', 'stock', 'min_stock', 'category_id', 'image'];

    public function getByCategory(?int $categoryId = null)
    {
        if ($categoryId !== null) {
            $this->where('category_id', $categoryId);
        }
        return $this->orderBy('name')->findAll();
    }

    /** Stock status: in_stock, low_stock, out_of_stock */
    public static function getStockStatus(int $stock, int $minStock): string
    {
        if ($stock <= 0) {
            return 'out_of_stock';
        }
        if ($minStock > 0 && $stock <= $minStock) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function getLowStockCount(): int
    {
        $products = $this->select('stock, min_stock')->findAll();
        $count = 0;
        foreach ($products as $p) {
            $min = (int) ($p['min_stock'] ?? 0);
            $stock = (int) ($p['stock'] ?? 0);
            if ($min > 0 && $stock > 0 && $stock <= $min) {
                $count++;
            }
        }
        return $count;
    }

    public function getOutOfStockCount(): int
    {
        return $this->where('stock <=', 0)->countAllResults();
    }

    public function getInventoryList(?string $search = null, ?int $categoryId = null, ?string $statusFilter = null): array
    {
        $builder = $this->db->table('products as p')
            ->select('p.id, p.name, p.price, p.stock, p.min_stock, p.category_id, c.name as category_name')
            ->join('categories c', 'c.id = p.category_id', 'left');

        if ($search !== null && $search !== '') {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('c.name', $search)
                ->groupEnd();
        }
        if ($categoryId !== null && $categoryId !== '') {
            $builder->where('p.category_id', (int) $categoryId);
        }

        $rows = $builder->orderBy('p.name')->get()->getResultArray();
        $list = [];
        foreach ($rows as $row) {
            $stock = (int) ($row['stock'] ?? 0);
            $minStock = (int) ($row['min_stock'] ?? 0);
            $status = self::getStockStatus($stock, $minStock);
            if ($statusFilter !== null && $statusFilter !== '' && $status !== $statusFilter) {
                continue;
            }
            $row['stock_status'] = $status;
            $list[] = $row;
        }
        return $list;
    }
}

