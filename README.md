# php.class.MiniDB v1.2

Version 1.2

> New functionality in v1.1  
> methods: `select()` & `options()`  
> must be invoked BEFORE! the main  
> query constructor methods:  
> `insert`, `insertMultiple`, `get`, `update`, `delete`
> New functionality in v1.2
> `delete()` can now work directly with `id` parameter,
> the same way `update()` can.

### Get Access to lib
```php
require("lib/DB.php");
```

### Configuration
```php
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

// insert single row to table, with on duplicate key clause
$db->options('ON DUPLICATE KEY UPDATE id = id')->insert('table_name', [
	'id'     => 1,
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

// select only some fields
$db->select('id')->get("table_name");

// return limited table content
$db->options('LIMIT 1')->get("table_name");

// combine the methods
$db->select('id')->options('LIMIT 1')->get("table_name");

// return filtered results
$db->get('table_name', [
    'name', '=', "pesho"
]);

$db->get('table_name', [
    'name', 'IN', ["pesho", "gosho"]
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
// delete by id
$db->delete('table_name', 3);

// delete table entry
$db->delete('table_name', [
    'name', '=', "silvester"
]);

$count = $db->count();
```

### Extras
```php
// select fields from table, use before `get()`
$db->select('id');

// query options: LIMIT.., ON DUPLICATE KEY.., ORDER BY..
$db->options();

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

// EXAMPLES:
$db->pdo()->quote(); // exactly equivalent to mysql_real_escape_string()
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
