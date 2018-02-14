<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page in ('aset_berwujud.php','aset_tidak_berwujud.php')",
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
		$titleHTML="PENAMBAHAN/PENGURANGAN NILAI ASET";
		
		$_SESSION['waktu']=time()+1800;
		
		$lastPosting=getValue("max(finis_trans)","((select '1900-01-01' as finis_trans) UNION (select tgl_finish from t_posting order by tgl_finish desc limit 0,1)) x","1=1");
		$lastSusut=getValue("max(tgl_trans)","((select '1900-01-01' as tgl_trans) UNION (select tgl_trans from t_susut where no_trans<>'' order by tgl_trans desc limit 0,1)) x","1=1");
		
		$_REV=array();
		$_DATA['masa_susut']=array();
		$_DATA['nilai']=array();
		$_DATA['residu']=array();
		
		$a=queryDb("select a.id_aset,a.nama,a.masa_susut,ifnull(count(s.id_susut),0) as jml_susut,(a.nilai_perolehan-a.nilai_susut) as nilai,ifnull(sum(s.nominal),0) as total_susut,a.residu,a.tgl_susut,a.nilai_susut,a.stat_aktif from t_aset a
							left join t_susut s on s.id_aset=a.id_aset and s.no_trans<>''
						where a.id_aset='".$_GT['id']."' and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".implode("','",array_keys($_UNITS))."') and a.status='1'
						group by a.id_aset,a.nama,a.masa_susut,a.nilai_perolehan,a.residu,a.tgl_susut,a.nilai_susut,a.stat_aktif");
		
		while($b=mysql_fetch_array($a)) {
			$titleHTML.=" (".$b['nama'].")";
			
			$_REV[0]=$b['id_aset'];
			
			$_DATA['tgl'][0]=$b['tgl_susut'];
			$_DATA['masa_susut'][0]=$b['masa_susut'];
			$_DATA['nilai'][0]=$b['nilai'];
			$_DATA['residu'][0]=$b['residu'];
			$_DATA['ket'][0]="Input data baru";
			$_DATA['stat_delete'][0]=0;
			$xStatAktif=$b['stat_aktif'];
			
			$xJmlSusut=$b['jml_susut'];
			$xTotalSusut=$b['total_susut'];
		}
		
		$a=queryDb("select a.id_aset_rev,a.tgl_rev,a.masa_susut_tambah,a.nilai_tambah,a.residu_tambah,a.keterangan,if(a.tgl_rev>'".$lastPosting."' && a.tgl_rev>'".$lastSusut."',1,0) as stat_delete from t_aset_rev a where a.id_aset='".$_REV[0]."' order by a.tanggal asc");
		while($b=mysql_fetch_array($a)) {
			$_REV[$b['id_aset_rev']]=$b['id_aset_rev'];
			
			$_DATA['tgl'][$b['id_aset_rev']]=$b['tgl_rev'];
			$_DATA['masa_susut'][$b['id_aset_rev']]=$b['masa_susut_tambah'];
			$_DATA['nilai'][$b['id_aset_rev']]=$b['nilai_tambah'];
			$_DATA['residu'][$b['id_aset_rev']]=$b['residu_tambah'];
			$_DATA['ket'][$b['id_aset_rev']]=$b['keterangan'];
			$_DATA['stat_delete'][$b['id_aset_rev']]=$b['stat_delete'];
		}
		
		if($_REV[0] && $xStatAktif==1 && $_GT['del']) {
			$masaSusut=array_sum($_DATA['masa_susut'])-$_DATA['masa_susut'][$_GT['del']];
			$nilaiAset=(array_sum($_DATA['nilai'])-array_sum($_DATA['residu']))-($_DATA['nilai'][$_GT['del']]-$_DATA['residu'][$_GT['del']]);
			
			if(dateDiff($_DATA['tgl'][$_GT['del']],$lastPosting)>0 && dateDiff($_DATA['tgl'][$_GT['del']],$lastSusut)>0 && $masaSusut>=$xJmlSusut && $nilaiAset>=$xTotalSusut) {
				queryDb("delete from t_aset_rev where id_aset_rev='".$_GT['del']."' and id_aset='".$_REV[0]."'");
				header("location:?id=".bs64_e($_GT['id']));
				exit;
			}
		}
		
		if($_REV[0] && $xStatAktif==1 && $_PT['tSimpan']) {
			$tTanggal=$_PT['tTanggal'];
			$tMasaSusut=($_PT['tMasaSusut'])?$_PT['tMasaSusut']:0;
			$tNominal=$_PT['tNominal'];
			$tResidu=$_PT['tResidu'];
			$tKeterangan=$_PT['tKeterangan'];
			
			$errtTanggal=(!balikTanggal($tTanggal))?"Format Tanggal masih salah (DD/MM/YYYY)":((dateDiff(TANGGAL2,balikTanggal($tTanggal))<0)?"Tanggal tidak boleh lebih besar dari sekarang":((dateDiff(balikTanggal($tTanggal),$lastPosting)<=0)?"Tanggal harus lebih besar dari tanggal Tutup Buku":((dateDiff(balikTanggal($tTanggal),$lastSusut)<=0)?"Tanggal harus lebih besar dari tanggal Penyusutan Sebelumnya":((dateDiff(balikTanggal($tTanggal),$_DATA['tgl'][0])<=0)?"Tanggal harus lebih besar dari tanggal Perolehan / Cutoff Penyusutan":""))));
			$errtMasaSusut=($tMasaSusut && !is_numeric(clearRupiah($tMasaSusut)))?"Data Umur Manfaat bukan angka":(($tMasaSusut && ($tMasaSusut+array_sum($_DATA['masa_susut']))<$xJmlSusut)?"Umur Manfaat tidak boleh lebih kecil dari umur yang telah disusutkan":((!$tMasaSusut && !$tNominal && !$tResidu)?"Perubahan Masa Susut, Nilai, dan Residu masih kosong":""));
			$errtNominal=($tNominal && !is_numeric(clearRupiah($tNominal)))?"Nilai Aset masih kosong atau bukan angka":(($tNominal && ((clearRupiah($tNominal)+array_sum($_DATA['nilai']))-(clearRupiah($tResidu)+array_sum($_DATA['residu'])))<$xTotalSusut)?"Nilai Aset tidak boleh lebih kecil dari Nilai yang telah disusutkan":"");
			$errtResidu=($tResidu && !is_numeric(clearRupiah($tResidu)))?"Nilai Residu bukan angka":(($tResidu && ((clearRupiah($tNominal)+array_sum($_DATA['nilai']))<(clearRupiah($tResidu)+array_sum($_DATA['residu']))))?"Nilai Residu tidak boleh lebih besar dari Nilai Aset":"");
			$errtKeterangan=(!$tKeterangan)?"Data Keterangan masih kosong":"";
			
			if(!$errtTanggal && !$errtMasaSusut && !$errtNominal && !$errtResidu && !$errtKeterangan) {
				$tId=((getValue("id_aset_rev","t_aset_rev","1=1 order by id_aset_rev desc limit 0,1")*1)+1);
				$tTanggal=balikTanggal($tTanggal);
				
				queryDb("insert into t_aset_rev(id_aset_rev,id_aset,tgl_rev,masa_susut_tambah,nilai_tambah,residu_tambah,keterangan,tanggal) 
							values('".$tId."','".$_REV[0]."','".$tTanggal."','".clearRupiah($tMasaSusut)."','".clearRupiah($tNominal)."','".clearRupiah($tResidu)."','".$tKeterangan."','".TANGGAL."')");
				queryDb("update t_aset set tanggal='".TANGGAL."' where id_aset='".$_REV[0]."'");
				
				header("location:?id=".bs64_e($_REV[0]));
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
<script src="../js/dateVal.js" type="text/javascript"></script>
<script src="../js/jquery-1.5.js" type="text/javascript"></script>
<script src="../js/ui/jquery.ui.core.js" type="text/javascript"></script>
<script src="../js/ui/jquery.ui.widget.js" type="text/javascript"></script>
<script src="../js/ui/jquery.ui.datepicker.js" type="text/javascript"></script>
<script language="javascript">//if(self.document.location==top.document.location) self.document.location="../index.php?logout=1";</script>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<link href="../css/ui/base/jquery.ui.all.css" rel="stylesheet" />
<style type="text/css">
#titleTop ol li:nth-child(1) , #list ul li:nth-child(1) { width:30px;text-align:center; }
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:110px;text-align:center; }
#titleTop ol li:nth-child(3) , #list ul li:nth-child(3) { width:125px;text-align:center; }
#titleTop ol li:nth-child(4) , #list ul li:nth-child(4) { width:125px; }
#titleTop ol li:nth-child(5) , #list ul li:nth-child(5) { width:125px; }
#titleTop ol li { text-align:center; }
#list ul li:nth-child(4),#list ul li:nth-child(5) { text-align:right; }

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
                  <td>TGL PERUBAHAN</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tTanggal" id="tTanggal" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" style="width:140px" onkeyup="hideFade('errtTanggal')" />
                    <div id="errtTanggal" class="err"><?=$errtTanggal?></div>
                  </td>
                </tr>
                <tr>
                  <td>UMUR MANFAAT</td>
                  <td>:</td>
                  <td>
                    <input type="text" id="tMasaSusutAwal" value="<?=htmlentities(showRupiah2(array_sum($_DATA['masa_susut'])))?>" readonly="readonly" class="readonly" style="text-align:right;width:110px;" /> + 
                    <input type="text" name="tMasaSusut" id="tMasaSusut" maxlength="3" value="<?=htmlentities($tMasaSusut)?>" required="required" onkeyup="hideFade('errtMasaSusut');sumangka(this,'tMasaSusut');" style="text-align:right;width:110px;" /> = 
                    <input type="text" id="tMasaSusutAkhir" value="<?=htmlentities(showRupiah2(array_sum($_DATA['masa_susut'])+clearRupiah($tMasaSusut)))?>" readonly="readonly" class="readonly" style="text-align:right;width:130px;" />
                    <div id="errtMasaSusut" class="err"><?=$errtMasaSusut?></div>
                  </td>
                </tr>
                <tr>
                  <td>NILAI ASET</td>
                  <td>:</td>
                  <td>
                    <input type="text" id="tNominalAwal" value="<?=htmlentities(showRupiah2(array_sum($_DATA['nilai'])))?>" readonly="readonly" class="readonly" style="text-align:right;width:110px;" /> + 
                    <input type="text" name="tNominal" id="tNominal" value="<?=htmlentities($tNominal)?>" maxlength="20" required="required" onkeyup="hideFade('errtNominal');sumangka(this,'tNominal');" style="text-align:right;width:110px;" /> = 
                    <input type="text" id="tNominalAkhir" value="<?=htmlentities(showRupiah2(array_sum($_DATA['nilai'])+clearRupiah($tNominal)))?>" readonly="readonly" class="readonly" style="text-align:right;width:130px;" />
                    <div id="errtNominal" class="err"><?=$errtNominal?></div>
                  </td>
                </tr>
                <tr>
                  <td>NILAI RESIDU</td>
                  <td>:</td>
                  <td>
                    <input type="text" id="tResiduAwal" value="<?=htmlentities(showRupiah2(array_sum($_DATA['residu'])))?>" readonly="readonly" class="readonly" style="text-align:right;width:110px;" /> + 
                    <input type="text" name="tResidu" id="tResidu" value="<?=htmlentities($tResidu)?>" maxlength="20" required="required" onkeyup="hideFade('errtResidu');sumangka(this,'tResidu');" style="text-align:right;width:110px;" /> = 
                    <input type="text" id="tResiduAkhir" value="<?=htmlentities(showRupiah2(array_sum($_DATA['residu'])+clearRupiah($tResidu)))?>" readonly="readonly" class="readonly" style="text-align:right;width:130px;" />
                    <div id="errtResidu" class="err"><?=$errtResidu?></div>
                  </td>
                </tr>
                <tr>
                  <td>NILAI HARUS DISUSUTKAN</td>
                  <td>:</td>
                  <td>
                  	<input type="text" id="tNominalTotal" value="<?=htmlentities(showRupiah2((array_sum($_DATA['nilai'])+clearRupiah($tNominal))-(array_sum($_DATA['residu'])+clearRupiah($tResidu))))?>" readonly="readonly" class="readonly" style="text-align:right;width:235px;" />
                  </td>
                </tr>
                <tr>
                  <td>TELAH DISUSUTKAN</td>
                  <td>:</td>
                  <td>
                  	<input type="text" value="<?=htmlentities(showRupiah2($xTotalSusut))?>" readonly="readonly" class="readonly" style="text-align:right;width:235px;" />
                  </td>
                </tr>
                <tr>
                <tr>
                  <td>KETERANGAN</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tKeterangan" id="tKeterangan" maxlength="255" value="<?=htmlentities($tKeterangan)?>" required="required" onkeyup="hideFade('errtKeterangan')" />
                    <div id="errtKeterangan" class="err"><?=$errtKeterangan?></div>
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
        $listTitle=array("#TANGGAL","#UMUR MANFAAT","#NILAI ASET","#RESIDU","#KETERANGAN");
		
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
	reset($_REV);
	while(list($a,$b)=each($_REV)) {
		$paging['start']++;
		echo "<ul id=\"".$a."\" onclick=\"listFocus(this,'del=".$_DATA['stat_delete'][$a]."')\"><li>".$paging['start'].".</li><li>".tglIndo($_DATA['tgl'][$a],2)."</li><li>".$_DATA['masa_susut'][$a]." bulan</li><li>".showRupiah2($_DATA['nilai'][$a])."</li><li>".showRupiah2($_DATA['residu'][$a])."</li><li>".$_DATA['ket'][$a]."</li></ul>";
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li style="width:140px;"></li>
    	<li class="c" style="width:125px;">UMUR MANFAAT</li>
    	<li class="c" style="width:135px;">NILAI ASET</li>
    	<li class="c" style="width:125px;">RESIDU</li>
    	<li class="c" style="width:auto;">NILAI YG HARUS DISUSUTKAN</li>
    </ol>
	<ol>
    	<li style="width:140px;"></li>
    	<li class="c" style="width:125px;"><?=array_sum($_DATA['masa_susut'])?> bulan</li>
    	<li class="c" style="width:135px;"><?=showRupiah(array_sum($_DATA['nilai']))?></li>
    	<li class="c" style="width:125px;"><?=showRupiah(array_sum($_DATA['residu']))?></li>
    	<li class="c" style="width:auto;"><?=showRupiah(array_sum($_DATA['nilai'])-array_sum($_DATA['residu']))?></li>
    </ol>
    <?php if($xStatAktif==1) { ?>
	<ol>
    	<li class="r" style="width:auto;">
    	  <button id="btn-new" class="icon-new" onclick="setTanggal('<?=date("d/m/Y")?>');newData('tMasaSusut,tNominal,tResidu,tKeterangan');">TAMBAH</button>
          <button id="btn-del" class="icon-del disabled" onclick="delData('del,id');">HAPUS</button>
        </li>
    </ol>
    <?php } ?>
</div>
<script language="javascript">
	function setTanggal(v) {
		v=(v)?v:"<?=date("d/m/Y")?>";
		
		$(function() {
			$("#tTanggal").datepicker();
			$("#tTanggal").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal").datepicker("setDate",v);
		});
	}
	
	function sumangka(e,t) {
		valnominal(e);		
		elm(t+'Akhir').value=shownominal(clearRupiah(elm(t+'Awal').value)+clearRupiah(elm(t).value));		
		elm('tNominalTotal').value=shownominal(clearRupiah(elm('tNominalAkhir').value)-clearRupiah(elm('tResiduAkhir').value));
	}
	
	setTanggal("<?=$tTanggal?>");
	
	
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtTanggal || $errtMasaSusut || $errtNominal || $errtResidu || $errtKeterangan)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
