# Security

Security is a Chaos MVC user module for publishing site-owned cryptographic identity and observable security capabilities.

## Public information

- PGP public key
- RSA public key
- Responsible-disclosure `security.txt` link
- Sentinel monitoring, only when the installed perimeter integration is detected

Keys belong to each site installation and are stored in the module-owned database table. They are not embedded in the module package or its signing metadata.

## Sentinel detection

Security mirrors the optional Sentinel boundary used by Chaos MVC bootstrap. It verifies that the real, unlinked file:

```text
/user/modules/sentinel/lib/sentinel_perimeter.php
```

is confined beneath the real Sentinel module directory. Security does not load Sentinel or call `sentinel_perimeter::inspect()`; bootstrap owns request inspection. The module only uses the verified presence of that integration to decide whether to publish the monitoring statement.

## Database lifecycle

```text
/admin/security
→ Install SQL
→ configure public keys
→ optional Delete Data
→ Core-owned Nuke
```

- Public requests never install or migrate SQL.
- Install SQL, Save Settings, and Delete Data require POST and CSRF.
- Delete Data removes settings while preserving `security_settings` and the installed module.
- Nuke posts to Core `/admin/uninstall`.

## Database ownership

```text
security_settings
```

No Chaos MVC Core files are modified.

**Secure the Core. Grow outwards.**
