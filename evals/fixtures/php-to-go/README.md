# php-to-go

A transform-my-repo eval fixture for a **PARTIAL / missing-equivalent**
verdict. A small PHP app that leans on two things a naive port would miss:

- **Missing equivalent:** Twig templating (`twig/twig`) has no drop-in Go
  counterpart — the census must mark it *missing* and list real options.
- **Platform coupling:** `$GLOBALS['request_count']` relies on PHP's
  request-per-process state reset; Go's long-lived process would persist and
  race it.

Expected behavior is in `../../scenarios.md` (Scenario 2).
