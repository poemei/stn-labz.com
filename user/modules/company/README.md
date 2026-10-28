# Company 1.1.0

Extends the existing STN-LABZ Company module using the Legal module's JSON/editor pattern.

## Public and admin URLs

- `/company/view/careers`
- `/company/view/acknowledgments`
- `/company/security` (generated UTF-8 security.txt)
- `/admin/company` (level 7 and higher; POST plus Core CSRF required for saves)
- Existing `/company`, `/company/strategic`, and `/company/contact` behavior remains.

The public `view()` action accepts exactly one allowlisted route parameter. Its signature
is compatible with the base controller. Internal rendering calls `parent::view()` so
URL input cannot select an admin template. Public actions are declared in module.json.

## Storage

`data/company.json` contains careers, acknowledgments, and security information.
The PHP process needs read/write permission on this file. Reads acquire shared locks;
each save holds an exclusive lock across the full read-modify-write operation. Saves
preserve unrelated sections. Invalid or missing JSON raises an error instead of silently
replacing data. Back up this file before deployment or module updates. Writes are locked
but not crash-atomic; a process/disk failure during a write may require restoring backup.

`company_model` deliberately does not invoke the database-backed base constructor.
The new pages and editor do not open a database connection. The pre-existing contact
model still uses its existing database and email flow; the surrounding site may also
use a database. No SQL or Core files were changed for this addition.

Page content is trusted administrator-authored HTML, matching Legal. Titles and editor
values are escaped. Validation failures retain submitted input. Security fields reject
control characters, validate link/contact formats, and require a future Expires value.
The initial Expires value is March 1, 2027; review and renew it through Admin. Expired
or invalid security information returns HTTP 503 from the generated endpoint.

Existing security links were copied from the site's security.txt, not independently
verified. Contact is now a plain mailto URI. Engineering remains an editable custom field.
The format reference is https://www.rfc-editor.org/rfc/rfc9116.html.

## Canonical URL integration

The local `public/.htaccess` now maps `/.well-known/security.txt` to the Company
controller. Admin changes therefore supply both URLs when this configuration is
deployed under Apache. The old static file is retained as a rollback fallback.

In `public/.htaccess`, immediately after `RewriteEngine On` and BEFORE the existing-file
checks, the following rule is installed:

```apache
# Serve the canonical security.txt from Company JSON, even when the old file exists.
RewriteRule ^\.well-known/security\.txt$ index.php?url=company/security [END]
```

This uses the site's existing Apache front controller and requires Apache 2.4.
It intentionally discards incoming query parameters. Do not append QSA, which could
allow another `url` parameter to compete with the fixed destination. No Core router
change is needed. An equivalent host-level mapping is needed on other web servers.
After activation, verify the canonical URL returns 200, `text/plain; charset=utf-8`,
and the current editor values. Revert this one rule to restore the static file.

## Validation

PHP syntax checks passed for all module PHP files. An isolated fixture using the site's
actual controller, model, and router verified public document dispatch, security output,
admin form rendering, field validation, CSRF rejection, preservation of unrelated JSON
sections, invalid-JSON protection, and rejection of public admin-template access.
This is not a claim of live deployment or production web-server verification.
