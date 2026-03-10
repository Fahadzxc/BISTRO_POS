<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $DBGroup = 'default';

    public function getTodaySales(): float
    {
        $row = $this->db->table('orders')
            ->select('COALESCE(SUM(total),0) AS total')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->get()->getRowArray();
        return (float) ($row['total'] ?? 0);
    }

    public function getTodayOrdersCount(): int
    {
        return (int) $this->db->table('orders')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();
    }

    public function getActiveKtvRoomsCount(): int
    {
        $row = $this->db->table('ktv_rooms')
            ->select('COUNT(*) AS cnt')
            ->where('status', 'occupied')
            ->get()->getRowArray();
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Daily sales for last 7 days. Fills missing days with 0 so chart always has 7 points.
     */
    public function getDailySalesLast7Days(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $days[$date] = ['label' => $date, 'value' => 0];
        }

        $rows = $this->db->table('orders')
            ->select('DATE(created_at) AS dt, COALESCE(SUM(total), 0) AS value')
            ->where('created_at >=', date('Y-m-d 00:00:00', strtotime('-6 days')))
            ->where('created_at <=', date('Y-m-d 23:59:59'))
            ->groupBy('DATE(created_at)')
            ->get()->getResultArray();

        foreach ($rows as $r) {
            $dt = $r['dt'];
            if (isset($days[$dt])) {
                $days[$dt]['value'] = (float) $r['value'];
            }
        }

        return array_values($days);
    }

    /**
     * Monthly sales for last 12 months. Fills missing months with 0.
     */
    public function getMonthlySalesLast12Months(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime("first day of -{$i} months"));
            $months[$ym] = ['label' => $ym, 'value' => 0];
        }

        $rows = $this->db->table('orders')
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(total), 0) AS value")
            ->where('created_at >=', date('Y-m-01 00:00:00', strtotime('first day of -11 months')))
            ->groupBy("DATE_FORMAT(created_at, '%Y-%m')")
            ->get()->getResultArray();

        foreach ($rows as $r) {
            $ym = $r['ym'];
            if (isset($months[$ym])) {
                $months[$ym]['value'] = (float) $r['value'];
            }
        }

        return array_values($months);
    }

    /**
     * Top N products by quantity sold (real data from order_items).
     */
    public function getTopProducts(int $limit = 10): array
    {
        return $this->db->table('order_items oi')
            ->select('COALESCE(p.name, \'Unknown\') AS name, SUM(oi.qty) AS total_qty, COALESCE(SUM(oi.subtotal), 0) AS total_amount')
            ->join('products p', 'p.id = oi.product_id', 'left')
            ->groupBy('oi.product_id')
            ->orderBy('total_qty', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    /**
     * KTV room usage (revenue) for chart. Returns ALL rooms so VIP etc. show even with 0 revenue.
     * Ended sessions that ended within the date range; rooms with no sessions get 0.
     */
    public function getKtvUsageByRoom(string $from, string $to): array
    {
        $rooms = $this->db->table('ktv_rooms')
            ->select('id, room_name')
            ->orderBy('room_name')
            ->get()->getResultArray();

        $usage = $this->db->table('ktv_sessions s')
            ->select('s.room_id, COUNT(s.id) AS total_sessions, COALESCE(SUM(s.total_minutes),0) AS total_minutes, COALESCE(SUM(s.total_amount),0) AS total_revenue')
            ->where('s.status', 'ended')
            ->where('s.end_time >=', $from)
            ->where('s.end_time <=', $to)
            ->groupBy('s.room_id')
            ->get()->getResultArray();

        $byRoom = [];
        foreach ($usage as $row) {
            $byRoom[(int) $row['room_id']] = $row;
        }

        $result = [];
        foreach ($rooms as $r) {
            $id = (int) $r['id'];
            $u = $byRoom[$id] ?? null;
            $result[] = [
                'room_name'      => $r['room_name'],
                'total_sessions' => $u ? (int) $u['total_sessions'] : 0,
                'total_minutes'  => $u ? (int) $u['total_minutes'] : 0,
                'total_revenue'  => $u ? (float) $u['total_revenue'] : 0,
            ];
        }

        return $result;
    }
}
