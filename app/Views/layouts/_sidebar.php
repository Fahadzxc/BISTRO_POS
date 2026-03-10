<?php
$role = session()->get('role');
$current = $currentPage ?? 'dashboard';

// Role-based menu visibility
$canAccess = [
    'dashboard'   => in_array($role, ['admin', 'cashier', 'staff']),
    'pos'         => in_array($role, ['admin', 'cashier']),
    'orders'      => in_array($role, ['admin', 'cashier']),
    'products'    => in_array($role, ['admin', 'cashier']),
    'inventory'   => in_array($role, ['admin', 'cashier']),
    'ktv_rooms'   => in_array($role, ['admin', 'staff']),
    'reports'     => $role === 'admin',
    'users'       => $role === 'admin',
];
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <h5>
            <span class="brand-icon"><i class="bi bi-mic-fill"></i></span>
            <span>Bistro POS</span>
        </h5>
    </div>
    <nav class="sidebar-nav">
        <?php if ($canAccess['dashboard']): ?>
        <a href="<?= site_url('dashboard') ?>" class="nav-link <?= $current === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>
        <?php endif; ?>

        <?php if ($canAccess['pos']): ?>
        <a href="<?= site_url('pos') ?>" class="nav-link <?= $current === 'pos' ? 'active' : '' ?>">
            <i class="bi bi-cart-plus-fill"></i>
            <span>POS</span>
        </a>
        <?php endif; ?>

        <?php if ($canAccess['orders']): ?>
        <a href="<?= site_url('orders') ?>" class="nav-link <?= $current === 'orders' ? 'active' : '' ?>">
            <i class="bi bi-receipt"></i>
            <span>Orders</span>
        </a>
        <?php endif; ?>

        <?php if ($canAccess['products']): ?>
        <a href="<?= site_url('products') ?>" class="nav-link <?= $current === 'products' ? 'active' : '' ?>">
            <i class="bi bi-box-seam"></i>
            <span>Products</span>
        </a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
        <a href="<?= site_url('categories') ?>" class="nav-link <?= $current === 'categories' ? 'active' : '' ?>">
            <i class="bi bi-tags"></i>
            <span>Categories</span>
        </a>
        <?php endif; ?>

        <?php if ($canAccess['inventory']): ?>
        <a href="<?= site_url('inventory') ?>" class="nav-link <?= $current === 'inventory' ? 'active' : '' ?>">
            <i class="bi bi-archive"></i>
            <span>Inventory</span>
        </a>
        <?php endif; ?>

        <?php if ($canAccess['ktv_rooms']): ?>
        <a href="<?= site_url('ktv-rooms') ?>" class="nav-link <?= $current === 'ktv_rooms' ? 'active' : '' ?>">
            <i class="bi bi-door-open"></i>
            <span>KTV Rooms</span>
        </a>
        <?php endif; ?>

        <?php if ($canAccess['reports']): ?>
        <a href="<?= site_url('reports/sales') ?>" class="nav-link <?= $current === 'reports' ? 'active' : '' ?>">
            <i class="bi bi-graph-up"></i>
            <span>Reports</span>
        </a>
        <?php endif; ?>

        <?php if ($canAccess['users']): ?>
        <a href="<?= site_url('users') ?>" class="nav-link <?= $current === 'users' ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i>
            <span>Users</span>
        </a>
        <?php endif; ?>

        <a href="<?= site_url('logout') ?>" class="nav-link text-danger">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
    </nav>
</aside>
