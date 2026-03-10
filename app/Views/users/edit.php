<?= view('layouts/_sidebar', ['currentPage' => 'users']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title">Edit User</span>
        <div class="user-info">
            <a href="<?= site_url('users') ?>" class="btn btn-outline-secondary btn-sm">Back to Users</a>
        </div>
    </header>

    <main class="content-area">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php
        $errors = session()->getFlashdata('errors');
        if ($errors): foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= esc($e) ?></div>
        <?php endforeach; endif; ?>

        <div class="card border-0 shadow-sm" style="max-width: 500px;">
            <div class="card-body">
                <form method="post" action="<?= site_url('users/update/' . $user['user_id']) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="<?= esc(old('name', $user['name'] ?? '')) ?>" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= esc(old('email', $user['email'] ?? '')) ?>" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= esc($r) ?>" <?= (old('role', $user['role'] ?? '') === $r) ? 'selected' : '' ?>><?= esc(ucfirst($r)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" <?= (old('status', $user['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (old('status', $user['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="<?= site_url('users') ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </main>
</div>
