<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='buku_besar.php'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		$sql="select g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,g1.nama as nama1,g2.nama as nama2,g3.nama as nama3,g.nama as nama4
						from t_glu g
							inner join t_gl3 g3 on g3.id_gl1=g.id_gl1 and g3.id_gl2=g.id_gl2 and g3.id_gl3=g.id_gl3
							inner join t_gl2 g2 on g2.id_gl1=g.id_gl1 and g2.id_gl2=g.id_gl2
							inner join t_gl1 g1 on g1.id_gl1=g.id_gl1
						where concat(g.id_unit1,'.',g.id_unit2,'.',g.id_unit3)='".$_GT['edit']."'
						order by g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4";
											
		$a=queryDb($sql);
		while($b=mysql_fetch_array($a)) {
			$_G1[$b['id_gl1']]=$b['nama1'];
			$_G2[$b['id_gl1']][$b['id_gl2']]=$b['nama2'];
			$_G3[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]=$b['nama3'];
			$_G4[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']][$b['id_gl4']]=$b['nama4'];
			
			$_S[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']][$b['id_gl4']]="<input type=\"radio\" name=\"tGl\" value=\"".$b['id_gl4']."\" />";
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(3) { width:30px;text-align:center; }

#list ul li:nth-child(1) { width:30px;text-align:center; }
#list div ul li:nth-child(1) { width:53px;text-align:center; }
#list div div ul li:nth-child(1) { width:92px;text-align:center; }
#list div div div ul li:nth-child(1) { width:157px;text-align:center; }

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
  	<ol><li><?=strtoupper($titleHTML)." ".getValue("nama","t_unit3","concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_GT['edit']."' limit 1")?></li></ol>
    <ol class="tab"><li>
    	<a href="buku_besar.php?edit=<?=bs64_e($_GT['edit'])?>" class="on">UNIT KERJA</a>
        <a href="#"><?=strtoupper($_IST['gl1.php'])?></a>
    </li></ol>
	<ol class="title">
    	<li><?=strtoupper($_IST['gl1.php'])?></li>
    	<li class="only">&nbsp;</li>
    </ol>
</div>
<div id="list">
	<?php
	if(is_array($_G1)) {
		while(list($a,$b)=each($_G1)) {
			echo "<ul class='g' onclick=\"menuSub('".$a."',getHeightChild('".$a."'))\"><li>".$a."</li><li>".$b."</li><li></li></ul><div id='".$a."'>";
			while(list($aa,$bb)=each($_G2[$a])) {
				echo "<ul class='h' onclick=\"menuSub('".$a.$aa."',getHeightChild('".$a.$aa."'))\"><li>".$a.".".$aa."</li><li>".$bb."</li><li></li></ul><div id='".$a.$aa."'>";
				while(list($aaa,$bbb)=each($_G3[$a][$aa])) {
					echo "<ul class='i' onclick=\"menuSub('".$a.$aa.$aaa."',getHeightChild('".$a.$aa.$aaa."'))\"><li>".$a.".".$aa.".".$aaa."</li><li>".$bbb."</li><li></li></ul><div id='".$a.$aa.$aaa."'>";
					while(list($aaaa,$bbbb)=each($_G4[$a][$aa][$aaa])) {
						echo "<ul><li>".$a.".".$aa.".".$aaa.".".$aaaa."</li><li>".$bbbb."</li><li>".$_S[$a][$aa][$aaa][$aaaa]."</li></ul>";
					}
					echo "</div>";
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
    	<li style="width:auto;" class="l">
        	PERIODE :  
            <input type="text" name="tTanggal" id="tTanggal" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" style="width:147px;" /> s/d
            <input type="text" name="tTanggal2" id="tTanggal2" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" style="width:147px;" />
        </li>
    	<li style="width:auto;" class="r">
        	<button id="btn-edit" class="icon-edit" onclick="viewLap2();">SIGNATURE</button>
        	<button id="btn-search" class="icon-search" onclick="viewLap();">TAMPILKAN LAPORAN</button>
        </li>
    </ol>
</div>
<script language="javascript">
	function setTanggal(v,v2) {
		$(function() {
			$("#tTanggal").datepicker();
			$("#tTanggal").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal").datepicker("setDate",v);
		});
		
		$(function() {
			$("#tTanggal2").datepicker();
			$("#tTanggal2").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal2").datepicker("setDate",v2);
		});
	}
	
	setTanggal('<?=date("1/m/Y")?>','<?=date("d/m/Y")?>');
	
	function viewLap() {
		var x=document.getElementsByName('tGl');
		var gl=new Array();
		
		for(var i=0;i<x.length;i++) { if(x[i].checked) gl.push(x[i].value); }
		
		if(gl.length<=0) { alert('Data belum dipilih'); return false; }
		
		if(windowPopUp['x']) windowPopUp['x'].close();
		popUp('../popup/v_buku_besar.php?unit=<?=bs64_e($_GT['edit'])?>&gl='+bs64_e(gl.join("#"))+'&periode1='+bs64_e(elm('tTanggal').value)+'&periode2='+bs64_e(elm('tTanggal2').value),1000,600,'x');
		return false;
	}
	
	function viewLap2() {
		var x=document.getElementsByName('tGl');
		var gl=new Array();
		
		for(var i=0;i<x.length;i++) { if(x[i].checked) gl.push(x[i].value); }
		
		if(gl.length<=0) { alert('Data belum dipilih'); return false; }
		
		if(windowPopUp['x']) windowPopUp['x'].close();
		popUp('../popup/tandatangan_lap.php?x=2&unit=<?=bs64_e($_GT['edit'])?>&gl='+bs64_e(gl.join("#"))+'&periode1='+bs64_e(elm('tTanggal').value)+'&periode2='+bs64_e(elm('tTanggal2').value),1000,600,'x');
		return false;
	}
	
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load");
</script>
</body>
</html>
<?php } ?>