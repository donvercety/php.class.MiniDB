<?php

require_once("init.php");

// return full table content
$db->get("table_name");

var_dump($db->results());

// return filtered results
$db->get('table_name', [
    'name', '=', "pesho"
]);

var_dump($db->results());