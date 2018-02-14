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
	$a=queryDb("select t.id_siswa_tagih,concat('[',t.id_siswa,'] ',t.nama_siswa) as nm,(t.nominal-t.total_bayar) as nominal,jb.nama as jenis,t.keterangan from t_siswa_tagih t
									inner join t_kelas j on j.id_kelas=t.id_kelas and concat(j.id_unit1,'.',j.id_unit2,'.',j.id_unit3)='".$_SESSION['unit']."'
									inner join t_jns_bayar_siswa jb on jb.id_jns_bayar_siswa=t.id_jns_bayar_siswa
								where concat('[',t.id_siswa,'] ',t.nama_siswa,' ',t.nominal,' ',jb.nama,' ',t.keterangan) like '%".$_GT['s']."%' and t.id_kelas='".$_GT['q']."' and t.nominal>t.total_bayar
								order by t.nama_siswa 
								limit 50");
						
	if(!mysql_num_rows($a)) echo "<div class='err'>Data tidak ditemukan</div>";
	
	while($b=mysql_fetch_array($a)) {
	?>
		<ul><li onclick="<?php echo "sendText('".$b['id_siswa_tagih']."','".$b['nm']."','".showRupiah2($b['nominal'])."','".$b['jenis']." - ".$b['keterangan']."')"; ?>"><?php echo $b['nm']." || ".showRupiah2($b['nominal'])." || ".$b['jenis']." || ".substr($b['keterangan'],0,40); ?>...</li></ul>
<?php } ?>
</div>
<script language="javascript">
	parent.hideFade('imgSiswa<?=$_GT['v']?>');
	parent.showFade('fSiswa<?=$_GT['v']?>');
	
	function sendText(a,b,c,d) {
		parent.elm('iSiswa<?=$_GT['v']?>').value=a;
		parent.elm('nSiswa<?=$_GT['v']?>').value=b;
		parent.elm('tNominal<?=$_GT['v']?>').value=c;
		parent.elm('tDesc<?=$_GT['v']?>').value=d;
		setTimeout("parent.hideFade('fSiswa<?=$_GT['v']?>');",100);
	}
</script>
</body>
</html>
<?php
	}
?>
