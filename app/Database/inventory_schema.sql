-- Inventory: add min_stock to products and create stock_logs
-- Prefer running migration: php spark migrate

ALTER TABLE `products`
ADD COLUMN `min_stock` INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `stock`;

CREATE TABLE IF NOT EXISTS `stock_logs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) UNSIGNED NOT NULL,
    `qty_before` INT(11) NOT NULL,
    `qty_change` INT(11) NOT NULL,
    `qty_after` INT(11) NOT NULL,
    `action_type` VARCHAR(20) NOT NULL,
    `remarks` TEXT NULL,
    `created_at` DATETIME NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
