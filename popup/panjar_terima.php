<?php
	session_start();
	
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
	$a=queryDb("select p.id_panjar,p.no_trans,p.nm_pegawai,p.tgl_expired,p.nominal,p.keterangan from t_panjar p
							inner join t_glu u on u.id_gl4=p.id_gl4 and concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3)='".$_SESSION['unit']."'
						where p.no_trans2<>'' and p.no_trans3='' and concat(p.no_trans,' ',p.nm_pegawai,' ',p.nominal,' ',p.keterangan) like '%".$_GT['s']."%' and concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3)<>concat(p.id_unit1,'.',p.id_unit2,'.',p.id_unit3) and p.id_gl4='".$_GT['v']."'
						group by p.id_panjar,p.no_trans,p.nm_pegawai,p.tgl_expired,p.nominal,p.keterangan
						order by p.tgl_trans limit 50");
						
	if(!mysql_num_rows($a)) echo "<div class='err'>Data tidak ditemukan</div>";
	
	while($b=mysql_fetch_array($a)) {
	?>
		<ul><li onclick="<?="sendText('".$b['id_panjar']."','".$b['no_trans']."','".$b['nm_pegawai']."','".str_replace("-","/",balikTanggal($b['tgl_expired']))."','".showRupiah2($b['nominal'])."','".$b['keterangan']."')"?>">
						<?=$b['no_trans']." || ".$b['nm_pegawai']." || ".str_replace("-","/",balikTanggal($b['tgl_expired']))." || ".showRupiah2($b['nominal'])?></li></ul>
<?php } ?>
</div>
<script language="javascript">
	parent.hideFade('imgPanjar');
	parent.showFade('fPanjar');
	
	function sendText(a,b,c,d,e,f) {
		parent.elm('iPanjar').value=a;
		parent.elm('nPanjar').value=b;
		parent.elm('tPegawai').value=c;
		parent.elm('tExpired').value=d;
		parent.elm('tNominal').value=e;
		parent.elm('tDesc').value=f;
		setTimeout("parent.hideFade('fPanjar');",100);
	}
</script>
</body>
</html>
<?php
	}
?>
