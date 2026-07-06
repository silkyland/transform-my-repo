# transform-my-repo

**Evidence-based feasibility and migration strategy for architecture
transformations — allowed to tell you "don't".**

`transform-my-repo` is an [agent skill](https://vercel.com/docs/agent-resources/skills)
for the big, scary changes: porting to another language (PHP→Python,
Rust→Go, Assembly→C#), re-platforming standalone software as a service, or
adopting a framework in a vanilla codebase. Migrations die from optimism —
the equivalent library that doesn't exist, the runtime behavior nobody knew
was load-bearing, the big-bang rewrite that never ships. This skill replaces
optimism with a census.

## What it does

```
/transform-my-repo PHP → Python, driver: team hiring, root: .
```

1. **Frame** — source → target, the *driver* (why migrate — the verdict is
   only meaningful relative to it), constraints, transformation type(s).
2. **Source census** — full inventory plus two migration-specific censuses:
   every dependency (with call-site depth) and every platform coupling
   (runtime semantics the code secretly relies on), all with `file:line`.
3. **Target ground truth** — every census entry verified in the target
   ecosystem *now* (registry/docs, URL + version). "Surely Python has an
   equivalent" is not evidence. Missing equivalents are the headline finding.
4. **Gap analysis + difficulty heatmap** — semantic gaps (typing,
   concurrency, numerics, error idioms) and a per-module difficulty ranking:
   the concrete answer to "what will be hard".
5. **Verdict** ⛔ — **GO / PARTIAL / NO-GO** with the evidence shown, then
   stops for the user's decision. A NO-GO names the cheaper alternative that
   serves the driver — and counts as a successful outcome.
6. **Strategy** — behavioral parity harness first (golden-master tests pin
   current behavior before anything moves), then a decided migration
   pattern (strangler fig by default; big-bang must be proven unavoidable),
   coexistence bridge, per-phase cutover and rollback.
7. **Assessment document** — `docs/TRANSFORM.md`: verdict, censuses, heatmap,
   strategy, and a phased roadmap where Phase 1 is a low-risk calibrator
   module and every phase is sized to be one
   [deep-plan](https://github.com/silkyland/deep-plan) run.

## Covered transformation types

| Type | The trap it guards against |
|------|---------------------------|
| Language/stack port | Assuming semantic equivalence (runtime model, typing, numerics, error idioms) |
| Standalone → as-a-service | Treating it as a deployment change when it's a data-model and security change (multi-tenancy, isolation, authn/z, ops you now own) |
| Vanilla → framework | Adopting the letter but not the inversion of control — two architectures forever |

Combined transformations (port **and** SaaS-ify) are sequenced, not stacked —
a parity harness can't isolate which change broke behavior when both move at once.

## The skill family

| Skill | Moment |
|-------|--------|
| [know-my-repo](https://github.com/silkyland/know-my-repo) | Day one: onboard onto a repo with zero knowledge |
| [deep-plan](https://github.com/silkyland/deep-plan) | Plan the next feature/refactor — evidence-gated, 7 phases |
| [deep-plan-ingest](https://github.com/silkyland/deep-plan) | Distill an accepted plan into living knowledge files |
| [clean-slate](https://github.com/silkyland/clean-slate) | Reset rotten knowledge files — backup-gated |
| **transform-my-repo** | Change the architecture itself: feasibility, strategy, migration roadmap |

Shared law: **no claim without evidence** — here on both sides of the
migration: source facts need `file:line`, target facts need a versioned
registry/docs citation fetched now.

## Install

```bash
npx skills add silkyland/transform-my-repo
```

Or copy this directory into your agent's skills folder
(e.g. `~/.claude/skills/transform-my-repo/`).

## Structure

```
transform-my-repo/
├── SKILL.md                              # 8-step workflow + the verdict gate
└── references/
    ├── transformation-catalog.md         # Type-specific checklists (port / SaaS / framework)
    ├── feasibility-rubric.md             # Heatmap scoring + GO/PARTIAL/NO-GO rules
    └── assessment-template.md            # docs/TRANSFORM.md structure
```

Follows the [Vercel skills](https://github.com/vercel-labs/skills) single-skill
layout and [Anthropic's skill authoring best practices](https://platform.claude.com/docs/en/agents-and-tools/agent-skills/best-practices).

## License

MIT
