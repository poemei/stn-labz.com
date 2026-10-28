/* [AI:GPT-5.6 | 2026-09-01 05:00:00 UTC] */
CREATE TABLE IF NOT EXISTS `announcements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `title` VARCHAR(255) NOT NULL,
    `body` MEDIUMTEXT NOT NULL,
    `content_type` ENUM('markdown', 'html', 'text') NOT NULL DEFAULT 'markdown',
    `published` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/* [End AI:GPT-5.6] */
