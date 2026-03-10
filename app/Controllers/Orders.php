<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderModel;

class Orders extends BaseController
{
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
}
