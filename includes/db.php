<?php
include "../php/php-sql.php";

function query($query) {
    $db = new fSqlEnvironment;
    $db->define_db("db","../databases/database.data");
    $db->select_db("db");

    $con = $db->query($query) or die($db->error());
    return $con;
    $db->free_result($con);
}
?>