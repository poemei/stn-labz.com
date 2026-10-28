ALTER TABLE `announcements`
    ADD COLUMN `archived_at` DATETIME NULL DEFAULT NULL AFTER `published`,
    ADD KEY `idx_archived_at` (`archived_at`);
