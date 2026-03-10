<?= view('layouts/_sidebar', ['currentPage' => 'products']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Products</span>
        <div class="user-info">
            <span class="text-muted small"><?= esc(session()->get('name')) ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-left me-1"></i>Logout</a>
        </div>
    </header>

    <main class="content-area">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= esc(session()->getFlashdata('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= esc(session()->getFlashdata('error')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Product List</h4>
            <a href="<?= site_url('products/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Product</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px">Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th width="140">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No products yet.</td></tr>
                            <?php else: ?>
                                <?php
                                $baseUrl = base_url();
                                foreach ($products as $p):
                                    $imgUrl = ! empty($p['image']) ? $baseUrl . $p['image'] : '';
                                    $status = $p['stock_status'] ?? 'in_stock';
                                    $badge = $status === 'in_stock' ? 'bg-success' : ($status === 'low_stock' ? 'bg-warning text-dark' : 'bg-danger');
                                    $label = $status === 'in_stock' ? 'In Stock' : ($status === 'low_stock' ? 'Low Stock' : 'Out of Stock');
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($imgUrl): ?>
                                            <img src="<?= esc($imgUrl) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px">
                                        <?php else: ?>
                                            <div style="width:48px;height:48px;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center"><i class="bi bi-box-seam text-muted"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($p['name']) ?></td>
                                    <td><?= esc($p['category_name'] ?? '-') ?></td>
                                    <td>₱<?= number_format((float) ($p['price'] ?? 0), 2) ?></td>
                                    <td><?= (int) ($p['stock'] ?? 0) ?></td>
                                    <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
                                    <td>
                                        <a href="<?= site_url('products/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <a href="<?= site_url('products/delete/' . $p['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?');">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
