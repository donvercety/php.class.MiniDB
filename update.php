<?php

require_once("init.php");

// update by id
$db->update('table_name', 3, [
    'name' => 'user_new',
    'pass' => 'qwerty_again'
]);

var_dump($db->count());

// update by custom field, update where "name" = "tosho"
$db->update('table_name', ["name", "=" ,"tosho"], [
    'name' => 'atanas',
    'pass' => 'megatanas'
]);

var_dump($db->count());