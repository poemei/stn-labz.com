/* Existing announcements previously rendered as escaped plain text. */
ALTER TABLE `announcements`
    ADD COLUMN `content_type` ENUM('markdown', 'html', 'text')
    NOT NULL DEFAULT 'text' AFTER `body`;
