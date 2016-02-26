<?php

require_once("init.php");

// insert single row to table
$db->insert('table_name', [
	'name'   => 'user',
	'pass'   => 'qwerty',
	'msisdn' => "0882204604",
	'city'  => 'Sofia',
	'code'   => 1000
]);

var_dump($db->count());

// insert multiple rows to table
$db->insertMultiple("table_name", ["name", "pass", "msisdn", "city", "code"], [
	["mark", "test21", "0883304504", "London", 4312],
	["pesho", "best123", "0883304504", "Varna", 1421],
	["tosho", "ytrewq", "0883304504", "Sofia", 1618],
	["silvester", "markus", "0883304504", "New York", 5454]
]);

var_dump($db->count());