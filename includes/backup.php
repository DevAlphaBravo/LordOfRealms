<?php
$f = fopen("../database/members.php","r") or die("Unable to open file!");
echo fread($f,filesize("../database/members.php"));
fclose($f);
?>