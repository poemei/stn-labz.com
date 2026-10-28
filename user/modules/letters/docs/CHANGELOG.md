# Letters Changelog

## 1.1.1 - 2026-08-31

- Restored explicit Admin `Install SQL` and `Update SQL` lifecycle actions for module-owned database setup and schema updates.
- Database lifecycle actions require authenticated Admin POST requests with CSRF validation.
- `Update SQL` applies the packaged legacy-to-1.1.1 migration and verifies the schema reaches the ready state before normal module administration resumes.

- Removed module-local schema installation and update actions.
- Confined database updates to the signed, exact-version Core migration lifecycle.
- Added direct migrations from legacy 1.0.0 and 1.0.1 installations to 1.1.1.
- Added the documented `views/admin/index.php` entry point while retaining Module Builder compatibility.
- Added signing metadata required by Core update discovery and Module Builder validation.

## 1.1.0 - 2026-08-31

- Reworked Letters as a standalone Chaos MVC module.
- Added a public name-and-email opt-in form with explicit consent capture.
- Added token-based subscriber unsubscribe confirmation.
- Added module-owned subscriber storage and removed the dependency on Core accounts.
- Added Admin subscriber management, broadcast composition, and delivery history.
- Initially added module-local database lifecycle actions; superseded by 1.1.1.
- Added migrations from 1.0.0 and 1.0.1 that preserve the existing letter archive.
- Declared `letters` and `letters_subscribers` as module-owned tables for Core-controlled Nuke.

<!-- [AI:GPT-5.6 | 2026-09-01 04:00:00 UTC] -->
<!-- [End AI:GPT-5.6] -->
