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
		
		$dym=date("Ym");
		define("NOFAK",$dym.substr(((getValue("substring(no_trans,7,4)","t_glt","no_trans like '".$dym."%' order by no_trans desc limit 0,1")*1)+10001),1,4));
		define("MTRANS","10");
		$lastPosting=getValue("max(finis_trans)","((select '1900-01-01' as finis_trans) UNION (select tgl_finish from t_posting order by tgl_finish desc limit 0,1)) x","1=1");
		$statHapusTrans=getValue("stat_hapus_trans","t_user","id_user='".$_SESSION['user']."'");
		
		$lastPengakuan=getValue("max(tgl_trans)","((select '1900-01-01' as tgl_trans) UNION (select tgl_trans from t_siswa_pengakuan order by tgl_trans desc limit 0,1)) x","1=1");
		
		if($_GT['del']) {
			if($statHapusTrans==1) {
				queryDb("delete from t_glt where no_trans='".$_GT['del']."' and id_mtrans='".MTRANS."' and tgl_trans>'".$lastPosting."' and tgl_trans='".$lastPengakuan."'");
							
				if(mysql_affected_rows()>0) {
					queryDb("delete from t_siswa_pengakuan where no_trans='".$_GT['del']."' and tgl_trans>'".$lastPosting."' and tgl_trans='".$lastPengakuan."'");
				}
				
				header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdTrans=$_PT['tIdTrans'];
			$tTanggal=$_PT['tTanggal'];
			$tNoBukti=$_PT['tNoBukti'];
			$tDesc=$_PT['tDesc'];
			$tBatas=$_PT['tBatas'];			
			
			$a=queryDb("select a.id_siswa_tagih,a.id_siswa,a.nama_siswa,a.id_kelas,a.id_jns_bayar_siswa,a.nominal,a.masa_pengakuan,
									a.total_pengakuan,a.count_masa_pengakuan,a.id_gl4_cr as id_gl4_db,j.id_gl4 as id_gl4_cr,
									concat(k.id_unit1,'.',k.id_unit2,'.',k.id_unit3) as id_unit
								from t_siswa_tagih a
									inner join t_kelas k on k.id_kelas=a.id_kelas
									inner join t_jns_bayar_siswa j on j.id_jns_bayar_siswa=a.id_jns_bayar_siswa
								where a.nominal>a.total_pengakuan and a.masa_pengakuan>a.count_masa_pengakuan");
							
			while($b=mysql_fetch_array($a)) {			
				$_DATA[$b['id_siswa_tagih']]['id_siswa']=$b['id_siswa'];
				$_DATA[$b['id_siswa_tagih']]['nama_siswa']=$b['nama_siswa'];
				$_DATA[$b['id_siswa_tagih']]['id_kelas']=$b['id_kelas'];
				$_DATA[$b['id_siswa_tagih']]['id_jns_bayar']=$b['id_jns_bayar_siswa'];
				$_DATA[$b['id_siswa_tagih']]['nominal_tagih']=$b['nominal'];
				$_DATA[$b['id_siswa_tagih']]['masa_pengakuan']=$b['masa_pengakuan'];
				
				$_DATA[$b['id_siswa_tagih']]['total_pengakuan']=$b['total_pengakuan'];
				$_DATA[$b['id_siswa_tagih']]['count_masa_pengakuan']=$b['count_masa_pengakuan'];
				$_DATA[$b['id_siswa_tagih']]['id_gl4_db']=$b['id_gl4_db'];
				$_DATA[$b['id_siswa_tagih']]['id_gl4_cr']=$b['id_gl4_cr'];
				
				
				if($_DATA[$b['id_siswa_tagih']]['masa_pengakuan']<=($_DATA[$b['id_siswa_tagih']]['count_masa_pengakuan']+1)) $_DATA[$b['id_siswa_tagih']]['nominal']=$_DATA[$b['id_siswa_tagih']]['nominal_tagih']-$_DATA[$b['id_siswa_tagih']]['total_pengakuan'];
				else $_DATA[$b['id_siswa_tagih']]['nominal']=ceil($_DATA[$b['id_siswa_tagih']]['nominal_tagih']/$_DATA[$b['id_siswa_tagih']]['masa_pengakuan']);
				
				$_UNIT[$b['id_unit']]=1;
				$_AKUNDN[$b['id_unit']][$b['id_gl4_db']]+=$_DATA[$b['id_siswa_tagih']]['nominal'];
				$_AKUNCN[$b['id_unit']][$b['id_gl4_cr']]+=$_DATA[$b['id_siswa_tagih']]['nominal'];
				
				$eLRD=substr($b['id_gl4_db'],0,1);
				$eLRC=substr($b['id_gl4_cr'],0,1);
				if($eLRD==4 || $eLRD==5 || $eLRC==4 || $eLRC==5) $statBatas=1;
			}
			
			if(!$errtTanggal) {
				if(!mysql_num_rows($a)) $errtTanggal="Tidak ada Data Penagihan yang dapat diakukan";
				else {			
					$errtTanggal=(!balikTanggal($tTanggal))?"Format Tanggal masih salah (DD/MM/YYYY)":((dateDiff(TANGGAL2,balikTanggal($tTanggal))<0)?"Tanggal tidak boleh lebih besar dari sekarang":((dateDiff(balikTanggal($tTanggal),$lastPosting)<=0)?"Tanggal harus lebih besar dari tanggal Tutup Buku":((dateDiff(balikTanggal($tTanggal),$lastPengakuan)<=0)?"Tanggal harus lebih besar dari tanggal Pengakuan Sebelumnya":"")));
					$errtNoBukti=(!$tNoBukti)?"No Bukti masih kosong":"";
					$errtBatas=($statBatas && !$tBatas)?"Pembatasan Belum dipilih":"";
					
					if(!$errtBatas) {
						reset($_UNIT);		
						while(list($x,$y)=each($_UNIT)) {		
							reset($_AKUNCN[$x]);							
							while(list($a,$b)=each($_AKUNCN[$x])) {
								if($a==$_AKUNDN[$x][$a]) {
									$errtBatas=$_IST['gl1.php']." Debit dan Kredit ada yang terduplikasi";
									break;
								}
							}
							
							if($errtBatas) break;
						}
					}	
				}
			}
			
			if(!$errtTanggal && !$errtNoBukti && !$errtBatas) {				
				if($tId) {
				
				}
				else {
					$tTanggal=balikTanggal($tTanggal);
					$dym=substr(str_replace("-","",$tTanggal),0,6);
					$tIdTrans=$dym.substr(((getValue("substring(no_trans,7,4)","t_glt","no_trans like '".$dym."%' order by no_trans desc limit 0,1")*1)+10001),1,4);
										
					reset($_UNIT);		
					while(list($x,$y)=each($_UNIT)) {					
						reset($_AKUNDN[$x]);							
						while(list($a,$b)=each($_AKUNDN[$x])) {
							queryDb("insert into t_glt(id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user)
																select '".MTRANS."','".$tTanggal."','".$tIdTrans."','".$tIdTrans."','".$tNoBukti."','".$tDesc."',g.id_unit1,g.id_unit2,g.id_unit3,g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,if(g.id_gl1='4' or g.id_gl1='5','".$tBatas."',''),g.id_aruskas,g.id_aruskas_sub,'".$_AKUNDN[$x][$a]."',0,if(g1.db_cr='D',(saldo+".$_AKUNDN[$x][$a]."),(saldo-".$_AKUNDN[$x][$a].")),'".TANGGAL."','".$_SESSION['user']."'
																		from t_glu g
																			inner join t_gl1 g1 on g1.id_gl1=g.id_gl1
																where concat(g.id_unit1,'.',g.id_unit2,'.',g.id_unit3)='".$x."' and g.id_gl4='".$a."'");
						}
																	
						reset($_AKUNCN[$x]);							
						while(list($a,$b)=each($_AKUNCN[$x])) {
							queryDb("insert into t_glt(id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user)
																select '".MTRANS."','".$tTanggal."','".$tIdTrans."','".$tIdTrans."','".$tNoBukti."','".$tDesc."',g.id_unit1,g.id_unit2,g.id_unit3,g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,if(g.id_gl1='4' or g.id_gl1='5','".$tBatas."',''),g.id_aruskas,g.id_aruskas_sub,0,'".$_AKUNCN[$x][$a]."',if(g1.db_cr='C',(saldo+".$_AKUNCN[$x][$a]."),(saldo-".$_AKUNCN[$x][$a].")),'".TANGGAL."','".$_SESSION['user']."'
																		from t_glu g
																			inner join t_gl1 g1 on g1.id_gl1=g.id_gl1
																where concat(g.id_unit1,'.',g.id_unit2,'.',g.id_unit3)='".$x."' and g.id_gl4='".$a."'");
						}
					}					
					
					reset($_DATA);
					while(list($a,$b)=each($_DATA)) {
						$tId=((getValue("id_siswa_pengakuan","t_siswa_pengakuan","1=1 order by id_siswa_pengakuan desc limit 0,1")*1)+1);
						queryDb("insert into t_siswa_pengakuan(id_siswa_pengakuan,id_siswa_tagih,id_jns_bayar_siswa,id_siswa,nama_siswa,id_kelas,nominal_tagih,masa_pengakuan,tgl_trans,no_trans,no_bukti,keterangan,nominal,id_gl4_db,id_gl4_cr,tanggal,id_user)
												values('".$tId."','".$a."','".$_DATA[$a]['id_jns_bayar']."','".$_DATA[$a]['id_siswa']."','".$_DATA[$a]['nama_siswa']."','".$_DATA[$a]['id_kelas']."','".$_DATA[$a]['nominal_tagih']."','".$_DATA[$a]['masa_pengakuan']."','".$tTanggal."','".$tIdTrans."','".$tNoBukti."','".$tDesc."','".$_DATA[$a]['nominal']."','".$_DATA[$a]['id_gl4_db']."','".$_DATA[$a]['id_gl4_cr']."','".TANGGAL."','".$_SESSION['user']."')");
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
#titleTop ol li:nth-child(6) , #list ul li:nth-child(6) { width:120px; }
#titleTop ol li:nth-child(7) , #list ul li:nth-child(7) { width:120px; }
#titleTop ol li { text-align:center; }
#list ul li:nth-child(6) { text-align:right; }

table input[type=text], table input[type=password],  table select { width:400px; }

div#input table tr td.x ol li:nth-child(1) { width:388px; }
div#input table tr td.x ul li:nth-child(1) input[type=text] { width:380px; }
div#input table tr td.x ul li:nth-child(1) input[type=text].n { width:170px;text-align:right; }
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
    <ol class="tab"><li><a href="#">PENGAKUAN PENDAPATAN</a><a href="#" class="off">DETAIL PENAGIHAN</a></li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("x.tgl_trans#Tanggal","x.no_trans#No Trans","x.no_bukti#No Bukti","x.keterangan#Keterangan","x.nominal#Nominal");
		
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
	$_UNITS=array();
										
	$a=queryDb("select concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) as id_unit,u.nama from t_user s 
						inner join t_entitas_unit e on e.id_entitas=s.id_entitas
						inner join t_unit3 u on u.id_unit1=e.id_unit1 and u.id_unit2=e.id_unit2 and u.id_unit3=e.id_unit3
					where s.id_user='".$_SESSION['user']."'");
	
	while($b=mysql_fetch_array($a)) {
		$_UNITS[$b['id_unit']]=$b['nama'];
	}	
	
	$row=50;
	$page=8;
	/*$sql="select tgl_trans,no_trans,no_bukti,keterangan,nominal,tanggal from (
					select s.tgl_trans,s.no_trans,s.no_bukti,s.keterangan,sum(s.nominal) as nominal,s.tanggal from t_siswa_pengakuan s
								inner join t_siswa_tagih a on a.id_siswa_tagih=s.id_siswa_tagih and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".implode("','",array_keys($_UNITS))."')
							group by s.tgl_trans,s.no_trans,s.no_bukti,s.keterangan,s.tanggal ) x
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				order by ".(($_GT['order'])?$_GT['order'].",tanggal desc":"tanggal desc");*/
				
	$sql="select x.tgl_trans,x.no_trans,x.no_bukti,x.keterangan,x.nominal,x.stat_delete from (
							SELECT b.tgl_trans,b.no_trans,b.no_bukti,b.keterangan,sum(b.nominal) as nominal,if(b.tgl_trans>'".$lastPosting."',if(b.tgl_trans='".$lastPengakuan."',1,0),0) as stat_delete,min(b.tanggal) as tanggal
										from t_siswa_pengakuan b
											inner join t_kelas k on k.id_kelas=b.id_kelas and concat(k.id_unit1,'.',k.id_unit2,'.',k.id_unit3) in ('".implode("','",array_keys($_UNITS))."')
									group by b.tgl_trans,b.no_trans,b.no_bukti,b.keterangan
						) x
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
			echo "<input type=\"hidden\" id=\"tNoBukti-".$b['no_trans']."\" value=\"".$b['no_bukti']."\" />";
			echo "<ul id=\"".$b['no_trans']."\" onclick=\"listFocus(this,'edit=1,del=".$b['stat_delete'].",search=1')\" ondblclick=\"_URI['id1']='".$b['no_trans']."';_URI['order1']='".$_GT['order']."';goAddress('id1,order1,hal,sort,search','pengakuan_pendapatan_detail.php')\"><li>".$paging['start'].".</li><li>".tglIndo(balikTanggal($b['tgl_trans']),2)."</li><li>".$b['no_trans']."</li><li>".$b['no_bukti']."</li>
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
            <input type="reset" class="icon-search" onclick="goAddress('sort,search,order,tab');" value="SEARCH" />
        </li>
    	<li style="width:auto;" class="paging">
			<span style="margin-right:40px;"><?=$paging['show']?></span><?=(($paging['page'])?"<span>Page :</span>".$paging['page']:"")?>
        </li>
    	<li class="r" style="width:auto;">
    	  <button id="btn-new" class="icon-new" onclick="elm('tIdTrans').value='<?=NOFAK?>';setTanggal('<?=date("d/m/Y")?>');newData('tId,tNoBukti,tDesc');">TAMBAH</button>
    	  <!--<button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tIdTrans,tNoBukti');">EDIT</button>-->
    	  <?=($statHapusTrans==1)?"<button id=\"btn-del\" class=\"icon-del disabled\" onclick=\"delData('del,hal,sort,search,order');\">HAPUS</button>":""?>
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
	
	function setTanggal(v) {
		$(function() {
			$("#tTanggal").datepicker();
			$("#tTanggal").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal").datepicker("setDate",v);
		});
	}
		
	<?php
		if($jsC) echo $jsC."elm('tAkunCCount').value=vc1.length;addAkun(vc1,vc2,vc3,errc);";
		
		if($tTanggal) echo "setTanggal('".$tTanggal."');";
	?>
	
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	
	hideFade("load","<?=($errtTanggal || $errtNoBukti || $errnAkunD || $errtBatas || count($_errnAkunC))?"showFade('input')":""?>");
		
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
