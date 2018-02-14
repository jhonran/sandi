<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='dana_anggaran.php'",
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
		
		$a=queryDb("select tgl_start,tgl_finish from t_danaanggaran where id_danaanggaran='".$_GT['edit']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."'");
		$_DANA=mysql_fetch_array($a);
		
		if($_PT['tSimpan']) {	
			$_PT['tNominal']=array_filter($_PT['tNominal']);
			
			if(is_array($_PT['tNominal'])) {			
				queryDb("delete from t_danaanggaran where concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' and tgl_start='".$_DANA['tgl_start']."' and id_gl4<>'0'");
				
				reset($_PT);
				while(list($a,$b)=each($_PT['tNominal'])) {
					$tId=(getValue("id_danaanggaran","t_danaanggaran","1=1 order by id_danaanggaran desc limit 0,1")*1)+1;
					queryDb("insert into t_danaanggaran(id_danaanggaran,id_unit1,id_unit2,id_unit3,id_gl4,tgl_start,tgl_finish,nominal,tanggal,id_user) 
								values('".$tId."','".$_UNIT['id_unit1']."','".$_UNIT['id_unit2']."','".$_UNIT['id_unit3']."','".$a."','".$_DANA['tgl_start']."','".$_DANA['tgl_finish']."','".clearRupiah($b)."','".TANGGAL."','".$_SESSION['user']."')");
				}
			}
			
			header("location:dana_anggaran.php?edit=".bs64_e($_GT['edit'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
			exit;
		}
		
		$sql="select g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,g1.nama as nama1,g2.nama as nama2,g3.nama as nama3,g.nama as nama4,ifnull(d.nominal,0) as nilai
						from t_gl4 g
							inner join t_gl3 g3 on g3.id_gl1=g.id_gl1 and g3.id_gl2=g.id_gl2 and g3.id_gl3=g.id_gl3
							inner join t_gl2 g2 on g2.id_gl1=g.id_gl1 and g2.id_gl2=g.id_gl2
							inner join t_gl1 g1 on g1.id_gl1=g.id_gl1
							left join t_danaanggaran d on d.id_gl4=g.id_gl4 and concat(d.id_unit1,'.',d.id_unit2,'.',d.id_unit3)='".$_SESSION['unit']."' and d.tgl_start='".$_DANA['tgl_start']."'
						where g1.balance='LR' or (g1.balance='NR' and (g.id_gl3='102002' or g.id_gl2='105'))
						order by g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4";
						
		$a=queryDb($sql);
		while($b=mysql_fetch_array($a)) {
			$_G1[$b['id_gl1']]=$b['nama1'];
			$_G2[$b['id_gl1']][$b['id_gl2']]=$b['nama2'];
			$_G3[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]=$b['nama3'];
			$_G4[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']][$b['id_gl4']]=$b['nama4'];
			
			$_S[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']][$b['id_gl4']]="<input type=\"text\" name=\"tNominal[".$b['id_gl4']."]\" value=\"".showRupiah2($b['nilai'])."\" maxlength=\"20\" onkeyup=\"valnominal(this);\" />";
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(3) { width:185px;text-align:center; }
#list ul li input[type=text] { width:170px;text-align:right; }

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
<form action="" target="_self" method="post" onsubmit="showFade('load');">
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)?></li></ol>
    <ol class="tab"><li>
    	<a href="dana_anggaran.php?edit=<?=bs64_e($_GT['edit'])?>&hal=<?=bs64_e($_GT['hal'])?>&sort=<?=bs64_e($_GT['sort'])?>&search=<?=bs64_e($_GT['search'])?>&order=<?=bs64_e($_GT['order'])?>" class="on">DANA ANGGARAN</a>
        <a href="#">SETTING NILAI BIAYA & PENDAPATAN</a>
    </li></ol>
	<ol class="title">
    	<li><?=strtoupper($_IST['gl1.php'])?></li>
    	<li class="only"></li>
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
	hideFade("load");
</script>
</body>
</html>
<?php } ?>