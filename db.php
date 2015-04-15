<?php
//A db engine written in pure php
define( "DB_ROOT", $_SERVER['DOCUMENT_ROOT'] . "db/");

class Row {
	public $index = null;
	public $table = null;

	public function __construct() {}

	/*
	 * Writes the contents of this row to the files
	 */
	public function write() {
		$fp = fopen(DB_ROOT . $this->table->name . "/" . $this->index . "/lockfile","r+");
		flock($fp,LOCK_EX);

		foreach($this->table->fields as $field) {
			file_put_contents(DB_ROOT . $this->table->name . "/" . $this->index . "/" . $field,$this->$field,LOCK_EX);
			chmod(DB_ROOT . $this->table->name . "/" . $this->index . "/" . $field,0664);
		}

		flock($fp,LOCK_UN);	
		fclose($fp);
	}

	/*
	 * Reads the contents of the row from the files
	 */
	public function read() {
		$fp = fopen(DB_ROOT . $this->table->name . "/" . $this->index . "/lockfile","r+");
		flock($fp,LOCK_EX);
		
		foreach($this->table->fields as $field) {
			$this->$field = rtrim(file_get_contents(DB_ROOT . $this->table->name . "/" . $this->index . "/" . $field,LOCK_EX));
		}

		flock($fp,LOCK_UN);	
		fclose($fp);
	}
}

class Table {
	public $name = null;
	public $fields = null;

	public function __construct() {}

	/*
	 * Write the schema out to the file
	 */
	public function writeSchema() {
		file_put_contents(DB_ROOT . $this->name . "/schema",implode("\n",$this->fields),LOCK_EX);
		chmod(DB_ROOT . $this->name . "/schema",0664);
	}

	/*
	 * Resets the index counter
	 * Does not delete all records, only resets the counter.
	 */
	public function resetCounter() {
		file_put_contents(DB_ROOT . $this->name . "/count","0",LOCK_EX);
		chmod(DB_ROOT . $this->name . "/count",0664);
	}

	/*
	 * Creates a new row in this table
	 * Increments the counter and creates the directory.
	 */
	public function createRow() {
		$fp = fopen(DB_ROOT . $this->name . "/count","r+");
		flock($fp,LOCK_EX);
		$count = (int)fread($fp,10);

		mkdir(DB_ROOT . $this->name . "/" . $count);
		chmod(DB_ROOT . $this->name . "/" . $count,0775);

		file_put_contents(DB_ROOT . $this->name . "/" . $count . "/lockfile","",LOCK_EX);
		chmod(DB_ROOT . $this->name . "/" . $count . "/lockfile",0664);

		$row = new Row();
		$row->table = $this;
		$row->index = $count;

		foreach($this->fields as $field) {
			$row->$field = null;
		}
		
		ftruncate($fp,0);
		rewind($fp);
		fwrite($fp,(string)($count + 1));
		flock($fp,LOCK_UN);	
		fclose($fp);
		return $row;
	}

	/*
	 * Gets all the rows in this table
	 */
	public function getRows() {
		$fp = fopen(DB_ROOT . $this->name . "/count","r+");
		flock($fp,LOCK_EX);
		$count = (int)fread($fp,10);

		$rows = [];

		for($i = 0;$i < $count;$i++) {
			if(!file_exists(DB_ROOT . $this->name . "/" . $i)) {
				continue;
			}

			$row = new Row();
			$row->table = $this;
			$row->index = $i;
			$row->read();
			
			array_push($rows,$row);
		}

		flock($fp,LOCK_UN);	
		fclose($fp);
		return $rows;
	}
		
	/*
	 * Tests to see if the table given by $name exists
	 */
	public static function exists($name) {
		return file_exists(DB_ROOT . $name);
	}

	/* 
	 * Creates the table object given by $name
	 * The field names are given by $fields
	 * Returns null if the table already exist
	 */
	public static function create($name,$fields) {
		if(self::exists($name)) {
			return false;
		}
		mkdir(DB_ROOT . $name);
		chmod(DB_ROOT . $name,0775);

		$table = new Table();
		$table->name = $name;
		$table->fields = $fields;
		$table->writeSchema();
		$table->resetCounter();

		return $table;
	}

	/* 
	 * Gets the table object given by $name
	 * Returns null if the table doesn't exist
	 */
	public static function open($name) {
		if(!self::exists($name)) {
			return false;
		}

		$table = new Table();
		$table->name = $name;
		$table->fields = explode("\n",rtrim(file_get_contents(DB_ROOT . $name . "/schema",LOCK_EX)));

		return $table;
	}
}
?>
