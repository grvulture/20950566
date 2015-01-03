<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//include_once("helpers".DS."meekrodb.2.2.class.php");
//		parent::__construct($host, $user, $password, $dbName, $port, $encoding);

// moved away from mysqli, now using pdo...
Class birdyDB extends PDO {
	protected static $_instance;
	protected $_debugValues = null;
	protected $hasActiveTransaction = false;

	public function __construct() {
		$host		= birdyConfig::$db_host;
		$dbName		= birdyConfig::$db_name;
		$user		= birdyConfig::$db_user;
		$password	= birdyConfig::$db_password;
		$port		= birdyConfig::$db_port;
		$encoding	= birdyConfig::$db_encoding;
		
        /**
         * set the (optional) options of the PDO connection. in this case, we set the fetch mode to
         * "objects", which means all results will be objects, like this: $result->user_name !
         * For example, fetch mode FETCH_ASSOC would return results like this: $result["user_name] !
         * @see http://www.php.net/manual/en/pdostatement.fetch.php
         */
        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE =>  PDO::ERRMODE_EXCEPTION);

        /**
         * Generate a database connection, using the PDO connector
         * @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
         * Also important: We include the charset, as leaving it out seems to be a security issue:
         * @see http://wiki.hashphp.org/PDO_Tutorial_for_MySQL_Developers#Connecting_to_MySQL says:
         * "Adding the charset to the DSN is very important for security reasons,
         * most examples you'll see around leave it out. MAKE SURE TO INCLUDE THE CHARSET!"
         */
        parent::__construct('mysql' . ':host=' . $host . ';dbname=' . $dbName . ';charset=utf8', $user, $password, $options);
		self::$_instance = $this;
	}
	
	public static function getInstance() {
		return self::$_instance;
	}
	
	public function execute($values=array())
	{
		$this->_debugValues = $values;
		try {
		$t = parent::execute($values);
		// maybe do some logging here?
		} catch (PDOException $e) {
		$this->debug($query);
		throw $e;
		}

		return $t;
	}

	public function loadObjectlist($query,$array=array()) {
		$query = $this->prepare($query);
		$query->execute($array);
		return $query->fetchAll(PDO::FETCH_OBJ);
	}
	
	public function loadObject($query,$array=array()) {
		$query = $this->prepare($query);
		$query->execute($array);
		return $query->fetch(PDO::FETCH_OBJ);
	}
	
	public function loadAssoclist($query,$array=array()) {
		$query = $this->prepare($query);
		$query->execute($array);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function loadAssoc($query,$array=array()) {
		$query = $this->prepare($query);
		$query->execute($array);
		return $query->fetch(PDO::FETCH_ASSOC);
	}
	
	public function loadResult($query,$array=array()) {
		$query = $this->prepare($query);
		$query->execute($array);
		return $query->fetchColumn();
	}

	public function count($table) {
		$nRows = $this->query('select count(*) from '.$table)->fetchColumn(); 
		return $nRows;
	}
	
	public function delete($table,$array=array()) { // 'articles', array('title'=>$title, 'content'=>$content)
		foreach ($array as $field=>$value) {
			$fields[] = $field;
			$args[] = $field.'=:'.$field;
			$values[':'.$field] = $value;
		}
		$query = "DELETE FROM $table WHERE ".implode(" AND ",$args);
		$stmt = $this->prepare($query);
		$stmt->execute($values);
		return $stmt->rowCount();
	}
	
	public function insert($table,$array=array()) { // 'articles', array('title'=>$title, 'content'=>$content)
		foreach ($array as $field=>$value) {
			$fields[] = $field;
			$args[] = ':'.$field;
			$values[':'.$field] = $value;
		}
		$query = "INSERT INTO $table (".implode(",",$fields).") VALUES (".implode(",",$args).")";
		$stmt = $this->prepare($query);
		$stmt->execute($values);
		return $stmt->rowCount();
	}
	
	public function insertUpdate($table,$array=array(),$where) { // 'articles', array('title'=>$title, 'content'=>$content)
		foreach ($array as $field=>$value) {
			$fields[] = $field;
			$args[] = ':'.$field;
			$values[':'.$field] = $value;
			$update[] = $field.'=:'.$field;
		}
		foreach ($where as $field=>$value) {
			$whereargs[] = $field.'=:'.$field;
			$wherevalues[':'.$field] = $value;
		}
		$query = "SELECT ".$fields[0]." FROM ".$table." WHERE ".implode(" AND ",$whereargs);
		$stmt = $this->prepare($query);
		$stmt->execute($wherevalues);
		if ($stmt->rowCount()>0) 
			$query = "INSERT INTO $table (".implode(",",$fields).") VALUES (".implode(",",$args).")";
		else {
			$query = 'UPDATE '.$table.' SET '.implode(",",$update).' WHERE '.implode(" AND ",$whereargs);
			$values = array_merge_recursive($values,$wherevalues);
		}
		
		$stmt = $this->prepare($query);
		$stmt->execute($values);
		return $stmt->rowCount();
	}
	
	public function insertMultiple($query,$rows) { // "INSERT INTO Table (col1, col2, col3)" , array(array('abc', 'def', 'ghi'),array('abc', 'def', 'ghi'),array('abc', 'def', 'ghi'))
		if (count($rows)>0) {
			//echo "COUNT ROWS=".count($rows)." count rows[0]=".count($rows[0]);
			$args = array_fill(0, count($rows[0]), '?');

			$params = array();
			foreach($rows as $row)
			{
				$values[] = "(".implode(',', $args).")";
				foreach($row as $value)
				{
					$params[] = $value;
				}
			}

			//echo "<br />ROWS: <pre>";print_r($rows);echo "</pre>";
			$query = $query." VALUES ".implode(',', $values);
			//echo "<br />QUERY: $query";
			//echo "<br />PARAMS: <pre>";print_r($params);echo "</pre>";
			//exit;
			$stmt = $this->prepare($query);
			$stmt->execute($params);
			return $stmt->rowCount();
		}
		return false;
	}
	
	public function update($table,$set,$where,$args) {
		$query = 'UPDATE '.$table.' SET '.$set.' WHERE '.$where;
		$stmt = $this->prepare($query);
		$stmt->execute($args);
		return $stmt->rowCount();
	}
	
	public function beginTransaction () {
		if ( $this->hasActiveTransaction ) {
			return false;
		} else {
			$this->hasActiveTransaction = parent::beginTransaction ();
			return $this->hasActiveTransaction;
		}
	}

	public function commit () {
		parent::commit ();
		$this->hasActiveTransaction = false;
	}

	public function rollback () {
		parent::rollback ();
		$this->hasActiveTransaction = false;
	}

	public function debug($query) {
		echo "<pre>";var_dump( $query->queryString, $query->_debugQuery() );echo "</pre>";
	}
	
	private function _debugQuery($replaced=true)
	{
		$q = $this->queryString;

		if (!$replaced) {
		return $q;
		}

		return preg_replace_callback('/:([0-9a-z_]+)/i', array($this, '_debugReplace'), $q);
	}

	protected function _debugReplace($m)
	{
		$v = $this->_debugValues[$m[1]];
		if ($v === null) {
		return "NULL";
		}
		if (!is_numeric($v)) {
		$v = str_replace("'", "''", $v);
		}

		return "'". $v ."'";
	}

}
