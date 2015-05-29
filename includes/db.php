<?php
function query($query, $array) {
    $dsn = "mysql:host=mysql4.worldplanethosting.com;dbname=alphaks_lordofrealms";
    try {
        $db = new PDO($dsn, "alphaks_admin", "pirate12");
    } catch(Exception $e) {
        die($e->getMessage());
    }

    try {
        $con = $db->query($query, $array) or die($db->errorCode());
    } catch(Exception $e) {
        die($e->getMessage());
    }
    return $con;

    $db = null;
}
?>