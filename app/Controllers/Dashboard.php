<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DashboardModel;
use App\Models\ProductModel;

class Dashboard extends BaseController
{
    public function index()
    {
        helper('url');
        $role = session()->get('role');

        if ($role !== 'admin') {
            return $this->simpleDashboard();
        }

        return view('templates/template', [
            'title'     => 'Dashboard | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard layout-dashboard-analytics',
            'content'   => view('dashboard/index', [
                'urlStats' => site_url('dashboard/stats'),
            ]),
        ]);
    }

    public function stats()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false])->setStatusCode(403);
        }

        $dash    = new DashboardModel();
        $product = new ProductModel();

        $from = date('Y-m-d 00:00:00', strtotime('-6 days'));
        $to   = date('Y-m-d 23:59:59');

        $daily   = $dash->getDailySalesLast7Days();
        $monthly = $dash->getMonthlySalesLast12Months();
        $top     = $dash->getTopProducts(10);
        $ktv     = $dash->getKtvUsageByRoom($from, $to);

        return $this->response->setJSON([
            'success' => true,
            'widgets' => [
                'totalSalesToday'     => $dash->getTodaySales(),
                'totalOrdersToday'    => $dash->getTodayOrdersCount(),
                'activeKtvRooms'      => $dash->getActiveKtvRoomsCount(),
                'totalCustomersToday' => $dash->getTodayOrdersCount(),
                'lowStockAlerts'      => $product->getLowStockCount(),
                'outOfStockAlerts'    => $product->getOutOfStockCount(),
            ],
            'charts' => [
                'dailySales'   => ['labels' => array_column($daily, 'label'), 'data' => array_map('floatval', array_column($daily, 'value'))],
                'monthlySales' => ['labels' => array_column($monthly, 'label'), 'data' => array_map('floatval', array_column($monthly, 'value'))],
                'topProducts'  => ['labels' => array_column($top, 'name'), 'data' => array_map('intval', array_column($top, 'total_qty'))],
                'ktvUsage'     => ['labels' => array_column($ktv, 'room_name'), 'data' => array_map('floatval', array_column($ktv, 'total_revenue'))],
            ],
        ]);
    }

    private function simpleDashboard()
    {
        $product = new ProductModel();
        return view('templates/template', [
            'title'     => 'Dashboard | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard',
            'content'   => view('dashboard', [
                'low_stock_count'    => $product->getLowStockCount(),
                'out_of_stock_count' => $product->getOutOfStockCount(),
            ]),
        ]);
    }
}
