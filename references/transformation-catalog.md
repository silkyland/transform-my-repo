# Transformation Catalog

The three transformation types, each with the extra checks it adds on top
of the core workflow. Combinations multiply risk — recommend sequencing
(one transformation, stabilize, then the next), and say so in the verdict.

## Type A — Language / stack port (PHP→Python, Rust→Go, Assembly→C#, …)

The trap: assuming semantic equivalence. Extra checks:

- **Runtime model:** request-per-process vs long-running worker (globals,
  static state, connection reuse suddenly persist!), threads vs event loop
  vs goroutines, GC vs manual/ownership. Find every place the code relies
  on the source model (`file:line`).
- **Type discipline:** dynamic→static surfaces every implicit coercion as a
  decision; static→dynamic silently removes checks the code depended on.
- **Numeric & string semantics:** integer overflow behavior, float
  precision, decimal/money handling, string encoding, locale/collation.
- **Error idioms:** exceptions vs result/error returns — a mechanical
  translation usually loses error information or swallows cases.
- **Ecosystem idiom:** a port that fights the target's idiom (Go written
  like Rust) fails code review forever — note the idiom shifts the port
  requires, they are effort too.
- **Low-level ports** (Assembly/C → managed): identify hardware access,
  timing assumptions, pointer arithmetic, interrupt/syscall use — each is
  either a supported mechanism in the target (cite it) or a design change.

## Type B — Standalone → as-a-service (SaaS)

The trap: thinking it is a deployment change. It is a data-model and
security change. Extra checks:

- **Multi-tenancy model — the central decision:** shared schema with
  tenant_id vs schema-per-tenant vs instance-per-tenant. Census every
  query/cache/file path that assumes "there is only one customer"
  (`file:line`) — that list IS the migration.
- **Tenant isolation:** every read/write must be provably tenant-scoped;
  one missed query is a cross-tenant data leak. Plan the enforcement seam
  (middleware, RLS, repository layer), not per-call vigilance.
- **AuthN/AuthZ:** local users → org/member/role model, SSO expectations,
  API tokens.
- **Config & state:** installation-specific config files, local
  filesystem writes, cron assumptions → per-tenant config, object storage,
  scheduled jobs with tenant fan-out.
- **Operations you now own:** upgrades without downtime, backups
  per-tenant, noisy-neighbor limits, metering/billing hooks, support
  access. These are new subsystems — they go on the roadmap explicitly.

## Type C — Vanilla → framework

The trap: adopting the framework's letter but not its inversion of
control — ending up with two architectures forever. Extra checks:

- **Ownership inversion census:** who currently owns routing, lifecycle,
  DB access, config, rendering? Each moves from "your code calls it" to
  "the framework calls you" — list every entry point that must be
  restructured (`file:line`).
- **Framework ground truth:** verify (vendor source / versioned docs) that
  the framework actually blesses the patterns you plan to keep — escape
  hatches for the parts that won't fit its conventions.
- **The half-adopted trap:** define the DONE criterion — e.g. "no direct
  `mysqli_query` outside the repository layer" — else the strangler never
  finishes strangling and the repo carries both styles permanently.
- Migration order: framework's outer shell first (routing/bootstrap), then
  pull modules in one by one behind the same URLs, parity-tested.

## Sequencing combined transformations

"PHP standalone → Python SaaS" is Type A + Type B. Doing both in one motion
means a parity harness can't isolate which change broke behavior. Recommend:
port first with identical behavior (parity-testable), SaaS-ify second (its
changes are then deliberate, not accidental) — or the reverse if the SaaS
driver is urgent and the source stack can carry multi-tenancy. Justify the
chosen order against the driver in the verdict.
