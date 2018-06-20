+<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Installation Processor</title>

<script>
window.onpageshow = function(evt) {
    // If persisted then it is in the page cache, force a reload of the page.
    if (evt.persisted) {
        document.body.style.display = "none";
        location.reload();
    }
};
</script>

</head>
<body>
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

require_once __DIR__.'/sql_parse.php';

//Process and load form data
$servername = $_POST["servername"];
$rootuser = $_POST["rootuser"];
$rootpass = $_POST["rootpass"];
$databasename = $_POST["database"];
$dbuser = $rootuser;//$_POST["dbuser"];
$dbpass1 = $rootpass;//$_POST["dbpass1"];
$dbpass2 = $rootpass;//$_POST["dbpass2"];
$adminuser = $_POST["adminuser"];
$adminpass1 = $_POST["adminpass1"];
$adminpass2 = $_POST["adminpass2"];
$action = $_POST["selectaction"];
$adminnamefirst = $_POST["adminnamefirst"];
$adminnamelast = $_POST["adminnamelast"];
$adminemail = $_POST["adminemail"];

//Create the MD5 hash value for the admin password
$adminhash = md5($adminpass1);

//-----------------Do some validation---------
$validerror ='';
//Validate DB password
echo "Validating Entries...";
flush();

if ($dbpass1 != $dbpass2)
	{
		$validerror .= "<br><strong>Your Database passwords do not match.</strong>";
	}

//Validate admin password
if ($adminpass1 != $adminpass2) {
		$validerror .= "<br><strong>Your Administrator account passwords do not match.</strong>";
	}

echo "Success!<br>";
flush();

//Validate DB connectivity
echo "Checking DB connectivity...";
flush();
$mysqli = new mysqli($servername,$rootuser,$rootpass);

if (mysqli_connect_errno())
{
$validerror .= "<br><strong>Cannot connect the the database using the supplied information.</strong>";
}
echo "Success!<br>";
flush();

//Validate that the config directories are writable
echo "Checking config folder permissions...";
flush();
if (!is_writable(dirname('../../includes/functions.php')))
{
$validerror .= "<br><strong>Cannot write the configuration files. Please check the /includes/ folder permissions. See the RPints Installation page on www.raspberrypints.com.</strong>";
}

if (!is_writable(dirname('../../admin/includes/checklogin.php')))
{
$validerror .= "<br><strong>Cannot write the configuration files. Please check the /admin/includes/ folder permissions. See the RPints Installation page on www.raspberrypints.com.</strong>";
}
//$mysqli->close();
echo "Success!<br>";
flush();

//##TODO## Check if administrator account already exists



//Display errors and die
if ($validerror !='') 
	{
		echo "<html><body>";
		echo $validerror;
		echo "<br /><br />Please press the back button on your browser to fix these errors";
		echo "</body></html>";
		die();
	}
// CLEAR INSTALLATION DATA ROUTINES
if ($action == 'remove')
{
	echo "Deleting " . $databasename . " database...";
	flush();
	//$mysqli = new mysqli($servername,$rootuser,$rootpass);
	// Check connection

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . $mysqli->connect_error();
	}

	$sql = "DROP database " . $databasename . ";";
	$result = $mysqli->query($sql);
	//$mysqli->close();
	echo "Success!<br>";
	flush();
	
	echo "Removing configuration files...";
	flush();
	try {
    	if (file_exists('../../includes/config.php'))      unlink('../../includes/config.php');
    	if (file_exists('../../admin/includes/conn.php'))  unlink('../../admin/includes/conn.php');
    	//if (file_exists('../../includes/configp.php'))   unlink('../../admin/includes/configp.php');
	} catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	
	//unlink('../../includes/config.php');
	//unlink('../../admin/includes/conn.php');
	//unlink('../../admin/includes/configp.php');

	echo "Success!<br>";
	flush();
}
	
