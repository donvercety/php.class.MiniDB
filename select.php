<?php

require_once("init.php");

// return full table content
$db->select('name, city, code')->options('LIMIT 3')->get("table_name");

var_dump($db->results());

$db->get('table_name', [
    'name', '=', "pesho"
]);

var_dump($db->results());
