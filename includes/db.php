<?php
function query($query, $array) {
    $dsn = "mysql:host=mysql4.worldplanethosting.com;dbname=alphaks_lordofrealms";
    try {
        $db = new PDO($dsn, "alphaks_admin", "pirate12");
    } catch(Exception $e) {
        die($e->getMessage());
    }

    $con = $db->query($query,$array);
    return $con;

    $db = null;
}
?>