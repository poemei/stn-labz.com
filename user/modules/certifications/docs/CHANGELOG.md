# Certifications Module Changelog

## 1.0.0 — 2026-10-29 — Staged, Operator testing required

- Initial account-linked certificate registry, random mixed-alphanumeric certificate IDs, Tier 9 management, explicit SQL install and status updates.
- Certificate registry is not yet connected to `organization_credentials` permission enforcement; authorization unaffected.
- No SQL installed or production account data changed by source commits.

## 1.0.1 — 2026-10-29 — System registry dropdown

- Module-owned registered system/product catalog and Tier 9 management (add, disable, enable).
- Issuance now uses a catalog dropdown; server rejects unknown/inactive system IDs.
- Added module-owned SQL update for existing installations; new installs include both tables. No data migration or existing certificate changes.
- Operator runtime and database testing pending; not yet certified.
