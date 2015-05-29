<?php
require_once("fSQL.php");

function db() {
    $db = new fSQLEnvironment;

    $db->define_db("database","../database.db");
    $db->select_db("database");

    return $db;
}
?>