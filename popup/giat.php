<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$a=queryDb("select u.id_unit1,u.id_unit2,u.id_unit3 from t_user s 
						inner join t_entitas_unit e on e.id_entitas=s.id_entitas
						inner join t_unit3 u on u.id_unit1=e.id_unit1 and u.id_unit2=e.id_unit2 and u.id_unit3=e.id_unit3 and concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3)='".$_SESSION['unit']."'
					where s.id_user='".$_SESSION['user']."' limit 1");
	
	$_UNIT=mysql_fetch_array($a);
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time()) {
		session_destroy();
		
		header("location:index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else if(!isset($_UNIT['id_unit3'])) {		
		header("location:../pilih_unit.php?direct=".bs64_e($_SERVER['REQUEST_URI']));
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
	$a=queryDb("select concat(d.id_giat,'.',d.id_detail) as id,concat('[',d.id_giat,'.',d.id_detail,'] ',d.nama) as nm from t_detail d
							inner join t_anggaran a on a.id_donatur=d.id_donatur and a.id_giat=d.id_giat and a.id_program=d.id_program and a.id_detail=d.id_detail and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3)='".$_SESSION['unit']."'
							inner join t_program p on p.id_donatur=d.id_donatur and p.id_program=d.id_program and p.expired>='".balikTanggal($_GT['v'])."'
						where concat('[',d.id_giat,'.',d.id_detail,'] ',d.nama) like '%".$_GT['s']."%' and p.id_donatur='".$_GT['q']."' and p.id_program='".$_GT['r']."'
						group by id,nm 
						order by id limit 50");
						
	if(!mysql_num_rows($a)) echo "<div class='err'>Data tidak ditemukan</div>";
	
	while($b=mysql_fetch_array($a)) {
	?>
		<ul><li onclick="<?php echo "sendText('".$b['id']."','".$b['nm']."')"; ?>"><?php echo $b['nm']; ?></li></ul>
<?php } ?>
</div>
<script language="javascript">
	parent.hideFade('imgGiat');
	parent.showFade('fGiat');
	
	function sendText(a,b) {
		parent.elm('iGiat').value=a;
		parent.elm('nGiat').value=b;
		setTimeout("parent.hideFade('fGiat');",100);
	}
</script>
</body>
</html>
<?php
	}
?>
