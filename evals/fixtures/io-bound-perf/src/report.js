// Reporting endpoint — transform-my-repo eval fixture (NO-GO scenario).
//
// Answer key: the stated driver is "port to Rust for speed." But this hot
// path is I/O-BOUND — nearly all wall-clock time is spent awaiting the two
// sequential database round-trips below, not burning CPU. A language port
// cannot move a driver that a faster runtime doesn't serve; the do-nothing
// baseline (fix the N+1 query, add an index, parallelize the awaits, cache)
// buys the latency win at a fraction of the cost and risk.
//
// The correct transform-my-repo verdict is NO-GO, recommending those targeted
// optimizations instead of a rewrite. A GO verdict here is a worth-it-test
// failure: it migrated for a driver the migration doesn't actually serve.

async function buildReport(db, orgId) {
  // Round-trip 1: fetch the org (network wait).
  const org = await db.query('SELECT * FROM orgs WHERE id = $1', [orgId]);

  // Round-trip 2: N+1 — one query per member, all sequential (network wait).
  const members = [];
  for (const memberId of org.memberIds) {
    members.push(await db.query('SELECT * FROM users WHERE id = $1', [memberId]));
  }

  // The only CPU work: a trivial in-memory shape. Microseconds.
  return { org: org.name, memberCount: members.length };
}

module.exports = { buildReport };
