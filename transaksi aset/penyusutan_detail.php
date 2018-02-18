<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='penyusutan.php'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	
	$_UNITS=array();
										
	$a=queryDb("select concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) as id_unit,u.nama from t_user s 
						inner join t_entitas_unit e on e.id_entitas=s.id_entitas
						inner join t_unit3 u on u.id_unit1=e.id_unit1 and u.id_unit2=e.id_unit2 and u.id_unit3=e.id_unit3
					where s.id_user='".$_SESSION['user']."'");
	
	while($b=mysql_fetch_array($a)) {
		$_UNITS[$b['id_unit']]=$b['nama'];
	}
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
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
#titleTop ol li:nth-child(1) , #list ul li:nth-child(1) { width:30px;text-align:center; }
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:110px;text-align:center; }
#titleTop ol li:nth-child(3) , #list ul li:nth-child(3) { width:230px; }
#titleTop ol li:nth-child(4) , #list ul li:nth-child(4) { width:110px;text-align:center; }
#titleTop ol li:nth-child(6) , #list ul li:nth-child(6) { width:125px;text-align:center; }
#titleTop ol li:nth-child(7) , #list ul li:nth-child(7) { width:150px; }
#titleTop ol li:nth-child(8) , #list ul li:nth-child(8) { width:150px; }
#titleTop ol li { text-align:center; }
#list ul li:nth-child(7),#list ul li:nth-child(8) { text-align:right; }

table input[type=text], table input[type=password],  table select { width:400px; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<div id="titleTop">
  	<ol><li>DETAIL <?=strtoupper($titleHTML)?> No Trans : <?=$_GT['id1']?></li></ol>
    <ol class="tab"><li>
    	<a href="penyusutan.php?edit=<?=bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order1'])?>" class="on">PENYUSUTAN</a>
        <a href="#">DETAIL ASET</a>
    </li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("x.tgl_perolehan#Tgl Peroleh","x.nama#Nama Aset","x.no_inventaris#No Inventaris","x.nm_ruang#Posisi Aset","masa_susut#Umur Manfaat","nilai_aset#Harus Disusutkan","x.nominal#Nilai Disusutkan");
		
		if(is_array($listTitle)) {
			reset($listTitle);
			foreach($listTitle as $value) {
				$arrayValue=explode("#",$value);
				echo "<li ".(($arrayValue[0])?("id=\"".$arrayValue[0]."\" onclick=\"return goOrder('order,id1,hal,sort,search,order1',this.id);\" ".(($_GT['order']==($arrayValue[0]." asc"))?"class=\"asc\"":(($_GT['order']==($arrayValue[0]." desc"))?"class=\"desc\"":""))." title=\"Urutkan berdasarkan ".strtoupper($arrayValue[1])."\""):"class=\"only\"")."><span>".strtoupper($arrayValue[1])."</span></li>";
			}
		}
		?>
    </ol>
</div>
<div id="list">
	<?php
	$paging['start']=0;	
	$a=queryDb("select x.id_aset,x.tgl_perolehan,x.nama,x.no_inventaris,(x.masa_susut+ifnull(sum(r.masa_susut_tambah),0)) as masa_susut,(x.nilai_aset+ifnull(sum(r.nilai_tambah-r.residu_tambah),0)) as nilai_aset,x.nominal,x.nm_ruang,x.tanggal from (
							select a.id_aset,a.tgl_perolehan,a.nama,a.no_inventaris,a.masa_susut,(a.nilai_perolehan-a.residu) as nilai_aset,s.nominal,concat('[',a.id_unit1,'.',a.id_unit2,'.',a.id_unit3,'.',a.id_ruangan,'] ',r.nama) as nm_ruang,a.tanggal from t_aset a
								inner join t_ruangan r on r.id_unit1=a.id_unit1 and r.id_unit2=a.id_unit2 and r.id_unit3=a.id_unit3 and r.id_ruangan=a.id_ruangan
								inner join t_susut s on s.id_aset=a.id_aset and no_trans='".$_GT['id1']."'
							where concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".implode("','",array_keys($_UNITS))."')
						) x
						left join t_aset_rev r on r.id_aset=x.id_aset
					".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
					group by x.id_aset,x.tgl_perolehan,x.nama,x.no_inventaris,x.nilai_aset,x.nominal,x.nm_ruang,x.tanggal
					order by ".(($_GT['order'])?$_GT['order'].",x.tanggal desc":"x.tanggal desc"));
						
	while($b=mysql_fetch_array($a)) {
		$paging['start']++;
		echo "<ul id=\"".$b['id_aset']."\"><li>".$paging['start'].".</li><li>".tglIndo($b['tgl_perolehan'],2)."</li><li>".$b['nama']."</li><li>".$b['no_inventaris']."</li>
					<li>".$b['nm_ruang']."</li><li>".$b['masa_susut']." ".PRDSUSUT."</li><li>".showRupiah2($b['nilai_aset'])."</li><li>".showRupiah2($b['nominal'])."</li></ul>";
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li style="width:auto;" class="r"></li>
    	<li class="r" style="width:auto;"></li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load");
</script>
</body>
</html>
<?php } ?>
