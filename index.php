<?php
include "includes/db.php";

$aon = query("SELECT * FROM members WHERE session=:session",array(
    ":session" => $_COOKIE['session']
));

if(!isset($_COOKIE['session']) || $aon->rowCount() == 0) {
    header("Location: /menu");
    die("");
}
?>
<html>
<head>
    <title>Lord Of Realms</title>
    <style>
        body {
            margin:0;
        }
    </style>
    <script type="text/javascript" src="libs/crafty-min.js"></script>
    <script src="libs/create_mocks_module.js"></script>
    <script src="libs/tiledmapbuilder.js"></script>
    <script type="text/javascript" src="game.js"></script>
</head>
<body>

</body>
</html>