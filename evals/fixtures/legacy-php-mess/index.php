<?php
// Legacy vanilla PHP — transform-my-repo eval fixture (modernize + data migration).
//
// Answer key (modernization): raw SQL is scattered inline across this file and
// report.php with no data-access layer, and it hits cryptic tables/columns
// (`tbl_usr_x2.flg1`). A transform-my-repo run must (a) recommend consolidating
// SQL behind a repository layer with a DONE criterion ("no direct mysqli_query
// outside the repository layer"), and (b) treat the renames as a SEPARATE,
// post-parity modernization phase via expand-contract — never a rename at
// cutover. See schema.sql for the bad names and the dirty seed data.

$link = mysqli_connect('localhost', 'root', '', 'shop');

// Inline SQL #1 — cryptic names, no layer.
$res = mysqli_query($link, "SELECT * FROM tbl_usr_x2 WHERE flg1 = 1");
while ($row = mysqli_fetch_assoc($res)) {
    // String-concatenated query — also a SQL-injection finding for the audit.
    $oid = $_GET['oid'];
    $o = mysqli_query($link, "SELECT * FROM ord_tbl WHERE usr = " . $row['id'] . " AND id = " . $oid);
    echo $row['nm1'] . "\n";
}
