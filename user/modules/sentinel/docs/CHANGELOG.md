# Changelog

## 1.9.1 — Evidence-bounded HTTP denial telemetry

- New locally blocked pattern matches include `details.response_status: 403` when Sentinel takes its explicit HTTP 403 termination path.
- Requests passed through to the application do not receive fabricated HTTP response codes. Existing historical Threat API records are unchanged.
- The Threat API already persists and returns submitted `details` fields; no API schema mutation required.



## 1.9.0 — ChAoS Module and Data Lifecycles

- Added deterministic SQL lifecycle states: missing, update, current, invalid.
- Added Admin-controlled Install SQL and Update SQL actions; exact legacy-to-1.9.0 migration creates a config backup, consolidates duplicate keys, and adds a unique setting-key index.
- Added sentinel_schema marker, fresh-install version state, and migration metadata.
- Separated explicit Delete Data and Data Reset from stored configuration and schema. Both clear mutable local Sentinel event records; there are no packaged canonical threat reference records.
- Perimeter enforcement remains available while a compatible SQL migration is pending. No migrations run during visitor requests or API synchronization.
- Existing databases are not automatically migrated. A complete database backup is required before choosing Update SQL.



## 1.8.7 — Stable configuration rows

- Stop inserting duplicate config rows on a legacy `id`-primary-key installation: existing settings are updated in place only when their value changes.
- Read the latest (`id` DESC) legacy value deterministically until migration.
- Added `sql/migrate_config_unique.sql` as an explicit one-time, backup-first legacy migration; it keeps the latest row per key and establishes a unique constraint on `setting_key`.
- Fresh install schema now has an `id` column and unique `setting_key`.
- Migration is never triggered automatically during sync or page requests. Existing production data is not modified by these repository commits.



## 1.8.6 — PHP 8.5 compatibility

- Removed deprecated `curl_close()` calls in STN-LABZ intelligence synchronization and threat-report submission.
- Kept transport and HTTP error handling intact; no change to raw observations, enforcement, plugin configuration, or API protocol.
- Host/core index-refresh error is a separate call site and is not fixed by this module patch.


## 1.8.5 — Native Sentinel plugin foundation

- Registered an explicitly allowlisted optional plugin interface with http:BL as first provider.
- Removed http:BL ENV dependency; credential uses the existing sentinel_config table and password-style Admin configuration.
- Kept request inspection, raw observation logging, enforcement and two-way API telemetry native and independent of plugins.
- Manual Admin-only lookup remains advisory and CSRF protected; absent credential disables only enrichment.


## 1.8.4 — Manual http:BL reputation inspection

- Added a CSRF-protected, level-9 Admin-only IPv4 lookup against Project Honey Pot http:BL.
- Historical implementation used an environment setting; superseded by the existing sentinel_config-backed plugin configuration in 1.8.5.
- Shows observable DNS classification and reputation fields without conflating absent responses with clean verdicts.
- No per-request DNS queries, no automatic blocking, no Core edits, no new database tables.


## 1.8.3 recovery (ChAoS MVC module compatibility)

- Restored STN-LABZ API intelligence ingestion and threat reporting from the 1.8.3 recovery baseline.
- Restored local IP blocking and URI-pattern inspection.
- Added explicit Admin SQL installation and Delete Data actions with POST + CSRF.
- Added current user-land module manifest and public entry point.
- Kept perimeter enforcement local-only; intelligence sync is initiated explicitly from Admin.
- Recovery is on a dedicated branch and is not a production deployment.
