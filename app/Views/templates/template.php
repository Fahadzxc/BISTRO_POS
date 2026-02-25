<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'KTV Bistro POS') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* ========== GLOBAL / SHARED ========== */
        :root {
            --pos-primary: #1a365d;
            --pos-secondary: #2c5282;
            --pos-accent: #ed8936;
            --pos-success: #38a169;
            --sidebar-width: 260px;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* ========== LOGIN LAYOUT ========== */
        body.layout-login {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a365d 0%, #2d3748 50%, #1a202c 100%);
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, var(--pos-primary), var(--pos-secondary));
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
        }
        .login-header .brand-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 0.75rem;
        }
        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.02em;
        }
        .login-header p {
            margin: 0.25rem 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .login-body {
            padding: 2rem 1.5rem;
        }
        .form-control, .form-control:focus {
            border-radius: 10px;
            padding: 0.65rem 1rem;
        }
        .form-control:focus {
            border-color: var(--pos-primary);
            box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.2);
        }
        .input-group .form-control {
            border-right: 0;
        }
        .input-group .btn-outline-secondary {
            border-radius: 0 10px 10px 0;
            border-color: #dee2e6;
            background: #f8f9fa;
        }
        .input-group .btn-outline-secondary:hover {
            background: #e9ecef;
            border-color: #dee2e6;
        }
        .btn-login {
            background: linear-gradient(135deg, var(--pos-primary), var(--pos-secondary));
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 10px;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, var(--pos-secondary), var(--pos-primary));
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 54, 93, 0.4);
        }
        .alert-danger-custom {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 10px;
        }
        .form-check-input:checked {
            background-color: var(--pos-primary);
            border-color: var(--pos-primary);
        }

        /* ========== DASHBOARD LAYOUT ========== */
        body.layout-dashboard {
            background: #f1f5f9;
            min-height: 100vh;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1a365d 0%, #2d3748 100%);
            color: white;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-brand h5 {
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar-brand .brand-icon {
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar-nav {
            padding: 1rem 0;
        }
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 0.65rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.15s;
        }
        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
        }
        .sidebar-nav .nav-link.active {
            background: rgba(237, 137, 54, 0.25);
            color: #ed8936;
            border-left: 3px solid #ed8936;
        }
        .sidebar-nav .nav-link i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        .top-navbar {
            background: white;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .top-navbar .nav-title {
            font-weight: 600;
            color: #1e293b;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .role-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            text-transform: uppercase;
            font-weight: 600;
        }
        .content-area {
            padding: 1.5rem;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            transition: box-shadow 0.2s;
        }
        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }
        .stat-card .stat-label {
            font-size: 0.85rem;
            color: #64748b;
        }
        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            background: white;
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s;
        }
        .quick-action-btn:hover {
            border-color: var(--pos-primary);
            color: var(--pos-primary);
            background: rgba(26, 54, 93, 0.04);
        }
        .quick-action-btn i {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 1rem;
        }
        @media (max-width: 991px) {
            .sidebar {
                width: 72px;
                padding: 0;
            }
            .sidebar-brand h5 span,
            .sidebar-nav .nav-link span { display: none; }
            .main-wrapper { margin-left: 72px; }
        }

        /* ========== POS LAYOUT ========== */
        .layout-pos .main-wrapper { display: flex; flex-direction: column; }
        .layout-pos .pos-container {
            display: flex;
            height: calc(100vh - 60px);
            overflow: hidden;
        }
        .layout-pos .pos-products {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }
        .layout-pos .pos-cart {
            width: 380px;
            min-width: 340px;
            background: white;
            border-left: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }
        .pos-category-bar {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .pos-category-bar .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            min-height: 44px;
        }
        .pos-product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.75rem;
        }
        .pos-product-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            min-height: 160px;
        }
        .pos-product-card .btn { cursor: pointer; }
        .pos-product-card:hover {
            border-color: var(--pos-primary);
            box-shadow: 0 4px 12px rgba(26,54,93,0.15);
        }
        .pos-product-card .product-img {
            width: 64px;
            height: 64px;
            background: #f1f5f9;
            border-radius: 8px;
            margin: 0 auto 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #94a3b8;
        }
        .pos-product-card .product-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.25rem;
            line-height: 1.2;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .pos-product-card .product-price {
            font-size: 1rem;
            font-weight: 700;
            color: var(--pos-primary);
        }
        .pos-product-card .pos-add-btn {
            min-height: 36px;
            font-weight: 600;
        }
        .pos-cart-header {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .pos-cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }
        .pos-cart-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        .pos-cart-item .item-name { flex: 1; font-size: 0.9rem; font-weight: 500; }
        .pos-cart-item .item-qty {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .pos-cart-item .btn-qty {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pos-cart-item .item-subtotal { font-weight: 600; min-width: 70px; text-align: right; }
        .pos-cart-footer {
            padding: 1rem;
            border-top: 2px solid #e2e8f0;
        }
        .pos-cart-total { font-size: 1.25rem; font-weight: 700; color: var(--pos-primary); }
        .btn-checkout {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            min-height: 52px;
            background: linear-gradient(135deg, var(--pos-primary), var(--pos-secondary));
            border: none;
        }
        .btn-checkout:hover { background: linear-gradient(135deg, var(--pos-secondary), var(--pos-primary)); }
        .pos-empty-cart {
            text-align: center;
            padding: 2rem;
            color: #94a3b8;
        }
        @media (max-width: 991px) {
            .layout-pos .pos-container { flex-direction: column; height: auto; }
            .layout-pos .pos-cart { width: 100%; min-width: 100%; }
        }

        /* ========== KTV ROOMS ========== */
        .layout-ktv .content-area { padding: 1rem; }
        .ktv-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }
        .ktv-room-card {
            border-radius: 12px;
            padding: 1.25rem;
            border: 2px solid #e2e8f0;
            background: white;
            transition: all 0.2s;
        }
        .ktv-room-card.status-available { border-color: #22c55e; background: #f0fdf4; }
        .ktv-room-card.status-occupied { border-color: #ef4444; background: #fef2f2; }
        .ktv-room-card.status-cleaning { border-color: #f97316; background: #fff7ed; }
        .ktv-room-card .room-name { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; }
        .ktv-room-card .room-timer { font-size: 1.75rem; font-weight: 700; font-family: monospace; margin: 0.5rem 0; }
        .ktv-room-card .room-bill { font-size: 1.1rem; font-weight: 600; color: var(--pos-primary); margin-bottom: 1rem; }
        .ktv-room-card .room-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .ktv-room-card .room-actions .btn { min-height: 44px; }
    </style>
    <?= $styles ?? '' ?>
</head>
<body class="<?= esc($bodyClass ?? '') ?>">

<?= $content ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?= $scripts ?? '' ?>
</body>
</html>
