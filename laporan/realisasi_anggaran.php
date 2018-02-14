<?php
	session_start();
	
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(3) { width:30px;text-align:center; }

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
	<ol class="title">
    	<li>UNIT KERJA</li>
    	<li class="only"><input type="checkbox" id="tUnit[]All" onclick="checkAll(this.checked,'tUnit[]')" /></li>
    </ol>
</div>
<div id="list">
	<?php
	if(is_array($_U1)) {
		while(list($a,$b)=each($_U1)) {
			echo "<ul class='g' onclick=\"menuSub('".$a."',getHeightChild('".$a."'))\"><li>".$a."</li><li>".$b."</li><li></li></ul><div id='".$a."'>";
			while(list($aa,$bb)=each($_U2[$a])) {
				echo "<ul class='h' onclick=\"menuSub('".$a.$aa."',getHeightChild('".$a.$aa."'))\"><li>".$a.".".$aa."</li><li>".$bb."</li><li></li></ul><div id='".$a.$aa."'>";
				while(list($aaa,$bbb)=each($_U3[$a][$aa])) {
					echo "<ul><li>".$a.".".$aa.".".$aaa."</li><li>".$bbb."</li><li>".$_S[$a][$aa][$aaa]."</li></ul>";
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
    	<li style="width:auto;" class="l">PILIH :
        	<select name="tJenisLap" id="tJenisLap" onchange="setJenis(this)">
            	<option value="periode">Per Periode</option>
            	<option value="bulan">Bulanan</option>
            	<option value="tahun">Tahunan</option>
            </select>
        </li>
    	<li style="width:auto;" class="l" id="dPeriode">
        	<input type="text" name="tTanggal" id="tTanggal" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" style="width:147px;" /> s/d
            <input type="text" name="tTanggal2" id="tTanggal2" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" style="width:147px;" />
        </li>
    	<li style="width:auto;display:none;" class="l" id="dBulan">
        	<select name="tBulan" id="tBulan">
            	<?php
                for($i=1;$i<=12;$i++) {
					echo "<option value=\"".$i."\" ".(($i==date("n"))?"selected=\"selected\"":"").">".$array_bulan[($i-1)]."</option>";
				} 
				?>
            </select>
        </li>
    	<li style="width:auto;display:none;" class="l" id="dTahun">
        	<select name="tTahun" id="tTahun">
            	<?php
				$tTahun=date("Y");
                for($i=2012;$i<=$tTahun;$i++) {
					echo "<option value=\"".$i."\" ".(($i==$tTahun)?"selected=\"selected\"":"").">".$i."</option>";
				} 
				?>
            </select>
        </li>        
    	<li class="r" style="width:auto;">
        	<select name="tLevel" id="tLevel">
            	<option value="2"><?=$_IST['gl1.php']?> Level 2</option>
            	<option value="3"><?=$_IST['gl1.php']?> Level 3</option>
            	<option value="4"><?=$_IST['gl1.php']?> Level 4</option>
            </select>
        </li>
    	<li style="width:auto;" class="r">
        	<button id="btn-edit" class="icon-edit" onclick="viewLap2();">SIGNATURE</button>
        	<button id="btn-search" class="icon-search" onclick="viewLap();">TAMPILKAN LAPORAN</button>
        </li>
    </ol>
</div>
<script language="javascript">
	function setJenis(e) {
		if(e.value=="tahun") {
			elm('dPeriode').style.display="none";
			elm('dBulan').style.display="none";
			elm('dTahun').style.display="inline-block";
		}
		else if(e.value=="bulan") {
			elm('dPeriode').style.display="none";
			elm('dBulan').style.display="inline-block";
			elm('dTahun').style.display="inline-block";
		}
		else {
			elm('dPeriode').style.display="inline-block";
			elm('dBulan').style.display="none";
			elm('dTahun').style.display="none";
		}
	}
	
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
		var x=document.getElementsByName('tUnit[]');
		var units=new Array();
		
		for(var i=0;i<x.length;i++) { if(x[i].checked) units.push(x[i].value); }
		
		if(units.length<=0) { alert('Data belum dipilih'); return false; }
		
		if(windowPopUp['x']) windowPopUp['x'].close();
		popUp('../popup/v_realisasi_anggaran.php?unit='+bs64_e(units.join("#"))+'&jenis='+bs64_e(elm('tJenisLap').value)+'&periode1='+bs64_e(elm('tTanggal').value)+'&periode2='+bs64_e(elm('tTanggal2').value)+'&bulan='+bs64_e(elm('tBulan').value)+'&tahun='+bs64_e(elm('tTahun').value)+'&level='+bs64_e(elm('tLevel').value),1000,600,'x');
		return false;
	}
	
	function viewLap2() {
		var x=document.getElementsByName('tUnit[]');
		var units=new Array();
		
		for(var i=0;i<x.length;i++) { if(x[i].checked) units.push(x[i].value); }
		
		if(units.length<=0) { alert('Data belum dipilih'); return false; }
		
		if(windowPopUp['x']) windowPopUp['x'].close();
		popUp('../popup/tandatangan_lap.php?x=7&unit='+bs64_e(units.join("#"))+'&jenis='+bs64_e(elm('tJenisLap').value)+'&periode1='+bs64_e(elm('tTanggal').value)+'&periode2='+bs64_e(elm('tTanggal2').value)+'&bulan='+bs64_e(elm('tBulan').value)+'&tahun='+bs64_e(elm('tTahun').value)+'&level='+bs64_e(elm('tLevel').value),1000,600,'x');
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