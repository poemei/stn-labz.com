# stn-labz.com: Mission Control — CHANGELOG

This is the source-change record for stn-labz.com. Dates are source dates, not deployment or acceptance dates. Module versions are independent of the release. A GitHub commit, successful compilation, or a module reconnect does not establish qualification or operator acceptance.

## 2026-10-29 — STAGED / DOCUMENTATION ONLY — Awaiting Operator Intent

- Added `docs/ASSESSMENT.md`: read-only, evidence-based assessment of the Codex mission-authorization implementation, distinguishing confirmed source changes from unverified runtime defects and unresolved approval questions.
- Added `docs/ROADMAP.md`: proposed Operator-gated sequence for intent clarification, inventory, verification, approved corrections, regression and acceptance.
- No production code, framework reference, database, deployment, qualification status, or released version changed by this documentation stage.
- Assessment and roadmap are **pending Operator review**; staging documentation does not authorize development or signify acceptance.

## 2026-10-29 — STAGED / ROADMAP CLARIFICATION — Operator Operational Model

- Reworked `docs/ROADMAP.md` around the Operator's governing rule: **Certification + Mission Assignment = Access**, with existing position/tier and qualification boundaries.
- Defined employee/AI login evaluation, Level 0 public fallback, mission-based Admin module loading, and server-side enforcement.
- Clarified tier/agent work destinations (example: **Tier 2 — Digit**) rather than mandatory individual employee assignment; documented notifications, case acceptance, escalation and requester communication boundaries.
- Identified implementation mismatches and unknown decisions without approving fixes. Documentation change only; no application code, database, version or deployment change. Pending Operator review.

## 2026-10-29 — Level 7 Developer/AI access compatibility fix — OPERATOR TEST PENDING

- Ticket `#10a04e`: Rictus reported successful `/admin` login with no displayed modules. Restored the existing per-account `account_module_access` fallback for accounts whose current persisted role is `developer` or `ai` and whose effective/current level is 7, only for assignable modules, in `app/lib/admin_access.php`.
- Current mission authorization remains preferred. This is a transitional continuity exception pending the Operator's certification-plus-mission adoption design; it is **not** a declaration that the final authorization model is implemented.
- Source revision: `a70372bdce4e7617763ad8e98a0d82c84f2d22f6`. No database, account records, production deployment, module source or Support escalation changed.
- Repository-source verification only; no production session, PHP runtime regression suite or Operator client acceptance recorded. **AWAITING OPERATOR END-TO-END TESTING AND ACCEPTANCE** before the separate Support escalation repair.

## 2026-10-29 — Support Tier Queue Escalation Fix — OPERATOR TEST PENDING

- Removed the forced individual employee selection from Support ticket escalation and reassignment; receiving destinations are now stored as `tier:t1` through `tier:t5` in the existing `assigned_to` field. No schema change or records migrated.
- The existing Support model's transactional `escalateTicket()` retains tier and assignment events, and the existing centralized tier-change notification remains invoked. This change does **not** implement per-recipient tier notification delivery, on-duty tracking, AI-agent acceptance, or account-to-agent mapping; those remain roadmap work.
- Tier 1 users are constrained to Tier 2 escalation in both controller validation and displayed selections. Other existing action and requester-email authorization boundaries are retained.
- Scope: `user/modules/support/controllers/support.php` and `user/modules/support/views/admin/ticket.php`; revisions `6b7ea25`, `a406c5d`, `8b97967`. No Core, accounts, database, or deployment changes.
- Source checks only; PHP runtime, mail delivery and end-to-end operator tests are outstanding. **AWAITING OPERATOR END-TO-END TESTING AND ACCEPTANCE**.

## 2026-10-29 — Support Audit No-Op Correction — OPERATOR TEST PENDING

- Based on the Operator's ticket `#10a04e` live history, adjusted `user/modules/support/models/support_model.php` so unchanged tier and unchanged assigned destination do not generate duplicate `t4 -> t4` or `tier:t4 -> tier:t4` audit records.
- Existing real transitions continue through the same SQL writes and history events; historic entries remain untouched. Legacy `dev` is normalized to Tier 5 for no-op comparison only.
- Source revision `1d3f11694ae59e84ccdef21b508be217b66cd493`. No SQL schema update is required, and no database migration, Core change, deployment or historical data cleanup was performed.
- PHP runtime and Operator UI tests remain outstanding. **AWAITING OPERATOR END-TO-END TESTING AND ACCEPTANCE**.

## 2026-10-29 — Support Requester Lifecycle Email and History Display — OPERATOR TEST PENDING

- Send requester-facing email on **actual status changes** regardless of whether the authorized actor changing status is Tier 1/2 or Tier 9; this is system-generated lifecycle mail, not permission to write manual replies. No notification on unchanged status submissions.
- Send requester-facing transfer email when tickets change support tier (from either Tier/Escalation or Assignment). Exclude private escalation notes, internal support findings, recipient identities and internal admin links from requester mail. Preserve separate existing internal central Support notices.
- Render Support event names clearly, e.g. `Internal Note`, as a separate display block from the actor/time metadata.
- Log email delivery exceptions in PHP error log instead of silently discarding them. Email sending remains best-effort with existing mailer configuration; delivery is not yet verified and message retries/queueing are not implemented.
- Changed only `user/modules/support/controllers/support.php` (`02fbf495`) and `user/modules/support/views/admin/ticket.php` (`6377b47f`). No SQL schema change, migration, Core change or deployment performed.
- **AWAITING OPERATOR END-TO-END TESTING AND ACCEPTANCE**.

## 2026-10-29 — Support Assignment Selector Current Tier — OPERATOR TEST PENDING

- Ticket `#10a04e` exposed that the `Assign to Support Tier` dropdown showed its first option (Tier 1) even while the ticket was assigned to Tier 4.
- The Support ticket view now visibly displays the persisted current support tier and preselects that exact tier in the assignment dropdown, including legacy `dev` -> `t5` presentation. Existing tier permissions and server-side validation are unchanged.
- Changed only `user/modules/support/views/admin/ticket.php` at `fefbaba9d4734b9aef6145896cd52e6211a1b70a`; no SQL, Core, routing, or deployment change. Live operator verification outstanding.

## 2026-10-29 — Mission Control Negative Authorization Tests — OPERATOR TEST PENDING

- Added `tests/mission_policy_denials.php`, a no-database PHP policy matrix for all Tiers 1–8 plus Level 0 and Tier 9. Cases cover absent, withdrawn, expired and wrong-scope missions; absent, pending, suspended, expired, revoked and wrong-system certifications; and missing/wrong/revoked technical qualifications.
- The added test exercises `organization_policy::permits()` only. The temporary Level 7 allowance path in `admin_access::canAccess()` is **not** covered or eliminated and may grant access despite invalid mission credentials; real effective authorization is therefore not yet proven to satisfy Certification + Mission Assignment = Access.
- Existing `tests/organization_access.php` contains obsolete expectations from before the Level 7 restoration and Support notification/routing corrections; it must be reconciled before claiming the complete suite passes.
- Source commit: `3760338634c74f17879a0ec443825322e39a5df1`. No runtime test execution, application authorization changes, SQL changes or deployment performed. **AWAITING OPERATOR END-TO-END TESTING AND ACCEPTANCE**.