if ($action == 'install')
{
	
require_once __DIR__.'/config_files.php';
	
	//-----------------Create the main config file-----------------
	echo "Update config files...";
	flush();
	
	file_put_contents('../../includes/config.php', $mainconfigstring);

	echo "Success!<br>";
	flush();
	// -----------------Create the admin files----------------------
	echo "Update admin config files...";
	flush();

	file_put_contents('../../admin/includes/conn.php', $adminconfig1);
	//file_put_contents('../../admin/includes/configp.php', $adminconfig2);
	
	echo "Success!<br>";
	flush();

	//-----------------Create RPints Database----------------------
	echo "Creating RPints database...";
	flush();

	$sql = "DROP DATABASE `" . $databasename . "`;";
	$result = $mysqli->query($sql);
	$sql = "CREATE DATABASE `" . $databasename . "` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;";
	$result = $mysqli->query($sql);
	$sql = "USE `" . $databasename . "`;";
	$result = $mysqli->query($sql);
	//mysqli_close($con);
	echo "Success!<br>";
	flush();

	//-----------------Create RPints User--------------------------
	echo "Creating RPints database user...";
	flush();
	//$mysqli = new mysqli($servername,$rootuser,$rootpass);
	// Check connection

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . $mysqli->connect_error();
	}

	$sql = "GRANT ALL ON *.* TO '" . $dbuser . "'@'" . $servername . "' IDENTIFIED BY '" . $dbpass1 . "' WITH GRANT OPTION;";
	$result = $mysqli->query($sql);
	//$mysqli->close();
	echo "Success!<br>";
	flush();

	//-----------------Run The Schema File-------------------------
	echo "Running Database Script...";
	flush();
	$dbms_schema = "../../sql/schema.sql";

		
	$sql_query = @fread(@fopen($dbms_schema, 'r'), @filesize($dbms_schema)) or die('Cannot find SQL schema file. ');
	
	$sql_query = remove_remarks($sql_query);
	$sql_query = remove_comments($sql_query);
	$sql_query = split_sql_file($sql_query, ';');


	//$mysqli = new mysqli($servername,$rootuser,$rootpass) or die('error connection');

	$i=1;
	foreach($sql_query as $sql){
		if(rtrim($sql) == "") continue;
		//echo "	";
		//echo $sql;
		//echo "<br>";
		$mysqli->query($sql) or die('error in query '.$i.'['.substr($sql,0,80).'] ['.$mysqli->error.']');
		//echo "<br>";
		$i++;
	}

	//$mysqli->close();
	echo "Success!<br>";
	flush();

	//-----------------Add the admin user to the Users DB----------
	echo "Adding new admin user...";
	flush();
	////$mysqli = new mysqli($servername,$rootuser,$rootpass,"raspberrypints");
	// Check connection

	if (mysqli_connect_errno())
	{
	echo "Failed to connect to MySQL: " . $mysqli->connect_error();
	}
	$currentdate = Date('Y-m-d H:i:s');
	$sql = "INSERT INTO users (username, password, nameFirst, nameLast, email, active, isAdmin, createdDate, modifiedDate) VALUES ('" . $adminuser . "','" . $adminhash . "','" . $adminnamefirst . "','" . $adminnamelast . "','" . $adminemail . "', 1, 1,'" . $currentdate . "','" . $currentdate . "');";
	$result = $mysqli->query($sql);
	//$mysqli->close();
	echo "Success!<br>";
	flush();
	
	//-----------------Delete the index.html page, if it exists -----------------
	$index = '../../index.html';
	echo "Deleting default Apache index...";
	flush();
	if (file_exists($index)) {
		unlink($index);
		echo "Success! <br>";
	}	else {
		echo "Success! File already deleted <br>";
	}
	flush();
	
	//-----------------Load the sample data if requested-----------

		if(!empty($_POST['sampledata'])) 
		{
			echo "Adding sample data...";
			flush();
			
			$dbms_schema = "../../sql/test_data.sql";

		
			$sql_query = @fread(@fopen($dbms_schema, 'r'), @filesize($dbms_schema)) or die('Cannot find SQL schema file. ');
			
			$sql_query = remove_remarks($sql_query);
			$sql_query = remove_comments($sql_query);
			$sql_query = split_sql_file($sql_query, ';');


			////$mysqli = new mysqli($servername,$rootuser,$rootpass) or die('error connection');

			$i=1;
			foreach($sql_query as $sql){
				if(rtrim($sql) == "") continue;
				//echo "	";
				//echo $sql;
				//echo "<br>";
				$mysqli->query($sql) or die('error in query '.$i.'['.substr($sql,0,80).'] ['.$mysqli->error.']');
				//echo "<br>";
				$i++;
			}

			
			//$mysqli->close();
			echo "Success!<br>";
			flush();
		}
}
$mysqli->close();


if ($action != 'remove')
{
	##TODO## Add better error handling before showing the Success message
	echo '<br /><br /><br /><h3> Congratulations! Your Raspberry Pints has been setup successfully.<br />';
	echo 'Click for - <a href="../../index.php">Tap List</a><br />';
	echo 'Click for - <a href="../../admin/index.php">Administration </a><br />';
}

?>
</body>
</html>