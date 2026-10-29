# Certifications Module v1.0.0

Separate from the existing public `certification` information module.

- Records certificate identifiers in format `certificat-0x-[A-Za-z0-9]{24}`.
- Links each issued certificate to an existing employee or AI account.
- Tier 9-only issue, suspend, revoke and reactivate controls.
- Certificate metadata includes system, evidence, issuing account, dates and status.
- Admin explicitly installs module-owned SQL via CSRF-protected POST.
- No automatic data installation or credential grant, no changes to core authorization, and no inference that a registry entry alone authorizes module use.
- Existing `organization_credentials` stays authoritative for current Mission Control permission decisions until a separately approved bridge integrates these records.
- Status history/audit enhancements and SQL patch lifecycle are future controlled revisions. Do not treat version 1.0.0 as Chaos MVC certified without validation.

## Product / system dropdown (v1.0.1)

- The certificate issuance field is populated from the module-owned `certifications_systems` table; arbitrary slugs are rejected server-side.
- Tier 9 may register a product/system with a human-readable name and stable slug, then enable or disable it. Disabling prevents new certificates for that system without changing existing certificate records.
- Existing installations: use **Update Module SQL** in `/admin/certifications` once to add the catalog table, then add products through the Admin form. New installations: **Install SQL** creates both records and catalog tables.
- No products are silently assumed or automatically certified. Existing certificate records remain unchanged. Registry remains separate from current Mission Control `organization_credentials` decisions.
