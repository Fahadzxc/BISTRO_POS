<?= view('layouts/_sidebar', ['currentPage' => 'categories']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title"><?= $category ? 'Edit' : 'Add' ?> Category</span>
        <div class="user-info">
            <a href="<?= site_url('categories') ?>" class="btn btn-outline-secondary btn-sm">Back to list</a>
        </div>
    </header>

    <main class="content-area">
        <?php
        $errors = session()->getFlashdata('errors');
        if ($errors) {
            foreach ($errors as $e) {
                echo '<div class="alert alert-danger">' . esc($e) . '</div>';
            }
        }
        ?>
        <div class="card border-0 shadow-sm" style="max-width: 500px;">
            <div class="card-body">
                <form method="post" action="<?= esc($action) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" class="form-control" value="<?= $category ? esc($category['name']) : '' ?>" required maxlength="100">
                    </div>
                    <button type="submit" class="btn btn-primary"><?= $category ? 'Update' : 'Create' ?></button>
                    <a href="<?= site_url('categories') ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </main>
</div>
