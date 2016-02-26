<?php

require("lib/DB.php");

// configure connection
DB::settings([
    "host" => "localhost",
    "db"   => "test",
    "user" => "test",
    "pass" => "qwerty"
]);

// get class instance
$db = DB::getInstance();
