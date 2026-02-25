<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\StockLogModel;

class Inventory extends BaseController
{
    public function index()
    {
        helper('url');
        $categoryModel = new CategoryModel();
        $productModel  = new ProductModel();

        return view('templates/template', [
            'title'           => 'Inventory | KTV Bistro POS',
            'bodyClass'       => 'layout-dashboard layout-inventory',
            'content'         => view('inventory/inventory', [
                'categories'     => $categoryModel->findAll(),
                'lowStockCount'  => $productModel->getLowStockCount(),
                'outOfStockCount'=> $productModel->getOutOfStockCount(),
                'urlList'        => site_url('inventory/list'),
                'urlAdjust'      => site_url('inventory/adjust'),
                'urlLogs'        => site_url('inventory/logs'),
                'csrfToken'      => csrf_hash(),
                'csrfName'       => csrf_token(),
            ]),
        ]);
    }

    public function list()
    {
        $search       = $this->request->getGet('search');
        $categoryId   = $this->request->getGet('category_id');
        $statusFilter = $this->request->getGet('status');
        $model        = new ProductModel();
        $list         = $model->getInventoryList($search, $categoryId ? (int) $categoryId : null, $statusFilter);
        return $this->response->setJSON(['success' => true, 'data' => $list]);
    }

    public function adjust()
    {
        $productId = (int) $this->request->getPost('product_id');
        $direction = $this->request->getPost('direction'); // 'in' or 'out'
        $qty       = max(0, (int) $this->request->getPost('qty'));
        $reason    = $this->request->getPost('reason');

        if (! in_array($direction, ['in', 'out'], true) || $qty <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid direction or quantity']);
        }

        $qtyChange = $direction === 'in' ? $qty : -$qty;
        $actionType = $direction;
        $remarks   = $reason ?: 'Manual adjustment';

        $stockLogModel = new StockLogModel();
        $result = $stockLogModel->adjustStock($productId, $qtyChange, $actionType, $remarks, false);

        return $this->response->setJSON($result);
    }

    public function updateMinStock()
    {
        $productId = (int) $this->request->getPost('product_id');
        $minStock  = max(0, (int) $this->request->getPost('min_stock'));

        $productModel = new ProductModel();
        $product = $productModel->find($productId);
        if (! $product) {
            return $this->response->setJSON(['success' => false, 'message' => 'Product not found']);
        }

        $productModel->update($productId, ['min_stock' => $minStock]);
        return $this->response->setJSON(['success' => true, 'message' => 'Minimum stock updated']);
    }

    public function logs()
    {
        $productId = (int) $this->request->getGet('product_id');
        $limit     = min(100, max(10, (int) ($this->request->getGet('limit') ?: 50)));
        $model     = new StockLogModel();
        $logs      = $model->getByProduct($productId, $limit);
        return $this->response->setJSON(['success' => true, 'data' => $logs]);
    }
}
