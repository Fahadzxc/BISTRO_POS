<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReportModel;

class Reports extends BaseController
{
    public function sales()
    {
        helper('url');
        $from = $this->request->getGet('from') ?: date('Y-m-d');
        $to   = $this->request->getGet('to') ?: date('Y-m-d');
        return view('templates/template', [
            'title'     => 'Sales Reports | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard layout-reports',
            'content'   => view('reports/sales', [
                'from'    => $from,
                'to'      => $to,
                'urlData' => site_url('reports/sales/data'),
            ]),
        ]);
    }

    public function salesData()
    {
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');
        $m = new ReportModel();
        return $this->response->setJSON([
            'success' => true,
            'summary' => $m->getSalesSummary($from, $to),
            'series'  => $m->getSalesTimeseries($from, $to, 'date'),
            'orders'  => $m->getSalesOrders($from, $to),
        ]);
    }

    public function ktv()
    {
        helper('url');
        $from = $this->request->getGet('from') ?: date('Y-m-d');
        $to   = $this->request->getGet('to') ?: date('Y-m-d');
        return view('templates/template', [
            'title'     => 'KTV Room Usage Reports | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard layout-reports',
            'content'   => view('reports/ktv', [
                'from'    => $from,
                'to'      => $to,
                'urlData' => site_url('reports/ktv/data'),
            ]),
        ]);
    }

    public function ktvData()
    {
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');
        $m = new ReportModel();
        $summary = $m->getKtvSummary($from, $to);
        $byRoom  = $m->getKtvByRoom($from, $to);
        $summary['most_used_room'] = ! empty($byRoom) ? ($byRoom[0]['room_name'] ?? null) : null;
        return $this->response->setJSON([
            'success' => true,
            'summary' => $summary,
            'byRoom'  => $byRoom,
        ]);
    }

    public function inventory()
    {
        helper('url');
        $from = $this->request->getGet('from') ?: date('Y-m-d');
        $to   = $this->request->getGet('to') ?: date('Y-m-d');
        return view('templates/template', [
            'title'     => 'Inventory Reports | KTV Bistro POS',
            'bodyClass' => 'layout-dashboard layout-reports',
            'content'   => view('reports/inventory', [
                'from'    => $from,
                'to'      => $to,
                'urlData' => site_url('reports/inventory/data'),
            ]),
        ]);
    }

    public function inventoryData()
    {
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');
        $m = new ReportModel();
        return $this->response->setJSON([
            'success' => true,
            'summary' => $m->getStockSummaryByAction($from, $to),
            'logs'    => $m->getStockLogs($from, $to),
            'lowStock'=> $m->getLowStockList(),
            'issues'  => $m->getDamageExpiredLogs($from, $to),
        ]);
    }
}
