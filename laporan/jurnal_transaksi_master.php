<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='jurnal_transaksi.php'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		$sql="select a.id_mtrans,a.nama from t_mtrans a where status='1' group by a.id_mtrans,a.nama order by a.id_mtrans";
											
		$a=queryDb($sql);
		while($b=mysql_fetch_array($a)) {
			$_M[$b['id_mtrans']]=$b['nama'];			
			$_S[$b['id_mtrans']]="<input type=\"checkbox\" name=\"tMutasi[]\" value=\"".$b['id_mtrans']."\" onclick=\"uncheckAll('tMutasi[]');\" />";
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
#titleTop ol li:nth-child(3) , #list ul li:nth-child(3) { width:30px;text-align:center; }
#titleTop ol li { text-align:center; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)." ".getValue("nama","t_unit3","concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_GT['edit']."' limit 1")?></li></ol>
    <ol class="tab"><li>
    	<a href="jurnal_transaksi.php?edit=<?=bs64_e($_GT['edit'])?>" class="on">UNIT KERJA</a>
        <a href="#">MASTER TRANSAKSI</a>
    </li></ol>
	<ol class="title">
    	<li>ID TRANS</li>
    	<li class="only">NAMA</li>
    	<li class="only"><input type="checkbox" id="tMutasi[]All" onclick="checkAll(this.checked,'tMutasi[]')" /></li>
    </ol>
</div>
<div id="list">
	<?php
	if(is_array($_M)) {
		while(list($a,$b)=each($_M)) {
			echo "<ul><li>".$a."</li><li>".$b."</li><li>".$_S[$a]."</li></ul>";
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
		var x=document.getElementsByName('tMutasi[]');
		var mutasi=new Array();
		
		for(var i=0;i<x.length;i++) { if(x[i].checked) mutasi.push(x[i].value); }
		
		if(mutasi.length<=0) { alert('Data belum dipilih'); return false; }
		
		if(windowPopUp['x']) windowPopUp['x'].close();
		popUp('../popup/v_jurnal_transaksi.php?unit=<?=bs64_e($_GT['edit'])?>&mutasi='+bs64_e(mutasi.join("#"))+'&periode1='+bs64_e(elm('tTanggal').value)+'&periode2='+bs64_e(elm('tTanggal2').value),1000,600,'x');
		return false;
	}
	
	function viewLap2() {
		var x=document.getElementsByName('tMutasi[]');
		var mutasi=new Array();
		
		for(var i=0;i<x.length;i++) { if(x[i].checked) mutasi.push(x[i].value); }
		
		if(mutasi.length<=0) { alert('Data belum dipilih'); return false; }
		
		if(windowPopUp['x']) windowPopUp['x'].close();
		popUp('../popup/tandatangan_lap.php?x=1&unit=<?=bs64_e($_GT['edit'])?>&mutasi='+bs64_e(mutasi.join("#"))+'&periode1='+bs64_e(elm('tTanggal').value)+'&periode2='+bs64_e(elm('tTanggal2').value),1000,600,'x');
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