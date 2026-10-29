# Mission Control — Recovery and Alignment Roadmap

**Date:** 2026-10-29  
**Status:** PROPOSED / STAGED — planning only; no implementation authorization  
**Target:** `poemei/stn-labz.com` only  
**Reference-only:** `stnlabz/chaos-mvc`  
**Baseline assessment:** [Mission Control Codex Assessment](ASSESSMENT.md)

## Governing boundary

The Operator defines Mission Control requirements, architecture, scope, priority, and acceptance. This roadmap **does not authorize** implementing any phase. Changes to the protected Core or existing contracts require the applicable explicit approval. Do not convert a roadmap item, a passing test, or a suggestion into task authority. The original handoff remains the requirements source pending the Operator's forthcoming clarification.

## Stage 0 — Operator intent and acceptance baseline (PENDING)

1. Receive Operator's description of what Mission Control is meant to do.
2. Identify intended operational relationships between organizational position/tier, certification, qualification, mission, and module allowance.
3. Identify existing account types, live certifications, qualifications, assignments, and Support workflows that must survive.
4. Record intended Core-versus-module ownership and exact changes the Operator permits.
5. Set measurable acceptance tests and any approved rollout constraints.

**Exit:** Operator-approved scope, target behavior, and evidence criteria. **Do not infer acceptance.**

## Stage 1 — Establish implementation inventory (NOT STARTED)

1. Compare `poemei/stn-labz.com` actual current source against its pre-Codex baseline; preserve the Codex commit identity.
2. Read applicable repository instructions, relevant Chaos MVC contracts, and current installation-owned module definitions.
3. Map existing storage and authorization paths: login/session, admin navigation, Core gates, user modules, account allowances, certification, and Support.
4. Build a requirement-to-path inventory separating authorized customization from introduced regressions.

**Exit:** Evidence-backed inventory with no source changes.

## Stage 2 — Validate behavior and isolate actual failures (NOT STARTED)

1. Test Tier 9 and all lower tiers with explicit expected-versus-observed results.
2. Test direct routes, menu visibility, CSRF, expired/withdrawn missions, wrong-system access, and legacy allowances.
3. Test existing and new Support tickets across Tiers 1–5; verify who may see, assign, escalate, investigate, and email.
4. Test migration/readiness against the actual deployment storage engine, not only the SQLite substitute.
5. Classify each finding as VERIFIED DEFECT, EXPECTED DESIGN, OPERATOR DECISION, or UNKNOWN.

**Exit:** Reproducible defect list and tested baseline; preserve failed evidence.

## Stage 3 — Operator chooses corrections (BLOCKED ON APPROVAL)

1. Present each verified defect with exact affected path, contract, severity and proposed minimal correction.
2. Obtain explicit approval for each Core change, interface change, schema change, migration, or module behavior change.
3. Sequence approved work one operator-authorized item at a time; keep unrelated discoveries separate.

**Exit:** A named and bounded correction task, not a broad rewrite mandate.

## Stage 4 — Implement approved minimal patches (BLOCKED ON APPROVAL)

1. Patch only the approved file(s) and responsibility layer; preserve existing working behavior.
2. Maintain Chaos MVC Core/module boundaries, coding and annotation requirements, and STN-Labz versioning and changelog discipline.
3. Record the exact source revision and changed paths, plus why each change was necessary.
4. Stop at the defined acceptance boundary rather than adding improvements.

**Exit:** Reviewable patch and evidence; no automatic deployment.

## Stage 5 — Verify, qualify and seek Operator acceptance (BLOCKED ON PRIOR STAGES)

1. Execute relevant automated, negative and regression checks; document results and failures.
2. Verify intended database and notification behavior against authorized environment and test data.
3. Submit exact operator client test procedure and expected results.
4. Distinguish compilation/test results from qualification, deployment, and Operator acceptance.
5. Stop and await the Operator's explicit acceptance and authorization for any next task.

**Exit:** Recorded Operator outcome for the named revision and scenario.

## Standing constraints

- No ChatGPT Work routing when a GitHub repository is supplied; use repository-native work only.
- No changes to the Chaos MVC reference repository.
- No deployment, schema install, data migration, reset, cleanup, or access revocation without explicit target-specific authorization.
- No undocumented hardcoded workarounds, new architecture, or speculative corrections.
- No stage is self-executing. **Operator direction is final within authorized scope.**

## Current position

**Stage 0: Awaiting the Operator's description of intended Mission Control behavior.** The source assessment is available for discussion. No engineering repair has begun.
