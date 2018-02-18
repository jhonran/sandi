<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='kelas.php'",
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
		
		if($_PT['tSimpan']) {
			while(list($a,$b)=each($_PT['tNominal'])) {
				$tNominal[$a]=$b;
				$errtNominal[$a]=($b && !is_numeric(clearRupiah($b)))?"Nilai bukan angka":"";			
			}
			
			$errtNominal=array_filter($errtNominal);
			
			if(!count($errtNominal) && is_array($tNominal)) {			
				queryDb("delete from t_kelas_jns_bayar where id_kelas in (select id_kelas from t_kelas where id_kelas='".$_GT['id1']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."'))");
				
				$tNominal=array_filter($tNominal);
				
				while(list($a,$b)=each($tNominal)) {
					queryDb("insert into t_kelas_jns_bayar(id_kelas,id_jns_bayar_siswa,nominal) 
									select id_kelas,'".$a."','".clearRupiah($b)."' from t_kelas where id_kelas='".$_GT['id1']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."')");
				}
				
				header("location:kelas.php?edit=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order1']));
				exit;
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
<script language="javascript">if(self.document.location==top.document.location) self.document.location="../index.php?logout=1";</script>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#titleTop ol li:nth-child(1) , #list ul li:nth-child(1) { width:30px;text-align:center; }
#titleTop ol li:nth-child(3) , #list ul li:nth-child(3) { width:130px;text-align:center; }
#titleTop ol li { text-align:center; }

#list ul li input[type=text] { width:120px;text-align:right; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<form action="" target="_self" method="post" onsubmit="showFade('load');">
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)." ".getValue("nama","t_kelas","id_kelas='".$_GT['id1']."' limit 1")?></li></ol>
    <ol class="tab"><li>
    	<a href="kelas.php?edit=<?=bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order1'])?>" class="on"><?=strtoupper($_IST['kelas.php'])?></a>
        <a href="#">SETTING JENIS PENERIMAAN</a>
    </li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("#Jenis Penerimaan ".$_IST['siswa.php'],"#Nilai");
		
		if(is_array($listTitle)) {
			reset($listTitle);
			foreach($listTitle as $value) {
				$arrayValue=explode("#",$value);
				echo "<li ".(($arrayValue[0])?("id=\"".$arrayValue[0]."\" onclick=\"return goOrder('order,sort,search',this.id);\" ".(($_GT['order']==($arrayValue[0]." asc"))?"class=\"asc\"":(($_GT['order']==($arrayValue[0]." desc"))?"class=\"desc\"":""))." title=\"Urutkan berdasarkan ".strtoupper($arrayValue[1])."\""):"class=\"only\"")."><span>".strtoupper($arrayValue[1])."</span></li>";
			}
		}
		?>
    </ol>
</div>
<div id="list">
	<?php
	$paging['start']=0;
	
	$a=queryDb("select j.id_jns_bayar_siswa,j.nama,ifnull(b.nominal,0) as nominal from t_jns_bayar_siswa j
						left join t_kelas_jns_bayar b on b.id_jns_bayar_siswa=j.id_jns_bayar_siswa and b.id_kelas in (select id_kelas from t_kelas where id_kelas='".$_GT['id1']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."'))
					order by j.id_jns_bayar_siswa");
	while($b=mysql_fetch_array($a)) {
		$paging['start']++;
		
		if(!$_PT['tSimpan']) {
			$tNominal[$b['id_jns_bayar_siswa']]=showRupiah2($b['nominal']);
		}
		
		echo "<ul>
					<li>".$paging['start'].".</li>
					<li>".$b['nama']."<div id=\"errtNominal_".$b['id_jns_bayar_siswa']."\" class=\"err\">".$errtNominal[$b['id_jns_bayar_siswa']]."</div></li>
					<li><input type=\"text\" name=\"tNominal[".$b['id_jns_bayar_siswa']."]\" value=\"".$tNominal[$b['id_jns_bayar_siswa']]."\" maxlength=\"20\" required=\"required\" onkeyup=\"hideFade('errtNominal_".$b['id_jns_bayar_siswa']."');valnominal(this);\" /></li>
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
	hideFade("load","<?=($errtIdKelas || $errtNama)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
