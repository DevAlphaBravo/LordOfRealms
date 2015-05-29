<?php
function pg_connection_string_from_database_url() {
    extract(parse_url($_ENV["DATABASE_URL"]));
    return "user=$user password=$pass host=$host dbname=" . substr($path, 1); # <- you may want to add sslmode=require there too
}

$db = pg_connect(pg_connection_string_from_database_url());

function query($query) {
    $a = pg_query($query) or die("Error");
    return $a;
}
?>