<?php
	//session_start();
	// ***************************************************
	// File masterConfig.php
	// File ini akan diperlukan oleh file-file php lainnya
	// ***************************************************
	
	if(preg_match("/masterConfig.php/",$_SERVER['PHP_SELF'])) {
		header("location:../index.php");
		die;
	}
	
	function replaceQuote($v) {
		return  str_replace("\"","〞",str_replace("'","`",$v));
	}
	
	$_GT=array();
	$_PT=array();
	
	while(list($a,$b)=each($_GET)) { $_GT[$a]=trim(base64_decode(urldecode($_GET[$a]))); }
	
	while(list($a,$b)=each($_POST)) {
		if(is_array($_POST[$a])) {
			while(list($aa,$bb)=each($_POST[$a])) { $_PT[$a][$aa]=replaceQuote(trim(stripslashes($_POST[$a][$aa]))); }
		}
		else $_PT[$a]=replaceQuote(trim(stripslashes($_POST[$a]))); 
	}
		
	require "varConfig.php";
	require "dbConfig.php";			//Pemanggilan koneksi databases
	require "function.php";
	
	$_IST=array();
	$a=queryDb("select judul,page from t_menu_sub");
	while($b=mysql_fetch_array($a)) {
		$_IST[$b['page']]=$b['judul'];
	}
?>