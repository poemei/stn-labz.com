# STN-Labz Mission Control — Operational Architecture Roadmap

**Date:** 2026-10-29  
**Status:** STAGED / OPERATOR REVIEW — requirements clarified in conversation; no implementation authorization  
**Target:** `poemei/stn-labz.com` (custom Mission Control website)  
**Framework reference only:** `stnlabz/chaos-mvc`  
**Codex baseline:** [Assessment](ASSESSMENT.md)  
**Source requirements:** Operator's *Organizational Authority, Certification and Mission Access* handoff, supplemented by the Operator's direct operational examples.

## 1. Mission Control in one sentence

Mission Control identifies the account, determines its organizational job and valid credentials, checks its active mission assignments, and presents **only the administrative modules and operations required by those authorized missions**. It also coordinates operational work between eligible support tiers, teams and AI agents.

**Governing access rule: CERTIFICATION + MISSION ASSIGNMENT = ACCESS.**

A certification establishes recognized competence for the system. A mission assignment establishes authorization to work in that system. Both are required for mission-controlled module access. Position and tier define the job and bounded responsibilities; qualification identifies demonstrated responsibility-specific capability where applicable. Neither tier nor certification alone grants unrelated access. Existing Tier 9 Administrator/TSC authority is preserved as defined in the original handoff.

This is a **deterministic operational control system** with intelligent coordination behavior. AI agents participate as assigned operational entities; they do not create their own authorization or override human direction.

## 2. Login, account identity and module access

### Required login decision sequence

1. Authenticate the account using existing Chaos MVC authentication.
2. Determine whether the authenticated account is a recognized, active employee/operational agent rather than an ordinary/public account.
3. **If NO:** retain **Level 0 public access** (public pages and ordinary public-access functions). Authentication alone does not confer administrative permissions. The existing ordinary account behavior must be reconciled with the Operator's Level 0 terminology; do not silently rewrite public account schema.
4. **If YES:** read its job assignment, position and tier; inspect applicable certification and qualification; read active mission assignments.
5. For each mission-controlled module, require **valid applicable certification AND active mission assignment**, then enforce the permitted job/tier/qualification scope for the requested operation.
6. Expose only the required authorized Admin modules in navigation and the assigned Admin interface; enforce the same decision server-side for direct URLs, APIs and actions.
7. Revalidate protected requests when credentials, missions, or position may have changed; do not trust a stale session as permanent authorization.
8. Missing, expired, suspended or revoked required credentials, or missing/withdrawn missions, yield **no mission-controlled module access**. Public access remains available.

**Illustrative cases:** Certified for Digit but no Digit mission => no Digit admin module. Digit mission but no valid Digit certification => no Digit admin module. Both current => only Digit-related modules and approved actions. Being certified for Digit, Rictus and Ezra but assigned only Digit => no Rictus/Ezra mission access.

**Open implementation details:** Authoritative employee/AI identity source; how existing `account_module_access` maps into missions; precise credential lifecycle and qualification requirements for nontechnical positions. These are **not** invented by this roadmap.

## 3. Operational assignments are not lists of people

Mission Control distinguishes:
- **Work destination:** support tier, organizational team, or named AI agent (for example, **Tier 2 — Digit**).
- **Assignment identity:** the authorized destination representing where work is queued, not necessarily an individual account ID.
- **Handler:** a qualified on-duty human or assigned AI agent that accepts/works the ticket, if the workflow calls for one.
- **Authority:** certification + mission assignment plus job/tier/action restrictions, enforced independently of what the UI labels.

The Support Module must be able to show **Tier 2 — Digit** as the ticket's assignment destination. It must **not require selecting an individual employee** merely to move the case to that destination. An agent's assignment must not imply unlimited execution authority.

The distinction matters because the current Codex `validatedAssignee()` requires a numeric person account ID and the interface offers individual people. This is a confirmed **mismatch with clarified Operator intent**, regardless of whether other Codex changes are sound.

