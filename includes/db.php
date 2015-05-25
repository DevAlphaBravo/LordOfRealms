<?php
function query($query, $array) {
    $dsn = "pgsql:"
        . "ec2-107-21-114-132.compute-1.amazonaws.com"
        . "dbname=d5m37u6amrua7j;"
        . "user=bwvztvafhoqrrp;"
        . "port=5432;"
        . "sslmode=require;"
        . "password=-HfsV65qYWwZpb3M20mQM-J6e7";

    $db = new PDO($dsn);

    $con = $db->query($query,$array);
    return $con;

    $db = null;
}
?>