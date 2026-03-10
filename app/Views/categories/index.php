<?= view('layouts/_sidebar', ['currentPage' => 'categories']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Categories</span>
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
            <h4 class="mb-0">Category List</h4>
            <a href="<?= site_url('categories/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Category</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th width="140">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr><td colspan="3" class="text-center text-muted py-4">No categories yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($categories as $c): ?>
                                <tr>
                                    <td><?= (int) $c['id'] ?></td>
                                    <td><?= esc($c['name']) ?></td>
                                    <td>
                                        <a href="<?= site_url('categories/edit/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <a href="<?= site_url('categories/delete/' . $c['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?');">Delete</a>
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
