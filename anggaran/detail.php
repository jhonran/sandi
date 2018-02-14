<?php
	session_start();
	
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
			queryDb("delete from php_detail where id_detail='".$_GT['delete']."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."'");
			header("location:?order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2'])."&id3=".bs64_e($_GT['id3'])."&order3=".bs64_e($_GT['order3']));
			exit;
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdDetail=$_PT['tIdDetail'];
			$tNama=$_PT['tNama'];
			$tNominal=$_PT['tNominal'];
			
			$errtNama=(!$tNama)?"Data Nama masih kosong":((getValue("1","php_detail","nama='".$tNama."' and id_detail<>'".$tId."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."' limit 1"))?"Nama Detail sudah terdaftar":"");
			$errtNominal=(!$tNominal || !is_numeric(clearRupiah($tNominal)))?"Nominal masih kosong atau bukan angka":"";
			
			if(!$errtNama && !$errtNominal) {				
				if($tId) {
					queryDb("update php_detail set nama='".$tNama."',nominal='".clearRupiah($tNominal)."',tanggal='".TANGGAL."' where id_detail='".$tId."' and id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."'");
				}
				else {
					$tId=substr(((getValue("id_detail","php_detail","id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."' order by id_detail desc limit 0,1")*1)+1001),1,3);
					queryDb("insert into php_detail(id_detail,id_donatur,id_program,id_giat,nama,nominal,tanggal) values('".$tId."','".$_GT['id1']."','".$_GT['id2']."','".$_GT['id3']."','".$tNama."','".clearRupiah($tNominal)."','".TANGGAL."')");
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
        <a href="giat.php?edit=<?=bs64_e($_GT['id3'])."&order=".bs64_e($_GT['order3'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2'])?>" class="on">KEGIATAN</a>
        <a href="#">DETAIL</a>
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
	$sql="select d.id_detail,d.nama,d.nominal from php_detail d
				where d.id_donatur='".$_GT['id1']."' and d.id_program='".$_GT['id2']."' and d.id_giat='".$_GT['id3']."'
				order by ".(($_GT['order'])?$_GT['order'].",d.tanggal desc":"d.tanggal desc");
					
	$a=queryDb($sql);
	while($b=mysql_fetch_array($a)) {
		$paging['start']++;
		echo "<input type=\"hidden\" id=\"tId-".$b['id_detail']."\" value=\"".$b['id_detail']."\" \>";
		echo "<input type=\"hidden\" id=\"tIdDetail-".$b['id_detail']."\" value=\"".$_GT['id1'].".".$_GT['id2'].".".$_GT['id3'].".".$b['id_detail']."\" \>";
		echo "<input type=\"hidden\" id=\"tNama-".$b['id_detail']."\" value=\"".$b['nama']."\" \>";
		echo "<input type=\"hidden\" id=\"tNominal-".$b['id_detail']."\" value=\"".showRupiah2($b['nominal'])."\" \>";
		echo "<ul id=\"".$b['id_detail']."\" onclick=\"listFocus(this,1,1)\"><li>".$paging['start'].".</li><li>".$_GT['id1'].".".$_GT['id2'].".".$_GT['id3'].".".$b['id_detail']."</li><li>".$b['nama']."</li><li>".showRupiah($b['nominal'])."</li></ul>";
	
		if($_GT['edit']==$b['id_detail']) $jsEdit="listFocus(elm('".$b['id_detail']."'),1,1);";
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li class="r" style="width:auto;">
    	  <button class="icon-new" onclick="elm('tIdDetail').value='<?=$_GT['id1'].".".$_GT['id2'].".".$_GT['id3'].".".substr(((getValue("id_detail","php_detail","id_donatur='".$_GT['id1']."' and id_program='".$_GT['id2']."' and id_giat='".$_GT['id3']."' order by id_detail desc limit 0,1")*1)+1001),1,3)?>';newData('tId,tNama,tNominal');">TAMBAH</button>
    	  <button id="bEdit" class="icon-edit disabled" onclick="editData('tId,tNama,tNominal,tIdDetail');">EDIT</button>
    	  <button id="bDelete" class="icon-delete disabled" onclick="deleteData('delete,id1,hal,sort,search,order1,id2,order2,id3,order3');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtNama || $errtNominal)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
