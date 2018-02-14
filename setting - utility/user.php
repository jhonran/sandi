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
		
		$_EXC=array();
		$a=queryDb("(select distinct id_user from t_danaanggaran) union
						(select distinct id_user from t_glt) union
						(select distinct id_user from t_panjar) union
						(select distinct id_user2 as id_user from t_panjar) union
						(select distinct id_user3 as id_user from t_panjar) union
						(select distinct id_user from t_posting) union
						(select distinct id_user from t_siswa_bayar) union
						(select distinct id_user from t_siswa_pengakuan) union
						(select distinct id_user from t_siswa_tagih) union
						(select distinct id_user from t_susut)");
		while($b=mysql_fetch_array($a)) {
			$_EXC[$b['id_user']]=1;
		}
		
		if($_GT['del']) {
			if(!$_EXC[$_GT['del']]) {
				queryDb("delete from t_user where id_user='".$_GT['del']."'");
				header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tUsername=strtolower($_PT['tUsername']);
			$tPassword=$_PT['tPassword'];
			$tNama=$_PT['tNama'];
			$tEntitas=$_PT['tEntitas'];
			$tAkses=$_PT['tAkses'];
			$tStatHapus=($_PT['tStatHapus'])?"1":"0";
			
			$errtUsername=(!preg_match(VALCHAR2,$tUsername))?"Data Username masih kosong atau format salah":((getValue("1","t_user","id_user='".$tUsername."' and id_user<>'".$tId."' limit 1"))?"Username sudah terdaftar":"");
			$errtPassword=(!$tId && !preg_match(VALCHAR2,$tPassword))?"Data Password masih kosong atau format salah":($tPassword && (!preg_match(VALCHAR2,$tPassword))?"Data Password masih kosong atau format salah":"");
			$errtNama=(!$tNama)?"Data Nama masih kosong":"";
			$errtEntitas=(!$tEntitas)?"Data Entitas masih kosong":((!getValue("1","t_entitas","id_entitas='".$tEntitas."' limit 1"))?"Data Entitas tidak terdaftar":"");
			$errtAkses=(!$tAkses)?"Data Akses masih kosong":((!getValue("1","t_akses","id_akses='".$tAkses."' limit 1"))?"Data Akses tidak terdaftar":"");
			
			if(!$errtUsername && !$errtPassword && !$errtNama && !$errtEntitas && !$errtAkses) {				
				if($tId) {
					queryDb("update t_user set id_user='".$tUsername."',".(($tPassword)?"pass=PASSWORD('".$tPassword."'),":"")."nama='".$tNama."',id_entitas='".$tEntitas."',id_akses='".$tAkses."',stat_hapus_trans='".$tStatHapus."',tanggal='".TANGGAL."' where id_user='".$tId."'");
				}
				else {
					queryDb("insert into t_user(id_user,pass,nama,id_entitas,id_akses,stat_hapus_trans,tanggal) values('".$tUsername."',PASSWORD('".$tPassword."'),'".$tNama."','".$tEntitas."','".$tAkses."','".$tStatHapus."','".TANGGAL."')");
				}
				
				header("location:?edit=".bs64_e($tUsername));
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:150px; }
#titleTop ol li:nth-child(4) , #list ul li:nth-child(4) { width:200px; }
#titleTop ol li:nth-child(5) , #list ul li:nth-child(5) { width:150px; }
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
                  <td>USERNAME</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tUsername" id="tUsername" value="<?=htmlentities($tUsername)?>" maxlength="25" required="required" onkeyup="hideFade('errtUsername')" />
                    <div id="errtUsername" class="err"><?=$errtUsername?></div>
                  </td>
                </tr>
                <tr>
                  <td>PASSWORD</td>
                  <td>:</td>
                  <td><input type="password" name="tPassword" id="tPassword" maxlength="12" onkeyup="hideFade('errtPassword')" />
                    <div id="errtPassword" class="err"><?=$errtPassword?></div>
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
                  <td>ENTITAS</td>
                  <td>:</td>
                  <td>
                  	<select name="tEntitas" id="tEntitas" required="required" onchange="hideFade('errtEntitas')">
                    	<option value="">- Pilih -</option>
                        <?php
                        $a=queryDb("select id_entitas,nama from t_entitas order by id_entitas");
						while($b=mysql_fetch_array($a)) {
							echo "<option value=\"".$b['id_entitas']."\" ".(($tEntitas==$b['id_entitas'])?"selected='selected'":"").">[".$b['id_entitas']."] ".$b['nama']."</option>";
						}
						?>
                    </select>
                    <div id="errtEntitas" class="err"><?=$errtEntitas?></div>
                  </td>
                </tr>
                <tr>
                  <td>HAK AKSES</td>
                  <td>:</td>
                  <td>
                  	<select name="tAkses" id="tAkses" required="required" onchange="hideFade('errtAkses')">
                    	<option value="">- Pilih -</option>
                        <?php
                        $a=queryDb("select id_akses,nama from t_akses order by id_akses");
						while($b=mysql_fetch_array($a)) {
							echo "<option value=\"".$b['id_akses']."\" ".(($tAkses==$b['id_akses'])?"selected='selected'":"").">[".$b['id_akses']."] ".$b['nama']."</option>";
						}
						?>
                    </select>
                    <div id="errtAkses" class="err"><?=$errtAkses?></div>
                  </td>
                </tr>
                <tr>
                  <td></td>
                  <td>:</td>
                  <td>
                  	<input type="checkbox" name="tStatHapus" id="tStatHapus" value="1" <?php echo ($tStatHapus==1)?"checked='checked'":""; ?> /> <strong>Otoritas Hapus Transaksi</strong>
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
        $listTitle=array("u.id_user#Username","u.nama#Nama User","e.nama#Entitas","a.nama#Hak Akses");
		
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
	$sql="select u.id_user,u.nama,e.nama as entitas,a.nama as akses,e.id_entitas,u.id_akses,u.stat_hapus_trans from t_user u
					inner join t_entitas e on e.id_entitas=u.id_entitas
					inner join t_akses a on a.id_akses=u.id_akses
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				order by ".(($_GT['order'])?$_GT['order'].",u.tanggal desc":"u.tanggal desc");
				
	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['id_user']."\" value=\"".$b['id_user']."\" />";
			echo "<input type=\"hidden\" id=\"tUsername-".$b['id_user']."\" value=\"".$b['id_user']."\" />";
			echo "<input type=\"hidden\" id=\"tNama-".$b['id_user']."\" value=\"".$b['nama']."\" >";
			echo "<input type=\"hidden\" id=\"tEntitas-".$b['id_user']."\" value=\"".$b['id_entitas']."\" />";
			echo "<input type=\"hidden\" id=\"tAkses-".$b['id_user']."\" value=\"".$b['id_akses']."\" />";
			echo "<input type=\"hidden\" id=\"tStatHapus-".$b['id_user']."\" value=\"".$b['stat_hapus_trans']."\" />";
			echo "<ul id=\"".$b['id_user']."\" onclick=\"listFocus(this,'edit=1,del=".(!$_EXC[$b['id_user']])."')\"><li>".$paging['start'].".</li><li>".$b['id_user']."</li><li>".$b['nama']."</li><li>".$b['entitas']."</li><li>".$b['akses']."</li></ul>";
		
			if($_GT['edit']==$b['id_user']) $jsEdit="listFocus(elm('".$b['id_user']."'),'edit=1,del=".(!$_EXC[$b['id_user']])."');";
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
    	  <button id="btn-new" class="icon-new" onclick="newData('tId,tUsername,tNama,tEntitas,tAkses,tStatHapus');">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tUsername,tNama,tEntitas,tAkses,tStatHapus');">EDIT</button>
    	  <button id="btn-del" class="icon-del disabled" onclick="delData('del,hal,sort,search,order');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtUsername || $errtPassword || $errtNama || $errtEntitas || $errtAkses)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
