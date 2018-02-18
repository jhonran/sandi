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
		
		//$akunYD="1,2,3";
		//$akunYC="1,2,3";
		define("MTRANS","07");
		
		$a=queryDb("select m.mutasi,g.id_gl4,concat('[',g.id_gl4,'] ',g.nama) as nm from t_gl4 g
							inner join t_mtrans_gl m on m.id_gl4=g.id_gl4 and m.id_mtrans='".MTRANS."'
						group by m.mutasi,g.id_gl4,nm 
						order by m.mutasi,g.id_gl4");
		while($b=mysql_fetch_array($a)) {
			$_AKUN[$b['mutasi']][$b['id_gl4']]=$b['nm'];
		}
		
		if(count($_AKUN['DB'])==1) {
			$iAkunD=key($_AKUN['DB']);
			$nAkunD=reset($_AKUN['DB']);
		}
		
		if(count($_AKUN['CR'])==1) {
			$iAkunC=key($_AKUN['CR']);
			$nAkunC=reset($_AKUN['CR']);
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdAsetKategori=$_PT['tIdAsetKategori'];
			$tNama=$_PT['tNama'];
			$tMasaSusut=($_PT['tMasaSusut'])?$_PT['tMasaSusut']:0;
			$tMetode=$_PT['tMetode'];
			$iAkunD=$_PT['iAkunD'];
			$nAkunD=$_PT['nAkunD'];
			$iAkunC=$_PT['iAkunC'];
			$nAkunC=$_PT['nAkunC'];
			
			$errtNama=(!$tNama)?"Data Nama masih kosong":((getValue("1","t_aset_kategori","nama='".$tNama."' and id_aset_kategori<>'".$tId."' limit 1"))?"Nama Kategori Aset sudah terdaftar":"");			
			$errtMasaSusut=(!is_numeric($tMasaSusut))?"Data Umur Manfaat bukan angka":"";
			$errtMetode=($tMasaSusut>0 && !$tMetode)?"Metode Penyusutan Belum dipilih":(($tMasaSusut>0 && !getValue("1","t_susut_rumus","id_susut_rumus='".$tMetode."' limit 1"))?"Metode Penyusutan tidak terdaftar":"");	
			//$errnAkunD=(!$iAkunD)?$_IST['gl1.php']." Debit masih kosong":((!getValue("1","t_gl4","id_gl4='".$iAkunD."' and (id_gl4 like '".str_replace(",","%' or id_gl4 like '",$akunYD)."%') limit 1"))?$_IST['gl1.php']." Debit salah":"");	
			//$errnAkunC=(!$iAkunC)?$_IST['gl1.php']." Kredit masih kosong":((!getValue("1","t_gl4","id_gl4='".$iAkunC."' and (id_gl4 like '".str_replace(",","%' or id_gl4 like '",$akunYC)."%') limit 1"))?$_IST['gl1.php']." Kredit salah":"");	
			$errnAkunD=(!$iAkunD)?$_IST['gl1.php']." Debit masih kosong":((!isset($_AKUN['DB'][$iAkunD]))?$_IST['gl1.php']." Debit tidak terdaftar":"");
			$errnAkunC=(!$iAkunC)?$_IST['gl1.php']." Kredit masih kosong":((!isset($_AKUN['CR'][$iAkunC]))?$_IST['gl1.php']." Kredit tidak terdaftar":"");
			
			if(!$errtNama && !$errtMasaSusut && !$errtMetode && !$errnAkunD && !$errnAkunC) {				
				if($tId) {
					queryDb("update t_aset_kategori a
									left join t_susut_rumus s on s.id_susut_rumus='".$tMetode."'
								set a.nama='".$tNama."',a.masa_susut='".$tMasaSusut."',a.id_susut_rumus=ifnull(s.id_susut_rumus,''),
									a.rumus=ifnull(s.rumus,''),a.id_gl4_db='".$iAkunD."',a.id_gl4_cr='".$iAkunC."',a.tanggal='".TANGGAL."'
								where a.id_aset_kategori='".$tId."'");
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
#titleTop ol li:nth-child(4) , #list ul li:nth-child(4) { width:130px;text-align:center; }
#list ul li:nth-child(5) { text-align:right; }
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
                  <td>ID KATEGORI</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdAsetKategori" id="tIdAsetKategori" value="<?=htmlentities($tIdAsetKategori)?>" maxlength="2" class="readonly" readonly="readonly" />
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
                  <td>UMUR MANFAAT</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tMasaSusut" id="tMasaSusut" placeholder="- isikan periode penyusutan dalam <?=PRDSUSUT?> -" maxlength="3" value="<?=htmlentities($tMasaSusut)?>" required="required" onkeyup="hideFade('errtMasaSusut');" />
                    <div id="errtMasaSusut" class="err"><?=$errtMasaSusut?></div>
                  </td>
                </tr>
                <tr>
                  <td>METODE PENYUSUTAN</td>
                  <td>:</td>
                  <td>
                  	<select name="tMetode" id="tMetode" onchange="hideFade('errtMetode')">
                    	<option value="">- Pilih -</option>
                        <?php
                        $a=queryDb("select id_susut_rumus,nama from t_susut_rumus order by id_susut_rumus");
						while($b=mysql_fetch_array($a)) {
							echo "<option value=\"".$b['id_susut_rumus']."\" ".(($tMetode==$b['id_susut_rumus'])?"selected='selected'":"").">[".$b['id_susut_rumus']."] ".$b['nama']."</option>";
						}
						?>
                    </select>
                    <div id="errtMetode" class="err"><?=$errtMetode?></div>
                  </td>
                </tr>
                <!--<tr>
                  <td><?=strtoupper($_IST['gl1.php'])?> (DEBIT)</td>
                  <td>:</td>
                  <td>
                      <input type="hidden" name="iAkunD" id="iAkunD" value="<?=htmlentities($iAkunD)?>" />
                      <input name="nAkunD" type="text" id="nAkunD" maxlength="200" value="<?=htmlentities($nAkunD)?>" autocomplete="off" onkeyup="goFramePopup(this,'AkunD','akun_all','D','<?=$akunYD?>','');hideFade('errnAkunD');" onfocus="framePopupFocus('AkunD')" onblur="framePopupBlur('AkunD')" required="required" />
                      <div class="framePopup"><img id="imgAkunD" src="../images/loader.gif" /><iframe id="fAkunD"></iframe></div>
                    <div id="errnAkunD" class="err"><?=$errnAkunD?></div>
                  </td>
                </tr>
                <tr>
                  <td><?=strtoupper($_IST['gl1.php'])?> (KREDIT)</td>
                  <td>:</td>
                  <td>
                      <input type="hidden" name="iAkunC" id="iAkunC" value="<?=htmlentities($iAkunC)?>" />
                      <input name="nAkunC" type="text" id="nAkunC" maxlength="200" value="<?=htmlentities($nAkunC)?>" autocomplete="off" onkeyup="goFramePopup(this,'AkunC','akun_all','C','<?=$akunYC?>','');hideFade('errnAkunC');" onfocus="framePopupFocus('AkunC')" onblur="framePopupBlur('AkunC')" required="required" />
                      <div class="framePopup"><img id="imgAkunC" src="../images/loader.gif" /><iframe id="fAkunC"></iframe></div>
                    <div id="errnAkunC" class="err"><?=$errnAkunC?></div>
                  </td>
                </tr>-->                
                <tr>
                  <td><?=strtoupper($_IST['gl1.php'])?> (DEBIT)</td>
                  <td>:</td>
                  <td>
                      <input type="hidden" name="iAkunD" id="iAkunD" value="<?=htmlentities($iAkunD)?>" />
                      <input name="nAkunD" type="text" id="nAkunD" maxlength="200" value="<?=htmlentities($nAkunD)?>" autocomplete="off" onkeyup="goFramePopup(this,'AkunD','akun_nonunit','D','<?=MTRANS?>','DB');hideFade('errnAkunD');" onfocus="framePopupFocus('AkunD')" onblur="framePopupBlur('AkunD')" required="required" />
                      <div class="framePopup"><img id="imgAkunD" src="../images/loader.gif" /><iframe id="fAkunD"></iframe></div>
                    <div id="errnAkunD" class="err"><?=$errnAkunD?></div>
                  </td>
                </tr>
                <tr>
                  <td><?=strtoupper($_IST['gl1.php'])?> (KREDIT)</td>
                  <td>:</td>
                  <td>
                      <input type="hidden" name="iAkunC" id="iAkunC" value="<?=htmlentities($iAkunC)?>" />
                      <input name="nAkunC" type="text" id="nAkunC" maxlength="200" value="<?=htmlentities($nAkunC)?>" autocomplete="off" onkeyup="goFramePopup(this,'AkunC','akun_nonunit','C','<?=MTRANS?>','CR');hideFade('errnAkunC');" onfocus="framePopupFocus('AkunC')" onblur="framePopupBlur('AkunC')" required="required" />
                      <div class="framePopup"><img id="imgAkunC" src="../images/loader.gif" /><iframe id="fAkunC"></iframe></div>
                    <div id="errnAkunC" class="err"><?=$errnAkunC?></div>
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
        $listTitle=array("d.id_aset_kategori#ID Kategori","d.nama#Nama","d.masa_susut#Umur Manfaat");
		
		if(is_array($listTitle)) {
			reset($listTitle);
			foreach($listTitle as $value) {
				$arrayValue=explode("#",$value);
				echo "<li ".(($arrayValue[0])?("id=\"".$arrayValue[0]."\" onclick=\"return goOrder('order',this.id);\" ".(($_GT['order']==($arrayValue[0]." asc"))?"class=\"asc\"":(($_GT['order']==($arrayValue[0]." desc"))?"class=\"desc\"":""))." title=\"Urutkan berdasarkan ".strtoupper($arrayValue[1])."\""):"class=\"only\"")."><span>".strtoupper($arrayValue[1])."</span></li>";
			}
		}
		?>
    </ol>
</div>
<div id="list">
	<?php
	$sql="select d.id_aset_kategori,d.nama,d.masa_susut,d.id_susut_rumus,gd.id_gl4 as id_gd,concat('[',gd.id_gl4,'] ',gd.nama) as nm_gd,gc.id_gl4 as id_gc,concat('[',gc.id_gl4,'] ',gc.nama) as nm_gc from t_aset_kategori d
					left join t_gl4 gd on gd.id_gl4=d.id_gl4_db
					left join t_gl4 gc on gc.id_gl4=d.id_gl4_cr
				order by ".(($_GT['order'])?$_GT['order'].",d.tanggal desc":"d.tanggal desc");
	
	$paging['start']=0;
		
	$a=queryDb($sql);
	while($b=mysql_fetch_array($a)) {
		$paging['start']++;
		echo "<input type=\"hidden\" id=\"tId-".$b['id_aset_kategori']."\" value=\"".$b['id_aset_kategori']."\" />";
		echo "<input type=\"hidden\" id=\"tIdAsetKategori-".$b['id_aset_kategori']."\" value=\"".$b['id_aset_kategori']."\" />";
		echo "<input type=\"hidden\" id=\"tNama-".$b['id_aset_kategori']."\" value=\"".$b['nama']."\" />";
		echo "<input type=\"hidden\" id=\"tMasaSusut-".$b['id_aset_kategori']."\" value=\"".$b['masa_susut']."\" />";
		echo "<input type=\"hidden\" id=\"tMetode-".$b['id_aset_kategori']."\" value=\"".$b['id_susut_rumus']."\" />";
		echo "<input type=\"hidden\" id=\"iAkunD-".$b['id_aset_kategori']."\" value=\"".$b['id_gd']."\" />";
		echo "<input type=\"hidden\" id=\"nAkunD-".$b['id_aset_kategori']."\" value=\"".$b['nm_gd']."\" />";
		echo "<input type=\"hidden\" id=\"iAkunC-".$b['id_aset_kategori']."\" value=\"".$b['id_gc']."\" />";
		echo "<input type=\"hidden\" id=\"nAkunC-".$b['id_aset_kategori']."\" value=\"".$b['nm_gc']."\" />";
		echo "<ul id=\"".$b['id_aset_kategori']."\" onclick=\"listFocus(this,'edit=1')\"><li>".$paging['start'].".</li><li>".$b['id_aset_kategori']."</li><li>".$b['nama']."</li><li>".$b['masa_susut']." ".PRDSUSUT."</li></ul>";
	
		if($_GT['edit']==$b['id_aset_kategori']) $jsEdit="listFocus(elm('".$b['id_aset_kategori']."'),'edit=1');";
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li class="r" style="width:auto;">
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tNama,tId,tIdAsetKategori,tMasaSusut,tMetode,iAkunD,nAkunD,iAkunC,nAkunC');">EDIT</button>
    	</li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtNama || $errtMasaSusut || $errtMetode || $errnAkunD || $errnAkunC)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>