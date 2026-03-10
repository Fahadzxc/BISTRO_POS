-- Report queries used by DashboardModel and ReportModel (CI4 Query Builder equivalent)
-- Tables: orders, order_items, products, ktv_rooms, ktv_sessions, stock_logs

-- Total sales today
-- SELECT COALESCE(SUM(total), 0) AS total FROM orders WHERE DATE(created_at) = CURDATE();

-- Total orders today
-- SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE();

-- Active KTV rooms (occupied)
-- SELECT COUNT(*) FROM ktv_rooms WHERE status = 'occupied';

-- Daily sales last 7 days
-- SELECT DATE(created_at) AS label, COALESCE(SUM(total), 0) AS value
-- FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
-- GROUP BY DATE(created_at) ORDER BY label ASC;

-- Monthly sales
-- SELECT DATE_FORMAT(created_at, '%Y-%m') AS label, COALESCE(SUM(total), 0) AS value
-- FROM orders GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY label ASC;

-- Top products by qty sold
-- SELECT p.name, SUM(oi.qty) AS total_qty, SUM(oi.subtotal) AS total_amount
-- FROM order_items oi JOIN products p ON p.id = oi.product_id
-- GROUP BY oi.product_id ORDER BY total_qty DESC LIMIT 10;

-- KTV usage by room (date range)
-- SELECT r.room_name, COUNT(s.id) AS total_sessions,
--   COALESCE(SUM(s.total_minutes), 0) AS total_minutes,
--   COALESCE(SUM(s.total_amount), 0) AS total_revenue
-- FROM ktv_sessions s JOIN ktv_rooms r ON r.id = s.room_id
-- WHERE s.status = 'ended' AND s.start_time >= ? AND s.start_time <= ?
-- GROUP BY s.room_id ORDER BY total_revenue DESC;

-- Sales summary (date range)
-- SELECT COALESCE(SUM(total), 0) AS total_amount, COUNT(*) AS total_orders
-- FROM orders WHERE created_at >= ? AND created_at <= ?;

-- Stock movement summary by action_type
-- SELECT action_type AS label, COUNT(*) AS movements, COALESCE(SUM(qty_change), 0) AS total_qty_change
-- FROM stock_logs WHERE created_at >= ? AND created_at <= ? GROUP BY action_type;
