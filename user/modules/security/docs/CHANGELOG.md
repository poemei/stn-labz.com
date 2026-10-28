# Security Module Changelog

## 1.1.0 — 2026-09-02

### Added

- Explicit Install SQL lifecycle using POST and CSRF.
- Module-owned `security_settings` table.
- Administrator-managed PGP public key.
- Administrator-managed RSA public key.
- Configurable `security.txt` link.
- Public cryptographic identity presentation.
- Automatic, inert Sentinel perimeter integration detection matching Core bootstrap boundaries.
- Module-owned Delete Data operation that preserves schema and installation.
- Core-owned Nuke control.
- Theme-library header and footer rendering.

### Changed

- Public Security now reports unavailable while its required schema is missing.
- Replaced placeholder Admin and public views with operational interfaces.

## 1.0.0 — 2026-09-02

### Added

- Initial module scaffold.
