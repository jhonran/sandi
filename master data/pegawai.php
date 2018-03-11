<?php
	error_reporting(0);
	//session_start();
	
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
		$idAkses = getValue("id_akses","t_user","id_user='".$_SESSION['user']."'");
		if($idAkses !== '01') {
			$firstdate = date("Y-m-01");
			$today = date("Y-m-d");
			$aPosting = "SELECT tgl_finish FROM `t_posting` WHERE tgl_finish BETWEEN '".$firstdate."' AND '".$today."' ORDER BY tgl_finish DESC LIMIT 1";
			$bPosting = queryDb($aPosting);
			$cPosting = mysql_num_rows($bPosting);
			if($cPosting == 0) {
				$statPosting = '0';
			} else {
				$statPosting = '1';
			}
		}
		if($_GT['del']) {
			if(!getValue("1","t_panjar","id_pegawai='".$_GT['del']."' limit 0,1")) {
				queryDb("delete from t_pegawai where id_pegawai='".$_GT['del']."'");
				header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdPegawai=$_PT['tIdPegawai'];
			$tNama=$_PT['tNama'];
			$tJabatan=$_PT['tJabatan'];
			$tStatPeg=$_PT['tStatPeg'];
			
			$errtIdPegawai=(!preg_match(VALCHAR,$tIdPegawai))?"ID ".$_IST['pegawai.php']." masih kosong atau format salah":((getValue("1","t_pegawai","id_pegawai='".$tIdPegawai."' and id_pegawai<>'".$tId."' limit 1"))?"ID ".$_IST['pegawai.php']." sudah terdaftar":"");
			$errtNama=(!$tNama)?"Data Nama masih kosong":"";
			$errtJabatan=(!$tJabatan)?"Data Jabatan masih kosong":"";
			$errtStatPeg=(!$tStatPeg)?"Data Status ".$_IST['pegawai.php']." masih kosong":((!getValue("1","t_statpegawai","id_statpegawai='".$tStatPeg."' limit 1"))?"Data Status ".$_IST['pegawai.php']." tidak terdaftar":"");
			
			if(!$errtIdPegawai && !$errtNama && !$errtJabatan && !$errtStatPeg) {				
				if($tId) {
					queryDb("update t_pegawai set id_pegawai='".$tIdPegawai."',nama='".$tNama."',jabatan='".$tJabatan."',id_statpegawai='".$tStatPeg."',tanggal='".TANGGAL."' where id_pegawai='".$tId."'");
				}
				else {
					queryDb("insert into t_pegawai(id_pegawai,nama,jabatan,id_statpegawai,tanggal) values('".$tIdPegawai."','".$tNama."','".$tJabatan."','".$tStatPeg."','".TANGGAL."')");
				}
				
				header("location:?edit=".bs64_e($tIdPegawai));
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:150px;text-align:center; }
#titleTop ol li:nth-child(4) , #list ul li:nth-child(4) { width:250px; }
#titleTop ol li:nth-child(5) , #list ul li:nth-child(5) { width:200px;text-align:center; }
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
                  <td>ID <?=strtoupper($_IST['pegawai.php'])?></td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdPegawai" id="tIdPegawai" value="<?=htmlentities($tIdPegawai)?>" maxlength="18" required="required" onkeyup="hideFade('errtIdPegawai')" />
                    <div id="errtIdPegawai" class="err"><?=$errtIdPegawai?></div>
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
                  <td>JABATAN</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tJabatan" id="tJabatan" maxlength="100" value="<?=htmlentities($tJabatan)?>" required="required" onkeyup="hideFade('errtJabatan')" />
                    <div id="errtJabatan" class="err"><?=$errtJabatan?></div>
                  </td>
                </tr>
                <tr>
                  <td>STAT <?=strtoupper($_IST['pegawai.php'])?></td>
                  <td>:</td>
                  <td>
                    <select name="tStatPeg" id="tStatPeg" required="required" onchange="hideFade('errtStatPeg')">
                    	<option value="">- Pilih -</option>
                        <?php
                        $a=queryDb("select id_statpegawai,nama from t_statpegawai order by id_statpegawai");
						while($b=mysql_fetch_array($a)) {
							echo "<option value=\"".$b['id_statpegawai']."\" ".(($tStatPeg==$b['id_statpegawai'])?"selected='selected'":"").">".$b['nama']."</option>";
						}
						?>
                    </select>
                    <div id="errtStatPeg" class="err"><?=$errtStatPeg?></div>
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
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("d.id_pegawai#ID ".$_IST['pegawai.php'],"d.nama#Nama","d.jabatan#Jabatan","s.nama#Status ".$_IST['pegawai.php']);
		
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
	$sql="select d.id_pegawai,d.nama,d.jabatan,s.id_statpegawai,s.nama as statpeg,if(min(j.id_pegawai) is null,1,0) as stat_delete from t_pegawai d
					inner join t_statpegawai s on s.id_statpegawai=d.id_statpegawai
					left join t_panjar j on j.id_pegawai=d.id_pegawai
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				group by d.id_pegawai,d.nama,d.jabatan,s.id_statpegawai,s.nama
				order by ".(($_GT['order'])?$_GT['order'].",d.tanggal desc":"d.tanggal desc");
				
	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['id_pegawai']."\" value=\"".$b['id_pegawai']."\" />";
			echo "<input type=\"hidden\" id=\"tIdPegawai-".$b['id_pegawai']."\" value=\"".$b['id_pegawai']."\" />";
			echo "<input type=\"hidden\" id=\"tNama-".$b['id_pegawai']."\" value=\"".$b['nama']."\" />";
			echo "<input type=\"hidden\" id=\"tJabatan-".$b['id_pegawai']."\" value=\"".$b['jabatan']."\" />";
			echo "<input type=\"hidden\" id=\"tStatPeg-".$b['id_pegawai']."\" value=\"".$b['id_statpegawai']."\" />";
			echo "<ul id=\"".$b['id_pegawai']."\" onclick=\"listFocus(this,'edit=1,del=".$b['stat_delete']."')\"><li>".$paging['start'].".</li><li>".$b['id_pegawai']."</li><li>".$b['nama']."</li><li>".$b['jabatan']."</li><li>".$b['statpeg']."</li></ul>";
		
			if($_GT['edit']==$b['id_pegawai']) $jsEdit="listFocus(elm('".$b['id_pegawai']."'),'edit=1,del=".$b['stat_delete']."');";
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
		  <button id="btn-new" class="icon-new" onclick="newData('tId,tIdPegawai,tNama,tJabatan,tStatPeg');">TAMBAH</button>
	      <button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tIdPegawai,tNama,tJabatan,tStatPeg');">EDIT</button>
		  <button id="btn-del" class="icon-del disabled" onclick="delData('del,hal,sort,search,order');">HAPUS</button>
		  
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtIdPegawai || $errtNama || $errtJabatan || $errtStatPeg)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
