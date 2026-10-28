# Announcements

The Public announcements module for the ChAoS MVC

## Version

1.3.6

## Module Location

```text
/user/modules/announcements
```

## Module Files

```text
/user/modules/announcements/controllers/announcements.php
/user/modules/announcements/models/announcements_model.php
/user/modules/announcements/views/admin/announcements.php
/user/modules/announcements/views/admin/index.php
/user/modules/announcements/views/index.php
/user/modules/announcements/sql/schema.sql
/user/modules/announcements/module.json
```

## Administration

Open `/admin/announcements` after installing the module.

The Admin screen uses theme-provided surfaces and controls. It does not force
dark backgrounds, light text, or module-specific colors. Every row provides a
visible Edit button and a CSRF-protected Delete button.

All Admin writes use explicit POST actions with CSRF verification. The
module-owned `announcements` table is declared for Core-controlled Nuke.
Fresh installations include `sql/schema.sql` for the database installation
process selected by the domain operator.

The Admin controller now implements the complete database lifecycle. It detects
whether the table exists, installs or idempotently checks the schema, deletes
module-owned rows without removing the module, and delegates complete removal
to Core's Nuke action. Verified future migration files belong under
`sql/patches/`.

The module manifest uses the canonical `algorithm`, `fingerprint`, `sha256`,
`key_id`, and `public_key` signing shape.

## Content formats

Admin explicitly selects Markdown, HTML, or plain text for each announcement.
New announcements default to Markdown. The frontend follows only the stored
format: Markdown is rendered by the Core controller's
`render_md::markdown()` service, HTML is emitted unchanged,
and plain text is escaped while preserving line breaks. Unknown stored values
also use the safe plain-text behavior.

Existing installations report an outdated database state until the administrator
runs **Update Announcements database**. The `1.3.5-to-1.3.6` patch adds
`content_type` as `text`, matching the module's previous escaped rendering. No
schema installation or migration runs while public or Admin pages are viewed.
