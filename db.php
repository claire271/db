<?php
//A db engine written in pure php
define( "ROOT_PATH", $_SERVER['DOCUMENT_ROOT']);
define( "DB_ROOT", $_SERVER['DOCUMENT_ROOT'] . "db/");

class Table {
	public $name = null;
	public $fields = null;

	public function __construct() {}

	/*
	 * Write the schema out to the file
	 * Always writes, even if there are no changes.
	 */
	public function writeSchema() {
		file_put_contents(DB_ROOT . $this->name . "/schema.txt",implode("\n",$this->fields));
		chmod(DB_ROOT . $this->name . "/schema.txt",0664);
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
		$table->fields = explode("\n",file_get_contents(DB_ROOT . $name . "/schema.txt"));

		return $table;
	}
}




?>
<html>
	<head>
		<title>PHP DB Control Panel</title>
	</head>
	<body>
		<pre>
			<?php
			$table = Table::open("test");
			print_r($table);
			
			?>
		</pre>
	</body>
</html>
