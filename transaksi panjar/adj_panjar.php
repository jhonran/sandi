<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='".basename($_SERVER["SCRIPT_FILENAME"])."'",
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
		define("MTRANS","06");
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
				
		if($_GT['del']) {
			if($statHapusTrans==1) {
				/*queryDb("insert into t_gltd(id,id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user,tanggald,id_userd)
							select id,id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user,'".TANGGAL."','".$_SESSION['user']."' from t_glt 
								where no_trans='".$_GT['del']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' and id_mtrans='".MTRANS."' and tgl_trans>'$lastPosting'");
				*/
				
				queryDb("delete from t_glt where no_trans='".$_GT['del']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' and id_mtrans='".MTRANS."' and tgl_trans>'".$lastPosting."'");
							
				if(mysql_affected_rows()>0) {
					queryDb("update t_panjar set no_trans3='',no_bukti3='',keterangan3='',nominal3='0',tanggal3='".TANGGAL."',id_user3='".$_SESSION['user']."' where no_trans3='".$_GT['del']."' and tgl_trans3>'".$lastPosting."'");
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
			$iPanjar=$_PT['iPanjar'];
			$nPanjar=$_PT['nPanjar'];
			$tPegawai=$_PT['tPegawai'];
			$tExpired=$_PT['tExpired'];
			$tDesc=$_PT['tDesc'];
			//$tBatas=$_PT['tBatas'];
			
			$a=queryDb("select if(p.tgl_trans2>'".balikTanggal($tTanggal)."',0,1) as stat_panjar_awal,p.nominal from t_panjar p
								 inner join t_glu u on u.id_gl4=p.id_gl4 and concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3)='".$_SESSION['unit']."'
							where p.no_trans2<>'' and p.no_trans3='' and p.id_panjar='".$iPanjar."' and concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3)<>concat(p.id_unit1,'.',p.id_unit2,'.',p.id_unit3) and p.id_gl4='".$iAkunC."' limit 1");
			$b=mysql_fetch_array($a);
			
			$tStatPanjarAwal=$b['stat_panjar_awal'];
			$tNominal=$b['nominal'];
			
			$eLR=substr($iAkunD,0,1);
			if($eLR==4 || $eLR==5) $statBatas=1;			
			
			$eLR=substr($iAkunC,0,1);
			if($eLR==4 || $eLR==5) $statBatas=1;
			
			$errtTanggal=(!balikTanggal($tTanggal))?"Format Tanggal masih salah (DD/MM/YYYY)":((dateDiff(TANGGAL2,balikTanggal($tTanggal))<0)?"Tanggal tidak boleh lebih besar dari sekarang":((dateDiff(balikTanggal($tTanggal),$lastPosting)<=0)?"Tanggal harus lebih besar dari tanggal Tutup Buku":(($tStatPanjarAwal!=1)?"Tanggal tidak boleh lebih kecil dari Tanggal Penerimaan Panjar":"")));
			$errtNoBukti=(!$tNoBukti)?"No Bukti masih kosong":"";
			$errnAkunD=(!$iAkunD)?$_IST['gl1.php']." Debit masih kosong":((!isset($_AKUN['DB'][$iAkunD]))?$_IST['gl1.php']." Debit tidak terdaftar":"");
			$errnAkunC=(!$iAkunC)?$_IST['gl1.php']." Kredit masih kosong":((!isset($_AKUN['CR'][$iAkunC]))?$_IST['gl1.php']." Kredit tidak terdaftar":(($iAkunC==$iAkunD)?$_IST['gl1.php']." Debit dan Kredit tidak boleh sama":""));
			$errnPanjar=(!$iPanjar)?"Panjar masih kosong":((!$tNominal || $tNominal==0)?"Data Panjar tidak terdaftar":"");
			$errtBatas=($statBatas && !$tBatas)?"Pembatasan Belum dipilih":"";
			
			$tNominal=showRupiah2($tNominal);
		
			if(!$errtTanggal && !$errtNoBukti && !$errnAkunD && !$errnAkunC && !$errnPanjar && !$errtBatas) {	
				if($tId) {
				
				}
				else {
					$tTanggal=balikTanggal($tTanggal);
					$dym=substr(str_replace("-","",$tTanggal),0,6);
					$tIdTrans=$dym.substr(((getValue("substring(no_trans,7,4)","t_glt","no_trans like '".$dym."%' order by no_trans desc limit 0,1")*1)+10001),1,4);
					
					queryDb("insert into t_glt(id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user)
														select '".MTRANS."','".$tTanggal."','".$tIdTrans."','".$tIdTrans."','".$tNoBukti."','".$tDesc."',g.id_unit1,g.id_unit2,g.id_unit3,g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,if(g.id_gl1='4' or g.id_gl1='5','".$tBatas."',''),g.id_aruskas,g.id_aruskas_sub,'".clearRupiah($tNominal)."',0,if(g1.db_cr='D',(saldo+".clearRupiah($tNominal)."),(saldo-".clearRupiah($tNominal).")),'".TANGGAL."','".$_SESSION['user']."'
																from t_glu g
																	inner join t_gl1 g1 on g1.id_gl1=g.id_gl1
																where concat(g.id_unit1,'.',g.id_unit2,'.',g.id_unit3)='".$_SESSION['unit']."' and g.id_gl4='".$iAkunD."'");
					
					queryDb("insert into t_glt(id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user)
														select '".MTRANS."','".$tTanggal."','".$tIdTrans."','".$tIdTrans."','".$tNoBukti."','".$tDesc."',g.id_unit1,g.id_unit2,g.id_unit3,g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,if(g.id_gl1='4' or g.id_gl1='5','".$tBatas."',''),g.id_aruskas,g.id_aruskas_sub,0,'".clearRupiah($tNominal)."',if(g1.db_cr='C',(saldo+".clearRupiah($tNominal)."),(saldo-".clearRupiah($tNominal).")),'".TANGGAL."','".$_SESSION['user']."'
																from t_glu g
																	inner join t_gl1 g1 on g1.id_gl1=g.id_gl1
																where concat(g.id_unit1,'.',g.id_unit2,'.',g.id_unit3)='".$_SESSION['unit']."' and g.id_gl4='".$iAkunC."'");
																
					queryDb("update t_panjar p
									inner join t_unit3 u on concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3)='".$_SESSION['unit']."'
								set tgl_trans3='".$tTanggal."',no_trans3='".$tIdTrans."',no_bukti3='".$tNoBukti."',keterangan3='".$tDesc."',nominal3='".clearRupiah($tNominal)."',tanggal3='".TANGGAL."',id_user3='".$_SESSION['user']."'
								where p.no_trans2<>'' and p.no_trans3='' and p.id_panjar='".$iPanjar."'");
					
					/*queryDb("insert into t_panjar(no_trans,id_unit1,id_unit2,id_unit3,id_pegawai,nm_pegawai,tgl_expired,tanggal,id_user)
									select '".$tIdTrans."',id_unit1,id_unit2,id_unit3,'".$iPegawai."','".$nPegawai."','".$tExpired."','".TANGGAL."','".$_SESSION['user']."' from t_unit3
										where concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$iUnit."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)<>'".$_SESSION['unit']."'");*/
					
					/*queryDb("update t_glu u 
										inner join t_gl1 g1 on g1.id_gl1=u.id_gl1 
										inner join t_glt t on t.id_unit1=u.id_unit1 and t.id_unit2=u.id_unit2 and t.id_unit3=u.id_unit3 and t.id_gl4=u.id_gl4 and concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3)='".$_SESSION['unit']."' and t.no_trans='".$tIdTrans."' and t.id_mtrans='".MTRANS."'
									set u.mutasi_d=(u.mutasi_d+t.nominal_d),u.mutasi_c=(u.mutasi_c+t.nominal_c),u.saldo=(u.saldo+((t.nominal_d-t.nominal_c)*if(g1.db_cr='D',1,-1)))");
					
					$mutasi=getValue("sum(t.nominal_d-t.nominal_c)","t_glt t inner join t_gl1 g1 on g1.id_gl1=t.id_gl1","concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3)='".$_SESSION['unit']."' and t.no_trans='".$tIdTrans."' and t.id_mtrans='".MTRANS."' and g1.balance='LR'");
					
					queryDb("update t_glu u 
									set u.mutasi_d=(u.mutasi_d+".$mutasi."),u.saldo=(u.saldo-".$mutasi.")
									where concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3)='".$_SESSION['unit']."' and u.id_gl4='".$tBatas."'");*/
				}
				
				header("location:?edit=".bs64_e($tIdTrans));
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
#titleTop ol li:nth-child(5) , #list ul li:nth-child(5) { width:100px;text-align:center; }
#titleTop ol li:nth-child(6) , #list ul li:nth-child(6) { width:150px;text-align:center; }
#titleTop ol li:nth-child(8) , #list ul li:nth-child(8) { width:120px; }
#titleTop ol li { text-align:center; }
#list ul li:nth-child(8) { text-align:right; }

table input[type=text], table input[type=password],  table select { width:400px; }

div#input table tr td.x ol li:nth-child(1) { width:388px; }
div#input table tr td.x ul li:nth-child(1) input[type=text] { width:380px; }
div#input table tr td.x ul li:nth-child(1) input[type=text].n { width:170px;text-align:right; }

#list ul.g { background:#FFF4F4; }
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
                      <input name="nAkunD" type="text" id="nAkunD" maxlength="200" value="<?=htmlentities($nAkunD)?>" autocomplete="off" onkeyup="goFramePopup(this,'AkunD','akun','D','<?=MTRANS?>','DB');hideFade('errnAkunD');clearElms(0);" onfocus="framePopupFocus('AkunD')" onblur="framePopupBlur('AkunD')" required="required" />
                      <div class="framePopup"><img id="imgAkunD" src="../images/loader.gif" /><iframe id="fAkunD"></iframe></div>
                    <div id="errnAkunD" class="err"><?=$errnAkunD?></div>
                  </td>
                </tr>
                <tr>
                  <td><?=strtoupper($_IST['gl1.php'])?> (KREDIT)</td>
                  <td>:</td>
                  <td>
                      <input type="hidden" name="iAkunC" id="iAkunC" value="<?=htmlentities($iAkunC)?>" />
                      <input name="nAkunC" type="text" id="nAkunC" maxlength="200" value="<?=htmlentities($nAkunC)?>" autocomplete="off" onkeyup="goFramePopup(this,'AkunC','akun','C','<?=MTRANS?>','CR');hideFade('errnAkunC');clearElms(0);" onfocus="framePopupFocus('AkunC')" onblur="framePopupBlur('AkunC')" required="required" />
                      <div class="framePopup"><img id="imgAkunC" src="../images/loader.gif" /><iframe id="fAkunC"></iframe></div>
                    <div id="errnAkunC" class="err"><?=$errnAkunC?></div>
                  </td>
                </tr>
                <tr>
                  <td>DATA PANJAR</td>
                  <td>:</td>
                  <td>
                      <input type="hidden" name="iPanjar" id="iPanjar" value="<?=htmlentities($iPanjar)?>" />
                      <input name="nPanjar" type="text" id="nPanjar" maxlength="200" value="<?=htmlentities($nPanjar)?>" autocomplete="off" onkeyup="goFramePopup(this,'Panjar','panjar_terima',elm('iAkunC').value);hideFade('errnPanjar');clearElmX();" onfocus="framePopupFocus('Panjar')" onblur="framePopupBlur('Panjar')" />
                      <div class="framePopup"><img id="imgPanjar" src="../images/loader.gif" /><iframe id="fPanjar"></iframe></div>
                    <div id="errnPanjar" class="err"><?=$errnPanjar?></div>
                  </td>
                </tr>
                <tr>
                  <td><?=strtoupper($_IST['pegawai.php'])?></td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tPegawai" id="tPegawai" maxlength="12" autocomplete="off" value="<?=htmlentities($tPegawai)?>" required="required" class="readonly" readonly="readonly" />
                  </td>
                </tr>
                <tr>
                  <td>BATAS WAKTU</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tExpired" id="tExpired" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" value="<?=htmlentities($tExpired)?>" required="required" class="readonly" readonly="readonly" />
                  </td>
                </tr>
                <tr>
                  <td>NOMINAL</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNominal" id="tNominal" maxlength="20" required="required" value="<?=htmlentities($tNominal)?>" class="readonly" readonly="readonly" />
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
                <!--<tr>
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
                </tr>-->
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
        $listTitle=array("x.tgl_trans#Tanggal","x.no_trans#No Trans","x.no_bukti#No Bukti","j.no_trans#No Panjar","j.tgl_expired#Jatuh Tempo Adj","x.keterangan#Keterangan","x.nominal#Nominal");
		
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
	$sql="SELECT x.tgl_trans,x.no_trans,x.no_bukti,x.keterangan,x.nominal,if(x.tgl_trans>'".$lastPosting."',1,0) as stat_delete,j.no_trans as panjar,if(j.tgl_expired<'".TANGGAL."',1,0) as stat_expired,
						j.id_pegawai,j.nm_pegawai,j.tgl_expired
					from (
						SELECT t.tgl_trans,t.no_trans,t.no_bukti,t.keterangan,sum(t.nominal_d) as nominal,t.tanggal
								FROM t_glt t
								where concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3)='".$_SESSION['unit']."' and t.no_trans_ref=t.no_trans and t.id_mtrans='".MTRANS."'
								group by t.tgl_trans,t.no_trans,t.no_bukti,t.keterangan,t.tanggal
						) x
					inner join t_panjar j on j.no_trans3=x.no_trans
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				order by ".(($_GT['order'])?$_GT['order'].",x.tanggal desc":"x.tanggal desc");
	
	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['no_trans']."\" value=\"".$b['no_trans']."\" />";
			echo "<input type=\"hidden\" id=\"tIdTrans-".$b['no_trans']."\" value=\"".$b['no_trans']."\" />";
			echo "<input type=\"hidden\" id=\"tNoBukti-".$b['no_trans']."\" value=\"".$b['nama']."\" />";
			echo "<ul id=\"".$b['no_trans']."\" ".(($b['stat_expired']==1)?"class=\"g\"":"")." onclick=\"listFocus(this,'edit=1,del=".$b['stat_delete'].",search=1')\"><li>".$paging['start'].".</li><li>".tglIndo(balikTanggal($b['tgl_trans']),2)."</li><li>".$b['no_trans']."</li><li>".$b['no_bukti']."</li><li>".$b['panjar']."</li>
					<li>".tglIndo($b['tgl_expired'])."</li><li>".$b['keterangan']."</li><li>".showRupiah2($b['nominal'])."</li></ul>";
		
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
            <input type="reset" class="icon-search" onclick="goAddress('sort,search,order,tab');" value="SEARCH" />
        </li>
    	<li style="width:auto;" class="paging">
			<span style="margin-right:40px;"><?=$paging['show']?></span><?=(($paging['page'])?"<span>Page :</span>".$paging['page']:"")?>
        </li>
    	<li class="r" style="width:auto;">
    	  <button id="btn-new" class="icon-new" onclick="elm('tIdTrans').value='<?=NOFAK?>';setTanggal('<?=date("d/m/Y")?>');newData('tId,tNoBukti,tDesc');">TAMBAH</button>
    	  <!--<button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tIdTrans,tNoBukti');">EDIT</button>-->
    	  <?=($statHapusTrans==1)?"<button id=\"btn-del\" class=\"icon-del disabled\" onclick=\"delData('del,hal,sort,search,order,id1,order1');\">HAPUS</button>":""?>
          <button id="btn-search" class="icon-search disabled" onclick="viewJurnal();">VIEW</button>
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
	
	function clearElmX() {
		elm('tPegawai').value='';
		elm('tExpired').value='';
		elm('tNominal').value='';
	}
	
	function clearElms(v) {
		if(v==0) { elm('iPanjar').value='';elm('nPanjar').value='';clearElmX(); }
	}
	
	function setTanggal(v1,v2) {
		$(function() {
			$("#tTanggal").datepicker();
			$("#tTanggal").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal").datepicker("setDate",v1);
		});
	}
		
	<?php
		if($tTanggal) echo "setTanggal('".$tTanggal."');";
	?>
	
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	
	hideFade("load","<?=($errtTanggal || $errtNoBukti || $errnAkunD || $errnAkunC || $errnPanjar || $errtBatas)?"showFade('input')":""?>");
		
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
