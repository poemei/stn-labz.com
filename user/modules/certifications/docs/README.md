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
