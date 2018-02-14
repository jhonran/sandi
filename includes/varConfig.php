<?php
	// ***************************************************
	// File varConfig.php
	// File ini akan diperlukan oleh file-file php lainnya
	// ***************************************************
	
	if(preg_match("/varConfig.php/",$_SERVER['PHP_SELF'])) {
		header("location:../index.php");
		die;
	}
	
	date_default_timezone_set("Asia/Jakarta");
	
	$array_bln=array("Jan","Feb","Mar","Apr","Mei","Jun","Jul","Ags","Sep","Okt","Nov","Des");
	$array_bulan=array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");

	//---- konfigurasi untuk database
	define('DB_HOST','localhost',true);
	define('DB_USER','root',true);
	define('DB_PASSWORD','',true);
	define('DB_DATABASE','db_sandi',true);
	
	
	if(file_exists("config.inc")) $_CONF=explode("\n",implode("",file("config.inc")));
	else if(file_exists("../config.inc")) $_CONF=explode("\n",implode("",file("../config.inc")));
	
	define('INSTANSI',$_CONF[0],true);
	define('NPWP',$_CONF[1],true);
	define('KOTA',$_CONF[2],true);
	
	define('TANGGAL',date("Y-m-d H:i:s"),true);
	define('TANGGAL2',date("Y-m-d"),true);
	define("VALDOMAIN","/^([-0-9A-Z_]+\.)+([0-9A-Z]){2,4}$/i",true);
	define('VALHURUF',"/^[A-Za-z]+$/i",true);
	define('VALCHAR',"/^[A-Za-z0-9\.\_\@-]+$/i",true);
	define('VALCHAR2',"/^[A-Za-z0-9-]+$/i",true);
	define('VALCHAR3',"/^[A-Za-z0-9\_\ -]+$/i",true);
	
	define('PRDSUSUT','bulan',true);
?>