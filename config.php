<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'mis19018');
define('DB_PASSWORD', 'misp@ss');
define('DB_NAME', 'mis19018');
 

$link=mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD,DB_NAME)
	or die ('cannot connect to the database because: ' . mysqli_error());
	

	
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>
	