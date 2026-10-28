# Letters

Letters is a standalone opt-in newsletter and distribution module for Chaos
MVC. It owns its subscriber list and does not read recipients from the Core
`accounts` table.

## Public routes

- `/letters` — name, email, and explicit-consent subscription form
- `/letters/unsubscribe/{token}` — CSRF-confirmed unsubscribe flow

## Administration

`/admin/letters` provides subscriber management, newsletter broadcasting
through the built-in mailer, letter history, and successful-recipient counts.
Every state change uses POST and CSRF. Database updates are performed only by
the signed Core module-update lifecycle.

## Database ownership

- `letters` — newsletter history
- `letters_subscribers` — module-owned opt-in distribution list

Fresh installs include `sql/schema.sql` for the database installation process
explicitly selected by the domain operator. Exact signed migrations from 1.0.0
and 1.0.1 preserve existing letters while adding recipient counts and the
subscriber table. Both tables are declared for Core-controlled Nuke.

## Version

1.1.1
