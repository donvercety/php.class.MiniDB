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
    private static $_instance = null;

    /**
     * Connection credentials
     * @var array
     */
    private static $_cfg = [
        "host" => null,
        "db"   => null,
        "user" => null,
        "pass" => null,
        "type" => null
    ];

    /**
     * Query specific
     * @var type
     */
    private $_pdo,
            $_quety,
            $_results,
            $_lastInsertId,
            $_error   = false,
            $_count   = 0,
            $_select  = '*',
            $_options = '';

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
			$this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->_pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    private function _reset() {
        $this->select('*');
        $this->options('');
    }

	private function _bindQuestions($values) {
		$x = 1; $bind = '';

		foreach ($values as $value) {
			$bind .= '?';
			if ($x < count($values)) {
				$bind .= ', ';
			}
			$x++;
		}
		return $bind;
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
    public static function settings($_cfg, $type = null) {
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
        $this->_error = false;
        $this->_quety = $this->_pdo->prepare("$sql {$this->_options}");

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

            } else {
                $this->_error = true;
            }

            $this->_reset();
        }
        return $this;
    }

    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
    // Secondary Methods
    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    public function action($action, $table, $where = array()) {
        if (count($where) === 3) {

			if (strtoupper($where[1]) == "IN" && is_array($where[2])) {
				$field  = $where[0];
				$values = $where[2];
				
				$bind = $this->_bindQuestions($values);
				
				$sql = "{$action} FROM `{$table}` WHERE {$field} IN ({$bind})";
				if (!$this->query($sql, $values)->error()) {
					return $this;
				}
			
			} else {
				$operators = array('=', '>', '<', '>=', '<=');

				$field    = $where[0];
				$operator = $where[1];
				$value    = $where[2];
			
				if (in_array($operator, $operators)) {
					$sql = "{$action} FROM `{$table}` WHERE {$field} {$operator} ?";
					if (!$this->query($sql, array($value))->error()) {
						return $this;
					}
				}
			}

        } else {
            $sql = "{$action} FROM `{$table}`";
            if (!$this->query($sql)->error()) {
                return $this;
            }
        }
        return false;
    }

    public function select($fields) {
        $this->_select = $fields;
        return $this;
    }

    public function options($options) {
        $this->_options = $options;
        return $this;
    }

    public function get($table, $where = array()) {
        return $this->action("SELECT {$this->_select}", $table, $where);
    }

    public function delete($table, $where) {
        if (is_array($where)) {
			return $this->action('DELETE', $table, $where);
		} 

		$id  = (int) $where;
		$sql = "DELETE FROM {$table} WHERE id = {$id}";

		if (!$this->query($sql)->error()) {
			return true;
		}

        return false;
    }

    public function insert($table, $fields = array()) {

        $keys = array_keys($fields);
        $bind = $this->_bindQuestions($fields);

        $sql = "INSERT INTO {$table} (`" . implode('`, `', $keys) . "`) VALUES ({$bind})";

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
