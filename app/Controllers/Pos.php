<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Models\StockLogModel;

class Pos extends BaseController
{
    private const CART_KEY = 'pos_cart';

    public function index()
    {
        helper(['url', 'form']);

        return view('templates/template', [
            'title'     => 'POS | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard layout-pos',
            'content'   => view('pos/pos', [
                'categories'   => (new CategoryModel())->findAll(),
                'csrfToken'    => csrf_hash(),
                'csrfName'     => csrf_token(),
                'urlAddToCart' => site_url('pos/add-to-cart'),
                'urlUpdateCart'=> site_url('pos/update-cart'),
                'urlRemoveFromCart' => site_url('pos/remove-from-cart'),
                'urlGetCart'   => site_url('pos/get-cart'),
                'urlGetProducts'=> site_url('pos/get-products'),
                'urlCheckout'  => site_url('pos/checkout'),
            ]),
        ]);
    }

    public function getProducts()
    {
        helper('url');
        $categoryId = $this->request->getGet('category_id');
        $model      = new ProductModel();
        $products   = $model->getByCategory($categoryId ? (int) $categoryId : null);
        foreach ($products as &$p) {
            if (! empty($p['image'])) {
                $p['image'] = base_url($p['image']);
            }
        }
        return $this->response->setJSON($products);
    }

    public function addToCart()
    {
        $productId = (int) $this->request->getPost('product_id');
        $qty       = max(1, (int) $this->request->getPost('qty'));

        $product = (new ProductModel())->find($productId);
        if (! $product) {
            return $this->response->setJSON(['success' => false, 'message' => 'Product not found']);
        }
        if ($product['stock'] < $qty) {
            return $this->response->setJSON(['success' => false, 'message' => 'Insufficient stock']);
        }

        $cart = session()->get(self::CART_KEY) ?? [];
        $key  = 'p' . $productId;

        if (isset($cart[$key])) {
            $newQty = $cart[$key]['qty'] + $qty;
            if ($newQty > $product['stock']) {
                return $this->response->setJSON(['success' => false, 'message' => 'Insufficient stock']);
            }
            $cart[$key]['qty']      = $newQty;
            $cart[$key]['subtotal'] = $newQty * (float) $product['price'];
        } else {
            $cart[$key] = [
                'product_id' => $productId,
                'name'       => $product['name'],
                'price'      => (float) $product['price'],
                'qty'        => $qty,
                'subtotal'   => $qty * (float) $product['price'],
            ];
        }

        session()->set(self::CART_KEY, $cart);
        return $this->response->setJSON(['success' => true, 'cart' => $this->getCartSummary()]);
    }

    public function updateCart()
    {
        $productId = (int) $this->request->getPost('product_id');
        $qty       = max(0, (int) $this->request->getPost('qty'));

        $cart = session()->get(self::CART_KEY) ?? [];
        $key  = 'p' . $productId;

        if (! isset($cart[$key])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item not in cart']);
        }

        if ($qty === 0) {
            unset($cart[$key]);
        } else {
            $product = (new ProductModel())->find($productId);
            if ($product && $product['stock'] < $qty) {
                return $this->response->setJSON(['success' => false, 'message' => 'Insufficient stock']);
            }
            $cart[$key]['qty']      = $qty;
            $cart[$key]['subtotal'] = $qty * (float) $cart[$key]['price'];
        }

        session()->set(self::CART_KEY, $cart);
        return $this->response->setJSON(['success' => true, 'cart' => $this->getCartSummary()]);
    }

    public function removeFromCart()
    {
        $cartKey   = $this->request->getPost('cart_key');
        $productId = (int) $this->request->getPost('product_id');
        $cart      = session()->get(self::CART_KEY) ?? [];

        if ($cartKey !== null && $cartKey !== '') {
            if (isset($cart[$cartKey])) {
                unset($cart[$cartKey]);
                session()->set(self::CART_KEY, $cart);
            }
        } else {
            $key = 'p' . $productId;
            if (isset($cart[$key])) {
                unset($cart[$key]);
                session()->set(self::CART_KEY, $cart);
            }
        }

        return $this->response->setJSON(['success' => true, 'cart' => $this->getCartSummary()]);
    }

    public function getCart()
    {
        return $this->response->setJSON(['success' => true, 'cart' => $this->getCartSummary()]);
    }

    /**
     * Add KTV room charge to POS cart (called when KTV session ends).
     */
    public function addRoomCharge()
    {
        $roomName = trim((string) $this->request->getPost('room_name'));
        $amount   = (float) $this->request->getPost('amount');
        $hours    = trim((string) $this->request->getPost('hours'));

        if ($roomName === '' || $amount <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid room charge data']);
        }

        $productModel = new ProductModel();
        $roomChargeProduct = $productModel->where('name', 'KTV Room Charge')->first();
        if (! $roomChargeProduct) {
            return $this->response->setJSON(['success' => false, 'message' => 'KTV Room Charge product not found']);
        }

        $name = $roomName . ($hours !== '' ? ' (' . $hours . ')' : '');
        $key  = 'room_' . uniqid();

        $cart = session()->get(self::CART_KEY) ?? [];
        $cart[$key] = [
            'product_id' => (int) $roomChargeProduct['id'],
            'name'       => $name,
            'price'      => $amount,
            'qty'        => 1,
            'subtotal'   => $amount,
        ];
        session()->set(self::CART_KEY, $cart);

        return $this->response->setJSON(['success' => true, 'cart' => $this->getCartSummary()]);
    }

