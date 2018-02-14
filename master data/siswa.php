<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='".basename($_SERVER["SCRIPT_FILENAME"])."'",
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
		
		$_EXC=array();
		$a=queryDb("(select distinct id_siswa from t_siswa_bayar) union
						(select distinct id_siswa from t_siswa_tagih)");
		while($b=mysql_fetch_array($a)) {
			$_EXC[$b['id_siswa']]=1;
		}
		
		if($_GT['del']) {
			if(!$_EXC[$_GT['del']]) {
				queryDb("delete from t_siswa where id_siswa='".$_GT['del']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."')");
				
				header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdSiswa=$_PT['tIdSiswa'];
			$tNama=$_PT['tNama'];
			$tAlamat=$_PT['tAlamat'];
			$tKelas=$_PT['tKelas'];
			
			$errtIdSiswa=(!preg_match(VALCHAR,$tIdSiswa))?"ID ".$_IST['siswa.php']." masih kosong atau format salah":((getValue("1","t_siswa","id_siswa='".$tIdSiswa."' and id_siswa<>'".$tId."' limit 1"))?"ID ".$_IST['siswa.php']." sudah terdaftar":"");
			$errtNama=(!$tNama)?"Data Nama masih kosong":"";
			$errtAlamat=(!$tAlamat)?"Data Alamat masih kosong":"";
			$errtKelas=(!$tKelas)?$_IST['kelas.php']." Belum dipilih":((!getValue("1","t_kelas","id_kelas='".$tKelas."' and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."') limit 1"))?$_IST['kelas.php']." tidak terdaftar":"");	
			
			if(!$errtIdSiswa && !$errtNama && !$errtAlamat && !$errtKelas) {				
				if($tId) {
					queryDb("update t_siswa m
										inner join t_kelas j on j.id_kelas='".$tKelas."' and concat(j.id_unit1,'.',j.id_unit2,'.',j.id_unit3) in ('".implode("','",array_keys($_UNITS))."') 
									set m.id_siswa='".$tIdSiswa."',m.id_unit1=j.id_unit1,m.id_unit2=j.id_unit2,m.id_unit3=j.id_unit3,m.id_kelas=j.id_kelas,m.nama='".$tNama."',m.alamat='".$tAlamat."',m.tanggal='".TANGGAL."'
									where m.id_siswa='".$tId."' and  concat(m.id_unit1,'.',m.id_unit2,'.',m.id_unit3) in ('".implode("','",array_keys($_UNITS))."') ");
				}
				else {
					queryDb("insert into t_siswa(id_siswa,id_unit1,id_unit2,id_unit3,id_kelas,nama,alamat,tanggal) 
									select '".$tIdSiswa."',id_unit1,id_unit2,id_unit3,id_kelas,'".$tNama."','".$tAlamat."','".TANGGAL."' from t_kelas where id_kelas='".$tKelas."' and  concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."')");
				}
				
				header("location:?edit=".bs64_e($tIdSiswa));
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:125px;text-align:center; }
#titleTop ol li:nth-child(3) , #list ul li:nth-child(3) { width:240px; }
#titleTop ol li:nth-child(5) , #list ul li:nth-child(5) { width:180px;text-align:center; }
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
                  <td>ID <?=strtoupper($_IST['siswa.php'])?></td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdSiswa" id="tIdSiswa" value="<?=htmlentities($tIdSiswa)?>" maxlength="24" required="required" onkeyup="hideFade('errtIdSiswa')" />
                    <div id="errtIdSiswa" class="err"><?=$errtIdSiswa?></div>
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
                  <td>ALAMAT</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tAlamat" id="tAlamat" maxlength="200" value="<?=htmlentities($tAlamat)?>" required="required" onkeyup="hideFade('errtAlamat')" />
                    <div id="errtAlamat" class="err"><?=$errtAlamat?></div>
                  </td>
                </tr>
                <tr>
                  <td><?=strtoupper($_IST['kelas.php'])?></td>
                  <td>:</td>
                  <td>
                  	<select name="tKelas" id="tKelas" required="required" onchange="hideFade('errtKelas')">
                    	<option value="">- Pilih -</option>
                        <?php
                        $a=queryDb("select id_kelas,nama from t_kelas where concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."')");
						while($b=mysql_fetch_array($a)) {
							echo "<option value=\"".$b['id_kelas']."\" ".(($tKelas==$b['id_kelas'])?"selected='selected'":"").">".$b['id_kelas']." - ".$b['nama']."</option>";
						}
						?>
                    </select>
                    <div id="errtKelas" class="err"><?=$errtKelas?></div>
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
    <ol class="tab"><li><a href="#"><?=strtoupper($_IST['siswa.php'])?></a><a href="#" class="off">SETTING JENIS PENERIMAAN</a></li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("m.id_siswa#ID ".$_IST['siswa.php'],"m.nama#Nama","m.alamat#Alamat","concat(j.id_kelas,' ',j.nama)#".$_IST['kelas.php']);
		
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
	$sql="select m.id_siswa,m.nama,m.alamat,j.id_kelas,j.nama as kelas from t_siswa m
					inner join t_kelas j on j.id_kelas=m.id_kelas and j.id_unit1=m.id_unit1 and j.id_unit2=m.id_unit2 and j.id_unit3=m.id_unit3
				where concat(m.id_unit1,'.',m.id_unit2,'.',m.id_unit3) in ('".implode("','",array_keys($_UNITS))."')
						".(($_GT['sort'] && $_GT['search'])?"and lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				order by ".(($_GT['order'])?$_GT['order'].",m.tanggal desc":"m.tanggal desc");
				
	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['id_siswa']."\" value=\"".$b['id_siswa']."\" />";
			echo "<input type=\"hidden\" id=\"tIdSiswa-".$b['id_siswa']."\" value=\"".$b['id_siswa']."\" />";
			echo "<input type=\"hidden\" id=\"tNama-".$b['id_siswa']."\" value=\"".$b['nama']."\" />";
			echo "<input type=\"hidden\" id=\"tAlamat-".$b['id_siswa']."\" value=\"".$b['alamat']."\" />";
			echo "<input type=\"hidden\" id=\"tKelas-".$b['id_siswa']."\" value=\"".$b['id_kelas']."\" />";
			echo "<ul id=\"".$b['id_siswa']."\" onclick=\"listFocus(this,'edit=1,del=".(!$_EXC[$b['id_siswa']])."')\" ondblclick=\"_URI['id1']='".$b['id_siswa']."';_URI['order1']='".$_GT['order']."';goAddress('id1,order1,hal,sort,search','siswa_detail.php')\"><li>".$paging['start'].".</li><li>".$b['id_siswa']."</li><li>".$b['nama']."</li><li>".$b['alamat']."</li><li>".$b['id_kelas']." - ".$b['kelas']."</li></ul>";
		
			if($_GT['edit']==$b['id_siswa']) $jsEdit="listFocus(elm('".$b['id_siswa']."'),'edit=1,del=".(!$_EXC[$b['id_siswa']])."');";
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
    	  <button id="btn-new" class="icon-new" onclick="newData('tId,tIdSiswa,tNama,tAlamat,tKelas');">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tIdSiswa,tNama,tAlamat,tKelas');">EDIT</button>
    	  <button id="btn-del" class="icon-del disabled" onclick="delData('del,hal,sort,search,order');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtIdSiswa || $errtNama || $errtAlamat || $errtKelas)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
