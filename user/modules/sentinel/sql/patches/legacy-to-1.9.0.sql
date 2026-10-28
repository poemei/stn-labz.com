CREATE TABLE sentinel_config_pre_dedupe_backup LIKE sentinel_config;
INSERT INTO sentinel_config_pre_dedupe_backup SELECT * FROM sentinel_config;
DELETE old_row FROM sentinel_config AS old_row INNER JOIN sentinel_config AS new_row ON old_row.setting_key = new_row.setting_key AND old_row.id < new_row.id;
