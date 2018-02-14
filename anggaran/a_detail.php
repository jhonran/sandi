<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='donatur.php'",
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
		
		if($_GT['del']) {
			if(!getValue("1","t_anggaran","id_detail='".$_GT['del']."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."' limit 0,1")) {
				queryDb("delete from t_detail where id_detail='".$_GT['del']."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."'");
				header("location:?order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2'])."&id3=".bs64_e($_GT['id3'])."&order3=".bs64_e($_GT['order3']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdDetail=$_PT['tIdDetail'];
			$tNama=$_PT['tNama'];
			
			$errtNama=(!$tNama)?"Data Nama masih kosong":((getValue("1","t_detail","nama='".$tNama."' and id_detail<>'".$tId."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."' limit 1"))?"Nama Detail sudah terdaftar":"");
			
			if(!$errtNama) {				
				if($tId) {
					queryDb("update t_detail set nama='".$tNama."',tanggal='".TANGGAL."' where id_detail='".$tId."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."'");
				}
				else {
					$tId=substr(((getValue("id_detail","t_detail","id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."' order by id_detail desc limit 0,1")*1)+1001),1,3);
					queryDb("insert into t_detail(id_detail,id_donatur,id_program,id_giat,nama,tanggal) values('".$tId."','".$_GT['id1']."','".$_GT['id2']."','".$_GT['id3']."','".$tNama."','".TANGGAL."')");
				}
				
				header("location:?edit=".bs64_e($tId)."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2'])."&id3=".bs64_e($_GT['id3'])."&order3=".bs64_e($_GT['order3']));
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
                  <td>ID DETAIL</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdDetail" id="tIdDetail" value="<?=htmlentities($tIdDetail)?>" maxlength="3" class="readonly" readonly="readonly" />
                  </td>
                </tr>
                <tr>
                  <td>NAMA</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNama" id="tNama" maxlength="255" value="<?=htmlentities($tNama)?>" required="required" onkeyup="hideFade('errtNama');" />
                    <div id="errtNama" class="err"><?=$errtNama?></div>
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
        <a href="#">DETAIL</a>
        <a href="#" class="off">ANGGARAN</a>
    </li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("d.id_detail#ID Detail","d.nama#Nama","nominal#Nominal");
		
		if(is_array($listTitle)) {
			reset($listTitle);
			foreach($listTitle as $value) {
				$arrayValue=explode("#",$value);
				echo "<li ".(($arrayValue[0])?("id=\"".$arrayValue[0]."\" onclick=\"return goOrder('order,id1,hal,sort,search,order1,id2,order2,id3,order3',this.id);\" ".(($_GT['order']==($arrayValue[0]." asc"))?"class=\"asc\"":(($_GT['order']==($arrayValue[0]." desc"))?"class=\"desc\"":""))." title=\"Urutkan berdasarkan ".strtoupper($arrayValue[1])."\""):"class=\"only\"")."><span>".strtoupper($arrayValue[1])."</span></li>";
			}
		}
		?>
    </ol>
</div>
<div id="list">
	<?php
	$paging['start']=0;
	$sql="select d.id_detail,d.nama,ifnull(sum(a.nominal),0) as nominal,if(min(a.id_anggaran) is null,1,0) as stat_delete from t_detail d
					left join t_anggaran a on a.id_program=d.id_program and a.id_donatur=d.id_donatur and a.id_giat=d.id_giat and a.id_detail=d.id_detail and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".implode("','",array_keys($_UNITS))."')	
				where d.id_donatur='".$_GT['id1']."' and d.id_program='".$_GT['id2']."' and d.id_giat='".$_GT['id3']."'
				group by d.id_detail,d.nama
				order by ".(($_GT['order'])?$_GT['order'].",d.tanggal desc":"d.tanggal desc");
	
	$a=queryDb($sql);
	while($b=mysql_fetch_array($a)) {
		$paging['start']++;
		echo "<input type=\"hidden\" id=\"tId-".$b['id_detail']."\" value=\"".$b['id_detail']."\" />";
		echo "<input type=\"hidden\" id=\"tIdDetail-".$b['id_detail']."\" value=\"".$_GT['id1'].".".$_GT['id2'].".".$_GT['id3'].".".$b['id_detail']."\" />";
		echo "<input type=\"hidden\" id=\"tNama-".$b['id_detail']."\" value=\"".$b['nama']."\" />";
		echo "<input type=\"hidden\" id=\"tNominal-".$b['id_detail']."\" value=\"".showRupiah2($b['nominal'])."\" />";
		echo "<ul id=\"".$b['id_detail']."\" onclick=\"listFocus(this,'edit=1,del=".$b['stat_delete']."')\" ondblclick=\"_URI['id4']='".$b['id_detail']."';_URI['order4']='".$_GT['order']."';goAddress('id1,hal,sort,search,order1,id2,order2,id3,order3,id4,order4','a_anggaran.php')\"><li>".$paging['start'].".</li><li>".$_GT['id1'].".".$_GT['id2'].".".$_GT['id3'].".".$b['id_detail']."</li><li>".$b['nama']."</li><li>".showRupiah($b['nominal'])."</li></ul>";
	
		if($_GT['edit']==$b['id_detail']) $jsEdit="listFocus(elm('".$b['id_detail']."'),'edit=1,del=".$b['stat_delete']."');";
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li class="r" style="width:auto;">
    	  <button class="icon-new" onclick="elm('tIdDetail').value='<?=$_GT['id1'].".".$_GT['id2'].".".$_GT['id3'].".".substr(((getValue("id_detail","t_detail","id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."' order by id_detail desc limit 0,1")*1)+1001),1,3)?>';newData('tId,tNama');">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tNama,tIdDetail');">EDIT</button>
    	  <button id="btn-del" class="icon-del disabled" onclick="delData('del,id1,hal,sort,search,order1,id2,order2,id3,order3');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtNama)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
