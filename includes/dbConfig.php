<?php
	if(preg_match("/dbConfig.php/", $_SERVER["PHP_SELF"])) {
		header("location: ../index.php" );
		exit;
	}
	
	$conn = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD) or die ("Gagal membuka database!");
	$db_select = mysql_select_db(DB_DATABASE);
		
	function closeDb($con) {
		mysql_close($con);
	}
	
	function queryDb($query,$con="") {
		global $conn;
		
		$con=($con=="")?$conn:$con;
		$result = @mysql_query($query,$con);
		return $result;
		closeDb($con);
	}
?>