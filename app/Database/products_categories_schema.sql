-- Products & Categories schema reference
-- Run migrations: php spark migrate

-- Categories (add created_at if not exists)
-- ALTER TABLE categories ADD COLUMN created_at DATETIME NULL AFTER name;

-- Products (min_stock added by inventory migration; created_at by AddCreatedAt migration)
-- id, name, category_id, price, stock, min_stock, image, created_at

-- Image uploads: store path in products.image e.g. "uploads/products/filename.jpg"
-- Upload directory: public/uploads/products (or FCPATH . 'uploads/products')
