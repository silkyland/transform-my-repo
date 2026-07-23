# io-bound-perf

A transform-my-repo eval fixture for a **NO-GO** verdict — the worth-it test
against the do-nothing baseline. The driver is "port to Rust for speed," but
`src/report.js`'s hot path is I/O-bound (two sequential DB round-trips, an
N+1 loop, trivial CPU work). A faster language can't serve a latency driver
dominated by network waits; fixing the N+1, adding an index, or parallelizing
the awaits wins more at a fraction of the cost.

Expected behavior is in `../../scenarios.md` (Scenario 3).
