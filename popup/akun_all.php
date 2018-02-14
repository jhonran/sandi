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
	$a=queryDb("select g.id_gl4,concat('[',g.id_gl4,'] ',g.nama) as nm from t_gl4 g
						where concat('[',g.id_gl4,'] ',g.nama) like '%".$_GT['s']."%' ".(($_GT['q'])?("and (g.id_gl4 like '".str_replace(",","%' or g.id_gl4 like '",$_GT['q'])."%')"):"")."
						group by g.id_gl4,nm 
						order by g.id_gl4 limit 50");
						
	if(!mysql_num_rows($a)) echo "<div class='err'>Data tidak ditemukan</div>";
	
	while($b=mysql_fetch_array($a)) {
	?>
		<ul><li onclick="<?php echo "sendText('".$b['id_gl4']."','".$b['nm']."')"; ?>"><?php echo $b['nm']; ?></li></ul>
<?php } ?>
</div>
<script language="javascript">
	parent.hideFade('imgAkun<?=$_GT['v']?>');
	parent.showFade('fAkun<?=$_GT['v']?>');
	
	function sendText(a,b) {
		parent.elm('iAkun<?=$_GT['v']?>').value=a;
		parent.elm('nAkun<?=$_GT['v']?>').value=b;
		setTimeout("parent.hideFade('fAkun<?=$_GT['v']?>');",100);
	}
</script>
</body>
</html>
<?php
	}
?>
