<?= view('layouts/_sidebar', ['currentPage' => 'products']) ?>

<div class="main-wrapper">
    <header class="top-navbar d-flex justify-content-between align-items-center">
        <span class="nav-title"><?= $product ? 'Edit' : 'Add' ?> Product</span>
        <div class="user-info">
            <a href="<?= site_url('products') ?>" class="btn btn-outline-secondary btn-sm">Back to list</a>
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
        <div class="card border-0 shadow-sm" style="max-width: 560px;">
            <div class="card-body">
                <form method="post" action="<?= esc($action) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control" value="<?= $product ? esc($product['name']) : '' ?>" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= ($product && (int)($product['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (₱)</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= $product ? esc($product['price']) : '0' ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" min="0" value="<?= $product ? (int)($product['stock'] ?? 0) : '0' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minimum Stock</label>
                            <input type="number" name="min_stock" class="form-control" min="0" value="<?= $product ? (int)($product['min_stock'] ?? 0) : '0' ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp" id="productImage">
                        <small class="text-muted">Optional. JPG, PNG, GIF, WebP. Max 2MB.</small>
                        <?php if ($product && ! empty($product['image'])): ?>
                            <div class="mt-2">
                                <p class="small mb-1">Current image:</p>
                                <img src="<?= esc(base_url($product['image'])) ?>" alt="" id="currentImage" style="max-width:120px;max-height:120px;object-fit:cover;border-radius:8px">
                            </div>
                        <?php endif; ?>
                        <div class="mt-2" id="imagePreview" style="display:none">
                            <p class="small mb-1">Preview:</p>
                            <img id="previewImg" src="" alt="" style="max-width:120px;max-height:120px;object-fit:cover;border-radius:8px">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= $product ? 'Update' : 'Create' ?></button>
                    <a href="<?= site_url('products') ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </main>
</div>
<script>
document.getElementById('productImage').addEventListener('change', function(e) {
    var f = e.target.files[0];
    var box = document.getElementById('imagePreview');
    var img = document.getElementById('previewImg');
    if (f && f.type.match('image.*')) {
        var r = new FileReader();
        r.onload = function() { img.src = r.result; box.style.display = 'block'; };
        r.readAsDataURL(f);
    } else {
        box.style.display = 'none';
    }
});
</script>
