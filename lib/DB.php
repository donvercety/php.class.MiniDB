<?php

/**
 * Database Wrapper.
 * Implements C.R.U.D. functionality.
 *
 * @author Tommy Vercety
 */
class DB {
    
    /**
     * DB Connection Instance
     * @var object
     */
    private static $_instance = NULL;
    
    /**
     * Connection credentials
     * @var array
     */
    private static $_cfg = [    
        "host" => NULL,
        "db"   => NULL,
        "user" => NULL,
        "pass" => NULL,
        "type" => Null
    ];

    /**
     * Query specific
     * @var type 
     */
    private $_pdo,
            $_quety,
            $_results,
            $_lastInsertId,
            $_error  = FALSE,
            $_count  = 0;

    /**
     * Connection initialization
     */
    private function __construct() {
        $type = self::$_cfg["type"];
        $host = self::$_cfg['host'];
        $db   = self::$_cfg['db'];
        
        try {

            // sqlite connection
            if ($type === "sqlite") {
                if ($db === NULL) {
                    throw new PDOException('SQLite db file not specified!');
                }
                $this->_pdo = new PDO("sqlite:{$db}");

            // mysql connection
            } else {
                $this->_pdo = new PDO("mysql:host={$host};dbname={$db}", self::$_cfg['user'], self::$_cfg['pass']);
                $this->_pdo->exec("SET CHARACTER SET utf8");
            }

            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
    
    /**
     * Singleton instance manager
     * @return object
     */
    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new DB();
        }
        return self::$_instance;
    }
    
    /**
     * DB Connection Settings
     * @param array $cfg
     */
    public static function settings($_cfg, $type = NULL) {
        if (isset($_cfg["user"])) {
            self::$_cfg["user"] = $_cfg["user"];
        }

        if (isset($_cfg["pass"])) {
            self::$_cfg["pass"] = $_cfg["pass"];
        }

        if (isset($_cfg["host"])) {
            self::$_cfg["host"] = $_cfg["host"];
        }

        if (isset($_cfg["db"])) {
            self::$_cfg["db"] = $_cfg["db"];
        }

        if ($type === "sqlite") {
            self::$_cfg["type"] = $type;
        }
    }
    
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
    // Main Method
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 

    public function query($sql, $params = array()) {
        $this->_error = FALSE;
        $this->_quety = $this->_pdo->prepare($sql);

        if ($this->_quety) {
            $x = 1;
            if (count($params)) {
                foreach ($params as $param) {
                    $this->_quety->bindValue($x, $param);
                    $x++;
                }
            }

            if ($this->_quety->execute()) {

                // check to use fetchAll(), only on result sets
                if ($this->_quety->columnCount()) {
                    $this->_results  = $this->_quety->fetchAll(PDO::FETCH_OBJ);
                }

                $this->_count        = $this->_quety->rowCount();
                $this->_lastInsertId = $this->_pdo->lastInsertId();

            }
            else {
                $this->_error = TRUE;
            }
        }
        return $this;
    }
    
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
    // Secondary Methods
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
    
    public function action($action, $table, $where = array()) {
        if (count($where) === 3) {
            $operators = array('=', '>', '<', '>=', '<=');
            
            $field    = $where[0];
            $operator = $where[1];
            $value    = $where[2];

            if (in_array($operator, $operators)) {
                $sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";
                if (!$this->query($sql, array($value))->error()) {
                    return $this;
                }
            }

        } else {
            $sql = "{$action} FROM {$table}";
            if (!$this->query($sql)->error()) {
                return $this;
            }
        }
        return FALSE;
    }
    
    public function get($table, $where = array()) {
        return $this->action('SELECT *', $table, $where);
    }
    
    public function delete($table, $where) {
        return $this->action('DELETE', $table, $where);
    }
    
    public function insert($table, $fields = array()) {
        
        $keys = array_keys($fields);
        $values = '';
        $x = 1;
        
        foreach ($fields as $field) {
            $values .= '?';
            if ($x < count($fields)) {
                $values .= ', ';
            }
            $x++;
        }

        $sql = "INSERT INTO {$table} (`" . implode('`, `', $keys) . "`) VALUES ({$values})";
              
        if (!$this->query($sql, $fields)->error()) {
            return TRUE;
        }

        return FALSE;
    }
    
    public function insertMultiple($table, $fields = array(), $values = array()) {
        $bind = ''; $x = 1;

        foreach ($fields as $field) {
            $bind .= '?';
            if ($x < count($fields)) {
                $bind .= ', ';
            }
            $x++;
        }

        $sql = "INSERT INTO {$table} (`" . implode('`, `', $fields) . "`) VALUES ({$bind})";

        for ($i = 1, $len = count($values); $i < $len; $i++) {
            $sql .= ", ({$bind})";
        }

        $valuesArray = array();

        foreach($values as $value) {
            foreach ($value as $data) {
                $valuesArray[] = $data;
            }
        }

        if (!$this->query($sql, $valuesArray)->error()) {
            return TRUE;
        }

        return FALSE;
    }

    public function update($table, $id, $fields) {
        $set = ''; $x = 1;
        
        foreach ($fields as $name => $value) {
            $set .= "{$name} = ?";
            if ($x < count($fields)) {
                $set .= ', ';
            }
            $x++;
        }
        
        if (is_array($id)) {
            $operators = array('=', '>', '<', '>=', '<=');

            $field    = $id[0];
            $operator = $id[1];
            $value    = $id[2];

            if (in_array($operator, $operators)) {
                $sql = "UPDATE {$table} SET {$set} WHERE {$field} {$operator} ?";

                array_push($fields, $value);

                if (!$this->query($sql, $fields)->error()) {
                    return TRUE;
                }
            }

        } else {
            $id  = (int) $id;
            $sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";

            if (!$this->query($sql, $fields)->error()) {
                return TRUE;
            }
        }
        
        return FALSE;
    }

    public function pdo() {
        return $this->_pdo;
    }

    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
    // Getters
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 

    public function results() {
        return $this->_results;
    }
    
    public function first() {
        return $this->results() ? $this->results()[0] : FALSE;
    }
    
    public function error() {
        return $this->_error;
    }
    
    public function count() {
        return $this->_count;
    }
    
    public function getLastInsertId() {
        return $this->_lastInsertId;
    }
    
}