ALTER TABLE `sentinel`
    ADD COLUMN `domain` VARCHAR(255) NULL AFTER `request_url`;
