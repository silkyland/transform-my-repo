# legacy-php-mess

A transform-my-repo eval fixture for the **modernization + data migration**
path. Vanilla legacy PHP with:

- **Scattered SQL, no data layer:** raw `mysqli_query` inline across
  `index.php` and `report.php` (one query is string-concatenated → also a
  SQL-injection audit finding).
- **Cryptic naming:** `tbl_usr_x2.flg1`, `ord_tbl.usr` — see `schema.sql` for
  the intended rename map.
- **Dirty data:** NULL emails, latin1 mojibake, a tri-state flag, a duplicate
  primary key, and an orphaned foreign key.

Expected behavior is in `../../scenarios.md` (Scenario 4): consolidate SQL
behind a repository layer with a DONE criterion, sequence the renames as a
post-parity modernization phase via expand-contract, and produce a data
migration plan (field mapping + dirty-data decisions + reconciliation gate) —
not a bare "migrate the data" line.
