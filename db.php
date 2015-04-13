<?php
//A db engine written in pure php
define( "ROOT_PATH", $_SERVER['DOCUMENT_ROOT']);
define( "DB_ROOT", $_SERVER['DOCUMENT_ROOT'] . "db/");

class Table {

}

/* 
 * Creates the table object given by $name
 * Returns null if the table already exist
 */
function db_create_table($name,$fields) {
	if(db_test_table($name)) {
		return null;
	}
	mkdir(DB_ROOT . $name);
	
}

/* 
 * Gets the table object given by $name
 * Returns null if the table doesn't exist
 */
function db_get_table($name) {
	if(!db_test_table($name)) {
		return null;
	}
}

/*
 * Tests to see if the table given by $name exists
 */
function db_test_table($name) {
	return file_exists(DB_ROOT . $name);
}

?>
<html>
	<head>
		<title>PHP DB Control Panel</title>
	</head>
	<body>
		<?php
		echo "HI " . (db_test_table("db.php") ? "TRUE" : "FALSE") . "<br/>";
		db_create_table("test",["hi1","hi2"]);
		?>
	</body>
</html>
