<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='tagihan_siswa.php'",
										"u.`id_user`='".$_SESSION['user']."'");
										
	$a=queryDb("select u.id_unit1,u.id_unit2,u.id_unit3 from t_user s 
						inner join t_entitas_unit e on e.id_entitas=s.id_entitas
						inner join t_unit3 u on u.id_unit1=e.id_unit1 and u.id_unit2=e.id_unit2 and u.id_unit3=e.id_unit3 and concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3)='".$_SESSION['unit']."'
					where s.id_user='".$_SESSION['user']."' limit 1");
	
	$_UNIT=mysql_fetch_array($a);
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else if(!isset($_UNIT['id_unit3'])) {		
		header("location:../pilih_unit.php?direct=".bs64_e($_SERVER['REQUEST_URI']));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		$dym=date("Ym");
		define("NOFAK",$dym.substr(((getValue("substring(no_trans,7,4)","t_glt","no_trans like '".$dym."%' order by no_trans desc limit 0,1")*1)+10001),1,4));
		define("MTRANS","08");
		$lastPosting=getValue("max(finis_trans)","((select '1900-01-01' as finis_trans) UNION (select tgl_finish from t_posting order by tgl_finish desc limit 0,1)) x","1=1");
		$statHapusTrans=getValue("stat_hapus_trans","t_user","id_user='".$_SESSION['user']."'");
		
		$a=queryDb("select m.mutasi,g.id_gl4,concat('[',g.id_gl4,'] ',g.nama) as nm from t_glu g
							inner join t_mtrans_gl m on m.id_gl4=g.id_gl4 and m.id_mtrans='".MTRANS."'
						where concat(g.id_unit1,'.',g.id_unit2,'.',g.id_unit3)='".$_SESSION['unit']."'
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
		
		$a=queryDb("select m.id_siswa,m.nama,group_concat(ifnull(b.nominal,0) order by j.id_jns_bayar_siswa asc separator '#') as x from t_siswa m
									inner join t_jns_bayar_siswa j
									left join t_siswa_jns_bayar b on b.id_siswa=m.id_siswa and b.id_jns_bayar_siswa=j.id_jns_bayar_siswa
							where m.id_kelas='".$_GT['id1']."'
							group by m.id_siswa,m.nama
							order by m.nama asc");
		while($b=mysql_fetch_array($a)) {
			$_SISWA[$b['id_siswa']]=$b['nama'];
			$_SISWAX[$b['id_siswa']]=$b['x'];
		}
		
		if($_GT['del']) {
			if($statHapusTrans==1 && !getValue("1","t_siswa_tagih h","h.no_trans='".$_GT['del']."' and h.id_kelas='".$_GT['id1']."' and h.id_siswa_tagih in (select id_siswa_tagih from t_siswa_bayar where id_siswa_tagih=h.id_siswa_tagih) limit 1")) {
				/*queryDb("insert into t_gltd(id,id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user,tanggald,id_userd)
							select id,id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user,'".TANGGAL."','".$_SESSION['user']."' from t_glt 
								where no_trans='".$_GT['del']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' and id_mtrans='".MTRANS."' and tgl_trans>'$lastPosting'");
				queryDb("delete from t using t_glt as t where t.no_trans='".$_GT['del']."' and concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3)='".$_SESSION['unit']."' and t.id_mtrans='".MTRANS."' and t.tgl_trans>'".$lastPosting."' 
							and t.no_trans not in (select h.no_trans from t_siswa_tagih h inner join t_siswa_bayar b on b.id_siswa_tagih=h.id_siswa_tagih where h.id_kelas='".$_GT['id1']."')");
				*/
				queryDb("delete from t_glt where no_trans='".$_GT['del']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' and id_mtrans='".MTRANS."' and tgl_trans>'".$lastPosting."' 
										and 1=(select if(total_bayar<>0 or total_pengakuan<>0 or count_masa_pengakuan<>0,0,1) from t_siswa_tagih where no_trans='".$_GT['del']."' and tgl_trans>'".$lastPosting."' and id_kelas='".$_GT['id1']."' order by total_bayar desc,total_pengakuan desc,count_masa_pengakuan desc limit 0,1)");
							
				if(mysql_affected_rows()>0) {
					queryDb("delete from t_siswa_tagih where no_trans='".$_GT['del']."' and tgl_trans>'".$lastPosting."' and id_kelas='".$_GT['id1']."' and total_bayar=0 and total_pengakuan=0 and count_masa_pengakuan=0");
				}
				 
				header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&order1=".bs64_e($_GT['order1']));
				exit;
			}
		}
			
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdTrans=$_PT['tIdTrans'];
			$tTanggal=$_PT['tTanggal'];
			$tNoBukti=$_PT['tNoBukti'];
			$iAkunD=$_PT['iAkunD'];
			$nAkunD=$_PT['nAkunD'];
			$iAkunC=$_PT['iAkunC'];
			$nAkunC=$_PT['nAkunC'];
			$tDesc=$_PT['tDesc'];
			$tBatas=$_PT['tBatas'];
			$tJenis=$_PT['tJenis'];
			
			for($i=0;$i>=0;$i++) {
				if(!$_PT['iSiswa'.$i]) break;
				else {
					$_NOMINAL[$_PT['iSiswa'.$i]]=clearRupiah($_PT['tNominal'.$i]);
					$_DESC[$_PT['iSiswa'.$i]]=$_PT['tDesc'.$i];
				}
			}
			
			$eLR=substr($iAkunD,0,1);
			if($eLR==4 || $eLR==5) $statBatas=1;			
			
			$eLR=substr($iAkunC,0,1);
			if($eLR==4 || $eLR==5) $statBatas=1;
			
			$errtTanggal=(!balikTanggal($tTanggal))?"Format Tanggal masih salah (DD/MM/YYYY)":((dateDiff(TANGGAL2,balikTanggal($tTanggal))<0)?"Tanggal tidak boleh lebih besar dari sekarang":((dateDiff(balikTanggal($tTanggal),$lastPosting)<=0)?"Tanggal harus lebih besar dari tanggal Tutup Buku":""));
			$errtNoBukti=(!$tNoBukti)?"No Bukti masih kosong":"";
			$errnAkunD=(!$iAkunD)?$_IST['gl1.php']." Debit masih kosong":((!isset($_AKUN['DB'][$iAkunD]))?$_IST['gl1.php']." Debit tidak terdaftar":((!is_array($_NOMINAL) || array_sum($_NOMINAL)<=0)?"Nominal masih kosong":""));
			$errnAkunC=(!$iAkunC)?$_IST['gl1.php']." Kredit masih kosong":((!isset($_AKUN['CR'][$iAkunC]))?$_IST['gl1.php']." Kredit tidak terdaftar":(($iAkunC==$iAkunD)?$_IST['gl1.php']." Debit dan Kredit tidak boleh sama":""));
			$errtBatas=($statBatas && !$tBatas)?"Pembatasan Belum dipilih":"";
			$errtJenis=(!$tJenis)?"Jenis Penerimaan Belum dipilih":((!getValue("1","t_jns_bayar_siswa","id_jns_bayar_siswa='".$tJenis."' limit 1"))?"Jenis Penerimaaan tidak terdaftar":"");
			
			$_errnSiswa=array();
			
			if(is_array($_NOMINAL)) {
				$_NOMINAL=array_filter($_NOMINAL);
				
				reset($_NOMINAL);
				while(list($a,$b)=each($_NOMINAL)) {
					$_errnSiswa[$a]=(!isset($_SISWA[$a]))?$_IST['siswa.php']." tidak terdaftar":((!$_NOMINAL[$a] || !is_numeric($_NOMINAL[$a]))?"Nominal masih kosong atau bukan angka":"");
				}
				
				$_errnSiswa=array_filter($_errnSiswa);
			}
			
			if(!$errtTanggal && !$errtNoBukti && !$errnAkunD && !$errnAkunC && !$errtBatas && !count($_errnSiswa)) {				
				if($tId) {
				
				}
				else {
					$tTanggal=balikTanggal($tTanggal);
					$dym=substr(str_replace("-","",$tTanggal),0,6);
					$tIdTrans=$dym.substr(((getValue("substring(no_trans,7,4)","t_glt","no_trans like '".$dym."%' order by no_trans desc limit 0,1")*1)+10001),1,4);
					
					queryDb("insert into t_glt(id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user)
														select '".MTRANS."','".$tTanggal."','".$tIdTrans."','".$tIdTrans."','".$tNoBukti."','".$tDesc."',g.id_unit1,g.id_unit2,g.id_unit3,g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,if(g.id_gl1='4' or g.id_gl1='5','".$tBatas."',''),g.id_aruskas,g.id_aruskas_sub,'".array_sum($_NOMINAL)."',0,if(g1.db_cr='D',(saldo+".array_sum($_NOMINAL)."),(saldo-".array_sum($_NOMINAL).")),'".TANGGAL."','".$_SESSION['user']."'
																from t_glu g
																	inner join t_gl1 g1 on g1.id_gl1=g.id_gl1 
																where concat(g.id_unit1,'.',g.id_unit2,'.',g.id_unit3)='".$_SESSION['unit']."' and g.id_gl4='".$iAkunD."'");
					
					queryDb("insert into t_glt(id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user)
														select '".MTRANS."','".$tTanggal."','".$tIdTrans."','".$tIdTrans."','".$tNoBukti."','".$tDesc."',g.id_unit1,g.id_unit2,g.id_unit3,g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,if(g.id_gl1='4' or g.id_gl1='5','".$tBatas."',''),g.id_aruskas,g.id_aruskas_sub,0,'".array_sum($_NOMINAL)."',if(g1.db_cr='C',(saldo+".array_sum($_NOMINAL)."),(saldo-".array_sum($_NOMINAL).")),'".TANGGAL."','".$_SESSION['user']."'
																from t_glu g
																	inner join t_gl1 g1 on g1.id_gl1=g.id_gl1
																where concat(g.id_unit1,'.',g.id_unit2,'.',g.id_unit3)='".$_SESSION['unit']."' and g.id_gl4='".$iAkunC."'");
					
					reset($_NOMINAL);
					while(list($a,$b)=each($_NOMINAL)) {
						$tId=((getValue("id_siswa_tagih","t_siswa_tagih","1=1 order by id_siswa_tagih desc limit 0,1")*1)+1);
						queryDb("insert into t_siswa_tagih(id_siswa_tagih,id_jns_bayar_siswa,id_siswa,nama_siswa,id_kelas,tgl_trans,no_trans,no_bukti,keterangan,uraian,nominal,masa_pengakuan,id_gl4_db,id_gl4_cr,total_bayar,total_pengakuan,count_masa_pengakuan,tanggal,id_user)												
												select 
														'".$tId."',id_jns_bayar_siswa,'".$a."','".$_SISWA[$a]."','".$_GT['id1']."','".$tTanggal."','".$tIdTrans."','".$tNoBukti."','".$tDesc."','".(($_DESC[$a])?$_DESC[$a]:$tDesc)."','".$b."',masa_pengakuan,'".$iAkunD."','".$iAkunC."',0,0,0,'".TANGGAL."','".$_SESSION['user']."'
													from t_jns_bayar_siswa where id_jns_bayar_siswa='".$tJenis."'");
					}
					
					/*queryDb("update t_glu u 
										inner join t_gl1 g1 on g1.id_gl1=u.id_gl1 
										inner join t_glt t on t.id_unit1=u.id_unit1 and t.id_unit2=u.id_unit2 and t.id_unit3=u.id_unit3 and t.id_gl4=u.id_gl4 and concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3)='".$_SESSION['unit']."' and t.no_trans='".$tIdTrans."' and t.id_mtrans='".MTRANS."'
									set u.mutasi_d=(u.mutasi_d+t.nominal_d),u.mutasi_c=(u.mutasi_c+t.nominal_c),u.saldo=(u.saldo+((t.nominal_d-t.nominal_c)*if(g1.db_cr='D',1,-1)))");
					
					$mutasi=getValue("sum(t.nominal_c-t.nominal_d)","t_glt t inner join t_gl1 g1 on g1.id_gl1=t.id_gl1","concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3)='".$_SESSION['unit']."' and t.no_trans='".$tIdTrans."' and t.id_mtrans='".MTRANS."' and g1.balance='LR'");
					
					queryDb("update t_glu u 
									set u.mutasi_c=(u.mutasi_c+".$mutasi."),u.saldo=(u.saldo+".$mutasi.")
									where concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3)='".$_SESSION['unit']."' and u.id_gl4='".$tBatas."'");*/
				}
				
				header("location:?edit=".bs64_e($tIdTrans)."&id1=".bs64_e($_GT['id1'])."&order1=".bs64_e($_GT['order1']));
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:100px;text-align:center; }
#titleTop ol li:nth-child(3) , #list ul li:nth-child(3) { width:100px;text-align:center; }
#titleTop ol li:nth-child(4) , #list ul li:nth-child(4) { width:120px;text-align:center; }
#titleTop ol li:nth-child(6) , #list ul li:nth-child(6) { width:120px; }
#titleTop ol li { text-align:center; }
#list ul li:nth-child(6) { text-align:right; }

table input[type=text], table input[type=password],  table select { width:400px; }

div#input table tr td.x ol li:nth-child(1) { width:218px; }
div#input table tr td.x ol li:nth-child(2) { width:127px; }
div#input table tr td.x ol li:nth-child(3) { width:218px; }
div#input table tr td.x ul li:nth-child(1) input[type=text] { width:210px; }
div#input table tr td.x ul li:nth-child(1) input[type=text].n { width:125px;text-align:right; }
div#input table tr td.x ul li:nth-child(1) input[type=text].k { width:192px; }
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
                  <td>NO TRAN / TGL</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdTrans" id="tIdTrans" value="<?=htmlentities($tIdTrans)?>" maxlength="10" class="readonly" readonly="readonly" style="width:250px;" />
                    <input type="text" name="tTanggal" id="tTanggal" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" style="width:147px;" onkeyup="hideFade('errtTanggal')" />
                    <div id="errtTanggal" class="err"><?=$errtTanggal?></div>
                  </td>
                </tr>
                <tr>
                  <td>NO BUKTI</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNoBukti" id="tNoBukti" maxlength="20" value="<?=htmlentities($tNoBukti)?>" required="required" onkeyup="hideFade('errtNoBukti')" />
                    <div id="errtNoBukti" class="err"><?=$errtNoBukti?></div>
                  </td>
                </tr>
                <tr>
                  <td><?=strtoupper($_IST['gl1.php'])?> (DEBIT)</td>
                  <td>:</td>
                  <td>
                      <input type="hidden" name="iAkunD" id="iAkunD" value="<?=htmlentities($iAkunD)?>" />
                      <input name="nAkunD" type="text" id="nAkunD" maxlength="200" value="<?=htmlentities($nAkunD)?>" autocomplete="off" onkeyup="goFramePopup(this,'AkunD','akun','D','<?=MTRANS?>','DB');hideFade('errnAkunD');" onfocus="framePopupFocus('AkunD')" onblur="framePopupBlur('AkunD')" required="required" />
                      <div class="framePopup"><img id="imgAkunD" src="../images/loader.gif" /><iframe id="fAkunD"></iframe></div>
                    <div id="errnAkunD" class="err"><?=$errnAkunD?></div>
                  </td>
                </tr>
                <tr>
                  <td><?=strtoupper($_IST['gl1.php'])?> (KREDIT)</td>
                  <td>:</td>
                  <td>
                      <input type="hidden" name="iAkunC" id="iAkunC" value="<?=htmlentities($iAkunC)?>" />
                      <input name="nAkunC" type="text" id="nAkunC" maxlength="200" value="<?=htmlentities($nAkunC)?>" autocomplete="off" onkeyup="goFramePopup(this,'AkunC','akun','C','<?=MTRANS?>','CR');hideFade('errnAkunC');" onfocus="framePopupFocus('AkunC')" onblur="framePopupBlur('AkunC')" required="required" />
                      <div class="framePopup"><img id="imgAkunC" src="../images/loader.gif" /><iframe id="fAkunC"></iframe></div>
                    <div id="errnAkunC" class="err"><?=$errnAkunC?></div>
                  </td>
                </tr>
                <tr>
                  <td>KETERANGAN</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tDesc" id="tDesc" maxlength="255" value="<?=htmlentities($tDesc)?>" onkeyup="hideFade('errtDesc')" />
                    <div id="errtDesc" class="err"><?=$errtDesc?></div>
                  </td>
                </tr>
                <tr>
                  <td>PEMBATASAN</td>
                  <td>:</td>
                  <td>
                  	<select name="tBatas" id="tBatas" onchange="hideFade('errtBatas')">
                    	<option value="">- Pilih -</option>
                        <?php
                        $a=queryDb("select id_gl4,nama from t_gl4 where id_gl1='3' order by id_gl4");
						while($b=mysql_fetch_array($a)) {
							echo "<option value=\"".$b['id_gl4']."\" ".(($tBatas==$b['id_gl4'])?"selected='selected'":"").">[".$b['id_gl4']."] ".$b['nama']."</option>";
						}
						?>
                    </select>
                    <div id="errtBatas" class="err"><?=$errtBatas?></div>
                  </td>
                </tr>
                <tr>
                  <td>JENIS PENERIMAAN</td>
                  <td>:</td>
                  <td>
                  	<select name="tJenis" id="tJenis" required="required" onchange="hideFade('errtJenis');setBiaya(this)">
                    	<option value="">- Pilih -</option>
                        <?php
                        $a=queryDb("select id_jns_bayar_siswa,nama from t_jns_bayar_siswa order by id_jns_bayar_siswa asc");
						while($b=mysql_fetch_array($a)) {
							echo "<option value=\"".$b['id_jns_bayar_siswa']."\" ".(($tJenis==$b['id_jns_bayar_siswa'])?"selected='selected'":"").">".$b['nama']."</option>";
						}
						?>
                    </select>
                    <div id="errtJenis" class="err"><?=$errtJenis?></div>
                  </td>
                </tr>
                <tr>
                  <td colspan="3" class="x">
                  	<ol class="title" style="margin:8px 0 0 0;"><li><?=strtoupper($_IST['siswa.php'])?></li><li>NOMINAL <button onclick="setBiaya('elm(`tJenis`)'); return false;" class="icon-reset" style="padding:0;width:25px;height:20px;vertical-align:top;background-position:0px -314px;" /></button></li><li>URAIAN</li></ol>
                    <div style="height:300px;overflow:auto;">
                    <?php
					if(is_array($_SISWA)) {
						reset($_SISWA);
						
						$no=0;
						while(list($a,$b)=each($_SISWA)) {
							$_ASISWAX[$no]=explode("#",$_SISWAX[$a]);
							while(list($aa,$bb)=each($_ASISWAX[$no])) {
								$_ASISWAX[$no][$aa]=showRupiah2($bb);
							}
							$jsx.="siswa[".$no."]=\"".implode("#",$_ASISWAX[$no])."\";";
							echo "<ul><li>
										<input type=\"hidden\" name=\"iSiswa".$no."\" id=\"iSiswa".$no."\" value=\"".$a."\" />
										<input type=\"text\" name=\"nSiswa".$no."\" id=\"nSiswa".$no."\" maxlength=\"200\" value=\"[".$a."] ".$_SISWA[$a]."\" readonly=\"readonly\" class=\"readonly\" />
										<input type=\"text\" name=\"tNominal".$no."\" id=\"tNominal".$no."\" value=\"".(($_NOMINAL[$a])?showRupiah2($_NOMINAL[$a]):"")."\" maxlength=\"20\" onkeyup=\"hideFade('errnSiswa".$no."');valnominal(this);\" class=\"n\" />
										<input type=\"text\" name=\"tDesc".$no."\" id=\"tDesc".$no."\" maxlength=\"200\" value=\"".$_DESC[$a]."\" class=\"k\" />
										<div id=\"errnSiswa".$no."\" class=\"err\">".$_errnSiswa[$a]."</div>
									</li></ul>";
							$no++;
						}
					}
					?>
                    </div>              
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
  	<ol><li><?=strtoupper($titleHTML)?> <?=strtoupper($_IST['kelas.php'])?> <?=getValue("nama","t_kelas","id_kelas='".$_GT['id1']."' limit 1")?></li></ol>
    <ol class="tab"><li>
    	<a href="tagihan_siswa.php?edit=<?=bs64_e($_GT['id1'])."&order=".bs64_e($_GT['order1'])?>" class="on">PILIH <?=strtoupper($_IST['kelas.php'])?></a>
        <a href="#">JURNAL PENAGIHAN <?=strtoupper($_IST['siswa.php'])?></a>
    </li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("x.tgl_trans#Tanggal","x.no_trans#No Trans","x.no_bukti#No Bukti","x.keterangan#Keterangan","x.nominal#Nominal");
		
		if(is_array($listTitle)) {
			reset($listTitle);
			foreach($listTitle as $value) {
				$arrayValue=explode("#",$value);
				echo "<li ".(($arrayValue[0])?("id=\"".$arrayValue[0]."\" onclick=\"return goOrder('order,sort,search,id1,order1',this.id);\" ".(($_GT['order']==($arrayValue[0]." asc"))?"class=\"asc\"":(($_GT['order']==($arrayValue[0]." desc"))?"class=\"desc\"":""))." title=\"Urutkan berdasarkan ".strtoupper($arrayValue[1])."\""):"class=\"only\"")."><span>".strtoupper($arrayValue[1])."</span></li>";
			}
		}
		?>
    </ol>
</div>
<div id="list">
	<?php
	$row=50;
	$page=8;
	/*$sql="SELECT x.tgl_trans,x.no_trans,x.no_bukti,x.keterangan,x.nominal,min(m.nama) as jenis,
						if(x.tgl_trans>'".$lastPosting."',if(total_bayar=0 and count_masa_pengakuan=0,1,0),0) as stat_delete
					from (
						SELECT t.tgl_trans,t.no_trans,t.no_bukti,t.keterangan,sum(t.nominal_d) as nominal,t.tanggal
								FROM t_glt t
								where concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3)='".$_SESSION['unit']."' and t.no_trans_ref=t.no_trans and t.id_mtrans='".MTRANS."'
								group by t.tgl_trans,t.no_trans,t.no_bukti,t.keterangan,t.tanggal
						) x
					inner join t_siswa_tagih b on b.no_trans=x.no_trans and b.id_kelas='".$_GT['id1']."'
					inner join t_jns_bayar_siswa m on m.id_jns_bayar_siswa=b.id_jns_bayar_siswa
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")."
				group by x.tgl_trans,x.no_trans,x.no_bukti,x.keterangan,x.nominal
				order by ".(($_GT['order'])?$_GT['order'].",x.tanggal desc":"x.tanggal desc");*/
				
	$sql="select x.tgl_trans,x.no_trans,x.no_bukti,x.keterangan,x.nominal,x.stat_delete from (	
							SELECT b.tgl_trans,b.no_trans,b.no_bukti,b.keterangan,sum(b.nominal) as nominal,if(b.tgl_trans>'".$lastPosting."' and max(total_bayar)=0 and max(total_pengakuan)=0 and max(count_masa_pengakuan)=0,1,0) as stat_delete,min(b.tanggal) as tanggal
										from t_siswa_tagih b
									where b.id_kelas='".$_GT['id1']."'
									group by b.tgl_trans,b.no_trans,b.no_bukti,b.keterangan
						) x
					".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")."
					order by ".(($_GT['order'])?$_GT['order'].",x.tanggal desc":"x.tanggal desc");

	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&order1=".bs64_e($_GT['order1']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		//echo($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['no_trans']."\" value=\"".$b['no_trans']."\" />";
			echo "<input type=\"hidden\" id=\"tIdTrans-".$b['no_trans']."\" value=\"".$b['no_trans']."\" />";
			echo "<input type=\"hidden\" id=\"tNoBukti-".$b['no_trans']."\" value=\"".$b['no_bukti']."\" />";
			echo "<ul id=\"".$b['no_trans']."\" onclick=\"listFocus(this,'edit=1,del=".$b['stat_delete'].",search=1,xls=1')\"><li>".$paging['start'].".</li><li>".tglIndo(balikTanggal($b['tgl_trans']),2)."</li><li>".$b['no_trans']."</li><li>".$b['no_bukti']."</li>
					<li>".$b['keterangan']."</li><li>".showRupiah2($b['nominal'])."</li></ul>";
		
			if($_GT['edit']==$b['no_trans']) $jsEdit="listFocus(elm('".$b['no_trans']."'),'edit=1,del=".$b['stat_delete'].",search=1');";
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
            <input type="reset" class="icon-search" onclick="goAddress('sort,search,order,id1,order1');" value="SEARCH" />
        </li>
    	<li style="width:auto;" class="paging">
			<span style="margin-right:40px;"><?=$paging['show']?></span><?=(($paging['page'])?"<span>Page :</span>".$paging['page']:"")?>
        </li>
    	<li class="r" style="width:auto;">
    	  <button id="btn-new" class="icon-new" onclick="elm('tIdTrans').value='<?=NOFAK?>';setTanggal('<?=date("d/m/Y")?>');newData('tNoBukti,tId,tDesc,tBatas');">TAMBAH</button>
    	  <!--<button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tIdTrans,tNoBukti');">EDIT</button>-->
    	  <?=($statHapusTrans==1)?"<button id=\"btn-del\" class=\"icon-del disabled\" onclick=\"delData('del,hal,sort,search,order,id1,order1');\">HAPUS</button>":""?>
          <button id="btn-search" class="icon-search disabled" onclick="viewJurnal();">VIEW</button>
    	  <button id="btn-xls" class="icon-xls disabled" onclick="goAddress('xls','tagihan_siswa_detail_xls.php');">XLS</button>
    	</li>
    </ol>
</div>
<script language="javascript">
	function viewJurnal() {
		if(!_URI['search']) { alert('Data belum dipilih'); return false; }
		
		if(windowPopUp['x']) windowPopUp['x'].close();
		popUp('../popup/v_jurnal_view.php?id='+bs64_e(_URI['search']),1000,600,'x');
		return false;
	}
	
	var siswa=new Array();
	<?=$jsx?>
	
	function setBiaya(e) {
		var mx=false;
		var ix=(e.selectedIndex)-1;
		
		if(ix>=0)  {
			for(var i=0;i<siswa.length;i++) {
				mx=siswa[i].split("#");
				elm('tNominal'+i).value=mx[ix];
			}
		}
		else {
			for(var i=0;i<siswa.length;i++) {
				elm('tNominal'+i).value=0;
			}
		}
	}
	
	function setTanggal(v) {
		$(function() {
			$("#tTanggal").datepicker();
			$("#tTanggal").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal").datepicker("setDate",v);
		});
	}
		
	<?php
		if($tTanggal) echo "setTanggal('".$tTanggal."');";
	?>
	
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	
	hideFade("load","<?=($errtTanggal || $errtNoBukti || $errnAkunD || $errnAkunC || $errtBatas || count($_errnSiswa))?"showFade('input')":""?>");
		
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
