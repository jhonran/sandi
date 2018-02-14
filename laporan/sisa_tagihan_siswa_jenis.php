<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='sisa_tagihan_siswa.php'",
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
		
		$sql="select id_jns_bayar_siswa,nama,sum(ns-ms) as nominal from (
						select x.id_siswa_tagih,x.id_jns_bayar_siswa,j.nama,sum(n) as ns,sum(m) as ms from (
								select id_siswa_tagih,id_jns_bayar_siswa,nominal as n,0 as m from t_siswa_tagih where id_kelas='".$_GT['edit']."'
								UNION
								(select id_siswa_tagih,id_jns_bayar_siswa,0 as n,sum(nominal) as m from t_siswa_bayar where id_kelas='".$_GT['edit']."' group by id_siswa_tagih,id_jns_bayar_siswa)
							) x
							inner join t_jns_bayar_siswa j on j.id_jns_bayar_siswa=x.id_jns_bayar_siswa
							group by x.id_siswa_tagih,x.id_jns_bayar_siswa,j.nama
							having sum(n)<>0
					) y
					group by id_jns_bayar_siswa,nama
					having sum(ns-ms)<>0
					order by id_jns_bayar_siswa";
											
		$a=queryDb($sql);
		while($b=mysql_fetch_array($a)) {
			$_M[$b['id_jns_bayar_siswa']]=$b['nama'];			
			$_S[$b['id_jns_bayar_siswa']]="<input type=\"checkbox\" name=\"tJenis[]\" value=\"".$b['id_jns_bayar_siswa']."\" onclick=\"uncheckAll('tJenis[]');\" />";
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

#titleBottom ol li img { border:2px solid #ffffff;box-shadow: 1px 1px 5px rgba(0,0,0,0.40); }
#titleBottom ol li div.framePopup { position:relative;height:0;z-index:1000; }
#titleBottom ol li div.framePopup img { 
	position:absolute;
	top:-23px;
	right:7px;
	border:0;
	display:none;
	border:none;
	box-shadow:none;
}
#titleBottom ol li div.framePopup iframe { position:absolute;width:100%;bottom:32px;right:0;height:100px;overflow:auto;display:none; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)?> <?=strtoupper($_IST['kelas.php'])?> <?=getValue("nama","t_kelas","id_kelas='".$_GT['edit']."' limit 1")?></li></ol>
    <ol class="tab"><li>
    	<a href="sisa_tagihan_siswa.php?edit=<?=bs64_e($_GT['edit'])?>" class="on"><?=strtoupper($_IST['kelas.php'])?> <?=strtoupper($_IST['siswa.php'])?></a>
        <a href="#">JENIS PIUTANG</a>
    </li></ol>
	<ol class="title">
    	<li>ID JENIS</li>
    	<li class="only">NAMA</li>
    	<li class="only"><input type="checkbox" id="tJenis[]All" onclick="checkAll(this.checked,'tJenis[]')" /></li>
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
        	PERIODE per :  
            <input type="text" name="tTanggal" id="tTanggal" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" style="width:147px;" />
        </li>
    	<li class="r" style="width:auto;">
        	<div style="display:inline-block;width:275px;">PILIH <?=strtoupper($_IST['siswa.php'])?> :
			  <input type="hidden" name="iSiswa" id="iSiswa" />
			  <input name="nSiswa" type="text" id="nSiswa" maxlength="200" autocomplete="off" onkeyup="goFramePopup(this,'Siswa','siswa','<?=$_GT['edit']?>');" onfocus="framePopupFocus('Siswa')" onblur="if(!elm('iSiswa').value) elm('iSiswa').value='-'; framePopupBlur('Siswa')" />
			  <div class="framePopup"><img id="imgSiswa" src="../images/loader.gif" /><iframe id="fSiswa"></iframe></div>
			</div>
		</li>
    	<li style="width:auto;" class="r">
        	<button id="btn-edit" class="icon-edit" onclick="viewLap2();">SIGNATURE</button>
        	<button id="btn-search" class="icon-search" onclick="viewLap();">TAMPILKAN LAPORAN</button>
        </li>
    </ol>
</div>
<script language="javascript">
	function setTanggal(v) {
		$(function() {
			$("#tTanggal").datepicker();
			$("#tTanggal").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal").datepicker("setDate",v);
		});
	}
	
	setTanggal('<?=date("d/m/Y")?>');
	
	function viewLap() {
		var x=document.getElementsByName('tJenis[]');
		var jenis=new Array();
		
		for(var i=0;i<x.length;i++) { if(x[i].checked) jenis.push(x[i].value); }
		
		if(jenis.length<=0) { alert('Data belum dipilih'); return false; }
		
		if(windowPopUp['x']) windowPopUp['x'].close();
		popUp('../popup/v_sisa_tagihan_siswa.php?kelas=<?=bs64_e($_GT['edit'])?>&jenis='+bs64_e(jenis.join("#"))+'&nik='+bs64_e(elm('iSiswa').value)+'&periode='+bs64_e(elm('tTanggal').value),1000,600,'x');
		return false;
	}
	
	function viewLap2() {
		var x=document.getElementsByName('tJenis[]');
		var jenis=new Array();
		
		for(var i=0;i<x.length;i++) { if(x[i].checked) jenis.push(x[i].value); }
		
		if(jenis.length<=0) { alert('Data belum dipilih'); return false; }
		
		if(windowPopUp['x']) windowPopUp['x'].close();
		popUp('../popup/tandatangan_lap.php?x=9&unit=<?=bs64_e($_SESSION['unit'])?>&kelas=<?=bs64_e($_GT['edit'])?>&jenis='+bs64_e(jenis.join("#"))+'&nik='+bs64_e(elm('iSiswa').value)+'&periode='+bs64_e(elm('tTanggal').value),1000,600,'x');
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