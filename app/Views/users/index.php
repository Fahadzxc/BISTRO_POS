<?= view('layouts/_sidebar', ['currentPage' => 'users']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Users</span>
        <div class="user-info">
            <span class="text-muted small"><?= esc(session()->get('name')) ?></span>
            <a href="<?= site_url('users/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i>Add User</a>
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
        <?php $errors = session()->getFlashdata('errors'); if ($errors): foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= esc($e) ?></div>
        <?php endforeach; endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $currentUserId = (int) session()->get('user_id'); ?>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No users yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= (int) $u['user_id'] ?></td>
                                    <td><?= esc($u['name']) ?></td>
                                    <td><?= esc($u['email']) ?></td>
                                    <td><span class="badge bg-secondary"><?= esc($u['role']) ?></span></td>
                                    <td>
                                        <?php $status = $u['status'] ?? 'active'; ?>
                                        <span class="badge <?= $status === 'active' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= esc($status) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($u['user_id'] == $currentUserId): ?>
                                            <span class="text-muted small">You (cannot edit self)</span>
                                        <?php else: ?>
                                            <a href="<?= site_url('users/edit/' . $u['user_id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <a href="<?= site_url('users/disable/' . $u['user_id']) ?>" class="btn btn-sm btn-outline-warning"><?= ($u['status'] ?? 'active') === 'active' ? 'Disable' : 'Enable' ?></a>
                                            <a href="<?= site_url('users/delete/' . $u['user_id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user? This cannot be undone.');">Delete</a>
                                        <?php endif; ?>
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
