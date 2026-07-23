# Evals

Manual test scenarios for `/transform-my-repo`, per Anthropic's guidance to
build evaluations before writing extensive documentation. There is no
automated harness — run each scenario against its fixture and check the
result against [scenarios.md](scenarios.md).

transform-my-repo edits no source files (it writes only the assessment
document), so each fixture is a source repo with a known answer key written
into its code comments. Grading = comparing the verdict, dependency census,
and platform-coupling census against that key. Target-ecosystem facts are
still verified live; the keys assert only registry-stable outcomes.

## Fixtures

- `fixtures/flask-to-fastapi/` — a same-ecosystem framework port where every
  dependency is covered. Exercises the **GO** path and difficulty not being
  inflated. (Scenario 1)
- `fixtures/php-to-go/` — leans on Twig (no Go equivalent) and PHP
  per-request global state. Exercises the **missing-equivalent headline** and
  the **platform-coupling census** → PARTIAL/NO-GO. (Scenario 2)
- `fixtures/io-bound-perf/` — an I/O-bound hot path someone wants to port to
  Rust for speed. Exercises the **worth-it test against the do-nothing
  baseline** → NO-GO as a success. (Scenario 3)
