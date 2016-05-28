<?php

require("lib/DB.php");

// configure mysql connection
DB::settings([
    "host" => "localhost",
    "db"   => "test",
    "user" => "test",
    "pass" => "qwerty"
]);

// configure sqlite connection
DB::settings([
    "db"   => "test.sqlite"
], "sqlite");

// get class instance
$db = DB::getInstance();

var_dump($db);