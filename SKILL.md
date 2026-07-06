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
- [ ] Step 0: Frame — source → target, driver, constraints, non-goals
- [ ] Step 1: Source census — inventory + platform couplings + dependency list
- [ ] Step 2: Target ground truth — every dependency & mechanism verified in target ecosystem
- [ ] Step 3: Gap analysis — semantic gaps + per-module difficulty heatmap
- [ ] Step 4: Verdict — GO / PARTIAL / NO-GO presented with evidence  ⛔ user gate
- [ ] Step 5: Strategy — parity harness, migration pattern, coexistence bridge, rollback
- [ ] Step 6: Assessment document written; roadmap phases sized for deep-plan
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
- Identify which transformation type(s) apply — see
  [references/transformation-catalog.md](references/transformation-catalog.md);
  each type has its own extra checklist. Combinations (port AND SaaS-ify)
  multiply risk: recommend sequencing them, not doing both at once.

## Step 1 — Source census

Inventory the source repo (reuse the know-my-repo discipline if that skill
is available; otherwise a deep read: structure, data flow, wiring, tests,
git trajectory). On top of that, two censuses specific to migration:

1. **Dependency census** — every runtime dependency with its role and how
   deeply its API is woven in (call-site count).
2. **Platform-coupling census** — every use of source-platform-specific
   behavior: runtime model (e.g. PHP's request-per-process state reset),
   language constructs with no direct target equivalent, OS/FFI calls,
   framework magic, numeric/string/encoding semantics the code relies on.

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

Show the reasoning: dependency coverage stats, worst gaps, heatmap summary,
estimated magnitude (S/M/L/XL per module group). **Stop and let the user
decide.** Only continue to Step 5 on an accepted GO/PARTIAL.

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

## Step 6 — Write the assessment document

Write to `docs/TRANSFORM.md` (or user-chosen path) following
[references/assessment-template.md](references/assessment-template.md):
verdict + evidence, censuses, gaps, heatmap, strategy, and a phased roadmap
where **each phase is sized to be one deep-plan run**, ordered by the
heatmap (start with a low-risk, representative module to calibrate real
cost — never the hardest one), each with parity gate + rollback point.

## Step 7 — Self-check and report

- Every target-ecosystem claim has URL + version. Every source claim has
  `file:line`. Zero "surely the target has this".
- The verdict follows from the evidence shown, not from enthusiasm either way.
- The user could hand Phase 1 of the roadmap to deep-plan right now.
- Report: verdict, top 3 hardest things (per the heatmap), missing
  equivalents, and the recommended first slice.
