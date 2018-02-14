<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='".basename($_SERVER["SCRIPT_FILENAME"])."'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		/*if($_GT['del']) {
			queryDb("delete from t_mtrans where id_mtrans='".$_GT['del']."'");
			
			header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
			exit;
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdTrans=$_PT['tIdTrans'];
			$tNama=$_PT['tNama'];
			$errtIdTrans=(!$tIdTrans || !is_numeric($tIdTrans) || ($tIdTrans*1)<1 || ($tIdTrans*1)>99)?"Data ID Transaksi masih kosong atau bukan angka":((getValue("1","t_mtrans","id_mtrans='".substr((($tIdTrans*1)+100),1,2)."' and id_mtrans<>'".$tId."' limit 1"))?"ID Transaksi sudah terdaftar":"");
			$errtNama=(!$tNama)?"Data Nama masih kosong":((getValue("1","t_mtrans","nama='".$tNama."' and id_mtrans<>'".$tId."' limit 1"))?"Nama Trans sudah terdaftar":"");
			
			if(!$errtIdTrans && !$errtNama) {
				$tIdTrans=substr((($tIdTrans*1)+100),1,2);	
				if($tId) {
					queryDb("update t_mtrans set id_mtrans='".$tIdTrans."',nama='".$tNama."',nama='".$tNama."',tanggal='".TANGGAL."' where id_mtrans='".$tId."'");
				}
				else {
					queryDb("insert into t_mtrans(id_mtrans,nama,tanggal) values('".$tIdTrans."','".$tNama."','".TANGGAL."')");
				}
				
				header("location:?edit=".bs64_e($tIdTrans));
				exit;
			}
		}*/
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
#input table ul li { text-align:left;width:auto; }
#input table ul li:first-child { width:30px; }
#titleTop ol li:nth-child(1) , #list ul li:nth-child(1) { width:30px;text-align:center; }
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:110px;text-align:center; }
#titleTop ol li { text-align:center; }

table input[type=text] { width:400px; }
table div.cMenu div.sMenu { height:auto; }
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
                  <td>ID TRANSAKSI</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdTrans" id="tIdTrans" required="required" value="<?=htmlentities($tIdTrans)?>" maxlength="2" onkeyup="hideFade('errtIdTrans');" />
                    <div id="errtIdTrans" class="err"><?=$errtIdTrans?></div>
                  </td>
                </tr>
                <tr>
                  <td>NAMA</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNama" id="tNama" maxlength="200" value="<?=htmlentities($tNama)?>" required="required" onkeyup="hideFade('errtNama')" />
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
    	<a href="#">MASTER TRANSAKSI</a>
        <a href="#" class="off"><?=strtoupper($_IST['gl1.php'])?></a>
    </li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("a.id_mtrans#ID TRANS","a.nama#Nama");
		
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
	$row=50;
	$page=8;
	$sql="select a.id_mtrans,a.nama from t_mtrans a
				where status='1' ".(($_GT['sort'] && $_GT['search'])?"and lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				order by ".(($_GT['order'])?$_GT['order'].",a.tanggal desc":"a.tanggal desc");
	
	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['id_mtrans']."\" value=\"".$b['id_mtrans']."\" />";
			echo "<input type=\"hidden\" id=\"tIdTrans-".$b['id_mtrans']."\" value=\"".$b['id_mtrans']."\" />";
			echo "<input type=\"hidden\" id=\"tNama-".$b['id_mtrans']."\" value=\"".$b['nama']."\" />";
			echo "<ul id=\"".$b['id_mtrans']."\" onclick=\"listFocus(this,'edit=1,del=1')\"><li>".$paging['start'].".</li><li>".$b['id_mtrans']."</li><li>".$b['nama']."</li></ul>";
			
			if($_GT['edit']==$b['id_mtrans']) $jsEdit="listFocus(elm('".$b['id_mtrans']."'),'edit=1,del=1');";
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
            <input type="reset" class="icon-search" onclick="goAddress('sort,search,order,tab');" value="SEARCH" />
        </li>
    	<li style="width:auto;" class="paging">
			<span style="margin-right:40px;"><?=$paging['show']?></span><?=(($paging['page'])?"<span>Page :</span>".$paging['page']:"")?>
        </li>
    	<li class="r" style="width:auto;">
    	  	<button class="icon-edit" onclick="_URI['mutasi']='DB'; if(_URI['edit']) goAddress('edit,hal,order,sort,search,mutasi','trans_gl_set.php');">SET <?=strtoupper($_IST['gl1.php'])?> DEBIT</button>
    	  	<button class="icon-edit" onclick="_URI['mutasi']='CR'; if(_URI['edit']) goAddress('edit,hal,order,sort,search,mutasi','trans_gl_set.php');">SET <?=strtoupper($_IST['gl1.php'])?> KREDIT</button>
    	  <!--<button id="btn-new" class="icon-new" onclick="newData('tId,tIdTrans,tNama');">TAMBAH</button>-->
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tIdTrans,tNama');" style="display:none;">EDIT</button>
    	  <!--<button id="btn-del" class="icon-del disabled" onclick="delData('del,hal,sort,search,order');">HAPUS</button>-->
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtIdTrans || $errtNama)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