## 4. Support operational workflow

### Example: unresolved Tier 1 case

1. A Tier 1 Support technician clocks in and signs in at `stn-labz.com`.
2. Mission Control validates employee status, Tier 1 job, relevant credentials and support mission; the assigned Admin shows the Support module.
3. The technician sees an eligible support ticket, investigates within Tier 1 authority and determines they cannot resolve it.
4. The technician **escalates the ticket to Tier 2** rather than selecting an arbitrary individual.
5. Mission Control places the ticket in the **Tier 2 destination/queue** and notifies the eligible Tier 2 personnel or assigned agent(s).
6. An eligible Tier 2 technician or agent sees and accepts the work, investigates, and either resolves it within its authority or escalates it to the next applicable specialty/tier.
7. The process may proceed to **Tier 3 Database Support**, **Tier 4 Code Support**, or **Tier 5 Support Management**, according to case needs and the established chain of responsibility. Tier 3 and Tier 4 are distinct technical functions, not interchangeable credentials; Tier 5 manages support operations.
8. Throughout, retain the original ticket, assignment/escalation history, internal findings, and requester communication context.

**Customer communication boundary:** Tier 1 and Tier 2 communicate directly with the requester. Tier 3–5 findings and management communications stay internal. Escalation does not transfer direct requester-email authority to the receiving technical tier.

**Notification requirement:** Notify the eligible receiving tier/agent when work reaches its queue. Notification and receipt/acceptance must be distinguishable from assignment itself; do not claim notifications are implemented or delivered until verified.

**Clock-in/on-duty:** The Operator's scenario includes clock-in and active personnel. Whether this requires a new attendance subsystem or uses existing active status is an **open decision**; do not add timekeeping architecture without explicit direction.

## 5. Required operational components and boundaries

| Component | Responsibility |
| --- | --- |
| Existing Chaos MVC authentication | Authenticate the account; do not replace with a competing login system. |
| Mission Control organizational registry | Recognize employees, AI agents, positions and job/tier responsibilities. |
| Certification and qualification | Record current product/system competency and, where required, responsibility-specific qualification; preserve existing approved records. |
| Mission assignment | Record active authorized work scope by system, module, project or department; withdrawal takes effect for protected requests. |
| Access resolution | Enforce certification + mission assignment; limit modules/actions to actual job authority; preserve Tier 9 exception. |
| Assigned Admin | Display authorized modules based on access resolution; hiding a menu is not security enforcement. |
| Support module | Route tickets to tier/agent destinations, notify eligible recipients, track acceptance, resolution, escalation and history. |
| Audit and operator oversight | Trace who or what made an assignment and why; document decisions and approvals. |

No module creates its own competing authentication or mission-grant authority. Chaos MVC remains the framework reference; custom Mission Control requirements stay scoped to this website.

## 6. Differences between the intent and Codex's implementation

| Area | Clarified intent | Observed implementation / concern |
| --- | --- | --- |
| Origin | Extend Level 7 Developer/AI module assignment into organization-wide Mission Control | Existing Level 7 module allowance mechanism persists in data but new `admin_access::canAccess()` ignores allowances for effective access. Review compatibility and authority mapping. |
| Access predicate | **Certification + mission assignment = access**, bounded by job/tier/qualification | Current policy applies different credential combinations based on tier and may admit some support/management roles by mission alone; reconcile with the Operator's governing rule. |
| Admin | Build each logged-in employee/agent's module view from authorized missions | Current routes, navigation and `require_admin()` were broadly changed; verify correct end-to-end gates and Level 0 behavior. |
| Assignment | Tier or AI-agent work destination (e.g. **Tier 2 — Digit**) | Codex's Support `validatedAssignee()` requires a numeric individual account; UI lists people. |
| Escalation | Queue transition, appropriate notifications, eligible agent/technician uptake | Current code changes tier and named assignee together; receiving-tier notifications and uptake must be verified and reconciled. |
| Preservation | Keep working Chaos MVC and existing data/contracts unless specifically changed | New schema and Core-wide authorization path warrant staged compatibility tests; modification of Core was contemplated by original handoff, not automatically forbidden. |

