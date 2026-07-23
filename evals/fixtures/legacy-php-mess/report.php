<?php
// Second file with its own inline SQL — proof the data access is scattered,
// not centralized. The rename map must cover every table touched across BOTH
// files, and the data migration plan must map all of them to the new shape.

$link = mysqli_connect('localhost', 'root', '', 'shop');

// Same cryptic tables, different file — this is why a repository layer is the
// modernization target.
$res = mysqli_query($link, "SELECT nm1, eml, flg1 FROM tbl_usr_x2");
$rows = array();
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
}
echo count($rows) . " users\n";
