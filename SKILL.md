---
name: transform-my-repo
description: >-
  Feasibility analysis and migration strategy for architecture
  transformations: language ports (PHP to Python, Rust to Go), framework
  migrations (Vue or React SPA to Next.js), major version upgrades,
  monolith to microservices and back, database migrations, cloud/container/
  serverless re-hosting, sync to event-driven, REST to GraphQL/gRPC, UI
  platform shifts, standalone to SaaS, and vanilla to framework. Produces
  an evidence-based GO/PARTIAL/NO-GO verdict with a worth-it test against
  the do-nothing baseline (dependency census verified in the target
  ecosystem, semantic gap analysis, per-module difficulty heatmap), then a
  migration roadmap with behavioral parity gates and rollback points.
  Allowed to recommend NOT migrating. Use when the user asks to migrate,
  port, rewrite, re-platform, upgrade a major version, split or merge
  services, switch databases or API paradigms, asks whether a migration is
  worth it, or mentions transform-my-repo or /transform-my-repo.
license: MIT
argument-hint: "[source → target] [project-root] [driver/reason]"
---

# Transform My Repo

Migrations die from optimism: the equivalent library that doesn't exist, the
runtime behavior nobody knew was load-bearing, the big-bang rewrite that
never ships. This skill plans transformations the evidence-first way — and
its most valuable possible output is a well-argued **"don't."**

Division of labor: this skill answers **whether to migrate, how hard, in
what order, and with what safety net**. The detailed implementation plan for
each migration phase is a **deep-plan** run — do not duplicate that work here.

## The Prime Directive (family rule)

> **No claim without evidence — on BOTH sides.** Source facts need
> `file:line`. Target-stack facts (a library exists, a mechanism behaves a
> certain way) need a registry/docs citation with version, checked NOW —
> training-data memory of an ecosystem is not evidence, and "surely Python
> has an equivalent" is how migrations lose months.

## Progress checklist

Copy this into your response and check items off:

```
Transform Progress:
- [ ] Step 0: Frame — source → target, driver, constraints, non-goals, feasibility questions
- [ ] Step 1: Source census — inventory + dependency, platform-coupling, operational censuses
- [ ] Step 2: Target ground truth — every dependency & mechanism verified in target ecosystem
- [ ] Step 3: Gap analysis — semantic gaps + per-module difficulty heatmap
- [ ] Step 4: Verdict — GO / PARTIAL / NO-GO as a 10–20 line brief  ⛔ user gate
- [ ] Step 5: Strategy — parity harness, pattern, bridge, rollback, ONE-WAY tags
- [ ] Step 6: Assessment document — skeleton-first roadmap, spikes for unknowns, pre-mortem risks
- [ ] Step 7: Self-check + report
```

## Step 0 — Frame the transformation

Pin down, before analyzing anything:

- **Source → target**, precisely (language+version, framework, deployment model).
- **The driver** — WHY migrate: performance, hiring, maintenance, licensing,
  business model (SaaS)? The verdict is only meaningful relative to the
  driver; "Go is nicer" and "PHP hosting blocks our SaaS pricing" produce
  different verdicts on identical code.
- Constraints: team skills, downtime tolerance, budget/deadline, data that
  must not be re-migrated twice.
