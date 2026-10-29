# Level 7 Developer/AI — Controlled Certification and Mission Transition

**Status:** STAGED / read-only inventory, 2026-10-29
**Scope:** stn-labz.com; Digit and Rictus Level 7 accounts.

## Goal

Bring working Level 7 module access under the definitive rule **Certification + Mission Assignment = Access** without repeating ticket #10a04e.

## Current source facts

- app/lib/admin_access.php first checks organization mission permission but then allows a legacy per-account module allowance for Level 7 developer/ai accounts.
- app/models/organization_model.php checks mission, credential and tier records through organization_policy.php.
- For technical Tier 7, current policy additionally requires qualification matching the actual responsibility tier.
- The existing Accounts screen maintains legacy module allowances; Organization management maintains missions and credentials separately.
- The Operator verified that Digit and Rictus have functioning assigned modules; their formal live credential/mission records have not been inventoried.
- The Operator verified PASS: 131 isolated policy checks, exit code 0. The test does not exercise the legacy Level 7 fallback.

## Read-only inventory — required before a code cutover

1. Using authorized Tier 9 organization and account screens, identify each Level 7 account's persisted role and user level.
2. Enumerate its existing account_module_access module slugs and actual working Admin modules.
3. For each module identify its product/system scope, active matching certification, Tier 7 qualification, and active module/system mission.
4. Record expiration/status/evidence and distinguish legacy-only access, mission-authorized access, and denied access.
5. Preserve inactive and revoked historical records. Do not automatically certify or grant missions from legacy allowances.
6. Test the authorized module operation and direct route separately for both Digit and Rictus.

## Operator-gated cutover

1. Operator reviews inventory and explicitly authorizes any needed credential and mission entries.
2. Record only evidenced, authorized entries via existing Tier 9 organization management.
3. Prove each module's mission-only permission, including negative and direct-route checks.
4. Submit a bounded correction for the legacy fallback and competing Accounts-grant UI.
5. Require explicit cutover approval before modifying effective permission logic; verify no authorized module disappears.
6. Retest mission withdrawal and expired/revoked certification in the complete application, not solely the isolated pure policy.

## Stop conditions

- Missing organization tables, unknown credential evidence, missing mission scope, or uncertain baseline.
- Any loss of approved Digit/Rictus access.
- Any proposed automated conversion that equates legacy module assignment with certification.

**No database mutations, migration, access revocation or code cutover are authorized by this document.**
