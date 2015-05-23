<?php
include "../includes/db.php";

if(isset($_POST['query'])) {
    query($_POST['query']);
    echo "Query Successful!";
    die();
}
?>
<form action="sql.php" method="post">
    <textarea cols="30" rows="25" name="query"></textarea><br>
    <input type="submit" value="Query">
</form>