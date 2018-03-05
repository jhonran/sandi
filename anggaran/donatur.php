<?php
	//session_start();
	error_reporting(0);
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","php_user u
											inner join php_hakakses h on h.id_akses=u.id_akses
											inner join php_otoritas_sub s on s.id_otoritas_sub=h.id_otoritas_sub and s.page='".basename($_SERVER["SCRIPT_FILENAME"])."'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		if($_GT['delete']) {
			if(!getValue("1","php_program","id_donatur='".$_GT['delete']."' limit 0,1")) {
				queryDb("delete from php_donatur where id_donatur='".$_GT['delete']."'");
				header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdDonatur=$_PT['tIdDonatur'];
			$tNama=$_PT['tNama'];
			$tAlamat=$_PT['tAlamat'];
			
			$errtNama=(!$tNama)?"Data Nama masih kosong":((getValue("1","php_donatur","nama='".$tNama."' and id_donatur<>'".$tId."' limit 1"))?"Nama Donatur sudah terdaftar":"");
			$errtAlamat=(!$tAlamat)?"Data Alamat masih kosong":"";
			
			if(!$errtNama && !$errtAlamat) {				
				if($tId) {
					queryDb("update php_donatur set nama='".$tNama."',alamat='".$tAlamat."',tanggal='".TANGGAL."' where id_donatur='".$tId."'");
				}
				else {
					$tId=substr(((getValue("id_donatur","php_donatur","1=1 order by id_donatur desc limit 0,1")*1)+1001),1,3);
					queryDb("insert into php_donatur(id_donatur,nama,alamat,tanggal) values('".$tId."','".$tNama."','".$tAlamat."','".TANGGAL."')");
				}
				
				header("location:?edit=".bs64_e($tId));
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:110px;text-align:center; }
#titleTop ol li:nth-child(4) , #list ul li:nth-child(4) { width:300px; }
#titleTop ol li:nth-child(5) , #list ul li:nth-child(5) { width:120px; }
#list ul li:nth-child(5) { text-align:right; }
#titleTop ol li { text-align:center; }

table input[type=text], table input[type=password],  table select { width:300px; }
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
                  <td>ID DONATUR</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdDonatur" id="tIdDonatur" value="<?=htmlentities($tIdDonatur)?>" maxlength="3" class="readonly" readonly="readonly" />
                  </td>
                </tr>
                <tr>
                  <td>NAMA</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNama" id="tNama" maxlength="100" value="<?=htmlentities($tNama)?>" required="required" onkeyup="hideFade('errtNama');" />
                    <div id="errtNama" class="err"><?=$errtNama?></div>
                  </td>
                </tr>
                <tr>
                  <td>ALAMAT</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tAlamat" id="tAlamat" maxlength="200" value="<?=htmlentities($tAlamat)?>" required="required" onkeyup="hideFade('errtAlamat')" />
                    <div id="errtAlamat" class="err"><?=$errtAlamat?></div>
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
    <ol class="tab"><li><a href="#">DONATUR</a><a href="#" class="off">PROGRAM</a><a href="#" class="off">KEGIATAN</a><a href="#" class="off">DETAIL</a></li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("d.id_donatur#ID Donatur","d.nama#Nama","d.alamat#Alamat","nominal#Nominal");
		
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
	$row=20;
	$page=8;
	$sql="select d.id_donatur,d.nama,d.alamat,ifnull((select sum(nominal) from php_detail where id_donatur=d.id_donatur),0) as nominal,if(min(p.id_program) is null,1,0) as stat_delete from php_donatur d
					left join php_program p on p.id_donatur=d.id_donatur
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				group by d.id_donatur,d.nama,d.alamat
				order by ".(($_GT['order'])?$_GT['order'].",d.tanggal desc":"d.tanggal desc");
				
	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['id_donatur']."\" value=\"".$b['id_donatur']."\" \>";
			echo "<input type=\"hidden\" id=\"tIdDonatur-".$b['id_donatur']."\" value=\"".$b['id_donatur']."\" \>";
			echo "<input type=\"hidden\" id=\"tNama-".$b['id_donatur']."\" value=\"".$b['nama']."\" \>";
			echo "<input type=\"hidden\" id=\"tAlamat-".$b['id_donatur']."\" value=\"".$b['alamat']."\" \>";
			echo "<ul id=\"".$b['id_donatur']."\" onclick=\"listFocus(this,1,".$b['stat_delete'].")\" ondblclick=\"_URI['id1']='".$b['id_donatur']."';_URI['order1']='".$_GT['order']."';goAddress('id1,order1,hal,sort,search','program.php')\"><li>".$paging['start'].".</li><li>".$b['id_donatur']."</li><li>".$b['nama']."</li><li>".$b['alamat']."</li><li>".showRupiah($b['nominal'])."</li></ul>";
		
			if($_GT['edit']==$b['id_donatur']) $jsEdit="listFocus(elm('".$b['id_donatur']."'),1,".$b['stat_delete'].");";
		}
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li style="width:auto;" class="l">
        	<select style="width:120px;" onchange="_URI['sort']=this.value;">
				<?php
				if(is_array($listTitle)) {
					reset($listTitle);
					
					if(count($listTitle)>1) echo "<option value=\"\">- Pilih -</option>";
					
					foreach($listTitle as $value) {
						$arrayValue=explode("#",$value);
						if($arrayValue[0]) echo "<option value=\"".$arrayValue[0]."\" ".(($_GT['sort']==$arrayValue[0])?"selected=\"selected\"":"").">".ucfirst($arrayValue[1])."</option>";
					}
				}
                ?>
            </select>
            <input type="text" style="width:120px;" onblur="_URI['search']=this.value;" onmouseout="_URI['search']=this.value;" value="<?=$_GT['search']?>" />
            <button class="icon-search" onclick="goAddress('sort,search,order');">SEARCH</button>
        </li>
    	<li style="width:auto;" class="paging">
			<span style="margin-right:40px;"><?=$paging['show']?></span><?=(($paging['page'])?"<span>Page :</span>".$paging['page']:"")?>
        </li>
    	<li class="r" style="width:auto;">
    	  <button class="icon-new" onclick="elm('tIdDonatur').value='<?=substr(((getValue("id_donatur","php_donatur","1=1 order by id_donatur desc limit 0,1")*1)+1001),1,3)?>';newData('tId,tNama,tAlamat');">TAMBAH</button>
    	  <button id="bEdit" class="icon-edit disabled" onclick="editData('tId,tNama,tIdDonatur,tAlamat');">EDIT</button>
    	  <button id="bDelete" class="icon-delete disabled" onclick="deleteData('delete,hal,sort,search,order');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtNama || $errtAlamat)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
