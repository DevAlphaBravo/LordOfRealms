<?php
require_once("xmldb.php");


$f[0] = array(
    "name" => "id",
    "primarykey" => 1,
    "type" => "integer",
    "extra" => "autoincrement"
);

$f[1] = array(
    "name" => "username",
    "type" => "text"
);
$f[2] = array(
    "name" => "email",
    "type" => "text"
);
$f[3] = array(
    "name" => "password",
    "type" => "text"
);
$f[4] = array(
    "name" => "ip",
    "type" => "text"
);
$f[5] = array(
    "name" => "sprite",
    "type" => "text"
);
$f[6] = array(
    "name" => "x",
    "type" => "integer"
);
$f[7] = array(
    "name" => "y",
    "type" => "integer"
);
$f[8] = array(
    "name" => "mx",
    "type" => "integer"
);
$f[9] = array(
    "name" => "my",
    "type" => "integer"
);

$err = createxmldatabase("database","../");
echo $err."<br>";

$err = createxmltable("database","database",$f,"../");
echo $err."<br>";
?>