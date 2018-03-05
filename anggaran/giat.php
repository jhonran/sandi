<?php
	//session_start();
	error_reporting(0);
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","php_user u
											inner join php_hakakses h on h.id_akses=u.id_akses
											inner join php_otoritas_sub s on s.id_otoritas_sub=h.id_otoritas_sub and s.page='donatur.php'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		if($_GT['delete']) {
			if(!getValue("1","php_detail","id_giat='".$_GT['delete']."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' limit 0,1")) {
				queryDb("delete from php_giat where id_giat='".$_GT['delete']."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."'");
				header("location:?order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdGiat=$_PT['tIdGiat'];
			$tNama=$_PT['tNama'];
			
			$errtNama=(!$tNama)?"Data Nama masih kosong":((getValue("1","php_giat","nama='".$tNama."' and id_giat<>'".$tId."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' limit 1"))?"Nama Giat sudah terdaftar":"");
			
			if(!$errtNama && !$errtAlamat) {				
				if($tId) {
					queryDb("update php_giat set nama='".$tNama."',tanggal='".TANGGAL."' where id_giat='".$tId."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."'");
				}
				else {
					$tId=substr(((getValue("id_giat","php_giat","id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' order by id_giat desc limit 0,1")*1)+101),1,2);
					queryDb("insert into php_giat(id_giat,id_donatur,id_program,nama,tanggal) values('".$tId."','".$_GT['id1']."','".$_GT['id2']."','".$tNama."','".TANGGAL."')");
				}
				
				header("location:?edit=".bs64_e($tIdGiat)."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2']));
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:130px;text-align:center; }
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
                  <td>ID KEGIATAN</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdGiat" id="tIdGiat" value="<?=htmlentities($tIdGiat)?>" maxlength="2" class="readonly" readonly="readonly" />
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
                  	<button class="icon-close" onclick="hideFade('input'); return false;">TUTUP</button></th>
                </tr>
              </table>
          </form>
        </li>
    </ul>
</div>
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)?></li></ol>
    <ol class="tab"><li>
        <a href="donatur.php?edit=<?=bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order1'])?>" class="on">DONATUR</a>
        <a href="program.php?edit=<?=bs64_e($_GT['id2'])."&order=".bs64_e($_GT['order2'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])?>" class="on">PROGRAM</a>
        <a href="#">KEGIATAN</a>
        <a href="#" class="off">DETAIL</a>
    </li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("g.id_giat#ID Kegiatan","g.nama#Nama","nominal#Nominal");
		
		if(is_array($listTitle)) {
			reset($listTitle);
			foreach($listTitle as $value) {
				$arrayValue=explode("#",$value);
				echo "<li ".(($arrayValue[0])?("id=\"".$arrayValue[0]."\" onclick=\"return goOrder('order,id1,hal,sort,search,order1,id2,order2',this.id);\" ".(($_GT['order']==($arrayValue[0]." asc"))?"class=\"asc\"":(($_GT['order']==($arrayValue[0]." desc"))?"class=\"desc\"":""))." title=\"Urutkan berdasarkan ".strtoupper($arrayValue[1])."\""):"class=\"only\"")."><span>".strtoupper($arrayValue[1])."</span></li>";
			}
		}
		?>
    </ol>
</div>
<div id="list">
	<?php
	$paging['start']=0;
	$sql="select g.id_giat,g.nama,ifnull((select sum(nominal) from php_detail where id_donatur=g.id_donatur and id_program=g.id_program and id_giat=g.id_giat),0) as nominal,if(min(d.id_detail) is null,1,0) as stat_delete from php_giat g
					left join php_detail d on d.id_giat=g.id_giat and d.id_donatur=g.id_donatur		
				where g.id_donatur='".$_GT['id1']."' and g.id_program='".$_GT['id2']."'
				group by g.id_giat,g.nama
				order by ".(($_GT['order'])?$_GT['order'].",g.tanggal desc":"g.tanggal desc");
					
	$a=queryDb($sql);
	while($b=mysql_fetch_array($a)) {
		$paging['start']++;
		echo "<input type=\"hidden\" id=\"tId-".$b['id_giat']."\" value=\"".$b['id_giat']."\" \>";
		echo "<input type=\"hidden\" id=\"tIdGiat-".$b['id_giat']."\" value=\"".$_GT['id1'].".".$_GT['id2'].".".$b['id_giat']."\" \>";
		echo "<input type=\"hidden\" id=\"tNama-".$b['id_giat']."\" value=\"".$b['nama']."\" \>";
		echo "<ul id=\"".$b['id_giat']."\" onclick=\"listFocus(this,1,".$b['stat_delete'].")\" ondblclick=\"_URI['id3']='".$b['id_giat']."';_URI['order3']='".$_GT['order']."';goAddress('id1,hal,sort,search,order1,id2,order2,id3,order3','detail.php')\"><li>".$paging['start'].".</li><li>".$_GT['id1'].".".$_GT['id2'].".".$b['id_giat']."</li><li>".$b['nama']."</li><li>".showRupiah($b['nominal'])."</li></ul>";
	
		if($_GT['edit']==$b['id_giat']) $jsEdit="listFocus(elm('".$b['id_giat']."'),1,".$b['stat_delete'].");";
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li class="r" style="width:auto;">
    	  <button class="icon-new" onclick="elm('tIdGiat').value='<?=$_GT['id1'].".".$_GT['id2'].".".substr(((getValue("id_giat","php_giat","id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' order by id_giat desc limit 0,1")*1)+101),1,2)?>';newData('tId,tNama');">TAMBAH</button>
    	  <button id="bEdit" class="icon-edit disabled" onclick="editData('tId,tNama,tIdGiat');">EDIT</button>
    	  <button id="bDelete" class="icon-delete disabled" onclick="deleteData('delete,order,id1,hal,sort,search,order1,id2,order2');">HAPUS</button>
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