    public function checkout()
    {
        $cart = session()->get(self::CART_KEY) ?? [];
        if (empty($cart)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cart is empty']);
        }

        $total = array_sum(array_column($cart, 'subtotal'));

        $productModel = new ProductModel();
        $orderModel  = new OrderModel();
        $db          = \Config\Database::connect();
        $cashierId   = session()->get('user_id');
        $stockLogModel = new StockLogModel();
        $editingOrderId = (int) (session()->get('pos_edit_order_id') ?? 0);

        $editingOrder = null;
        if ($editingOrderId > 0) {
            $editingOrder = $orderModel->getOrderWithItems($editingOrderId);
            if (! $editingOrder) {
                session()->remove('pos_edit_order_id');
                return $this->response->setJSON(['success' => false, 'message' => 'Editing order not found.']);
            }
            if (($editingOrder['status'] ?? 'pending') !== 'pending') {
                session()->remove('pos_edit_order_id');
                return $this->response->setJSON(['success' => false, 'message' => 'Only pending orders can be edited.']);
            }
        }

        $newQtyByProduct = [];
        foreach ($cart as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }
            $product = $productModel->find($productId);
            if (! $product || ($product['name'] ?? '') === 'KTV Room Charge') {
                continue;
            }
            $newQtyByProduct[$productId] = ($newQtyByProduct[$productId] ?? 0) + (int) ($item['qty'] ?? 0);
        }

        $oldQtyByProduct = [];
        if ($editingOrder) {
            foreach (($editingOrder['items'] ?? []) as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                if ($productId <= 0) {
                    continue;
                }
                $product = $productModel->find($productId);
                if (! $product || ($product['name'] ?? '') === 'KTV Room Charge') {
                    continue;
                }
                $oldQtyByProduct[$productId] = ($oldQtyByProduct[$productId] ?? 0) + (int) ($item['qty'] ?? 0);
            }
        }

        foreach ($newQtyByProduct as $productId => $newQty) {
            $product = $productModel->find($productId);
            if (! $product) {
                return $this->response->setJSON(['success' => false, 'message' => 'Product not found while validating stock.']);
            }
            $stock = (int) ($product['stock'] ?? 0);
            $oldReservedQty = (int) ($oldQtyByProduct[$productId] ?? 0);
            $available = $stock + $oldReservedQty;
            if ($available < $newQty) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Insufficient stock for: ' . ($product['name'] ?? '') . ' (need ' . $newQty . ', available ' . $available . ')',
                ]);
            }
        }

        $db->transStart();

        $invoiceNo = $editingOrder['invoice_no'] ?? $orderModel->generateInvoiceNo();
        $orderId = $editingOrder ? (int) $editingOrder['id'] : 0;
        if ($editingOrder) {
            $updated = $orderModel->update($orderId, [
                'total'          => $total,
                'cashier_id'     => $cashierId,
            ]);
            if (! $updated) {
                $db->transRollback();
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to update order']);
            }
            $db->table('order_items')->where('order_id', $orderId)->delete();
        } else {
            $orderId = $orderModel->insert([
                'invoice_no'    => $invoiceNo,
                'total'         => $total,
                'status'        => 'pending',
                'created_at'    => date('Y-m-d H:i:s'),
                'cashier_id'    => $cashierId,
            ]);
        }

        if (! $orderId) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to create order']);
        }

        foreach ($cart as $item) {
            $db->table('order_items')->insert([
                'order_id'   => $orderId,
                'product_id' => $item['product_id'],
                'qty'        => $item['qty'],
                'price'      => $item['price'],
                'subtotal'   => $item['subtotal'],
            ]);

        }

        $productIds = array_unique(array_merge(array_keys($newQtyByProduct), array_keys($oldQtyByProduct)));
        foreach ($productIds as $productId) {
            $newQty = (int) ($newQtyByProduct[$productId] ?? 0);
            $oldQty = (int) ($oldQtyByProduct[$productId] ?? 0);
            $diff = $newQty - $oldQty;
            if ($diff === 0) {
                continue;
            }
            $stockResult = $stockLogModel->adjustStock(
                (int) $productId,
                -$diff,
                'pos',
                ($editingOrder ? 'Edit Order #' : 'Order #') . $invoiceNo,
                true
            );
            if (! ($stockResult['success'] ?? false)) {
                $db->transRollback();
                return $this->response->setJSON(['success' => false, 'message' => (string) ($stockResult['message'] ?? 'Stock update failed')]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Transaction failed']);
        }

        session()->remove(self::CART_KEY);
        session()->remove('pos_edit_order_id');

        return $this->response->setJSON([
            'success'      => true,
            'invoice_no'   => $invoiceNo,
            'total'        => $total,
            'edited'       => $editingOrder ? true : false,
        ]);
    }

    private function getCartSummary(): array
    {
        $cart  = session()->get(self::CART_KEY) ?? [];
        $items = [];
        foreach ($cart as $key => $item) {
            $item['_key'] = $key;
            $items[]      = $item;
        }
        $subtotal = array_sum(array_column($items, 'subtotal'));
        return [
            'items'    => $items,
            'subtotal' => $subtotal,
            'total'    => $subtotal,
        ];
    }

}
