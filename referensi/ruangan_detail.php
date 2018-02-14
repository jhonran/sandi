<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='ruangan.php'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		if($_GT['del']) {
			if(!getValue("1","t_aset","id_ruangan='".$_GT['del']."' limit 0,1")) {
				queryDb("delete from t_ruangan where id_ruangan='".$_GT['del']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_GT['id1']."'");
				header("location:?order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdRuangan=$_PT['tIdRuangan'];
			$tNama=$_PT['tNama'];
			
			$errtIdRuangan=(!preg_match(VALCHAR2,$tIdRuangan))?"ID Ruangan masih kosong atau format salah":((getValue("1","t_ruangan","id_ruangan='".$tIdRuangan."' and id_ruangan<>'".$tId."' limit 1"))?"ID Ruangan sudah terdaftar":"");
			$errtNama=(!$tNama)?"Data Nama masih kosong":"";
			
			if(!$errtIdRuangan && !$errtNama) {				
				if($tId) {
					queryDb("update t_ruangan r
									inner join t_unit3 u on u.id_unit1=r.id_unit1 and u.id_unit2=r.id_unit2 and u.id_unit3=r.id_unit3
								set r.id_ruangan='".$tIdRuangan."',r.nama='".$tNama."',r.tanggal='".TANGGAL."' 
								where r.id_ruangan='".$tId."' and concat(r.id_unit1,'.',r.id_unit2,'.',r.id_unit3)='".$_GT['id1']."'");
				}
				else {
					$tId=$tIdRuangan;
					queryDb("insert into t_ruangan(id_unit1,id_unit2,id_unit3,id_ruangan,nama,tanggal) 
								select id_unit1,id_unit2,id_unit3,'".$tId."','".$tNama."','".TANGGAL."' from t_unit3 where concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_GT['id1']."'");
				}
				
				header("location:?edit=".bs64_e($tId)."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1']));
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:120px;text-align:center; }
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
                  <td>ID RUANGAN</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdRuangan" id="tIdRuangan" value="<?=htmlentities($tIdRuangan)?>" maxlength="3" required="required" onkeyup="hideFade('errtIdRuangan')" />
                    <div id="errtIdRuangan" class="err"><?=$errtIdRuangan?></div>
                  </td>
                </tr>
                <tr>
                  <td>NAMA</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNama" id="tNama" maxlength="100" value="<?=htmlentities($tNama)?>" required="required" onkeyup="hideFade('errtNama')" />
                    <div id="errtNama" class="err"><?=$errtNama?></div>
                  </td>
                </tr>
                <tr>
                  <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                  <th colspan="3">
                  	<input name="tSimpan" type="submit" class="icon-save" id="tSimpan" value="SIMPAN" />
                  	<input type="reset" id="tTutup" class="icon-no" onclick="hideFade('input'); return false;" value="TUTUP" /></th>
                </tr>
              </table>
          </form>
        </li>
    </ul>
</div>
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)?></li></ol>
    <ol class="tab"><li>
    	<a href="ruangan.php?edit=<?=bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order1'])?>" class="on">UNIT KERJA</a>
        <a href="#">RUANGAN</a>
    </li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("d.id_ruangan#ID Ruangan","d.nama#Nama");
		
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
	$sql="select d.id_ruangan,d.nama,if(min(m.id_ruangan) is null,1,0) as stat_delete from t_ruangan d
					left join t_aset m on m.id_ruangan=d.id_ruangan
				where concat(d.id_unit1,'.',d.id_unit2,'.',d.id_unit3)='".$_GT['id1']."' ".(($_GT['sort'] && $_GT['search'])?"and lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				group by d.id_ruangan,d.nama
				order by ".(($_GT['order'])?$_GT['order'].",d.tanggal desc":"d.tanggal desc");
	
	$a=queryDb($sql);
	while($b=mysql_fetch_array($a)) {
		$paging['start']++;
		echo "<input type=\"hidden\" id=\"tId-".$b['id_ruangan']."\" value=\"".$b['id_ruangan']."\" />";
		echo "<input type=\"hidden\" id=\"tIdRuangan-".$b['id_ruangan']."\" value=\"".$b['id_ruangan']."\" />";
		echo "<input type=\"hidden\" id=\"tNama-".$b['id_ruangan']."\" value=\"".$b['nama']."\" />";
		echo "<ul id=\"".$b['id_ruangan']."\" onclick=\"listFocus(this,'edit=1,del=".$b['stat_delete']."')\"><li>".$paging['start'].".</li><li>".$_GT['id1'].".".$b['id_ruangan']."</li><li>".$b['nama']."</li></ul>";
	
		if($_GT['edit']==$b['id_ruangan']) $jsEdit="listFocus(elm('".$b['id_ruangan']."'),'edit=1,del=".$b['stat_delete']."');";
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li class="r" style="width:auto;">
    	  <button id="btn-new" class="icon-new" onclick="newData('tId,tIdRuangan,tNama');">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tIdRuangan,tNama');">EDIT</button>
    	  <button id="btn-del" class="icon-del disabled" onclick="delData('del,order,id1,hal,sort,search,order1');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtIdRuangan || $errtNama)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
