<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='kas_keluar.php'",
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
		
		$no=0;
		$a=queryDb("select keterangan,jabatan,pegawai from t_trans_tandatangan where no_trans='".$_GT['id']."' order by id");
		while($b=mysql_fetch_array($a)) {
			$no++;
			
			$_T[$no]['keterangan']=$b['keterangan'];
			$_T[$no]['jabatan']=$b['jabatan'];
			$_T[$no]['pegawai']=$b['pegawai'];
		}
		
		if($no==0) {
			$a=queryDb("select keterangan,jabatan,pegawai from t_tandatangan where concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' order by id_tandatangan");
			while($b=mysql_fetch_array($a)) {
				$no++;
			
				$_T[$no]['keterangan']=$b['keterangan'];
				$_T[$no]['jabatan']=$b['jabatan'];
				$_T[$no]['pegawai']=$b['pegawai'];
			}
		}
	
		if($_PT['tSimpan']) {
			queryDb("delete from t_trans_tandatangan where no_trans='".$_GT['id']."'");
			
			for($no=1;$no<=5;$no++) {
				if(($_PT['tKeterangan'][$no] && $_PT['nPegawai'][$no]) || ($_PT['tJabatan'][$no] && $_PT['nPegawai'][$no])) {
					queryDb("insert into t_trans_tandatangan(no_trans,keterangan,jabatan,pegawai) values('".$_GT['id']."','".$_PT['tKeterangan'][$no]."','".$_PT['tJabatan'][$no]."','".$_PT['nPegawai'][$no]."')");
				}
			}
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
<!--<script language="javascript">if(self.document.location==top.document.location) self.document.location="../index.php?logout=1";</script>-->
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#titleTop ol li:nth-child(1) , #list ul li:nth-child(1) { width:30px;text-align:center; }
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:260px;text-align:center; }
#titleTop ol li:nth-child(3) , #list ul li:nth-child(3) { width:260px;text-align:center; }
#list ul li:nth-child(4) { text-align:center; }
#titleTop ol li { text-align:center; }

#list ul li input[type=text] { width:240px; }

#list ul li img { border:2px solid #ffffff;box-shadow: 1px 1px 5px rgba(0,0,0,0.40); }
#list ul li div.framePopup { position:relative;height:0;z-index:1000; }
#list ul li div.framePopup img { 
	position:absolute;
	top:-23px;
	right:7px;
	border:0;
	display:none;
	border:none;
	box-shadow:none;
}
#list ul li div.framePopup iframe { position:absolute;width:100%;top:0;right:0;height:200px;overflow:auto;display:none; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<form action="" target="_self" method="post" onsubmit="showFade('load');">
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)?></li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("#Keterangan","#Jabatan","#Nama ".$_IST['pegawai.php']);
		
		if(is_array($listTitle)) {
			reset($listTitle);
			foreach($listTitle as $value) {
				$arrayValue=explode("#",$value);
				echo "<li class=\"only\"><span>".strtoupper($arrayValue[1])."</span></li>";
			}
		}
		?>
    </ol>
</div>
<div id="list">
	<?php	
	for($no=1;$no<=5;$no++) {		
		echo "<ul>
					<li>".$no.".</li>
					<li><input type=\"text\" name=\"tKeterangan[".$no."]\" value=\"".$_T[$no]['keterangan']."\" maxlength=\"100\" /></li>
					<li><input type=\"text\" name=\"tJabatan[".$no."]\" value=\"".$_T[$no]['jabatan']."\" maxlength=\"100\" /></li>
					<li>
					  <div style=\"display:inline-block;width:240px;\">
						  <input type=\"hidden\" name=\"iPegawai[".$no."]\" id=\"iPegawai".$no."\" />
						  <input name=\"nPegawai[".$no."]\" type=\"text\" id=\"nPegawai".$no."\" maxlength=\"100\" value=\"".$_T[$no]['pegawai']."\" autocomplete=\"off\" onkeyup=\"goFramePopup(this,'Pegawai".$no."','pegawai_tandatangan','".$no."');\" onfocus=\"framePopupFocus('Pegawai".$no."')\" onblur=\"if(!elm('iPegawai".$no."').value) elm('iPegawai".$no."').value='-'; framePopupBlur('Pegawai".$no."')\" />
						  <div class=\"framePopup\"><img id=\"imgPegawai".$no."\" src=\"../images/loader.gif\" /><iframe id=\"fPegawai".$no."\"></iframe></div>
					  </div>					
					</li>
				</ul>";
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li class="r" style="width:auto;">
        	<input name="tSimpan" type="submit" class="icon-save" id="tSimpan" value="SIMPAN" />
        </li>
    </ol>
</div>
</form>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtNama)?"showFade('input')":""?>");	
	<?=($_PT['tSimpan'])?"this.close();":""?>
</script>
</body>
</html>
<?php } ?>
