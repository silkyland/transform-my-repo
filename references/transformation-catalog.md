# Transformation Catalog

Eleven transformation types, each with its trap, its extra checks on top of
the core workflow, and a **feasibility profile** (the signals that usually
decide the verdict). Combinations multiply risk — recommend sequencing (one
transformation, stabilize, then the next), and say so in the verdict.

## Contents

- Type A — Language / stack port
- Type B — Standalone → as-a-service (SaaS)
- Type C — Vanilla → framework
- Type D — Framework → framework
- Type E — Major version upgrade
- Type F — Monolith ↔ microservices
- Type G — Database / storage migration
- Type H — Hosting / runtime re-platforming (cloud, containers, serverless)
- Type I — Sync → async / event-driven
- Type J — API paradigm shift (REST → GraphQL / gRPC)
- Type K — UI platform shift (desktop ↔ web ↔ mobile)
- Sequencing combined transformations

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

**Feasibility profile:** GO when platform coupling is shallow, test
coverage gives cheap parity, and the driver is structural (hiring,
ecosystem, licensing). NO-GO signals: missing equivalents in the core
census, a purely aesthetic driver, or a hot-path performance driver with no
spike benchmark proving the target actually delivers it.

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

**Feasibility profile:** almost always L/XL — the verdict hinges on the
driver being revenue/business-model (then even XL can be worth it), and on
how many "only one customer" assumptions the census finds. PARTIAL is
common: SaaS-ify the product core, keep admin/tooling single-tenant.
NO-GO signal: isolation cannot be enforced at a seam (query patterns too
scattered) without a rewrite the business timeline can't absorb.

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

**Feasibility profile:** usually GO — it is incremental by nature (shell
first, then module by module) and each step ships. The risk is not
feasibility but completion: without the DONE criterion it converges on
two-architectures-forever, which is worse than not starting. NO-GO signal:
the framework's conventions fight the domain (heavy escape-hatch count in
the ground-truth check).

## Type D — Framework → framework (Vue→Next.js, React SPA→Next.js, Angular→Svelte, …)

The trap: "same language, so it's mostly renaming." The language survives;
the *model* doesn't. Extra checks:

- **Rendering model shift — usually the real migration:** CSR SPA →
  SSR/SSG/RSC changes *where* code runs. Census every browser-only
  assumption: `window`/`document` access at module scope, client-side data
  fetching in lifecycle hooks, auth kept in localStorage, code that assumes
  it runs exactly once per session (`file:line` each). Hydration
  mismatches and server/client boundaries are the top bug source — every
  component gets classified: server-safe / client-only / needs-splitting.
- **Component & reactivity model translation:** Vue reactivity
  (refs/computed/watchers) vs React hooks vs signals are NOT mechanical
  rewrites — census the patterns in use (watchers with side effects,
  v-model two-way binding, slots vs children/render-props, provide/inject
  vs context) and name the target idiom for each.
- **Routing & data paradigm:** file-based vs config routing, loaders/server
  functions vs client fetching, middleware/guards translation. Route
  inventory with per-route rendering strategy (static/SSR/client) is part
  of the census.
- **Ecosystem overlap is NOT coverage:** same npm registry, different
  compatibility — UI libraries, plugins, and state managers are often
  framework-bound (a Vue component library has zero React coverage).
  Run the full Step 2 census; do not skip it because "it's all JavaScript".
- **State management:** global stores (Vuex/Pinia/Redux) may need
  re-architecting when the server renders — what state is per-request vs
  per-client vs shared?
- **Build/deploy model:** SPA-on-CDN → a Node/edge runtime you now operate
  (or a platform bill). This lands in the worth-it test, not a footnote.
- **Incremental path:** page-by-page coexistence behind a proxy/rewrites
  (both apps serve the same domain during migration) is usually viable —
  verify the target supports it (cite docs) and make it the default
  strategy over a big-bang rewrite of all routes.

**Feasibility profile:** GO when the page-by-page coexistence path exists
and the worth-it test names concrete routes/metrics that benefit (SEO,
TTFB, DX). NO-GO signals: single first-party client with no rendering-model
need (the benefit column is all vibes), or a component-library dependency
with no target-side equivalent and no budget to rebuild it.

## Type E — Major version upgrade (Angular.js→Angular, Vue 2→3, Python 2→3, PHP 5→8)

The trap: treating it as "change the number" — a major with a broken
ecosystem is a port in disguise (Python 2→3 took the industry a decade).
Extra checks:

- **Official migration path:** does the vendor ship a guide, codemods, or a
  compat layer (e.g. `@vue/compat`)? Cite it and its coverage limits.
