<?php
	error_reporting(0);
	//session_start();
	
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
	$a=queryDb("select concat(r.id_unit1,'.',r.id_unit2,'.',r.id_unit3,'.',r.id_ruangan) as id_ruang,concat('[',r.id_unit1,'.',r.id_unit2,'.',r.id_unit3,'.',r.id_ruangan,'] ', r.nama) as nm from t_ruangan r 
								inner join t_user s on s.id_user='".$_SESSION['user']."'
								inner join t_entitas_unit e on e.id_entitas=s.id_entitas and e.id_unit1=r.id_unit1 and e.id_unit2=r.id_unit2 and e.id_unit3=r.id_unit3
						where concat('[',r.id_unit1,'.',r.id_unit2,'.',r.id_unit3,'.',r.id_ruangan,'] ', r.nama) like '%".$_GT['s']."%'
						group by r.id_unit1,r.id_unit2,r.id_unit3,r.id_ruangan,r.nama
						order by r.id_unit1,r.id_unit2,r.id_unit3,r.id_ruangan limit 50");
						
	if(!mysql_num_rows($a)) echo "<div class='err'>Data tidak ditemukan</div>";
	
	while($b=mysql_fetch_array($a)) {
	?>
		<ul><li onclick="<?php echo "sendText('".$b['id_ruang']."','".$b['nm']."')"; ?>"><?php echo $b['nm']; ?></li></ul>
<?php } ?>
</div>
<script language="javascript">
	parent.hideFade('imgRuang');
	parent.showFade('fRuang');
	
	function sendText(a,b) {
		parent.elm('iRuang').value=a;
		parent.elm('nRuang').value=b;
		setTimeout("parent.hideFade('fRuang');",100);
	}
</script>
</body>
</html>
<?php
	}
?>
