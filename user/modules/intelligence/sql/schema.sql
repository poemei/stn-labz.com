CREATE TABLE IF NOT EXISTS `intelligence_records` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `intelligence_id` VARCHAR(32) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `origin` VARCHAR(32) NOT NULL,
    `source` TEXT NULL,
    `status` VARCHAR(32) NOT NULL DEFAULT 'ACTIVE',
    `srt_id` VARCHAR(64) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_intelligence_id` (`intelligence_id`),
    KEY `idx_intelligence_status` (`status`),
    KEY `idx_intelligence_origin` (`origin`),
    KEY `idx_intelligence_srt_id` (`srt_id`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `intelligence_observations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `observation_id` VARCHAR(64) NOT NULL,
    `intelligence_id` VARCHAR(32) NOT NULL,
    `content` TEXT NOT NULL,
    `origin` VARCHAR(32) NOT NULL,
    `source` TEXT NULL,
    `observed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_observation_id` (`observation_id`),
    KEY `idx_observation_intelligence_id` (`intelligence_id`),
    KEY `idx_observation_origin` (`origin`),
    CONSTRAINT `fk_observation_intelligence`
        FOREIGN KEY (`intelligence_id`)
        REFERENCES `intelligence_records` (`intelligence_id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `intelligence_assignments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignment_id` VARCHAR(64) NOT NULL,
    `intelligence_id` VARCHAR(32) NOT NULL,
    `assignment_type` VARCHAR(32) NOT NULL,
    `status` VARCHAR(32) NOT NULL DEFAULT 'PENDING',
    `instructions` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `acknowledged_at` TIMESTAMP NULL DEFAULT NULL,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_assignment_id` (`assignment_id`),
    KEY `idx_assignment_intelligence_id` (`intelligence_id`),
    KEY `idx_assignment_status` (`status`),
    CONSTRAINT `fk_assignment_intelligence`
        FOREIGN KEY (`intelligence_id`)
        REFERENCES `intelligence_records` (`intelligence_id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;