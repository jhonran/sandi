<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='".basename($_SERVER["SCRIPT_FILENAME"])."'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	$a=queryDb("select u.id_unit1,u.id_unit2,u.id_unit3 from t_user s 
						inner join t_entitas_unit e on e.id_entitas=s.id_entitas
						inner join t_unit3 u on u.id_unit1=e.id_unit1 and u.id_unit2=e.id_unit2 and u.id_unit3=e.id_unit3 and concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3)='".$_SESSION['unit']."'
					where s.id_user='".$_SESSION['user']."' limit 1");
	
	$_UNIT=mysql_fetch_array($a);
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else if(!isset($_UNIT['id_unit3'])) {		
		header("location:../pilih_unit.php?direct=".bs64_e($_SERVER['REQUEST_URI']));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		$sql="select id_kelas,nama,sum(ns-ms) as nominal from (
						select id_siswa_tagih,id_kelas,nama,sum(n) as ns,sum(m) as ms from (
								select t.id_siswa_tagih,j.id_kelas,j.nama,t.nominal as n,0 as m from t_siswa_tagih t inner join t_kelas j on j.id_kelas=t.id_kelas and concat(j.id_unit1,'.',j.id_unit2,'.',j.id_unit3)='".$_SESSION['unit']."'
								union
								(select t.id_siswa_tagih,j.id_kelas,j.nama,0 as n,sum(t.nominal) as m from t_siswa_bayar t inner join t_kelas j on j.id_kelas=t.id_kelas and concat(j.id_unit1,'.',j.id_unit2,'.',j.id_unit3)='".$_SESSION['unit']."' group by t.id_siswa_tagih,j.id_kelas,j.nama)
							) x
							group by id_siswa_tagih,id_kelas,nama
							having sum(n)<>0
					) y
					group by id_kelas,nama
					having sum(ns-ms)<>0
					order by id_kelas";
											
		$a=queryDb($sql);
		while($b=mysql_fetch_array($a)) {
			$_J1[$b['id_kelas']]=$b['nama'];
		}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$titleHTML?></title>
<script src="../js/function.js" type="text/javascript"></script>
<script src="../js/effect.js" type="text/javascript"></script>
<script src="../js/submain.js" type="text/javascript"></script>
<script src="../js/dateVal.js" type="text/javascript"></script>
<script src="../js/jquery-1.5.js" type="text/javascript"></script>
<script src="../js/ui/jquery.ui.core.js" type="text/javascript"></script>
<script src="../js/ui/jquery.ui.widget.js" type="text/javascript"></script>
<script src="../js/ui/jquery.ui.datepicker.js" type="text/javascript"></script>
<script language="javascript">if(self.document.location==top.document.location) self.document.location="../index.php?logout=1";</script>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<link href="../css/ui/base/jquery.ui.all.css" rel="stylesheet" />
<style type="text/css">
#titleTop ol li:nth-child(1) , #list ul li:nth-child(1) { width:100px;text-align:center; }

#list div { overflow:hidden; }

#titleTop ol li { text-align:center; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)?></li></ol>
    <ol class="tab"><li>
    	<a href="#"><?=strtoupper($_IST['kelas.php'])?> <?=strtoupper($_IST['siswa.php'])?></a>
        <a href="#" class="off">JENIS PIUTANG</a>
    </li></ol>
	<ol class="title">
    	<li>ID <?=strtoupper($_IST['kelas.php'])?></li>
    	<li class="only">NAMA <?=strtoupper($_IST['kelas.php'])?></li>
    </ol>
</div>
<div id="list">
	<?php
	if(is_array($_J1)) {
		while(list($a,$b)=each($_J1)) {
			echo "<ul id=\"".$a."\" onclick=\"listFocus(this,'edit=1')\"><li>".$a."</li><li>".$b."</li></ul>";
			if($_GT['edit']==$a) $jsEdit="listFocus(elm('".$a."'),'edit=1');";
		}
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li style="width:auto;" class="r">
    	  	<button id="btn-edit" class="icon-edit disabled" onclick="if(_URI['edit']) goAddress('edit','sisa_tagihan_siswa_jenis.php');">JENIS PIUTANG</button>
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load");
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>