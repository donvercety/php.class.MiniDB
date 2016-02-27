# php.class.MiniDB v1.0

Version 1.0

### Get Access to lib
```php
require("lib/DB.php");
```

### Configuration
```php
// configure connection
DB::settings([
    "host" => "localhost",
    "db"   => "test",
    "user" => "test",
    "pass" => "qwerty"
]);
```

### Get Instance
```php
// get class instance
$db = DB::getInstance();
```

## C.R.U.D.

### Create
```php
// insert single row to table
$db->insert('table_name', [
	'name'   => 'user',
	'pass'   => 'qwerty',
	'msisdn' => "0882204604",
	'city'   => 'Sofia',
	'code'   => 1000
]);

// insert multiple rows to table
$db->insertMultiple("table_name", ["name", "pass", "msisdn", "city", "code"], [
	["mark", "test21", "0883304504", "London", 4312],
	["pesho", "best123", "0883304504", "Varna", 1421],
	["tosho", "ytrewq", "0883304504", "Sofia", 1618],
	["silvester", "markus", "0883304504", "New York", 5454]
]);

// affected rows
$count = $db->count();
```

### Read
```php
// return full table content
$db->get("table_name");

// return filtered results
$db->get('table_name', [
    'name', '=', "pesho"
]);

// get query results
$results = $db->results();
```

### Update
```php
// update by id
$db->update('table_name', 3, [
    'name' => 'user_new',
    'pass' => 'qwerty_again'
]);

$count = $db->count();

// update by custom field, update where "name" = "tosho"
$db->update('table_name', ["name", "=" ,"tosho"], [
    'name' => 'atanas',
    'pass' => 'megatanas'
]);

$count = $db->count();
```

### Delete
```php
// delete table entry
$db->delete('table_name', [
    'name', '=', "silvester"
]);

$count = $db->count();
```

### Extras
```php
// get query results
$db->results();

// get first row
$db->first();

// check for errors
$db->error();

// get affected rows
$db->count();

// get last inserted id
$db->getLastInsertId();

// make a direct query, for those complex ones :)
$db->query("SELECT * FROM table_name");

// ADVANCED: get direct access to the PDO objecct
$db->pdo();
```

### Good Practice
```php
// use exception handling when doing queries
try {
	$db->delete('table_name', [
	    'name', '=', "silvester"
	]);
	$count = $db->count();

} catch (PDOException $ex) {
	var_dump($ex);
}
```
