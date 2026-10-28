-- One-time migration for an existing legacy sentinel_config table.
-- Execute only after deploying fixed Sentinel code and taking a full DB backup.
-- Requires legacy columns: id, setting_key, setting_value.
-- Do not rerun without checking the backup-table name and existing index.
-- Keeps the row with the highest id (the effective value read by the model).
-- Does not modify any threat observation/event table.
CREATE TABLE sentinel_config_pre_dedupe_backup LIKE sentinel_config;
INSERT INTO sentinel_config_pre_dedupe_backup
SELECT * FROM sentinel_config;

START TRANSACTION;
DELETE older
FROM sentinel_config AS older
INNER JOIN sentinel_config AS newer
  ON older.setting_key = newer.setting_key
 AND older.id < newer.id;
COMMIT;

-- Must succeed only when all duplicate keys were removed.
-- MariaDB DDL commits implicitly; backup table remains for recovery.
ALTER TABLE sentinel_config
  ADD UNIQUE KEY sentinel_config_setting_key_unique (setting_key);

-- Validate: every setting has exactly one row.
SELECT setting_key, COUNT(*) AS row_count
FROM sentinel_config
GROUP BY setting_key
ORDER BY setting_key;
