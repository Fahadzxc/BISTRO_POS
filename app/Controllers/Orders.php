<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderModel;

class Orders extends BaseController
{
    private const ALLOWED_STATUSES = ['pending', 'processing', 'completed'];

    private function ensureAdmin()
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('orders'))->with('error', 'Only admin can modify orders.');
        }
        return null;
    }

    public function index()
    {
        helper('url');
        $model   = new OrderModel();
        $search  = $this->request->getGet('search');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo   = $this->request->getGet('date_to');
        $page     = max(1, (int) $this->request->getGet('page'));
        $perPage  = 20;

        $result = $model->getOrdersList($search, $dateFrom, $dateTo, $perPage, $page);

        return view('templates/template', [
            'title'     => 'Order History | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('orders/index', [
                'orders'    => $result['orders'],
                'pager'     => $result['pager'],
                'search'    => $search,
                'dateFrom'  => $dateFrom,
                'dateTo'    => $dateTo,
            ]),
        ]);
    }

    public function view(int $id)
    {
        helper('url');
        $model = new OrderModel();
        $order = $model->getOrderWithItems($id);
        if (! $order) {
            return redirect()->to(site_url('orders'))->with('error', 'Order not found.');
        }
        return view('templates/template', [
            'title'     => 'Order ' . esc($order['invoice_no']) . ' | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('orders/view', [
                'order' => $order,
            ]),
        ]);
    }

    public function print(int $id)
    {
        $model = new OrderModel();
        $order = $model->getOrderWithItems($id);
        if (! $order) {
            return redirect()->to(site_url('orders'))->with('error', 'Order not found.');
        }
        return view('orders/receipt', [
            'order' => $order,
        ]);
    }

    public function updateStatus(int $id)
    {
        $adminRedirect = $this->ensureAdmin();
        if ($adminRedirect !== null) {
            return $adminRedirect;
        }

        $status = strtolower(trim((string) $this->request->getPost('status')));
        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
            return redirect()->to(site_url('orders/view/' . $id))->with('error', 'Invalid order status.');
        }

        $model = new OrderModel();
        $order = $model->find($id);
        if (! $order) {
            return redirect()->to(site_url('orders'))->with('error', 'Order not found.');
        }

        $model->update($id, ['status' => $status]);
        return redirect()->to(site_url('orders/view/' . $id))->with('success', 'Order status updated.');
    }

    public function edit(int $id)
    {
        helper('url');

        $adminRedirect = $this->ensureAdmin();
        if ($adminRedirect !== null) {
            return $adminRedirect;
        }

        $model = new OrderModel();
        $order = $model->getOrderWithItems($id);
        if (! $order) {
            return redirect()->to(site_url('orders'))->with('error', 'Order not found.');
        }
        if (($order['status'] ?? 'pending') !== 'pending') {
            return redirect()->to(site_url('orders/view/' . $id))->with('error', 'Only pending orders can be edited.');
        }

        $cart = [];
        foreach ($order['items'] as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $key = 'p' . $productId;
            if (! isset($cart[$key])) {
                $cart[$key] = [
                    'product_id' => $productId,
                    'name'       => (string) ($item['product_name'] ?? 'Item'),
                    'price'      => (float) ($item['price'] ?? 0),
                    'qty'        => 0,
                    'subtotal'   => 0,
                ];
            }

            $cart[$key]['qty'] += (int) ($item['qty'] ?? 0);
            $cart[$key]['subtotal'] = $cart[$key]['qty'] * (float) $cart[$key]['price'];
        }

        session()->set('pos_cart', $cart);
        session()->set('pos_edit_order_id', $id);

        return redirect()->to(site_url('pos'))->with('success', 'Order loaded into POS cart for editing.');
    }

    public function update(int $id)
    {
        $adminRedirect = $this->ensureAdmin();
        if ($adminRedirect !== null) {
            return $adminRedirect;
        }

        $model = new OrderModel();
        $order = $model->getOrderWithItems($id);
        if (! $order) {
            return redirect()->to(site_url('orders'))->with('error', 'Order not found.');
        }
        if (($order['status'] ?? 'pending') !== 'pending') {
            return redirect()->to(site_url('orders/view/' . $id))->with('error', 'Only pending orders can be edited.');
        }

        $paymentMethod = trim((string) $this->request->getPost('payment_method'));
        if (! in_array($paymentMethod, ['cash', 'card'], true)) {
            return redirect()->back()->withInput()->with('error', 'Invalid payment method.');
        }

        $total = (float) ($order['total'] ?? 0);
        $cash = null;
        $changeAmount = null;

        if ($paymentMethod === 'cash') {
            $cash = (float) $this->request->getPost('cash');
            if ($cash < $total) {
                return redirect()->back()->withInput()->with('error', 'Cash amount must be at least total.');
            }
            $changeAmount = $cash - $total;
        }

        $model->update($id, [
            'payment_method' => $paymentMethod,
            'cash'           => $paymentMethod === 'cash' ? $cash : null,
            'change_amount'  => $paymentMethod === 'cash' ? $changeAmount : null,
        ]);

        return redirect()->to(site_url('orders/view/' . $id))->with('success', 'Order updated successfully.');
    }
}