- **Removed-API surface:** grep the deprecation list against the codebase —
  the hit count with `file:line` IS the effort estimate's backbone.
- **Ecosystem readiness census:** the framework may be ready while its
  plugins are not — every plugin/theme/extension checked for a
  target-version release (registry citation; unmaintained = missing).
- **Coexistence:** can old and new run in one build during migration, or is
  it atomic? Atomic majors need a frozen branch strategy.

**Feasibility profile:** usually GO — vendors pave this road, and staying
on an EOL major is itself a mounting cost (security patches stop; count
that in the do-nothing baseline). NO-GO signals: compat layer absent AND
removed-API hit count is huge, or a load-bearing plugin is dead on the new
major with no replacement.

## Type F — Monolith ↔ microservices

The trap (→ micro): the distributed monolith — same coupling, now with
network failures and no refunds. The reverse direction (consolidating
microservices back into a monolith) is cheaper and often the honest
recommendation. Extra checks:

- **Seam census by data ownership:** which entities/tables can have exactly
  ONE owning service? Entities that everyone writes (`file:line` of the
  writers) are where decomposition dies.
- **Transaction census:** every cross-boundary transaction becomes a
  saga/outbox/compensation — list them; each is a design task, not a refactor.
- **Operational maturity baseline:** microservices multiply deploys,
  observability, and on-call. If CI/CD and tracing aren't already solid,
  that infrastructure goes on the roadmap BEFORE service #1.
- **Extract-one-first:** the calibrator phase is one well-bounded, hot
  service (by the heatmap) — never a decomposition big-bang.

**Feasibility profile:** PARTIAL is the usual honest verdict (extract 1–3
services with real independent-scaling or team-boundary needs; keep the
rest a modular monolith). NO-GO for full decomposition when transactions
are pervasive or the team is smaller than the service count would demand.
The worth-it test kills most "for scale" drivers: no measured scaling
bottleneck = no benefit unit = NO-GO by default.

## Type G — Database / storage migration (MySQL→Postgres, SQL→NoSQL, self-hosted→managed, single→sharded)

The trap: "SQL is SQL." Dialects differ, and the schema plus every access
pattern were designed for the source engine's strengths. Extra checks:

- **Query census:** dialect-specific SQL (functions, upserts, locking,
  collations) with `file:line`; ORM-mediated queries are usually portable —
  raw SQL is where the work lives.
- **Feature census:** triggers, stored procedures, full-text search, JSON
  operators, extensions (PostGIS!) — each mapped to a target equivalent
  with citation, or flagged missing.
- **Data migration math:** volume × allowable downtime decides the
  mechanism — dump/restore window, dual-write, or CDC replication
  (verify the CDC tooling exists for this pair; cite it).
- **SQL→NoSQL is a redesign, not a migration:** model by access patterns,
  not by translating tables. Ad-hoc queries and reporting lose their home —
  where do they go (warehouse? kept relational?)?
- **Naming & schema hygiene (only if the driver includes cleanup):** cryptic
  names (`tbl_usr_x2`, `col_flg_1`), inconsistent conventions, and denormalized
  legacy junk are worth fixing — but treat it as *modernization*, sequenced
  AFTER a parity-faithful move, via **expand-contract** (add the new
  name/shape → backfill → dual-write → switch readers → drop the old), never a
  rename at cutover. Every rename goes in a **rename map** that the data
  migration plan consumes to map old rows to the new shape. Renaming and
  moving data in one destructive step is how a migration loses rows nobody can
  get back.
- **Dirty-data census:** the target's stricter types/constraints will reject
  data the source tolerated — bad encodings, orphaned FKs, duplicates, nulls
  where the new schema forbids them. Census the offender classes (`file:line`
  of the query or column) and decide clean / quarantine / reject per class;
  this feeds the backfill step and the reconciliation gate.

**Feasibility profile:** same-paradigm engine swaps behind an ORM are
usually GO and mostly S/M. Zero-downtime requirements push magnitude up one
size (dual-run + verification). SQL→NoSQL is frequently NO-GO when ad-hoc
querying/reporting is load-bearing — the honest alternative is fixing the
specific bottleneck (indexes, read replicas, cache) on the relational base.

## Type H — Hosting / runtime re-platforming (on-prem→cloud, VM→containers/K8s, →serverless)

The trap: lift-and-shift billed as transformation (nothing improves, bill
changes), or serverless adopted for workloads that hate it. Extra checks:

- **State census:** local disk writes, in-memory sessions, sticky-instance
  assumptions, background daemons — each with `file:line`; statelessness is
  the entry ticket to containers and serverless both.
