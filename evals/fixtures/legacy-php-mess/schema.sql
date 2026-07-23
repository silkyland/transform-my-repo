-- Legacy schema — transform-my-repo eval fixture.
--
-- Answer key (renames): the target's naming convention wants clean names, so
-- the rename map should read roughly:
--   tbl_usr_x2      -> users
--   tbl_usr_x2.id   -> users.id
--   tbl_usr_x2.nm1  -> users.name
--   tbl_usr_x2.eml  -> users.email
--   tbl_usr_x2.flg1 -> users.is_active   (int 0/1 -> bool; legacy 2 = soft-deleted -> is_active=false)
--   ord_tbl         -> orders
--   ord_tbl.usr     -> orders.user_id
-- These renames are a MODERNIZATION phase done after a parity-faithful move,
-- via expand-contract — not a rename during cutover.

CREATE TABLE tbl_usr_x2 (
  id   INT PRIMARY KEY,
  nm1  VARCHAR(255),   -- name; some rows hold latin1-mojibake (encoding dirt)
  eml  VARCHAR(255),   -- email; some rows NULL, which the new schema forbids
  flg1 INT             -- 0/1/2 tri-state; the new schema wants a clean bool
);

CREATE TABLE ord_tbl (
  id  INT PRIMARY KEY,
  usr INT              -- FK to tbl_usr_x2.id, but NOT enforced — orphans exist
);

-- Dirty seed data — the data migration plan's dirty-data census must handle
-- each of these classes (clean / quarantine / reject), and the reconciliation
-- gate must account for any rows it drops or quarantines:
INSERT INTO tbl_usr_x2 (id, nm1, eml, flg1) VALUES
  (1, 'Ada Lovelace',   'ada@example.com', 1),
  (2, 'Grace Hopper',   NULL,              1),   -- NULL email: forbidden in target
  (3, 'Jos\xe9 Mojibake','jose@example.com',2),  -- bad encoding + flg1=2 (soft-deleted)
  (4, 'Dup Row',        'dup@example.com', 1),
  (4, 'Dup Row',        'dup@example.com', 1);    -- duplicate primary key: dedup needed

INSERT INTO ord_tbl (id, usr) VALUES
  (10, 1),
  (11, 999);   -- orphaned FK: no user 999 exists -> quarantine, don't silently drop
