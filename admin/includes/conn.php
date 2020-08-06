<?php 
	//show/hide SQL statements in errors
	//$showSqlState = true;
  $host="localhost"; // Host name
  $username="RaspberryPints"; // Mysql username
  $password="RaspberryPints"; // Mysql password
  $db_name="raspberrypints"; // Database name
  $tbl_name="users";
	//Connect to server and select database.
	$mysqli = new mysqli("$host", "$username", "$password", "$db_name") or die("cannot connect to server");
?>
