-- KTV Room Management Tables
-- Run migrations instead: php spark migrate

-- KTV Rooms
CREATE TABLE IF NOT EXISTS `ktv_rooms` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_name` VARCHAR(50) NOT NULL,
    `hourly_rate` DECIMAL(10,2) NOT NULL,
    `status` ENUM('available','occupied','cleaning') DEFAULT 'available',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- KTV Sessions
CREATE TABLE IF NOT EXISTS `ktv_sessions` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id` INT(11) UNSIGNED NOT NULL,
    `start_time` DATETIME NULL,
    `end_time` DATETIME NULL,
    `paused_at` DATETIME NULL,
    `total_paused_seconds` INT(11) UNSIGNED DEFAULT 0,
    `total_minutes` INT(11) UNSIGNED NULL,
    `total_amount` DECIMAL(12,2) NULL,
    `cashier_id` INT(11) UNSIGNED NOT NULL,
    `status` ENUM('active','paused','ended') DEFAULT 'active',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`room_id`) REFERENCES `ktv_rooms`(`id`),
    FOREIGN KEY (`cashier_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
