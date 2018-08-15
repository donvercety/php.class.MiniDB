<?php

require_once("init.php");

try {

	// delete table entry
	$db->delete('table_name', [
	    'name', '=', "silvester"
	]);

	var_dump($db->count());

	$db->delete('table_name', 28);

	var_dump($db->count());

} catch (PDOException $ex) {
	var_dump($ex);
}
