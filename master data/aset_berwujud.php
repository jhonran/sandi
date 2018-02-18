<?php
	error_reporting(0);
	//session_start();
	
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
		
		define('JENIS',1,true);
		$_JENIS[1]=array('1'=>'Aset Tetap','2'=>'Aset Sewa','9'=>'Aset Lain-lain');
		$_JENIS[2]=array('0'=>'');
		
		if($_GT['del']) {
			$stat=getValue("if(a.stat_aktif=0,2,if(max(s.nominal) is null,1,0))","t_aset a left join t_susut s on s.id_aset=a.id_aset and s.no_trans<>''","a.id_aset='".$_GT['del']."' and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".implode("','",array_keys($_UNITS))."') and a.status='1' group by a.id_aset");
			
			if($stat==1) {
				queryDb("delete from t_aset where id_aset='".$_GT['del']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."')");
				queryDb("delete from t_aset_rev where id_aset='".$_GT['del']."'");
				queryDb("delete from t_susut where id_aset='".$_GT['del']."'");
				header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
				exit;
			}
			else if($stat==2) {
				queryDb("update t_aset set status='0'
								where id_aset='".$_GT['del']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."') and stat_aktif='0'");
				header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tNoBukti=$_PT['tNoBukti'];
			$tNama=$_PT['tNama'];
			$tInventaris=$_PT['tInventaris'];
			$tJumlah=($_PT['tJumlah'])?$_PT['tJumlah']:0;
			$tJenis=($_JENIS[JENIS][$_PT['tJenis']])?$_PT['tJenis']:key($_JENIS[JENIS]);
			$tKategori=$_PT['tKategori'];
			$tHibah=($_PT['tHibah']==1)?1:0;
			$tMasaSusut=($_PT['tMasaSusut'])?$_PT['tMasaSusut']:0;
			$tTanggal=$_PT['tTanggal'];
			$tNominal=$_PT['tNominal'];
			$tResidu=$_PT['tResidu'];
			$tTanggalSusut=$_PT['tTanggalSusut'];
			$tNominalSusut=($_PT['tNominalSusut'])?$_PT['tNominalSusut']:0;
			$iRuang=$_PT['iRuang'];
			$nRuang=$_PT['nRuang'];		
			
			$lastPosting=getValue("max(finis_trans)","((select '1900-01-01' as finis_trans) UNION (select tgl_finish from t_posting order by tgl_finish desc limit 0,1)) x","1=1");
			$lastSusut=getValue("max(tgl_trans)","((select '1900-01-01' as tgl_trans) UNION (select tgl_trans from t_susut where no_trans<>'' order by tgl_trans desc limit 0,1)) x","1=1");
		
			$errtNoBukti=($tId && !getValue("if(max(s.nominal) is null,1,0)","t_aset a left join t_susut s on s.id_aset=a.id_aset and s.no_trans<>''","a.id_aset='".$tId."' and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".implode("','",array_keys($_UNITS))."') and a.stat_aktif='1' and a.status='1'"))?"Data aset tidak terdaftar atau tidak bisa disusutkan":((!$tNoBukti)?"No Bukti masih kosong":"");
			$errtNama=(!$tNama)?"Data Nama masih kosong":"";
			$errtJumlah=(!is_numeric($tJumlah) || $tJumlah<1)?"Data Jumlah kosong atau bukan angka":"";
			$errtKategori=(!$tKategori)?"Kategori Aset Belum dipilih":((!getValue("1","t_aset_kategori","concat(id_aset_kategori,'#',masa_susut)='".$tKategori."' and jenis='".JENIS."' limit 1"))?"Kategori Aset tidak terdaftar":"");	
			$errtMasaSusut=(!is_numeric($tMasaSusut))?"Data Umur Manfaat bukan angka":"";
			$errtTanggal=(!balikTanggal($tTanggal))?"Format Tanggal masih salah (DD/MM/YYYY)":((dateDiff(TANGGAL2,balikTanggal($tTanggal))<0)?"Tanggal tidak boleh lebih besar dari sekarang":"");
			if(!$errtTanggal) $errtTanggal=(!$tNominal || !is_numeric(clearRupiah($tNominal)))?"Nilai Perolehan masih kosong atau bukan angka":"";
			if($tResidu) $errtResidu=(!is_numeric(clearRupiah($tResidu)))?"Nilai Residu bukan angka":(((clearRupiah($tResidu)>clearRupiah($tNominal)))?"Nilai Residu tidak boleh lebih besar dari Nilai Perolehan":"");
			
			if($tNominalSusut) {
				$errtTanggalSusut=(!is_numeric(clearRupiah($tNominalSusut)))?"Nilai yang disusutkan bukan angka":((clearRupiah($tNominalSusut)>(clearRupiah($tNominal)-clearRupiah($tResidu)))?"Nilai yang disusutkan lebih besar dari Nilai Perolehan":"");
				if(!$errtTanggalSusut) $errtTanggalSusut=(!balikTanggal($tTanggalSusut))?"Format Tanggal masih salah (DD/MM/YYYY)":((dateDiff(TANGGAL2,balikTanggal($tTanggalSusut))<0)?"Tanggal tidak boleh lebih besar dari sekarang":((dateDiff(balikTanggal($tTanggalSusut),$lastPosting)<=0)?"Tanggal harus lebih besar dari tanggal Tutup Buku":((dateDiff(balikTanggal($tTanggalSusut),$lastSusut)<=0)?"Tanggal harus lebih besar dari tanggal Penyusutan Sebelumnya":((dateDiff(balikTanggal($tTanggalSusut),balikTanggal($tTanggal))<0)?"Tanggal tidak boleh lebih kecil dari tanggal Perolehan":""))));
			}
			else {
				$tTanggalSusut=$tTanggal;
				
				if(!$errtTanggal) $errtTanggal=(dateDiff(balikTanggal($tTanggalSusut),$lastPosting)<=0)?"Tanggal harus lebih besar dari tanggal Tutup Buku":((dateDiff(balikTanggal($tTanggalSusut),$lastSusut)<=0)?"Tanggal harus lebih besar dari tanggal Penyusutan Sebelumnya":"");
			}
			
			$errnRuang=(!$iRuang)?"Ruangan masih kosong":((!getValue("concat(id_unit1,'.',id_unit2,'.',id_unit3)","t_ruangan","concat(id_unit1,'.',id_unit2,'.',id_unit3,'.',id_ruangan)='".$iRuang."' and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."')"))?"Ruangan tidak terdaftar":"");
			
			if(!$errtNoBukti && !$errtNama && !$errtJumlah && !$errtKategori && !$errtMasaSusut && !$errtTanggal && !$errtResidu && !$errtTanggalSusut && !$errnRuang) {				
				if($tId) {
					if(!getValue("1","t_susut","id_aset='".$tId."' and no_trans<>'' limit 0,1")) {
						queryDb("update t_aset  a
											inner join t_ruangan r on concat(r.id_unit1,'.',r.id_unit2,'.',r.id_unit3,'.',r.id_ruangan)='".$iRuang."'
											inner join t_aset_kategori k on concat(k.id_aset_kategori,'#',k.masa_susut)='".$tKategori."'
										set a.no_bukti='".$tNoBukti."',a.nama='".$tNama."',a.no_inventaris='".$tInventaris."',a.jumlah='".$tJumlah."',a.id_aset_kategori=k.id_aset_kategori,a.jenis='".$tJenis."',a.hibah='".$tHibah."',a.masa_susut='".$tMasaSusut."',a.rumus=k.rumus,
											a.tgl_perolehan='".balikTanggal($tTanggal)."',a.nilai_perolehan='".clearRupiah($tNominal)."',a.residu='".clearRupiah($tResidu)."',a.tgl_susut='".balikTanggal($tTanggalSusut)."',a.nilai_susut='".clearRupiah($tNominalSusut)."',
											a.id_unit1=r.id_unit1,a.id_unit2=r.id_unit2,a.id_unit3=r.id_unit3,a.id_ruangan=r.id_ruangan,
											a.id_gl4_db=k.id_gl4_db,a.id_gl4_cr=k.id_gl4_cr,a.tanggal='".TANGGAL."'
										where a.id_aset='".$tId."' and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".implode("','",array_keys($_UNITS))."') and a.stat_aktif='1' and a.status='1'");
						
						queryDb("delete from t_aset_rev where id_aset='".$tId."'");
						queryDb("delete from t_susut where id_aset='".$tId."'");
					}
				}
				else {
					$tId=((getValue("id_aset","t_aset","1=1 order by id_aset desc limit 0,1")*1)+1);
					queryDb("insert into t_aset(id_aset,no_bukti,nama,no_inventaris,jumlah,id_aset_kategori,jenis,hibah,masa_susut,rumus,tgl_perolehan,nilai_perolehan,residu,tgl_susut,nilai_susut,id_unit1,id_unit2,id_unit3,id_ruangan,id_gl4_db,id_gl4_cr,tanggal,stat_aktif,status) 
								select '".$tId."','".$tNoBukti."','".$tNama."','".$tInventaris."','".$tJumlah."',k.id_aset_kategori,'".$tJenis."','".$tHibah."','".$tMasaSusut."',k.rumus,
												'".balikTanggal($tTanggal)."','".clearRupiah($tNominal)."','".clearRupiah($tResidu)."','".balikTanggal($tTanggalSusut)."','".clearRupiah($tNominalSusut)."',r.id_unit1,r.id_unit2,r.id_unit3,r.id_ruangan,k.id_gl4_db,k.id_gl4_cr,'".TANGGAL."','1','1'
									from t_ruangan r
										inner join t_aset_kategori k on concat(k.id_aset_kategori,'#',k.masa_susut)='".$tKategori."'
									where concat(r.id_unit1,'.',r.id_unit2,'.',r.id_unit3,'.',r.id_ruangan)='".$iRuang."'");
				}
				
				if(clearRupiah($tNominalSusut)>0) {
					queryDb("insert into t_susut(id_aset,tgl_trans,no_trans,no_bukti,keterangan,nominal,tanggal,id_user)
									values('".$tId."','".balikTanggal($tTanggalSusut)."','','','Cut off penyusutan','".clearRupiah($tNominalSusut)."','".TANGGAL."','".$_SESSION['user']."')");
				}
				
				header("location:?edit=".bs64_e($tId));
				exit;
			}
		}
		
		if($_PT['tSimpan2']) {
			$tId2=$_PT['tId2'];
			$tNoBukti2=$_PT['tNoBukti2'];
			$tNama2=$_PT['tNama2'];
			$tInventaris2=$_PT['tInventaris2'];
			$tJumlah2=($_PT['tJumlah2'])?$_PT['tJumlah2']:0;
			$tTanggal2=$_PT['tTanggal2'];
			
			$lastPosting=getValue("max(finis_trans)","((select '1900-01-01' as finis_trans) UNION (select tgl_finish from t_posting order by tgl_finish desc limit 0,1)) x","1=1");
			$lastSusut=getValue("max(tgl_trans)","((select '1900-01-01' as tgl_trans) UNION (select tgl_trans from t_susut where no_trans<>'' order by tgl_trans desc limit 0,1)) x","1=1");
			$tglPerolehan=getValue("a.tgl_perolehan","t_aset a
														inner join t_susut s on s.id_aset=a.id_aset and s.no_trans<>''",
													"a.id_aset='".$tId2."' and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".implode("','",array_keys($_UNITS))."') and a.stat_aktif='1' and a.status='1' group by a.tgl_perolehan");
			
			$errtTanggal2=(!$tglPerolehan)?"Data Aset tidak dapat dilakukan Pelepasan":((!balikTanggal($tTanggal2))?"Format Tanggal masih salah (DD/MM/YYYY)":((dateDiff(TANGGAL2,balikTanggal($tTanggal2))<0)?"Tanggal tidak boleh lebih besar dari sekarang":((dateDiff(balikTanggal($tTanggal2),$lastPosting)<=0)?"Tanggal harus lebih besar dari tanggal Tutup Buku":((dateDiff(balikTanggal($tTanggal2),$lastSusut)<=0)?"Tanggal harus lebih besar dari tanggal Penyusutan Sebelumnya":((dateDiff(balikTanggal($tTanggal2),$tglPerolehan)<=0)?"Tanggal harus lebih besar dari tanggal Perolehan":"")))));
			
			if(!$errtTanggal2) {									
				queryDb("update t_aset set stat_aktif='0',tanggal='".TANGGAL."' where id_aset='".$tId2."' and stat_aktif='1' and status='1'");
				
				queryDb("insert into t_susut(id_aset,tgl_trans,no_trans,no_bukti,keterangan,nominal,tanggal,id_user)
							select a.id_aset,'".balikTanggal($tTanggal2)."','-','','Pelepasan Aset',(sum(s.nominal)*-1),'".TANGGAL."','".$_SESSION['user']."' from t_aset a
									inner join t_susut s on s.id_aset=a.id_aset
								where a.id_aset='".$tId2."' and a.stat_aktif='0' and a.status='1'
								group by a.id_aset");
						
				queryDb("insert into t_aset_rev(id_aset,tgl_rev,masa_susut_tambah,nilai_tambah,residu_tambah,keterangan,tanggal)
							select a.id_aset,'".balikTanggal($tTanggal2)."','0',((a.nilai_perolehan+ifnull(sum(r.nilai_tambah),0))*-1),((a.residu+ifnull(sum(r.residu_tambah),0))*-1),'Pelepasan Aset','".TANGGAL."' from t_aset a
										left join t_aset_rev r on r.id_aset=a.id_aset
									where a.id_aset='".$tId2."' and a.stat_aktif='0' and a.status='1'
									group by a.id_aset,a.nilai_perolehan,a.residu");
				
				header("location:?edit=".bs64_e($tId2));
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
<script language="javascript">if(self.document.location==top.document.location) self.document.location="../index.php?logout=1";</script>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<link href="../css/ui/base/jquery.ui.all.css" rel="stylesheet" />
<style type="text/css">
#titleTop ol li:nth-child(1) , #list ul li:nth-child(1) { width:30px;text-align:center; }
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:105px;text-align:center; }
#titleTop ol li:nth-child(3) , #list ul li:nth-child(3) { width:100px;text-align:center; }
#titleTop ol li:nth-child(4) , #list ul li:nth-child(4) { width:220px; }
#titleTop ol li:nth-child(5) , #list ul li:nth-child(5) { width:120px;text-align:center; }
#titleTop ol li:nth-child(7) , #list ul li:nth-child(7) { width:125px; }
#titleTop ol li:nth-child(8) , #list ul li:nth-child(8) { width:115px; }
#titleTop ol li:nth-child(9) , #list ul li:nth-child(9) { width:115px; }
#titleTop ol li { text-align:center; }
#list ul li:nth-child(7),#list ul li:nth-child(8),#list ul li:nth-child(9) { text-align:right; }
#list ul.g { background:#FFF4F4; }

table input[type=text], table input[type=password],  table select { width:400px; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<div id="input">
	<ul class="full" id="ul_input">
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
                  <td>NO BUKTI</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tNoBukti" id="tNoBukti" maxlength="20" value="<?=htmlentities($tNoBukti)?>" required="required" onkeyup="hideFade('errtNoBukti')" />
                    <div id="errtNoBukti" class="err"><?=$errtNoBukti?></div>
                  </td>
                </tr>
                <tr>
                  <td>NAMA ASET</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNama" id="tNama" maxlength="100" value="<?=htmlentities($tNama)?>" required="required" onkeyup="hideFade('errtNama')" />
                    <div id="errtNama" class="err"><?=$errtNama?></div>
                  </td>
                </tr>
                <tr>
                  <td>NO INVENTARIS</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tInventaris" id="tInventaris" maxlength="50" value="<?=htmlentities($tInventaris)?>" required="required" onkeyup="hideFade('errtInventaris')" />
                    <div id="errtInventaris" class="err"><?=$errtInventaris?></div>
                  </td>
                </tr>
                <tr>
                  <td>JUMLAH</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tJumlah" id="tJumlah" maxlength="3" value="<?=htmlentities($tJumlah)?>" required="required" onkeyup="hideFade('errtJumlah');" style="width:200px;text-align:right;" />
                    <div id="errtJumlah" class="err"><?=$errtJumlah?></div>
                  </td>
                </tr>
                <tr>
                  <td>JENIS ASET</td>
                  <td>:</td>
                  <td>
                  	<select name="tJenis" id="tJenis" required="required">
                    	<?php
                        while(list($a,$b)=each($_JENIS[JENIS])) {
							echo "<option value=\"".$a."\" ".(($tJenis==$a)?"selected=\"selected\"":"").">".$b."</option>";
						}
						?>
                    </select>
                    <div id="errtHibah" class="err"><?=$errtHibah?></div>
                  </td>
                </tr>
                <tr>
                  <td>KATEGORI</td>
                  <td>:</td>
                  <td>
                  	<select name="tKategori" id="tKategori" required="required" onchange="setMasaSusut(this);hideFade('errtKategori');">
                    	<option value="">- Pilih -</option>
                        <?php
                        $a=queryDb("select id_aset_kategori,nama,masa_susut from t_aset_kategori where jenis='".JENIS."' order by id_aset_kategori");
						while($b=mysql_fetch_array($a)) {
							echo "<option value=\"".$b['id_aset_kategori']."#".$b['masa_susut']."\" ".(($tKategori==($b['id_aset_kategori']."#".$b['masa_susut']))?"selected='selected'":"").">".$b['id_aset_kategori']." - ".$b['nama']." (".$b['masa_susut']." bln)</option>";
						}
						?>
                    </select>
                    <div id="errtKategori" class="err"><?=$errtKategori?></div>
                  </td>
                </tr>
                <tr>
                  <td>ASAL PEROLEHAN</td>
                  <td>:</td>
                  <td>
                  	<select name="tHibah" id="tHibah" required="required" onchange="if(this.value==1) elm('tMasaSusut').value=0;hideFade('errtHibah');">
                    	<option value="0" <?=($tHibah=="0")?"selected=\"selected\"":""?>>Non Hibah</option>
                    	<option value="1" <?=($tHibah=="1")?"selected=\"selected\"":""?>>Hibah</option>
                    </select>
                    <div id="errtHibah" class="err"><?=$errtHibah?></div>
                  </td>
                </tr>
                <tr>
                  <td>UMUR MANFAAT</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tMasaSusut" id="tMasaSusut" maxlength="3" value="<?=htmlentities($tMasaSusut)?>" required="required" onkeyup="hideFade('errtMasaSusut');" />
                    <br /><i>Terhitung setelah Cutoff Penyusutan</i>
                    <div id="errtMasaSusut" class="err"><?=$errtMasaSusut?></div>
                  </td>
                </tr>
                <tr>
                  <td>TGL / NILAI PEROLEHAN</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tTanggal" id="tTanggal" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" onkeyup="hideFade('errtTanggal')" style="width:120px;" />
                    <input type="text" name="tNominal" id="tNominal" value="<?=htmlentities($tNominal)?>" maxlength="20" required="required" onkeyup="hideFade('errtTanggal');valnominal(this);" style="width:200px;text-align:right;" />
                    <div id="errtTanggal" class="err"><?=$errtTanggal?></div>
                  </td>
                </tr>
                <tr>
                  <td>NILAI RESIDU</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tResidu" id="tResidu" value="<?=htmlentities($tResidu)?>" maxlength="20" required="required" onkeyup="hideFade('errtResidu');valnominal(this);" style="width:200px;text-align:right;" />
                    <div id="errtResidu" class="err"><?=$errtResidu?></div>
                  </td>
                </tr>
                <tr>
                  <td>TGL / NILAI SUSUT</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tTanggalSusut" id="tTanggalSusut" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" onkeyup="hideFade('errtTanggalSusut')" style="width:120px;" />
                    <input type="text" name="tNominalSusut" id="tNominalSusut" value="<?=htmlentities($tNominalSusut)?>" maxlength="20" required="required" onkeyup="hideFade('errtTanggalSusut');valnominal(this);" style="width:200px;text-align:right;" />
                    <br /><i>Tanggal dan Nilai Cutoff yang telah disusutkan</i>
                    <div id="errtTanggalSusut" class="err"><?=$errtTanggalSusut?></div>
                  </td>
                </tr>
                <tr>
                  <td>LOKASI ASET</td>
                  <td>:</td>
                  <td>
                      <input type="hidden" name="iRuang" id="iRuang" value="<?=htmlentities($iRuang)?>" />
                      <input name="nRuang" type="text" id="nRuang" maxlength="200" value="<?=htmlentities($nRuang)?>" autocomplete="off" onkeyup="goFramePopup(this,'Ruang','ruang');hideFade('errnRuang');" onfocus="framePopupFocus('Ruang')" onblur="framePopupBlur('Ruang')" />
                      <div class="framePopup"><img id="imgRuang" src="../images/loader.gif" /><iframe id="fRuang"></iframe></div>
                    <div id="errnRuang" class="err"><?=$errnRuang?></div>
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
	<ul class="full" id="ul_input2">
        <li style="text-align:center;">
          <form action="" target="_self" method="post" onsubmit="showFade('load');">
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <th colspan="3" class="title">FORM INPUT PELEPASAN ASET</th>
                </tr>
                <tr>
                  <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                  <td>NO BUKTI</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId2" id="tId2" value="<?=htmlentities($tId2)?>" />
                    <input type="text" name="tNoBukti2" id="tNoBukti2" maxlength="20" value="<?=htmlentities($tNoBukti2)?>" readonly="readonly" class="readonly" />
                  </td>
                </tr>
                <tr>
                  <td>NAMA ASET</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNama2" id="tNama2" maxlength="100" value="<?=htmlentities($tNama2)?>" readonly="readonly" class="readonly" />
                  </td>
                </tr>
                <tr>
                  <td>NO INVENTARIS</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tInventaris2" id="tInventaris2" maxlength="50" value="<?=htmlentities($tInventaris2)?>" readonly="readonly" class="readonly" />
                  </td>
                </tr>
                <tr>
                  <td>JUMLAH</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tJumlah2" id="tJumlah2" maxlength="3" value="<?=htmlentities($tJumlah2)?>" readonly="readonly" class="readonly" style="width:200px;text-align:right;" />
                  </td>
                </tr>
                <tr>
                  <td>TGL PELEPASAN</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tTanggal2" id="tTanggal2" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" onkeyup="hideFade('errtTanggal2')" style="width:120px;" />
                    <div id="errtTanggal2" class="err"><?=$errtTanggal2?></div>
                  </td>
                </tr>
                <tr>
                  <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                  <th colspan="3">
                  	<input name="tSimpan2" type="submit" class="icon-save" id="tSimpan2" value="SIMPAN" />
                  	<input type="reset" id="tTutup" class="icon-no" onclick="hideFade('input'); return false;" value="TUTUP" /></th>
                </tr>
              </table>
          </form>
        </li>
    </ul>
</div>
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)?></li></ol>
    <ol class="tab"><li><a href="#">ASET</a><a href="#" class="off">DETAIL PENYUSUTAN</a></li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("tgl_perolehan#Tgl Peroleh","no_bukti#No Bukti","nama#Nama Aset","no_inventaris#No Inventaris","nm_ruang#Lokasi Aset","masa_susut#Umur Manfaat","nilai_perolehan#Nilai Peroleh","nilai_aset#Nilai Aset");
		
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
	$page=4;
	$sql="select id_aset,no_bukti,nama,no_inventaris,jumlah,jenis,kategori,hibah,masa_susut,tgl_perolehan,nilai_perolehan,residu,tgl_susut,nilai_susut,nilai_aset,id_ruang,nm_ruang,jml_susut,stat_aktif,tanggal,if(stat_aktif=1 and jml_susut=0,1,0) as stat_edit,if(stat_aktif=0 or jml_susut=0,1,0) as stat_delete,if(stat_aktif=1 and jml_susut>0,1,0) as stat_lepas from (
						select a.id_aset,a.no_bukti,a.nama,a.no_inventaris,a.jumlah,a.jenis,concat(a.id_aset_kategori,'#',k.masa_susut) as kategori,a.hibah,a.masa_susut,a.tgl_perolehan,a.nilai_perolehan,a.residu,a.tgl_susut,a.nilai_susut,(a.nilai_perolehan-a.nilai_susut) as nilai_aset,concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3,'.',a.id_ruangan) as id_ruang,concat('[',a.id_unit1,'.',a.id_unit2,'.',a.id_unit3,'.',a.id_ruangan,'] ',r.nama) as nm_ruang,ifnull(count(s.id_susut),0) as jml_susut,a.stat_aktif,a.tanggal from t_aset a
							inner join t_aset_kategori k on k.id_aset_kategori=a.id_aset_kategori and k.jenis='".JENIS."'
							inner join t_ruangan r on r.id_unit1=a.id_unit1 and r.id_unit2=a.id_unit2 and r.id_unit3=a.id_unit3 and r.id_ruangan=a.id_ruangan
							left join t_susut s on s.id_aset=a.id_aset and s.no_trans<>''
						where concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".implode("','",array_keys($_UNITS))."') and a.status='1'
						group by a.id_aset,a.no_bukti,a.nama,a.no_inventaris,a.jumlah,a.jenis,a.id_aset_kategori,a.hibah,k.masa_susut,a.masa_susut,a.tgl_perolehan,a.nilai_perolehan,a.residu,a.tgl_susut,a.nilai_susut,a.id_unit1,a.id_unit2,a.id_unit3,a.id_ruangan,r.nama,a.stat_aktif,a.tanggal
					) x
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				order by ".(($_GT['order'])?$_GT['order'].",tanggal desc":"tanggal desc");

	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['id_aset']."\" value=\"".$b['id_aset']."\" />";
			echo "<input type=\"hidden\" id=\"tNoBukti-".$b['id_aset']."\" value=\"".$b['no_bukti']."\" />";
			echo "<input type=\"hidden\" id=\"tNama-".$b['id_aset']."\" value=\"".$b['nama']."\" />";
			echo "<input type=\"hidden\" id=\"tInventaris-".$b['id_aset']."\" value=\"".$b['no_inventaris']."\" />";
			echo "<input type=\"hidden\" id=\"tJumlah-".$b['id_aset']."\" value=\"".$b['jumlah']."\" />";
			echo "<input type=\"hidden\" id=\"tJenis-".$b['id_aset']."\" value=\"".$b['jenis']."\" />";
			echo "<input type=\"hidden\" id=\"tKategori-".$b['id_aset']."\" value=\"".$b['kategori']."\" />";
			echo "<input type=\"hidden\" id=\"tHibah-".$b['id_aset']."\" value=\"".$b['hibah']."\" />";
			echo "<input type=\"hidden\" id=\"tMasaSusut-".$b['id_aset']."\" value=\"".$b['masa_susut']."\" />";
			echo "<input type=\"hidden\" id=\"tTanggal-".$b['id_aset']."\" value=\"".str_replace("-","/",balikTanggal($b['tgl_perolehan']))."\" />";
			echo "<input type=\"hidden\" id=\"tNominal-".$b['id_aset']."\" value=\"".showRupiah2($b['nilai_perolehan'])."\" />";
			echo "<input type=\"hidden\" id=\"tResidu-".$b['id_aset']."\" value=\"".showRupiah2($b['residu'])."\" />";
			echo "<input type=\"hidden\" id=\"tTanggalSusut-".$b['id_aset']."\" value=\"".str_replace("-","/",balikTanggal($b['tgl_susut']))."\" />";
			echo "<input type=\"hidden\" id=\"tNominalSusut-".$b['id_aset']."\" value=\"".showRupiah2($b['nilai_susut'])."\" />";
			echo "<input type=\"hidden\" id=\"iRuang-".$b['id_aset']."\" value=\"".$b['id_ruang']."\" />";
			echo "<input type=\"hidden\" id=\"nRuang-".$b['id_aset']."\" value=\"".$b['nm_ruang']."\" />";
			
			echo "<input type=\"hidden\" id=\"tId2-".$b['id_aset']."\" value=\"".$b['id_aset']."\" />";
			echo "<input type=\"hidden\" id=\"tNoBukti2-".$b['id_aset']."\" value=\"".$b['no_bukti']."\" />";
			echo "<input type=\"hidden\" id=\"tNama2-".$b['id_aset']."\" value=\"".$b['nama']."\" />";
			echo "<input type=\"hidden\" id=\"tInventaris2-".$b['id_aset']."\" value=\"".$b['no_inventaris']."\" />";
			echo "<input type=\"hidden\" id=\"tJumlah2-".$b['id_aset']."\" value=\"".$b['jumlah']."\" />";
			echo "<input type=\"hidden\" id=\"tTanggal2-".$b['id_aset']."\" value=\"\" />";
			
			echo "<ul id=\"".$b['id_aset']."\" ".(($b['stat_aktif']=="0")?"class=\"g\"":"")." onclick=\"listFocus(this,'edit=".$b['stat_edit'].",no=".$b['stat_lepas'].",del=".$b['stat_delete'].",config=1')\" ondblclick=\"_URI['id1']='".$b['id_aset']."';_URI['order1']='".$_GT['order']."';goAddress('id1,order1,hal,sort,search','aset_berwujud_detail.php')\">
						<li>".$paging['start'].".</li>
						<li>".tglIndo($b['tgl_perolehan'],2)."</li>
						<li>".$b['no_bukti']."</li>
						<li>".$b['nama']."</li>
						<li>".$b['no_inventaris']."</li>
						<li>".$b['nm_ruang']."</li>
						<li>(".$b['masa_susut']."/".$b['jml_susut'].") ".PRDSUSUT."</li>
						<li>".showRupiah2($b['nilai_perolehan'])."</li>
						<li>".showRupiah2($b['nilai_aset'])."</li>
					</ul>";
		
			if($_GT['edit']==$b['id_aset']) $jsEdit="listFocus(elm('".$b['id_aset']."'),'edit=".$b['stat_edit'].",no=".$b['stat_lepas'].",del=".$b['stat_delete'].",config=1');";
		}
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li style="width:auto;" class="l">
        	<select style="width:80px;" onchange="_URI['sort']=this.value;">
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
            <input type="text" style="width:100px;" onblur="_URI['search']=this.value;" onmouseout="_URI['search']=this.value;" value="<?=$_GT['search']?>" />
            <input type="reset" class="icon-search" onclick="goAddress('sort,search,order,tab');" value="SEARCH" />
        </li>
    	<li style="width:auto;" class="paging">
			<span style="margin-right:40px;"><?=$paging['show']?></span><?=(($paging['page'])?"<span>Page :</span>".$paging['page']:"")?>
        </li>
    	<li class="r" style="width:auto;">
    	  <button id="btn-new" class="icon-new" onclick="newData('tNoBukti,tNama,tInventaris,tJumlah,tJenis,tKategori,tHibah,tMasaSusut,tTanggal,tNominal,tResidu,tTanggalSusut,tNominalSusut,iRuang,nRuang,tId');setTanggal('<?=date("d/m/Y")?>','<?=date("d/m/Y")?>');">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tNoBukti,tNama,tInventaris,tJumlah,tJenis,tKategori,tHibah,tMasaSusut,tTanggal,tNominal,tResidu,tTanggalSusut,tNominalSusut,iRuang,nRuang,tId');setTanggal(elm('tTanggal').value,elm('tTanggalSusut').value);">EDIT</button>
    	  <button id="btn-no" class="icon-no disabled" onclick="editData('tNoBukti2,tNama2,tInventaris2,tJumlah2,tTanggal2,tId2','','no');setTanggal2('<?=date("d/m/Y")?>');">PELESAPAN</button>
		  <button id="btn-del" class="icon-del disabled" onclick="delData('del,hal,sort,search,order');">HAPUS</button>
          <button id="btn-config" class="icon-config disabled" onclick="popRev();">REV</button>
        </li>
    </ol>
</div>
<script language="javascript">	
	function popRev() {
		if(!_URI['config']) { alert('Data belum dipilih'); return false; }
		
		if(windowPopUp['x']) windowPopUp['x'].close();
		popUp('../popup/aset_revisi.php?id='+bs64_e(_URI['config']),1000,600,'x');
	}
	
	function setTanggal(v,v2) {
		elm('ul_input').style.display="table";
		elm('ul_input2').style.display="none";
		
		$(function() {
			$("#tTanggal").datepicker();
			$("#tTanggal").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal").datepicker("setDate",v);
		});
		
		$(function() {
			$("#tTanggalSusut").datepicker();
			$("#tTanggalSusut").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggalSusut").datepicker("setDate",v2);
		});
	}
	
	function setTanggal2(v) {
		elm('ul_input').style.display="none";
		elm('ul_input2').style.display="table";
		
		$(function() {
			$("#tTanggal2").datepicker();
			$("#tTanggal2").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal2").datepicker("setDate",v);
		});
	}
	
	<?php
		if($tTanggal || $tTanggalSusut) echo "setTanggal('".$tTanggal."','".$tTanggalSusut."');";
		if($tTanggal2) echo "setTanggal2('".$tTanggal2."');";
	?>
	
	function setMasaSusut(e) {
		var x=e.value.split("#");
		elm('tMasaSusut').value=(x[1])?x[1]:'';
	}
	
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtNoBukti || $errtNama || $errtJumlah || $errtKategori || $errtMasaSusut || $errtTanggal || $errtResidu || $errtTanggalSusut || $errnRuang || $errtTanggal2)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
