# Feasibility Rubric

Structures the analysis; it does not decide it. Scores and coverage
figures organize evidence so a reader can see its shape — the verdict
follows from the cited findings, never from a number crossing a line.
Always show the underlying evidence next to the number.

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
`missing`. The sum ranks modules against each other — it orders the
roadmap and calibrates effort; it is not an input to the verdict. A
low-scoring module can still be un-migratable if its one dependency is
missing, and a high-scoring one can be fine if every gap has a cheap
handling.

## The worth-it test (break-even)

Difficulty says what the migration costs; this says whether it buys
anything — and against what. A migration is justified only if it beats
BOTH non-migration baselines on the driver's own terms. Name and argue
both explicitly; they are different options, not one:

- **Do-nothing baseline** — keep the current stack exactly as-is. Price
  it honestly: the driver stays unserved, and the census's mounting costs
  keep accruing (EOL/security exposure, shrinking hiring pool, license
  fees, the scaling ceiling the operational census found). Do-nothing is
  a real option with a real price — the verdict must price it, not assume
  it is free.
- **Improve-in-place baseline** — the best targeted change to the
  EXISTING system that could serve the driver: fix the hot path, extract
  the slow module, adopt the one missing library, incremental framework
  adoption, a bounded refactor. Ground it in what the census actually
  found broken or missing, and give it a rough magnitude. This is usually
  the strongest competitor to a migration — argue against it specifically,
  not against a strawman. If a baseline cannot serve the driver at all
  (a hard constraint — licensing, platform mandate, a partner requirement
  the current stack can never meet), say so with the evidence; that
  removes it as a competitor rather than ignoring it.

Then answer, before the verdict:

1. **What does the driver gain, concretely?** Name the benefit in the
   driver's own unit: "SSR fixes the SEO/TTFB problem on these 12 routes",
   "hiring pool: N× more candidates locally", "removes the license fee".
   "More modern" is not a unit. If the benefit can't be named, the verdict
   is NO-GO by default.
2. **What does each baseline deliver for the driver, at what cost?** The
   do-nothing price and the improve-in-place option, both argued above —
   restated here as the comparison the verdict actually makes.
3. **Where is break-even?** Benefit is usually per-month (velocity, infra
   cost, conversions); cost is one-time (heatmap total) plus any new
   running cost the target adds (e.g. SPA-on-CDN → server runtime you now
   operate). State roughly when cumulative benefit passes cost — "never"
   or "years out, driver may not survive that long" is a NO-GO signal even
   when the migration is technically easy.

Write the three answers into the verdict section — they are the difference
between "we can migrate", "we should", and "here is the cheaper way to get
what you actually want".

## Verdict rules

Compute and REPORT first — these are evidence, not thresholds:

- **Dependency coverage** = full / (full+partial+missing), weighted by
  call-site count (a missing lib used in 2 places ≠ missing ORM). Report
  the figure and the list behind it so the reader sees the shape of the
  gap. No percentage gates the verdict: what matters is WHICH entries are
  missing and whether each has an acceptable handling.
- **Blockers** = missing equivalents with no acceptable option, semantic
  gaps with no test-or-handle plan, or a driver the target provably does
  not serve (e.g. the perf driver, unbenchmarked).

Then reason from the evidence:

- **GO** — no blockers; every census gap has a handling; the worth-it
  test shows migration beating BOTH baselines for this driver; the
  heatmap has a viable low-risk first slice; cost magnitude proportionate
  to the driver.
- **PARTIAL** — blockers are confined to specific modules: migrate the
  rest, bridge or keep the blocked parts (name them and the bridge).
  Also the right verdict when only a subsystem serves the driver.
- **NO-GO** — blockers touch the core, or a baseline wins: name WHICH one
  (do-nothing or improve-in-place) and the alternative it represents —
  targeted refactor, optimization of the hot path, extracting one
  service, staying on the current stack, or a different target stack —
  with the evidence that it serves the driver better.

## Magnitude estimate

Per module group, S / M / L / XL relative to a normal feature in this repo
(not hours — agents estimating hours is theater). State the calibration
plan: **the first migrated slice is the estimate validator** — pick a
low-risk representative module, migrate it, compare actual vs estimated,
re-rate the rest of the heatmap before committing to the full roadmap.

## Honesty clauses

- If the coverage figure and the evidence disagree — high coverage but the
  driver unserved, low coverage but the missing piece irrelevant to the
  driver — say so and explain. No score or percentage overrides a finding,
  in either direction.
- A verdict that would flip if you re-ran the arithmetic but not if you
  re-read the evidence is a failed verdict: argue from the findings.
- Never let the driver's urgency inflate the verdict; the verdict protects
  the driver by being true.
- "The team wants to" is a valid driver — but record it as the driver, so
  the verdict is honest about what it optimizes.
