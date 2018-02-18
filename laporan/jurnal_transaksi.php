<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='".basename($_SERVER["SCRIPT_FILENAME"])."'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		$sql="select u3.id_unit1,u1.nama as nama1,u3.id_unit2,u2.nama as nama2,u3.id_unit3,u3.nama as nama3 
											from t_unit3 u3
												inner join t_unit2 u2 on u2.id_unit1=u3.id_unit1 and u2.id_unit2=u3.id_unit2
												inner join t_unit1 u1 on u1.id_unit1=u3.id_unit1
												inner join t_user s on s.id_user='".$_SESSION['user']."' 
												inner join t_entitas_unit e on e.id_entitas=s.id_entitas and e.id_unit1=u3.id_unit1 and e.id_unit2=u3.id_unit2 and e.id_unit3=u3.id_unit3
											order by u3.id_unit1,u3.id_unit2,u3.id_unit3";
		$a=queryDb($sql);
		while($b=mysql_fetch_array($a)) {
			$_U1[$b['id_unit1']]=$b['nama1'];
			$_U2[$b['id_unit1']][$b['id_unit2']]=$b['nama2'];
			$_U3[$b['id_unit1']][$b['id_unit2']][$b['id_unit3']]=$b['nama3'];
			
			$_S[$b['id_unit1']][$b['id_unit2']][$b['id_unit3']]="<input type=\"checkbox\" name=\"tUnit[]\" value=\"".$b['id_unit1'].".".$b['id_unit2'].".".$b['id_unit3']."\" onclick=\"uncheckAll('tUnit[]');\" />";
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
#list ul li:nth-child(1) { width:30px;text-align:center; }
#list div ul li:nth-child(1) { width:46px;text-align:center; }
#list div div ul li:nth-child(1) { width:62px;text-align:center; }

#list div { overflow:hidden; }

#titleTop ol li { text-align:center; }

#list ul.g { background:#FFF4F4; }
#list ul.h { background:#FFFEEC; }
#list ul.i { background:#EFFFEC; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)?></li></ol>
    <ol class="tab"><li>
    	<a href="#">UNIT KERJA</a>
        <a href="#" class="off">MASTER TRANSAKSI</a>
    </li></ol>
	<ol class="title">
    	<li>UNIT KERJA</li>
    </ol>
</div>
<div id="list">
	<?php
	if(is_array($_U1)) {
		while(list($a,$b)=each($_U1)) {
			echo "<ul class='g' onclick=\"menuSub('".$a."',getHeightChild('".$a."'))\"><li>".$a."</li><li>".$b."</li></ul><div id='".$a."'>";
			while(list($aa,$bb)=each($_U2[$a])) {
				echo "<ul class='h' onclick=\"menuSub('".$a.$aa."',getHeightChild('".$a.$aa."'))\"><li>".$a.".".$aa."</li><li>".$bb."</li></ul><div id='".$a.$aa."'>";
				while(list($aaa,$bbb)=each($_U3[$a][$aa])) {
					echo "<ul id=\"".$a.".".$aa.".".$aaa."\" onclick=\"listFocus(this,'edit=1')\"><li>".$a.".".$aa.".".$aaa."</li><li>".$bbb."</li></ul>";
					if($_GT['edit']==($a.".".$aa.".".$aaa)) $jsEdit="listFocus(elm('".$a.".".$aa.".".$aaa."'),'edit=1');";
				}
				echo "</div>";
			}
			echo "</div>";
		}
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li style="width:auto;" class="r">
    	  	<button id="btn-edit" class="icon-edit disabled" onclick="if(_URI['edit']) goAddress('edit','jurnal_transaksi_master.php');">MASTER TRANSAKSI</button>
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