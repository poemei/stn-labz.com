# STN-LABZ organization and mission access

Scope: `poemei/stn-labz.com` only. Status: implemented for review; not deployed.

## Authority and records

The existing Administrator classification remains Tier 9 / TSC. Organization
management uses that existing authority exclusively; managers do not acquire
credential-issuing or mission-assignment authority by seniority.

`/accounts/organization`, linked from Accounts, manages explicit positions,
product certifications, responsibility-specific qualifications and mission
assignments. The nine position labels follow the Operator's handoff. Tier 9
continues to be assigned through the existing account-classification interface.
An ordinary registered account remains a user, not General Support.

Credentials require approved standard and evidence references. This interface
records the Administrator's decision; it does not define or replace certification
standards. Certification and qualification are separate records. A qualification
identifies the responsibility tier it demonstrates, so database qualification
does not establish code-support or development qualification.

An explicit mission identifies one account, module, system, authority reference,
optional expiration date and active state. Expiration dates are inclusive UTC
dates. Withdrawing a mission marks its record inactive without deleting it.
Credential statuses are pending, active, suspended, expired and revoked.
Only active, unexpired credentials qualify. Records retain their creator/last
changing authority and timestamps; this is not an immutable change-history log.

## Authorization

- Core reads the account and organizational position from current storage rather
  than trusting a cached session level. Deleted or demoted accounts cannot retain
  TSC through an old session. Missing/partial organization storage denies new
  organizational access. Legacy allowances cannot bypass these checks.
- Tier 9 retains unrestricted administrative access.
- Tiers 1–8 require a mission for the module/system in question. Tiers 3, 4 and 7
  additionally require matching product certification and a qualification for
  their responsibility tier. Certification alone never grants access.
- Tiers 1–5 receive bounded Support operations. They do not receive arbitrary
  module-administration privileges from a support position or qualification.
- Tiers 6–8 may use explicitly assigned module administration, subject to the
  module's stricter action checks. This implementation does not invent new
  action-level APIs for unrelated modules.
- Core infrastructure such as Accounts, module installation and updates remains
  TSC-only. All new record mutations use POST, Core CSRF and prepared parameters.

Existing module allowance rows remain stored and visible for reference, but do
not substitute for explicit missions or required credentials. The Administrator
must record the approved missions and credentials before expecting non-TSC access
under this policy. Withdrawing or expiring a mission cannot reactivate an old
allowance. Existing developer/AI account classifications still identify Tier 7;
ordinary users are not promoted into organizational positions automatically.

## Support

Ticket `type` (the configured topic identifier, such as `digit` or `rictus`) is
the system scope. Use exactly the same identifier in missions and credentials.
The existing General topic remains a separate scope; it grants no product access.

| Position | Operations |
| --- | --- |
| Tier 1 | Assigned-system queue, notes, status, requester correspondence, escalation to eligible Tier 2 |
| Tier 2 | Assigned-system queue, notes, status, requester correspondence, eligible internal escalation/assignment |
| Tiers 3–4 | Assigned-system investigation and internal findings; no status, assignment, implementation or requester-email authority |
| Tier 5 | Assigned-system coordination, status, eligible escalation/assignment, internal notes; no requester email |
| Tier 9 | Administrative configuration, lifecycle, all tickets and internal operations |

Requester correspondence is restricted to Tiers 1 and 2, including when an
Administrator is operating the Support interface. The explicit communication
boundary is enforced separately from unrestricted TSC administration. Automatic
new-ticket receipts remain supported. Replies, status notifications and assignment
notifications all check the actor's requester-email permission. Technical notes
never appear in the public reply list. Escalation does not remove the assigned
system's Tier 1/2 personnel's ability to communicate with the requester.

Queues and direct ticket requests enforce product scope. A forged POST that omits
the internal-note checkbox cannot turn a technical note into a public reply.
Escalation and assignment require a real account with the target tier, current
mission and applicable credentials. Escalation changes tier and assignee in one
transaction. Existing free-text assignee values remain stored; subsequent
assignments use `account:<id>`. Legacy `dev` ticket values retain their existing
Tier 5 compatibility mapping, now displayed as Support Manager.

## Installation and adoption

1. Review and deploy this repository change through the established human process.
2. Sign in with the existing Administrator account and open Accounts → Organization.
3. Select **Install organization SQL**. It executes the additive,
   `CREATE TABLE IF NOT EXISTS` statements in `app/install/organization.sql`.
   GET requests do not install or migrate these tables. Retrying can complete a
   partially applied installation; MySQL DDL is not assumed transactional.
4. Record each person's approved position, certifications, qualifications and
   missions, using retained allowances as reference. Establish the required
   records before expecting any non-TSC staff access. Plan this setup as part of
   the deployment window; old allowances alone no longer grant access.
5. Verify the site's exact product/topic identifiers and the resulting access
   using representative accounts before accepting the deployment.

The organization tables are Core-owned account relationships. The certification
module remains the public explanation; uninstalling that module does not delete
authorization records. Existing accounts, Support data and module allowances are
not automatically rewritten or removed. Existing explicit account deletion retains
its established authority and cascades the deleted account's relationships.

## Verification and remaining environment checks

Run `php -d extension=pdo_sqlite tests/organization_access.php` on the Windows PHP
installation used for this change, or `php tests/organization_access.php` where
PDO SQLite is already loaded. The suite executes actual organization and Support
models/controllers against isolated SQLite tables, captures mail instead of
sending it, and renders the affected management views. Its transport substitutes
for the existing MySQL connection; it does not validate MySQL installation DDL.

Validated cases include all nine tier distinctions, ordinary-user exclusion,
current-account authority, product isolation, exact expiration boundaries,
credential status/qualification distinctions, immediate mission withdrawal,
legacy allowance preservation, partial-schema denial, queue/direct-URL filtering,
forged public replies, internal-note privacy, notification gates, eligible
escalation with transaction rollback, escaped credential evidence, CSRF, action
allowlists and Core route exposure. The final local run passed 128 regression
checks; PHP syntax checks passed for all 181 PHP files in app, user and tests.

Deployment-environment verification remains necessary for MySQL DDL, the position
upsert, production theme layout, real sessions and SMTP delivery. No production
database, deployment or real email was touched by this work. The public live
certification URL was unavailable to the retrieval tool during inspection;
its repository controller and views were inspected instead.

## Affected components

- Core: `admin_access`, `organization_policy`, `organization_model`, Controller
  authorization, Admin dispatch, Router guards, the account-deletion authorization
  check in Auth, Traffic and Modules entry guards, Accounts management, and
  additive organization SQL.
- Views: Accounts link, organization management, classic and STN-LABZ theme navigation.
- Certification: public position controller/view uses the shared nine-tier labels.
- Support: controller authorization/email/escalation, transactional assignment
  model method, queue and ticket controls.
- Tests: `tests/organization_access.php`.

No external product repositories or unrelated architecture were changed. Human
review and acceptance remain outstanding; passing tests do not constitute acceptance.
