# Feasibility Rubric

Turns the censuses into a defensible verdict and a difficulty heatmap.
Scores guide judgment; they do not replace it — always show the underlying
evidence next to the number.

## Per-module difficulty heatmap

Score each module 0–2 on five factors; sum = difficulty 0–10:

| Factor | 0 | 1 | 2 |
|--------|---|---|---|
| Size & complexity | small, linear | moderate | large / dense logic |
| Dependency density | stdlib only | few libs, shallow use | woven-in libs / partial-or-missing equivalents |
| Platform coupling | none | isolated behind seams | relies on source runtime semantics (`file:line` list) |
| Test coverage (inverted) | good seams + tests | patchy | none — parity harness must be built first |
| Churn | frozen | occasional | actively changing (moving target — coordinate or freeze) |

Output: table ranked hardest-first, each row with its worst factor named.
The **"what's hard" answer** = the top rows + every census entry marked
`missing`.

## Verdict rules

Compute first:

- **Dependency coverage** = full / (full+partial+missing), weighted by
  call-site count (a missing lib used in 2 places ≠ missing ORM).
- **Blockers** = missing equivalents with no acceptable option, semantic
  gaps with no test-or-handle plan, or a driver the target provably does
  not serve (e.g. the perf driver, unbenchmarked).

Then:

- **GO** — no blockers; weighted coverage ≥ ~80%; heatmap has a viable
  low-risk first slice; cost magnitude proportionate to the driver.
- **PARTIAL** — blockers are confined to specific modules: migrate the
  rest, bridge or keep the blocked parts (name them and the bridge).
  Also the right verdict when only a subsystem serves the driver.
- **NO-GO** — blockers touch the core, or the driver is served cheaper
  another way. A NO-GO must name the alternative: targeted refactor,
  optimization of the hot path, extracting one service, or a different
  target stack — with the evidence that it serves the driver.

## Magnitude estimate

Per module group, S / M / L / XL relative to a normal feature in this repo
(not hours — agents estimating hours is theater). State the calibration
plan: **the first migrated slice is the estimate validator** — pick a
low-risk representative module, migrate it, compare actual vs estimated,
re-rate the rest of the heatmap before committing to the full roadmap.

## Honesty clauses

- If coverage math and your gut disagree, say so and explain — the rubric
  is evidence-shaped, not oracle-shaped.
- Never let the driver's urgency inflate the verdict; the verdict protects
  the driver by being true.
- "The team wants to" is a valid driver — but record it as the driver, so
  the verdict is honest about what it optimizes.
