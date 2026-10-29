-- STN-Labz Certifications Module v1.0.0
-- Module-owned table; explicit Admin Install SQL only.
CREATE TABLE IF NOT EXISTS certifications_records (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 certificate_id VARCHAR(64) NOT NULL,
 account_id INT(11) NOT NULL,
 system_slug VARCHAR(63) NOT NULL,
 standard_reference VARCHAR(255) NOT NULL,
 evidence_reference VARCHAR(255) NOT NULL,
 issued_by INT(11) NOT NULL,
 issued_on DATE NOT NULL,
 expires_on DATE DEFAULT NULL,
 status VARCHAR(16) NOT NULL DEFAULT 'active',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY certifications_certificate_id (certificate_id),
 KEY certifications_holder (account_id),
 KEY certifications_system (system_slug, status),
 CONSTRAINT certifications_account_fk FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE RESTRICT,
 CONSTRAINT certifications_issuer_fk FOREIGN KEY (issued_by) REFERENCES accounts(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
