<?php
include "includes/db.php";

if(isset($_POST['query'])) {
    query($_POST['query']);
    echo "<font color='green'>You query was executed!</font>";
}
?>
<form action="sql.php" method="post">
    <textarea cols="30" rows="20" name="query"></textarea><br>
    <input type="submit" value="Query">
</form>