These are source-backed comparison and intent findings, **not** proof of production failure.

## 7. Roadmap stages and acceptance gates

### Stage 0 — Capture operational contract (CURRENT — STAGED)
- Document this access rule, public/nonemployee path, employee/AI identity path, assigned Admin, support escalation chain, notifications and tier/agent destinations.
- Confirm remaining implementation ambiguities with the Operator; do not substitute engineering assumptions.
- **Exit:** Operator approves the behavior specification and boundaries.

### Stage 1 — Inventory current Mission Control implementation (NOT STARTED)
- Compare current `poemei/stn-labz.com` code and pre-Codex baseline with the applicable `stnlabz/chaos-mvc` contracts.
- Inspect account Level 7/module allowances; employee/agent identity; certification and qualification records; mission schema; Core Router/Controller gates; Support queues and notification logic.
- Identify which existing implementation paths to reuse and which conflict with the confirmed operational contract.
- **Exit:** Evidence-backed requirements-to-code map; no patches.

### Stage 2 — Establish tests and validate differences (NOT STARTED)
- Exercise nonemployee Level 0, staff Tiers 1–8, Tier 9, Developer/AI accounts, direct protected routes and assigned Admin navigation.
- Test missing/expired/revoked certification, missing/withdrawn mission, unrelated product, role/qualification restrictions and stale sessions.
- Test **Tier 1 → Tier 2 — Digit** queue escalation *without choosing a person*, eligible notification, acceptance, internal escalation and requester-email boundaries.
- Verify against the real supported database/runtime and existing data; a SQLite substitute alone is not qualification.
- **Exit:** Categorized confirmed defects, known-good behavior, unresolved operator choices and reproducible evidence.

### Stage 3 — Submit minimal correction design for authorization (BLOCKED)
- Propose precise, bounded changes for access resolution, Admin module selection, organization records and Support work destinations.
- Preserve existing records and working behavior; specify any data compatibility/migration needs rather than assuming them.
- Require explicit Operator approval for Core contracts, schema, Support implementation, rollout and any new clock-in/on-duty behavior.
- **Exit:** Specific human-authorized patch scope.

### Stage 4 — Implement only approved changes (BLOCKED)
- Patch only approved files with existing Chaos MVC and STN-Labz annotation, development and versioning rules.
- Keep mission access enforcement separate from display/navigation and ticket work allocation.
- Preserve audit trail and data; no automatic deployment or permission migrations.
- **Exit:** Reviewable revision with exact change log and rollback considerations.

### Stage 5 — Verify and seek human acceptance (BLOCKED)
- Run functional, negative, end-to-end, MySQL and notification verification as applicable.
- Demonstrate login through assigned Admin and Tier 1 escalation to **Tier 2 — Digit**, then onward movement under certification and mission constraints.
- Report evidence, failure cases and outstanding decisions. Passing automated checks is not operator acceptance.
- **Exit:** Explicit Operator acceptance or requested revision; no inferred release.

## 8. Standing constraints

- GitHub repo supplied => **Work disabled**; GitHub-native workflow only.
- `stnlabz/chaos-mvc` is a reference, never a patch target.
- No unapproved code, Core rewrite, schema installation, deployment, migration, data reset, account revocation, or AI-initiated engineering change.
- No AI agent independently grants credentials, missions, permissions, deployments or technical implementation authority.
- Each stage is an **approval gate**, not an instruction to self-start the next stage.
- Versioning, changelog, source documentation, verification and operator acceptance remain distinct.

## Current disposition

**Operator behavior described; written roadmap staged, not yet accepted.** Next authorized step is an Operator review of this operational contract. No application code changes have been made for this roadmap.
