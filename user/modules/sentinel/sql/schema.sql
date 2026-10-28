CREATE TABLE IF NOT EXISTS `sentinel` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip_address` VARCHAR(45) NOT NULL,
    `threat_category` VARCHAR(64) NOT NULL,
    `request_url` VARCHAR(2048) NULL,
    `domain` VARCHAR(255) NULL,
    `status` VARCHAR(32) NOT NULL DEFAULT 'blocked',
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `sentinel_ip_category_unique` (`ip_address`, `threat_category`),
    KEY `sentinel_status_index` (`status`),
    KEY `sentinel_timestamp_index` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sentinel_config` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(64) NOT NULL,
    `setting_value` TEXT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `sentinel_config_setting_key_unique` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sentinel_schema (
    id TINYINT UNSIGNED NOT NULL,
    schema_version VARCHAR(64) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO sentinel_schema (id, schema_version)
VALUES (1, '1.9.0')
ON DUPLICATE KEY UPDATE schema_version = VALUES(schema_version);
