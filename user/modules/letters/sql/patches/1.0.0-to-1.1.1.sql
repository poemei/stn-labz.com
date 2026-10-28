/* [AI:GPT-5.6 | 2026-09-01 04:00:00 UTC] */
ALTER TABLE `letters`
    ADD COLUMN `recipient_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `body`;

CREATE TABLE IF NOT EXISTS `letters_subscribers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(254) NOT NULL,
    `unsubscribe_token` CHAR(64) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'active',
    `consent_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `unsubscribed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `letters_subscribers_email_unique` (`email`),
    UNIQUE KEY `letters_subscribers_token_unique` (`unsubscribe_token`),
    KEY `letters_subscribers_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/* [End AI:GPT-5.6] */
