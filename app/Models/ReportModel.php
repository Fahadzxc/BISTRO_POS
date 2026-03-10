<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $DBGroup = 'default';

    private function normalizeRange(?string $from, ?string $to): array
    {
        $fromDate = $from ?: date('Y-m-d');
        $toDate   = $to   ?: date('Y-m-d');
        return [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'];
    }

    public function getSalesSummary(?string $from, ?string $to): array
    {
        [$fromDt, $toDt] = $this->normalizeRange($from, $to);
        $row = $this->db->table('orders')
            ->select('COALESCE(SUM(total),0) AS total_amount, COUNT(*) AS total_orders')
            ->where('created_at >=', $fromDt)
            ->where('created_at <=', $toDt)
            ->get()->getRowArray();
        return [
            'total_amount' => (float) ($row['total_amount'] ?? 0),
            'total_orders' => (int) ($row['total_orders'] ?? 0),
        ];
    }

    public function getSalesTimeseries(?string $from, ?string $to, string $groupBy = 'date'): array
    {
        [$fromDt, $toDt] = $this->normalizeRange($from, $to);
        $builder = $this->db->table('orders')
            ->where('created_at >=', $fromDt)
            ->where('created_at <=', $toDt);
        if ($groupBy === 'month') {
            $builder->select("DATE_FORMAT(created_at, '%Y-%m') AS label, COALESCE(SUM(total),0) AS value")
                ->groupBy("DATE_FORMAT(created_at, '%Y-%m')")->orderBy('label', 'ASC');
        } else {
            $builder->select('DATE(created_at) AS label, COALESCE(SUM(total),0) AS value')
                ->groupBy('DATE(created_at)')->orderBy('label', 'ASC');
        }
        return $builder->get()->getResultArray();
    }

    public function getSalesOrders(?string $from, ?string $to): array
    {
        [$fromDt, $toDt] = $this->normalizeRange($from, $to);
        return $this->db->table('orders')
            ->where('created_at >=', $fromDt)
            ->where('created_at <=', $toDt)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function getKtvSummary(?string $from, ?string $to): array
    {
        [$fromDt, $toDt] = $this->normalizeRange($from, $to);
        $row = $this->db->table('ktv_sessions')
            ->select('COUNT(*) AS total_sessions, COALESCE(SUM(total_minutes),0) AS total_minutes, COALESCE(SUM(total_amount),0) AS total_revenue')
            ->where('status', 'ended')
            ->where('end_time >=', $fromDt)
            ->where('end_time <=', $toDt)
            ->get()->getRowArray();
        return [
            'total_sessions' => (int) ($row['total_sessions'] ?? 0),
            'total_minutes'  => (int) ($row['total_minutes'] ?? 0),
            'total_revenue'  => (float) ($row['total_revenue'] ?? 0),
        ];
    }

    public function getKtvByRoom(?string $from, ?string $to): array
    {
        [$fromDt, $toDt] = $this->normalizeRange($from, $to);
        return $this->db->table('ktv_sessions s')
            ->select('r.room_name, COUNT(s.id) AS total_sessions, COALESCE(SUM(s.total_minutes),0) AS total_minutes, COALESCE(SUM(s.total_amount),0) AS total_revenue')
            ->join('ktv_rooms r', 'r.id = s.room_id')
            ->where('s.status', 'ended')
            ->where('s.end_time >=', $fromDt)
            ->where('s.end_time <=', $toDt)
            ->groupBy('s.room_id')
            ->orderBy('total_revenue', 'DESC')
            ->get()->getResultArray();
    }

    public function getStockLogs(?string $from, ?string $to): array
    {
        [$fromDt, $toDt] = $this->normalizeRange($from, $to);
        return $this->db->table('stock_logs l')
            ->select('l.*, p.name AS product_name')
            ->join('products p', 'p.id = l.product_id')
            ->where('l.created_at >=', $fromDt)
            ->where('l.created_at <=', $toDt)
            ->orderBy('l.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function getStockSummaryByAction(?string $from, ?string $to): array
    {
        [$fromDt, $toDt] = $this->normalizeRange($from, $to);
        return $this->db->table('stock_logs')
            ->select('action_type AS label, COUNT(*) AS movements, COALESCE(SUM(qty_change),0) AS total_qty_change')
            ->where('created_at >=', $fromDt)
            ->where('created_at <=', $toDt)
            ->groupBy('action_type')
            ->get()->getResultArray();
    }

    public function getLowStockList(): array
    {
        return $this->db->table('products')
            ->select('id, name, stock, min_stock')
            ->where('min_stock >', 0)
            ->where('stock <= min_stock')
            ->orderBy('stock', 'ASC')
            ->get()->getResultArray();
    }

    public function getDamageExpiredLogs(?string $from, ?string $to): array
    {
        [$fromDt, $toDt] = $this->normalizeRange($from, $to);
        return $this->db->table('stock_logs l')
            ->select('l.*, p.name AS product_name')
            ->join('products p', 'p.id = l.product_id')
            ->where('l.created_at >=', $fromDt)
            ->where('l.created_at <=', $toDt)
            ->where('l.action_type', 'out')
            ->groupStart()
                ->like('l.remarks', 'Damage')
                ->orLike('l.remarks', 'Expired')
            ->groupEnd()
            ->orderBy('l.created_at', 'DESC')
            ->get()->getResultArray();
    }
}
