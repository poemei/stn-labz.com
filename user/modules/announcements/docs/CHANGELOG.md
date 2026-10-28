# Announcements Module Changes

## 1.3.6 - 2026-09-13

- Added the module-owned `announcements_content` rendering service and made all
  public retrieval APIs attach `rendered_body` while preserving raw `body`.
- Updated the Poemei homepage integration to consume `rendered_body` and obtain
  the Announcements model through Core's user-module model boundary. The homepage
  performs no Markdown loading, parsing, or content-type dispatch.
- Validated the shared public rendering path and homepage contract with 22
  passing checks; all module and changed homepage PHP files passed syntax checks,
  the manifest was complete, and Git whitespace validation passed.
- Corrected Markdown rendering to use the Core-initialized controller renderer
  through `$this->render_md->markdown()`; removed the nonexistent global
  `markdown_md()` call.
- Added explicit `markdown`, `html`, and `text` content formats to Admin create
  and edit operations; new announcements default to Markdown.
- Made the stored `content_type` authoritative: Markdown uses Core
  `render_md::markdown()`, HTML remains unchanged, and text or unknown values are
  escaped safely without content inspection.
- Added explicit database state detection and the normal Update SQL migration.
  Existing announcements migrate to `text`, preserving their previous escaped
  plain-text rendering; fresh installations default to `markdown`.
- Added migration metadata and 22 executable regression checks. PHP syntax,
  manifest JSON, dispatch behavior, safe fallback, lifecycle guards, and schema
  defaults passed validation.
- Re-ran the module suite after the renderer correction: all module PHP files
  passed syntax checks and all 16 content-format checks passed, including direct
  observation that only Markdown invokes `render_md::markdown()`.

## 1.3.5 - 2026-09-04

- Added database state detection and explicit install, update/check, and delete-data lifecycle actions.
- Kept announcement CRUD, Edit, and Delete behavior intact behind the current-state gate.
- Added the required `sql/patches/.gitkeep` migration location and declared it in `module.json`.
- Added Core Nuke delegation without duplicating Core uninstall behavior.
- Made the public view use theme rendering with the established Core fallback.
- Changed local signing trust from the legacy `type` field to canonical `algorithm`.
- Brought the package into Module Builder validation compliance.

## 1.3.4 - 2026-09-02

- Restored the missing `views/admin/index.php` Admin interface.
- Added visible, functional Edit and Delete controls for every announcement.
- Added create and edit forms with published/draft control.
- Kept all create, update, and delete operations behind administrator authorization and CSRF verification.
- Supported both `/admin/announcements/edit/{id}` and `/admin/announcements?edit={id}` edit selection.
- Corrected the controller to resolve the declared `admin/announcements` module view.
- Replaced the legacy signing algorithm field with the canonical signing metadata shape.
- Removed hard-coded dark and light presentation choices from the delivered Admin interface.
- Corrected the version to reflect the delivered Admin behavior.

## 1.3.3 - 2026-08-31

- Restored the established `get_latest_single()`, `get_latest()`, `get_active()`, `get_all()`, and `get_by_id()` read APIs used by existing domain modules.
- Preserved the compliant internal CRUD implementation without breaking installed consumers such as Home.
- Restored Admin edit routing for both supported Core parameter shapes.
- Restored a clearly visible Edit action in the Admin announcement list.
- Removed remaining surface and color assumptions so the Admin view follows the active theme.

## 1.3.2 - 2026-08-31

- Added explicit allowlisted Admin actions for create, update, and delete.
- Standardized administrator authorization and CSRF enforcement.
- Confined CRUD to fixed, parameterized module-model operations.
- Restored the fresh-install schema and current module view layout.
- Made the Admin presentation theme-agnostic.
- Added complete signing metadata for Core and Module Builder validation.

<!-- [AI:GPT-5.6 | 2026-09-01 05:00:00 UTC] -->
<!-- [End AI:GPT-5.6] -->
