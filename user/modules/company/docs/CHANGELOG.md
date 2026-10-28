# Company Module Changelog

## v1.2.0 — 2026-09-05

### Changed

- Refocused the Company module on company-managed public content.
- Removed obsolete and redundant Strategic and Contact functionality.
- Removed Security configuration from `company.json`.
- Removed the conflicting public `view()` controller action.
- Added the `page()` action for JSON-backed public company content.
- Limited JSON-backed public content to:
  - Careers
  - Acknowledgments
- Updated the public document view to consume controller-provided title and content directly.
- Simplified the Company Admin interface around explicitly supported content.
- Added explicit Admin action handling for:
  - `save_page`
  - `save_security`
- Added direct editing of the canonical public `/.well-known/security.txt` file.
- `security.txt` is now read from and written directly to:
  `public/.well-known/security.txt`
- Removed duplicate `security.txt` data storage from `company.json`.
- Added fixed-path `security.txt` read/write operations to `company_model`.
- Preserved JSON-backed storage for Careers and Acknowledgments.
- No database storage is used by the Company module.
- State-changing Admin operations require POST and CSRF validation.

### Public Content

The Company module manages:

- `/company/page/careers`
- `/company/page/acknowledgments`

The canonical security contact document remains publicly available at:

- `/.well-known/security.txt`

### Administration

Company-managed content is maintained through:

- `/admin/company`

The Admin interface provides editing for:

- Careers
- Acknowledgments
- `security.txt`

### Storage

- Careers and Acknowledgments:
  `user/modules/company/data/company.json`
- Security contact document:
  `public/.well-known/security.txt`