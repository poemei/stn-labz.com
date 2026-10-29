-- Explicit, additive installation. No existing account or grant is changed.
CREATE TABLE IF NOT EXISTS organization_positions (
    account_id int(11) NOT NULL PRIMARY KEY,
    tier tinyint unsigned NOT NULL,
    position_name varchar(150) NOT NULL,
    changed_by int(11) NOT NULL,
    updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS organization_credentials (
    id bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    account_id int(11) NOT NULL,
    kind varchar(20) NOT NULL,
    qualification_tier tinyint unsigned DEFAULT NULL,
    system_slug varchar(63) NOT NULL,
    standard_reference varchar(255) NOT NULL,
    evidence_reference varchar(255) NOT NULL,
    status varchar(16) NOT NULL DEFAULT 'active',
    expires_on date DEFAULT NULL,
    changed_by int(11) NOT NULL,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY organization_credentials_account (account_id),
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS organization_missions (
    id bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    account_id int(11) NOT NULL,
    module_slug varchar(63) NOT NULL,
    system_slug varchar(63) NOT NULL,
    mission_reference varchar(255) NOT NULL,
    active tinyint(1) NOT NULL DEFAULT 1,
    expires_on date DEFAULT NULL,
    changed_by int(11) NOT NULL,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY organization_missions_account (account_id, module_slug),
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
