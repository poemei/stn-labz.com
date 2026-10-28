/* [AI:GPT-5.6 Sol | 2026-09-02 UTC] */
CREATE TABLE IF NOT EXISTS `security_settings` (
    `id` TINYINT UNSIGNED NOT NULL,
    `pgp_label` VARCHAR(160) NOT NULL DEFAULT '',
    `pgp_public_key` MEDIUMTEXT NOT NULL,
    `rsa_label` VARCHAR(160) NOT NULL DEFAULT '',
    `rsa_public_key` MEDIUMTEXT NOT NULL,
    `security_txt_url` VARCHAR(2048) NOT NULL DEFAULT '/.well-known/security.txt',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/* [End AI:GPT-5.6 Sol] */