- Non-goals: what explicitly stays as-is.
- **Feasibility Questions** — a numbered list of what the verdict needs
  answered ("does the target ecosystem cover the ORM?", "does the target
  runtime actually serve the perf driver?", "is there a seam where old and
  new can coexist?"). Steps 1–3 exist to answer this list: each finding
  cites the question number it answers, questions discovered mid-census are
  appended, and research is complete when every question is answered with
  evidence or explicitly tagged `UNVERIFIED` — **not** when every file has
  been read.
- Identify which transformation type(s) apply — see
  [references/transformation-catalog.md](references/transformation-catalog.md);
  each type has its own extra checklist. Combinations (port AND SaaS-ify)
  multiply risk: recommend sequencing them, not doing both at once.

## Step 1 — Source census

Inventory the source repo (reuse the know-my-repo discipline if that skill
is available; otherwise a deep read: structure, data flow, wiring, tests,
git trajectory). On top of that, three censuses specific to migration:

1. **Dependency census** — every runtime dependency with its role and how
   deeply its API is woven in (call-site count).
2. **Platform-coupling census** — every use of source-platform-specific
   behavior: runtime model (e.g. PHP's request-per-process state reset),
   language constructs with no direct target equivalent, OS/FFI calls,
   framework magic, numeric/string/encoding semantics the code relies on.
3. **Operational census** — deploy pipeline, environments, real data scale
   (row counts, traffic — migrating 100 rows and 100M rows are different
   plans), and background jobs (cron/queue) touching the paths that move.
   Migrations fail on ops as often as on code; this census feeds the
   coexistence bridge and cutover math in Step 5.

Everything with `file:line`. Also record test coverage per module — it
determines parity-harness cost in Step 5.

## Step 2 — Target ground truth

For EVERY census entry, verify in the target ecosystem — registry, official
docs, source — with URL + version, fetched now:

| Census entry | Target equivalent | Coverage | Evidence |
|--------------|-------------------|----------|----------|
| <library / mechanism> | <package / built-in / none> | full / partial / **missing** | <registry or docs URL + version> |

Rules:

- "Partial" must say what is missing (features, maturity, maintenance
  status — a last-commit-3-years-ago port counts as missing).
- **Missing entries are the headline finding**, each with its realistic
  options: write it yourself (estimate), keep a sidecar in the source
  language, change approach, or verdict-blocker.
- Verify the target's runtime model actually serves the driver (e.g. if the
  driver is performance, find benchmarks or write a spike — do not assume).
- **No web access in this session?** Degrade honestly: verify what local
  evidence allows (installed target toolchains, lockfiles, vendored source,
  offline docs), tag everything else `UNVERIFIED — needs web check`, and
  say plainly that the verdict is provisional until those are checked.
  Never fill the Evidence column from memory.

## Step 3 — Gap analysis and difficulty heatmap

- **Semantic gaps:** behaviors that will silently differ after a faithful
  line-by-line port — typing discipline, concurrency model, error handling
  idioms, numeric precision, string/encoding, lifecycle/state model,
  transaction semantics. Each gap: where it bites (`file:line`) and how it
  will be handled or tested.
- **Difficulty heatmap:** score every module with the rubric in
  [references/feasibility-rubric.md](references/feasibility-rubric.md)
  (size, dependency density, platform coupling, test coverage, churn).
  Output a ranked table — this answers "what will be hard" concretely and
  later dictates migration order.

## Step 4 — The verdict ⛔

Apply the rubric and present, BEFORE designing anything further:

- **GO** — coverage and gaps manageable; expected cost proportionate to the driver.
- **PARTIAL** — migrate these modules, keep/bridge those; say which and why.
- **NO-GO** — the evidence says the driver is better served another way;
  name the alternative (targeted refactor, optimization, extraction,
  different target). A NO-GO with evidence is a successful outcome of this
  skill, not a failure.

Present it as a **Verdict Brief** — 10–20 lines in chat, not a document:
the verdict, dependency coverage stats, worst gaps, heatmap top rows,
estimated magnitude (S/M/L/XL per module group), the three worth-it
answers, and any feasibility question still `UNVERIFIED`. Ask for the
decision **once**. **Stop and let the user decide.** Only continue to
Step 5 on an accepted GO/PARTIAL.

If the user cannot respond (headless/CI run), degrade honestly: on GO or
PARTIAL, continue but tag the verdict `UNCONFIRMED — awaiting user` in the
document's Verdict section; on NO-GO, write the assessment with the
strategy and roadmap sections marked "not applicable — NO-GO" — designing
a migration nobody approved is waste.

## Step 5 — Migration strategy

Design the safety net before the route:

1. **Behavioral parity harness first.** Before any porting, pin current
   behavior: characterization/golden-master tests on the source for every
   module about to move, at its seams (HTTP responses, DB writes, file
   outputs). Untested behavior cannot be proven preserved — budget this
   honestly; it is often the largest single line item.
2. **Migration pattern — decide, don't menu:** default is **incremental
   with coexistence** (strangler fig at a routing/API seam,
   branch-by-abstraction, module-by-module with an interop bridge).
   A big-bang rewrite requires proof it is unavoidable (tiny codebase, or
   no viable seam), not preference.
3. **Coexistence bridge:** how old and new run together during migration —
   API seam, message queue, sidecar, FFI — and how data stays consistent
   across both (single writer? sync? cutover-per-table?).
4. **Cutover and rollback:** per phase — shadow/dual-run with output
   diffing where feasible, feature flags, the exact rollback trigger and
   procedure. A phase without a rollback path is not a phase; it is a bet.
5. **Classify every cutover action REVERSIBLE or ONE-WAY.** ONE-WAY =
   undo cost rivals do cost: in-place/destructive data conversion,
   decommissioning the old system, a cutover after which the two stores
   diverge, a public API contract change. Each ONE-WAY action needs
   explicit user confirmation before its phase runs — in headless runs,
   carry it into the document marked `UNCONFIRMED`. REVERSIBLE actions
   just need their rollback path named.

## Step 6 — Write the assessment document

Write to `docs/TRANSFORM.md` (or user-chosen path) following
[references/assessment-template.md](references/assessment-template.md):
verdict + evidence, censuses, gaps, heatmap, strategy, and a phased roadmap
where **each phase is sized to be one deep-plan run**, ordered by the
heatmap (start with a low-risk, representative module to calibrate real
cost — never the hardest one), each with parity gate + rollback point.

Two roadmap non-negotiables:

- **Phase 1 is a walking skeleton, not a code drop:** the calibrator
  module runs **end-to-end in the target stack** — built, deployed through
  the real pipeline, serving traffic through the actual coexistence
  bridge. "Ported and unit-tested" parks the integration risk (build,
  deploy, bridge, data) at the end of the migration, which is where
  migrations die.
- **Every `UNVERIFIED` tag and every `missing` census entry that survives
  into the roadmap becomes a named, timeboxed spike task** placed FIRST in
  the phase that depends on it (e.g. "Spike: prove <target lib> handles
  <feature> — 1 day"). No unknown may sit silently under a migration phase.

## Step 7 — Self-check and report

- Every target-ecosystem claim has URL + version. Every source claim has
  `file:line`. Zero "surely the target has this" — grep the document for
  `UNVERIFIED` and verify the count matches section 10.
- Every Step 0 feasibility question is answered with evidence or listed as
  `UNVERIFIED` in the document — and every surviving `UNVERIFIED`/`missing`
  entry has a named spike task first in the phase that depends on it.
- The verdict follows from the evidence shown, not from enthusiasm either
  way — and it was presented as a Verdict Brief (or, headless, tagged
  `UNCONFIRMED`).
- Every ONE-WAY cutover action is user-confirmed or marked `UNCONFIRMED`;
  every REVERSIBLE action names its rollback path.
- The risk register came from the pre-mortem — no risk that could be
  pasted into a different migration's assessment unchanged.
- Roadmap Phase 1 is a walking skeleton (deployed end-to-end through the
  bridge), and the operational census (deploy, data scale, background
  jobs) is reflected in the cutover plan.
- The user could hand Phase 1 of the roadmap to deep-plan right now.
- Report: verdict, top 3 hardest things (per the heatmap), missing
  equivalents, and the recommended first slice.

## When things go wrong

| Situation | Response |
|-----------|----------|
| Target ecosystem unreachable (no web access) | Degrade honestly (Step 2): verify what local evidence allows (installed toolchains, lockfiles, vendored source, offline docs), tag everything else `UNVERIFIED — needs web check`, state plainly verdict is provisional. Never fill Evidence column from memory. |
| Dependency has no target equivalent and no acceptable workaround | Missing entry becomes headline finding (Step 2). Realistic options: write it yourself (estimate), keep sidecar in source language, change approach, or verdict-blocker. If blocker touches core → NO-GO. |
| User cannot respond to Verdict Brief (headless/CI run) | Degrade honestly (Step 4): on GO/PARTIAL continue but tag verdict `UNCONFIRMED — awaiting user` in document; on NO-GO write assessment with strategy/roadmap marked "not applicable — NO-GO". Never design migration nobody approved. |
| Feasibility question remains unanswered after Step 3 | Tag `UNVERIFIED` explicitly (Step 0 rule), list in document section 10, create named timeboxed spike task FIRST in the phase that depends on it (Step 6). No unknown may sit silently under a migration phase. |
| ONE-WAY cutover action needed but user unavailable for confirmation | Mark `UNCONFIRMED` in document (Step 5). Carry into roadmap phase with confirmation status visible. Never execute destructive action without explicit user sign-off. |
| Migration stalls at 60%, both stacks running forever | Classic risk — gets explicit trigger and answer in risk register (Step 6). Define DONE criterion up front (e.g. "no direct mysqli_query outside repository layer"); strangler must finish strangling. |
