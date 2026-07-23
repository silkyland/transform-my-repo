# Eval Scenarios

Four scenarios, following Anthropic's skill-eval format (query + fixture +
expected_behavior). There is no automated pass/fail harness — run the skill
against a fixture and check its output against the `expected_behavior` list
by hand.

transform-my-repo writes only an assessment document (it edits no source
files), so each fixture is a source repo with a known answer key baked into
its code comments — grading is comparing the verdict and censuses against
that key. Target-ecosystem facts must still be verified live (registry/docs);
the keys below name only outcomes that are stable regardless of registry drift.

## Scenario 1 — same-ecosystem port, everything covered → GO

- **Fixture:** `fixtures/flask-to-fastapi/`
- **Query:** `/transform-my-repo Flask → FastAPI — root: ., driver: async throughput`
- **Expected behavior:**
  - Dependency census finds direct target equivalents (flask → fastapi on
    PyPI; requests keeps working or → httpx) — **zero missing** entries.
  - The one real semantic gap is named: sync handlers → async (`app.py`, the
    `/rate` outbound call), handled by making handlers `async` with httpx.
  - Verdict is **GO**, with difficulty not inflated — a same-language
    framework swap is not scored as an XL rewrite.

## Scenario 2 — missing equivalent + platform coupling → PARTIAL/NO-GO

- **Fixture:** `fixtures/php-to-go/`
- **Query:** `/transform-my-repo PHP → Go — root: ., driver: single static binary deploys`
- **Expected behavior:**
  - The census marks **Twig** (`composer.json`) a **missing** equivalent —
    no drop-in Go port — and lists real options (rewrite views in
    `html/template`, PHP render sidecar, third-party engine). It must not
    claim "surely Go has a Twig equivalent."
  - The platform-coupling census flags `$GLOBALS['request_count']`
    (`index.php`) as PHP request-per-process state that Go's long-lived
    process would persist and race — cited at file:line.
  - Verdict is **PARTIAL or NO-GO** with those two as headline findings, not
    a blanket GO.

## Scenario 3 — driver not served by the migration → NO-GO

- **Fixture:** `fixtures/io-bound-perf/`
- **Query:** `/transform-my-repo Node → Rust — root: ., driver: lower request latency`
- **Expected behavior:**
  - The analysis recognizes `src/report.js`'s hot path is **I/O-bound**
    (two sequential DB round-trips + an N+1 loop; trivial CPU work).
  - The worth-it test against the do-nothing baseline concludes a language
    port won't move a latency driver dominated by network waits.
  - Verdict is **NO-GO**, recommending the cheaper alternative (fix the N+1,
    add an index, parallelize the awaits, cache) — and this is scored as a
    successful outcome, not a failure to migrate.

## Scenario 4 — legacy mess: modernization + data migration plan

- **Fixture:** `fixtures/legacy-php-mess/`
- **Query:** `/transform-my-repo Vanilla PHP → a framework with a clean schema — root: ., driver: maintainability`
- **Expected behavior:**
  - Recommends consolidating the scattered inline SQL (`index.php`,
    `report.php`) behind a **repository layer**, with a DONE criterion (e.g.
    "no direct `mysqli_query` outside the repository layer").
  - Flags the string-concatenated query in `index.php` as a SQL-injection
    finding in the audit.
  - Treats the cryptic → clean renames (`tbl_usr_x2` → `users`, `flg1` →
    `is_active`, …) as a **separate post-parity modernization phase** using
    **expand-contract**, backed by a **rename map** — never a rename at
    cutover, never folded into the port.
  - Produces a **data migration plan** (assessment §8): field-level mapping
    for every renamed/retyped column, a **dirty-data census** with a decision
    per class (NULL email, mojibake, `flg1=2` soft-delete, duplicate PK,
    orphaned FK), a move mechanism, and a **reconciliation gate** (row
    counts/checksums/integrity) that must pass before the old store is
    decommissioned — not a bare "migrate the data" bullet.
