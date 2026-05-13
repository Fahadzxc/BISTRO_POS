<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $DBGroup = 'default';

    /**
     * Active sessions started today are counted as one-hour provisional sales
     * so dashboard widgets update immediately when Start is clicked.
     */
    private function getTodayActiveKtvProvisionalSales(): float
    {
        $row = $this->db->table('ktv_sessions s')
            ->select('COALESCE(SUM(r.hourly_rate),0) AS total')
            ->join('ktv_rooms r', 'r.id = s.room_id', 'inner')
            ->where('s.status', 'active')
            ->where('DATE(s.start_time)', date('Y-m-d'))
            ->get()->getRowArray();

        return (float) ($row['total'] ?? 0);
    }

    public function getTodaySales(): float
    {
        $orderSales = $this->db->table('orders')
            ->select('COALESCE(SUM(total),0) AS total')
            ->where('status', 'completed')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->get()->getRowArray();

        $ktvSales = $this->db->table('ktv_sessions')
            ->select('COALESCE(SUM(total_amount),0) AS total')
            ->where('status', 'ended')
            ->where('DATE(end_time)', date('Y-m-d'))
            ->get()->getRowArray();

        return (float) ($orderSales['total'] ?? 0)
            + (float) ($ktvSales['total'] ?? 0)
            + $this->getTodayActiveKtvProvisionalSales();
    }

    public function getTodayKtvSales(): float
    {
        $row = $this->db->table('ktv_sessions')
            ->select('COALESCE(SUM(total_amount),0) AS total')
            ->where('status', 'ended')
            ->where('DATE(end_time)', date('Y-m-d'))
            ->get()->getRowArray();

        return (float) ($row['total'] ?? 0) + $this->getTodayActiveKtvProvisionalSales();
    }

    public function getTodayKtvSessionsCount(): int
    {
        return (int) $this->db->table('ktv_sessions')
            ->where('status', 'ended')
            ->where('DATE(end_time)', date('Y-m-d'))
            ->countAllResults();
    }

    public function getActiveKtvRoomsDetails(): array
    {
        $rooms = $this->db->table('ktv_rooms r')
            ->select('r.id, r.room_name, r.hourly_rate, r.capacity, s.start_time, s.id as session_id')
            ->join('ktv_sessions s', 's.room_id = r.id AND s.status = "active"', 'inner')
            ->where('r.status', 'occupied')
            ->get()->getResultArray();

        $result = [];
        foreach ($rooms as $room) {
            $elapsed = time() - strtotime($room['start_time']);
            $remaining = max(0, 3600 - $elapsed);
            $result[] = [
                'room_name'  => $room['room_name'],
                'capacity'   => (int) $room['capacity'],
                'hourly_rate'=> (float) $room['hourly_rate'],
                'elapsed'    => $elapsed,
                'remaining'  => $remaining,
            ];
        }
        return $result;
    }

    public function getTodayOrdersCount(): int
    {
        return (int) $this->db->table('orders')
            ->where('status', 'completed')
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
     * Daily sales for last 7 days (orders + KTV sessions). Fills missing days with 0.
     */
    public function getDailySalesLast7Days(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $days[$date] = ['label' => $date, 'value' => 0];
        }

        $orderRows = $this->db->table('orders')
            ->select('DATE(created_at) AS dt, COALESCE(SUM(total), 0) AS value')
            ->where('status', 'completed')
            ->where('created_at >=', date('Y-m-d 00:00:00', strtotime('-6 days')))
            ->where('created_at <=', date('Y-m-d 23:59:59'))
            ->groupBy('DATE(created_at)')
            ->get()->getResultArray();

        foreach ($orderRows as $r) {
            if (isset($days[$r['dt']])) {
                $days[$r['dt']]['value'] += (float) $r['value'];
            }
        }

        $ktvRows = $this->db->table('ktv_sessions')
            ->select('DATE(end_time) AS dt, COALESCE(SUM(total_amount), 0) AS value')
            ->where('status', 'ended')
            ->where('end_time >=', date('Y-m-d 00:00:00', strtotime('-6 days')))
            ->where('end_time <=', date('Y-m-d 23:59:59'))
            ->groupBy('DATE(end_time)')
            ->get()->getResultArray();

        foreach ($ktvRows as $r) {
            if (isset($days[$r['dt']])) {
                $days[$r['dt']]['value'] += (float) $r['value'];
            }
        }

        return array_values($days);
    }

    /**
     * Monthly sales for last 12 months (orders + KTV sessions). Fills missing months with 0.
     */
    public function getMonthlySalesLast12Months(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime("first day of -{$i} months"));
            $months[$ym] = ['label' => $ym, 'value' => 0];
        }

        $orderRows = $this->db->table('orders')
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(total), 0) AS value")
            ->where('status', 'completed')
            ->where('created_at >=', date('Y-m-01 00:00:00', strtotime('first day of -11 months')))
            ->groupBy("DATE_FORMAT(created_at, '%Y-%m')")
            ->get()->getResultArray();

        foreach ($orderRows as $r) {
            if (isset($months[$r['ym']])) {
                $months[$r['ym']]['value'] += (float) $r['value'];
            }
        }

        $ktvRows = $this->db->table('ktv_sessions')
            ->select("DATE_FORMAT(end_time, '%Y-%m') AS ym, COALESCE(SUM(total_amount), 0) AS value")
            ->where('status', 'ended')
            ->where('end_time >=', date('Y-m-01 00:00:00', strtotime('first day of -11 months')))
            ->groupBy("DATE_FORMAT(end_time, '%Y-%m')")
            ->get()->getResultArray();

        foreach ($ktvRows as $r) {
            if (isset($months[$r['ym']])) {
                $months[$r['ym']]['value'] += (float) $r['value'];
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
            ->select('COALESCE(p.name, \'Unknown\') AS name, MAX(p.image) AS image, MAX(p.price) AS price, SUM(oi.qty) AS total_qty, COALESCE(SUM(oi.subtotal), 0) AS total_amount')
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