- **Execution-profile fit (serverless):** duration limits, cold starts vs
  the latency driver, connection pooling to the DB — check against the
  platform's documented limits (cite them, versions change).
- **Cost model projection:** always-on vs per-invocation vs cluster
  baseline — this goes straight into the worth-it break-even, both
  directions (serverless can 10× a steady load's cost, or /10 a spiky one).
- **New ops you adopt:** K8s is its own product to run; managed platforms
  trade control for toil — name the delta explicitly.

**Feasibility profile:** containerization of a stateless service is the
easiest GO in this catalog (S/M). Serverless: GO for spiky, event-shaped,
short-duration work; NO-GO for latency-critical always-hot paths or
long-running jobs unless platform features (provisioned concurrency, etc.)
are verified AND priced into the break-even.

## Type I — Sync → async / event-driven

The trap: the same coupling, now with queues — plus eventual consistency
surprises shipped to end users. Extra checks:

- **"Needs the answer now" census:** every call site where the caller
  genuinely requires the result to proceed (`file:line`) — those cannot
  simply become fire-and-forget; they need a different UX or a sync core.
- **Idempotency and ordering:** per consumer — can it survive duplicates
  and reordering? Every "no" is a design task (keys, dedup store, FIFO cost).
- **Failure design:** retries, dead-letter queues, poison messages, and who
  gets paged — an event dropped silently is worse than a 500.
- **Read-your-writes:** UI spots that expect immediate visibility of a
  write (`file:line`) — eventual consistency there is a user-facing bug.
- **Observability:** tracing across hops BEFORE migrating, or debugging
  becomes archaeology.

**Feasibility profile:** GO for naturally async work (email, exports,
webhooks, imports) — typically PARTIAL: async the background 20%, keep the
interactive core synchronous. NO-GO as a blanket rearchitecture when most
flows need immediate results; the worth-it test should compare against
"just add a job queue for the slow endpoints".

## Type J — API paradigm shift (REST → GraphQL / gRPC)

The trap: GraphQL's flexibility bills later — N+1 resolvers, per-field
authorization, caching that no longer rides on HTTP semantics. gRPC:
browsers don't speak it natively. Extra checks:

- **Client census:** who consumes the API and can they adopt the new
  paradigm (browser clients + gRPC = a gateway/proxy layer — count it).
- **Data-loading plan:** resolver batching (dataloader pattern) designed up
  front, not patched after the N+1 fire.
- **AuthZ granularity:** REST authorizes routes; GraphQL must authorize
  fields, gRPC methods — census current authz and map the translation.
- **Coexistence:** run the new paradigm as a facade over existing services
  first; big-bang endpoint cutovers break clients you forgot existed.

**Feasibility profile:** GO when many heterogeneous clients over/under-fetch
(the benefit has a unit: payload sizes, request counts, client velocity).
NO-GO when there is one first-party client — fixing the specific endpoints
is 10× cheaper than a paradigm migration.

## Type K — UI platform shift (desktop→web, native↔cross-platform, web→mobile)

The trap: estimating the UI rewrite while the real cost hides in business
logic entangled with the UI toolkit, and in platform capabilities the
target doesn't have. Extra checks:

- **Logic/UI separation census:** what fraction of code imports the UI
  toolkit? Entangled logic must be extracted FIRST (that extraction is its
  own phase, testable on the old platform — do it before any new UI).
- **Platform capability census:** filesystem access, offline operation,
  hardware (serial, printers, sensors), background execution, OS
  integration — each mapped to a target-platform mechanism (cite docs) or
  flagged as a redesign/loss the user must sign off on.
- **UX paradigm:** window/menu/keyboard density vs responsive/touch — a 1:1
  screen translation satisfies nobody; scope the redesign honestly.
- **Distribution & update model:** installers/auto-update vs URL vs app
  store review — changes release engineering, goes on the roadmap.

**Feasibility profile:** GO when core logic is separable or already behind
an API — the shift is then mostly a new frontend (M/L). NO-GO signals:
offline-first + local hardware access dominate on a web target, or the
"desktop app" is mostly UI over a local file format (that's a product
redesign, not a migration — say so).

## Sequencing combined transformations

"PHP standalone → Python SaaS" is Type A + Type B. Doing both in one motion
means a parity harness can't isolate which change broke behavior. Recommend:
port first with identical behavior (parity-testable), SaaS-ify second (its
changes are then deliberate, not accidental) — or the reverse if the SaaS
driver is urgent and the source stack can carry multi-tenancy. Justify the
chosen order against the driver in the verdict.
