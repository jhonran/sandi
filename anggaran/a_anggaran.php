<?php
	//session_start();
	error_reporting(0);
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='donatur.php'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	$_UNITS=array();
										
	$a=queryDb("select concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) as id_unit,u.nama,if(a.id_anggaran is null,0,1) as stat from t_user s 
						inner join t_entitas_unit e on e.id_entitas=s.id_entitas
						inner join t_unit3 u on u.id_unit1=e.id_unit1 and u.id_unit2=e.id_unit2 and u.id_unit3=e.id_unit3
						left join t_anggaran a on a.id_unit1=e.id_unit1 and a.id_unit2=e.id_unit2 and a.id_unit3=e.id_unit3 and a.id_donatur='".$_GT['id1']."' and a.id_program='".$_GT['id2']."' and a.id_giat='".$_GT['id3']."' and a.id_detail='".$_GT['id4']."'
					where s.id_user='".$_SESSION['user']."'");
	
	while($b=mysql_fetch_array($a)) {
		$_UNITS[$b['id_unit']]=$b['nama'];
		$_UNITSTAT[$b['id_unit']]=$b['stat'];
	}
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		if($_GT['del']) {
			queryDb("delete from t_anggaran where id_anggaran='".$_GT['del']."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."' and id_detail='".$_GT['id4']."'");
			header("location:?order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2'])."&id3=".bs64_e($_GT['id3'])."&order3=".bs64_e($_GT['order3'])."&id4=".bs64_e($_GT['id4'])."&order4=".bs64_e($_GT['order4']));
			exit;
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tUnit=$_PT['tUnit'];
			$tNominal=$_PT['tNominal'];
			
			$errtUnit=(!$tUnit)?"Data Unit belum dipilih":((!$_UNITS[$tUnit])?"Anda tidak memiliki hak atas Unit":((getValue("1","t_anggaran","concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$tUnit."' and id_anggaran<>'".$tId."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."' and id_detail='".$_GT['id4']."' limit 1"))?"Unit dalam Anggaran sudah terdaftar":""));
			$errtNominal=(!$tNominal || !is_numeric(clearRupiah($tNominal)))?"Nominal masih kosong atau bukan angka":"";
			
			if(!$errtUnit && !$errtNominal) {	
				$_UNIT=explode(".",$tUnit);
							
				if($tId) {
					queryDb("update t_anggaran set id_unit1='".$_UNIT[0]."',id_unit2='".$_UNIT[1]."',id_unit3='".$_UNIT[2]."',nominal='".clearRupiah($tNominal)."',tanggal='".TANGGAL."' where id_anggaran='".$tId."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."' and id_detail='".$_GT['id4']."'");
				}
				else {
					$tId=(getValue("id_anggaran","t_anggaran","1=1 order by id_anggaran desc limit 0,1")*1)+1;
					queryDb("insert into t_anggaran(id_anggaran,id_donatur,id_program,id_giat,id_detail,id_unit1,id_unit2,id_unit3,nominal,tanggal) values('".$tId."','".$_GT['id1']."','".$_GT['id2']."','".$_GT['id3']."','".$_GT['id4']."','".$_UNIT[0]."','".$_UNIT[1]."','".$_UNIT[2]."','".clearRupiah($tNominal)."','".TANGGAL."')");
				}
				
				header("location:?edit=".bs64_e($tId)."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2'])."&id3=".bs64_e($_GT['id3'])."&order3=".bs64_e($_GT['order3'])."&id4=".bs64_e($_GT['id4'])."&order4=".bs64_e($_GT['order4']));
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:140px;text-align:center; }
#titleTop ol li:nth-child(4) , #list ul li:nth-child(4) { width:120px; }
#list ul li:nth-child(4) { text-align:right; }
#titleTop ol li { text-align:center; }

table input[type=text], table input[type=password],  table select { width:400px; }
option.ro { color:#bbbbbb; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<div id="input">
	<ul class="full">
        <li style="text-align:center;">
          <form action="" target="_self" method="post" onsubmit="showFade('load');">
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <th colspan="3" class="title">FORM INPUT <?=strtoupper($titleHTML)?></th>
                </tr>
                <tr>
                  <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                  <td>DETAIL</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdDetail" id="tIdDetail" value="<?=$_GT['id1'].".".$_GT['id2'].".".$_GT['id3'].".".$_GT['id4']?>" maxlength="3" class="readonly" readonly="readonly" />
                  </td>
                </tr>
                <tr>
                  <td>UNIT</td>
                  <td>:</td>
                  <td>
                    <select name="tUnit" id="tUnit" required="required" onchange="hideFade('errtUnit')">
                    	<option value="">- Pilih -</option>
                        <?php
                        if(is_array($_UNITS)) {
							reset($_UNITS);
							reset($_UNITSTAT);
							while(list($a,$b)=each($_UNITS)) {
								echo "<option value=\"".$a."\" class=\"".(($_UNITSTAT[$a]==1)?"ro":"")."\" ".(($tUnit==$a)?"selected='selected'":"").">[".$a."] ".$b."</option>";
							}
						}
						?>
                    </select>
                    <div id="errtUnit" class="err"><?=$errtUnit?></div>
                  </td>
                </tr>
                <tr>
                  <td>NOMINAL</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNominal" id="tNominal" value="<?=htmlentities($tNominal)?>" maxlength="20" required="required" onkeyup="hideFade('errtNominal');valnominal(this);" />
                    <div id="errtNominal" class="err"><?=$errtNominal?></div>
                  </td>
                </tr>
                <tr>
                  <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                  <th colspan="3">
                  	<input name="tSimpan" type="submit" class="icon-save" id="tSimpan" value="SIMPAN" />
                  	<button class="icon-no" onclick="hideFade('input'); return false;">TUTUP</button></th>
                </tr>
              </table>
          </form>
        </li>
    </ul>
</div>
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)?></li></ol>
    <ol class="tab"><li>
        <a href="a_donatur.php?edit=<?=bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order1'])?>" class="on">DONATUR</a>
        <a href="a_program.php?edit=<?=bs64_e($_GT['id2'])."&order=".bs64_e($_GT['order2'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])?>" class="on">PROGRAM</a>
        <a href="a_giat.php?edit=<?=bs64_e($_GT['id3'])."&order=".bs64_e($_GT['order3'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2'])?>" class="on">KEGIATAN</a>
        <a href="a_detail.php?edit=<?=bs64_e($_GT['id4'])."&order=".bs64_e($_GT['order4'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2'])."&id3=".bs64_e($_GT['id3'])."&order3=".bs64_e($_GT['order3'])?>" class="on">DETAIL</a>
        <a href="#">ANGGARAN</a>
    </li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("a.id_detail#Detail","u.nama#Unit","a.nominal#Nominal");
		
		if(is_array($listTitle)) {
			reset($listTitle);
			foreach($listTitle as $value) {
				$arrayValue=explode("#",$value);
				echo "<li ".(($arrayValue[0])?("id=\"".$arrayValue[0]."\" onclick=\"return goOrder('order,id1,hal,sort,search,order1,id2,order2,id3,order3,id4,order4',this.id);\" ".(($_GT['order']==($arrayValue[0]." asc"))?"class=\"asc\"":(($_GT['order']==($arrayValue[0]." desc"))?"class=\"desc\"":""))." title=\"Urutkan berdasarkan ".strtoupper($arrayValue[1])."\""):"class=\"only\"")."><span>".strtoupper($arrayValue[1])."</span></li>";
			}
		}
		?>
    </ol>
</div>
<div id="list">
	<?php
	$paging['start']=0;
	$sql="select a.id_anggaran,concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) as id_unit,u.nama as namaUnit,a.nominal from t_anggaran a
					inner join t_unit3 u on u.id_unit1=a.id_unit1 and u.id_unit2=a.id_unit2 and u.id_unit3=a.id_unit3
				where a.id_donatur='".$_GT['id1']."' and a.id_program='".$_GT['id2']."' and a.id_giat='".$_GT['id3']."' and a.id_detail='".$_GT['id4']."' and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".implode("','",array_keys($_UNITS))."')
				order by ".(($_GT['order'])?$_GT['order'].",a.tanggal desc":"a.tanggal desc");
					
	$a=queryDb($sql);
	while($b=mysql_fetch_array($a)) {
		$paging['start']++;
		echo "<input type=\"hidden\" id=\"tId-".$b['id_anggaran']."\" value=\"".$b['id_anggaran']."\" />";
		echo "<input type=\"hidden\" id=\"tUnit-".$b['id_anggaran']."\" value=\"".$b['id_unit']."\" />";
		echo "<input type=\"hidden\" id=\"tNominal-".$b['id_anggaran']."\" value=\"".showRupiah2($b['nominal'])."\" />";
		echo "<ul id=\"".$b['id_anggaran']."\" onclick=\"listFocus(this,'edit=1,del=1')\"><li>".$paging['start'].".</li><li>".$_GT['id1'].".".$_GT['id2'].".".$_GT['id3'].".".$_GT['id4']."</li><li>[".$b['id_unit']."] ".$b['namaUnit']."</li><li>".showRupiah($b['nominal'])."</li></ul>";
	
		if($_GT['edit']==$b['id_anggaran']) $jsEdit="listFocus(elm('".$b['id_anggaran']."'),'edit=1,del=1');";
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li class="r" style="width:auto;">
    	  <button class="icon-new" onclick="newData('tId,tUnit,tNominal');">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tUnit,tNominal');">EDIT</button>
    	  <button id="btn-del" class="icon-del disabled" onclick="delData('del,id1,hal,sort,search,order1,id2,order2,id3,order3,id4,order4');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtUnit || $errtNominal)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
