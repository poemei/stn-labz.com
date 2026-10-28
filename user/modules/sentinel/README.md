# Sentinel for ChAoS MVC

**Module version:** 1.9.0  
**Module path:** `/user/modules/sentinel/`

Sentinel is a user-land ChAoS MVC security module with local request inspection, IP blocking, malicious URI pattern detection, and STN-LABZ Threat API intelligence exchange.

## Module files

- `controllers/sentinel.php` — public and Admin entry points, explicit POST + CSRF actions.
- `models/sentinel_model.php` — local data, SQL installation, API ingestion and reporting.
- `lib/sentinel_perimeter.php` — local-only request enforcement at an approved pre-dispatch hook.
- `views/index.php`, `views/admin/index.php` — user-land views.
- `sql/schema.sql` — explicit Admin-installed schema.
- `module.json` — module manifest.

## Operation

The module does not install schema on a public request. Open `/admin/sentinel` as an authorized administrator and select **Install SQL** when needed.

Admin provides configuration, manual IP blocking, intelligence synchronization, event history and **Delete Data**. All mutations require POST and CSRF.

The STN-LABZ API key is configured in Admin. Leaving the password field blank preserves the saved key. Intelligence synchronization is an explicit Admin action, not network I/O on every visitor request.

Local perimeter inspection must be invoked only from a supported ChAoS MVC pre-dispatch integration point; installing the module does not automatically modify Core or install a hook.

## Optional enhancement plugins (v1.8.5)

Modules extend; plugins enhance. Sentinel's raw observations, local enforcement, event preservation, and bidirectional STN-LABZ API exchange remain native responsibilities, not plugin operations. The internal `plugins/sentinel_plugins.php` registry explicitly allowlists plugin identifiers and source files. It currently registers the `httpbl` provider at `plugins/httpbl/httpbl.php`. Plugin calls are *only* made on explicitly authorized Admin actions, never from the request perimeter or telemetry ingest path. Missing plugin files or credentials can affect the optional lookup, not Sentinel itself. The registry is intentionally an allowlist, not arbitrary PHP execution or auto-discovery.

The optional http:BL credential is configured via the existing Sentinel Admin settings and stored under `httpbl_key` in the existing `sentinel_config` table. **No ENV configuration is used.** A blank key preserves the prior key. Without a configured key, Sentinel keeps enforcing, logging and synchronizing normally; the manual http:BL lookup alone is unavailable. Never commit real credentials.

The protected level-9 Admin screen provides manual, CSRF-protected IPv4 http:BL lookup. DNS A answers show last-activity days, threat score and type flags, while absent answers and lookup failures are explicitly inconclusive. Reputation does not itself cause blocks, alter raw observations, or prove an attack; a request's REMOTE_ADDR may be an intermediary such as a CDN.


## Module and Data Lifecycles (v1.9.0)

Sentinel follows the ChAoS MVC Example lifecycle states: `missing`, `update`, `current`, and `invalid`. The authorized Admin screen exposes **Install SQL** only in missing state and **Update SQL** only for the supported legacy-to-1.9.0 schema migration. An invalid state blocks Admin changes for review.

**Back up your entire database before Update SQL.** The explicit migration in `sql/patches/legacy-to-1.9.0.sql` copies legacy settings to `sentinel_config_pre_dedupe_backup`, preserves the highest-ID value of each setting, removes duplicate rows, enforces one row per setting, and marks schema version 1.9.0 only when successful. The saved credentials and other configuration values are retained. This update is not part of intelligence synchronization or perimeter requests. A pre-existing backup table blocks automatic retry pending manual review.

**Delete Data** and **Data Reset** are explicit CSRF-protected Admin actions. Both remove mutable local threat event records, but retain configuration, keys, schema, and migration history. Sentinel currently ships no canonical threat reference rows, so Data Reset does not repopulate events. Use these destructive actions only when intentionally disposing of local threat evidence; schema upgrades do not erase it.

Perimeter enforcement remains active against the existing compatible schema even while a migration is pending.
