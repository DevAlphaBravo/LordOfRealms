<?php
$f = array();
function add($array) {
    $f[count($f)] = $array;

    return $f;
}
add(array(
    "name" => "id",
    "primarykey" => 1,
    "type" => "integer",
    "extra" => "autoincrement"
));
add(array(
    "name" => "username",
    "type" => "text"
));
add(array(
    "name" => "password",
    "type" => "text"
));
add(array(
    "name" => "ip",
    "type" => "text"
));
add(array(
    "name" => "sprite",
    "type" => "text"
));
add(array(
    "name" => "x",
    "type" => "integer"
));
add(array(
    "name" => "y",
    "type" => "integer"
));
add(array(
    "name" => "mx",
    "type" => "integer"
));
add(array(
    "name" => "my",
    "type" => "integer"
));
add(array(
    "name" => "map",
    "type" => "text"
));
add(array(
    "name" => "color",
    "defaultvalue" => "white",
    "type" => "text"
));

$err = createxmldatabase("database","../");
echo $err."<br>";
$err = createxmltable("database","members","../");
echo $err."<br>";


$aon = new XMLTable("database","members","../");
$aonv = array(
    "username" => "AdminBot",
    "password" => "apple420",
    "ip" => "unknown",
    "sprite" => "jeff",
    "x" => 250,
    "y" => 250,
    "mx" => 250,
    "my" => 250,
    "map" => "map-0",
    "color" => "gold"
);

$aon->InsertRecord($aonv);
echo "Databases Created! Default Stuff Enabled!";
?>