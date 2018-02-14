<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time()) {
		session_destroy();
		
		header("location:index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title></title>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="list" style="border:1px solid #dddddd;background:#ffffff;" align="center">
<?php 
	$a=queryDb("select d.id_siswa,concat('[',d.id_siswa,'] ',d.nama) as nm from t_siswa d
						where d.id_kelas='".$_GT['v']."' and concat('[',d.id_siswa,'] ',d.nama) like '%".$_GT['s']."%'
						group by d.id_siswa,nm 
						order by d.id_siswa limit 50");
						
	//if(!mysql_num_rows($a)) echo "<div class='err'>Data tidak ditemukan</div>";
	
	while($b=mysql_fetch_array($a)) {
	?>
		<ul><li onclick="<?php echo "sendText('".$b['id_siswa']."','".$b['nm']."')"; ?>"><?php echo $b['nm']; ?></li></ul>
<?php } ?>
</div>
<script language="javascript">
	parent.hideFade('imgSiswa');
	parent.<?=(mysql_num_rows($a))?"showFade('fSiswa')":"hideFade('fSiswa')"?>;
	
	function sendText(a,b) {
		parent.elm('iSiswa').value=a;
		parent.elm('nSiswa').value=b;
		setTimeout("parent.hideFade('fSiswa');",100);
	}
</script>
</body>
</html>
<?php
	}
?>
