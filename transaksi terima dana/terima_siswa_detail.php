<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='terima_siswa.php'",
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
		define("MTRANS","09");
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
				queryDb("delete from t_glt where no_trans='".$_GT['del']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' and id_mtrans='".MTRANS."' and tgl_trans>'".$lastPosting."'");
				
				if(mysql_affected_rows()>0) {
					queryDb("delete from t_siswa_bayar where no_trans='".$_GT['del']."' and tgl_trans>'".$lastPosting."' and id_kelas='".$_GT['id1']."'");
				}
				 
				header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&order1=".bs64_e($_GT['order1']));
				exit;
			}
		}
		
		$_ftype["application/vnd.ms-excel"]="xls";
		//$_ftype["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"]="xlsx";
		
		if($_FILES["fileTerima"]["name"]) {
			if(isset($_ftype[$_FILES["fileTerima"]["type"]])) {
				$fileTerimaName=$_FILES["fileTerima"]["name"];
				$fileTerimaSize=$_FILES["fileTerima"]["color"];
				$fileTerimaTmp=$_FILES["fileTerima"]["tmp_name"];
				$fileTerimaType=$_FILES["fileTerima"]["type"];
			}
			else {
				$errtTanggal="Type file (".$_FILES["fileTerima"]["name"].") harus XLS";
			}
		}
		else if($HTTP_POST_FILES["fileTerima"]["name"]) {
			if(isset($_ftype[$HTTP_POST_FILES["fileTerima"]["type"]])) {
				$fileTerimaName=$HTTP_POST_FILES["fileTerima"]["name"];
				$fileTerimaSize=$HTTP_POST_FILES["fileTerima"]["color"];
				$fileTerimaTmp=$HTTP_POST_FILES["fileTerima"]["tmp_name"];
				$fileTerimaType=$HTTP_POST_FILES["fileTerima"]["type"];
			}
			else {
				$errtTanggal="Type file (".$HTTP_POST_FILES["fileTerima"]["name"].") harus XLS";
			}
		}
		
		if($fileTerimaName) {
			$fileTerimaName="../images/x1-".md5($_SESSION['user']).".".$_ftype[$fileTerimaType];
					
			if(file_exists($fileTerimaName)) unlink($fileTerimaName);
			@move_uploaded_file($fileTerimaTmp,$fileTerimaName);
			
			require_once "../includes/excel_reader.php";
			$xls=new Spreadsheet_Excel_Reader();
			
			$xls->read($fileTerimaName);
			
			error_reporting(E_ALL ^ E_NOTICE);
			
			for($i=1;$i<=$xls->sheets[0]['numRows'];$i++) {
				$xxx=($xls->sheets[0]['cells'][$i][1]);
				if(!is_numeric($xxx)) continue;
				
				$id_temp=explode(" ",$xls->sheets[0]['cells'][$i][3]);
				$tgl_temp=explode(" ",$xls->sheets[0]['cells'][$i][2]);
				
				$_XLSI[("xls".$i)]=substr($id_temp[0],6,100);
				$_XLSS[("xls".$i)]="[".substr($id_temp[0],8,100)."] ".trim($id_temp[1]." ".$id_temp[2]." ".$id_temp[3]." ".$id_temp[4]);
				$_XLST[("xls".$i)]=balikTanggal($tgl_temp[0]);
				$_XLSD[("xls".$i)]=$xls->sheets[0]['cells'][$i][4]." (".$xls->sheets[0]['cells'][$i][2].")";
				$_XLSN[("xls".$i)]=round(str_replace(",","",$xls->sheets[0]['cells'][$i][5]));
			}
			
			if(is_array($_XLSI)) {
				$a=queryDb("select t.id_siswa_tagih,concat(t.id_jns_bayar_siswa,t.id_siswa) as id,t.tgl_trans,concat('[',t.id_siswa,'] ',t.nama_siswa) as nama,(t.nominal-t.total_bayar) as sisa from t_siswa_tagih t
											inner join t_kelas j on j.id_kelas=t.id_kelas and concat(j.id_unit1,'.',j.id_unit2,'.',j.id_unit3)='".$_SESSION['unit']."'
										where concat(t.id_jns_bayar_siswa,t.id_siswa) in ('".implode("','",$_XLSI)."') and t.id_kelas='".$_GT['id1']."' and t.nominal>t.total_bayar
										order by t.tgl_trans");
									
				while($b=mysql_fetch_array($a)) {
					if(isset($_NO[$b['id']])) $_NO[$b['id']]++;
					else $_NO[$b['id']]=0;
					
					$_TAGIHI[$b['id']][$_NO[$b['id']]]=$b['id_siswa_tagih'];
					$_TAGIHT[$b['id']][$_NO[$b['id']]]=$b['tgl_trans'];
					$_TAGIHS[$b['id']][$_NO[$b['id']]]=$b['nama'];
					$_TAGIHN[$b['id']][$_NO[$b['id']]]=$b['sisa'];
				}
				
				while(list($a,$b)=each($_XLSI)) {
					if(!isset($_TAGIHI[$b])) {
						$_errnSiswa[$a]="Data Penagihan tidak terdaftar2";
						$_SISWA[$a]=$_XLSS[$a];
						$_SISWAN[$a]=$_XLSN[$a];
						$_SISWAD[$a]=$_XLSD[$a];
					}
					else {
						$statBreak=0;
						
						for($i=0;$i<count($_TAGIHI[$b]);$i++) {
							if(dateDiff($_XLST[$a],$_TAGIHT[$b][$i])>-1 && $_TAGIHN[$b][$i]>=($_SISWAN[$_TAGIHI[$b][$i]]+$_XLSN[$a])) {
								$_errnSiswa[$_TAGIHI[$b][$i]]="";
								
								$_SISWA[$_TAGIHI[$b][$i]]=$_TAGIHS[$b][$i];
								$_SISWAN[$_TAGIHI[$b][$i]]+=$_XLSN[$a];
								$_SISWAD[$_TAGIHI[$b][$i]].=$_XLSD[$a]." ";
								
								$statBreak=1;
								
								break;
							}
						}
						
						if($statBreak==0) {
							if(dateDiff($_XLST[$a],$_TAGIHT[$b][0])<0) {
								$_errnSiswa[$a]="Data Penagihan tidak terdaftar";
								$_SISWA[$a]=$_TAGIHS[$b][0];
								$_SISWAN[$a]=$_XLSN[$a];
								$_SISWAD[$a]=$_XLSD[$a];
							}
							else if($_TAGIHN[$b][0]<($_SISWAN[$_TAGIHI[$b][0]]+$_XLSN[$a])) {
								$_errnSiswa[$_TAGIHI[$b][0]]="Nilai lebih besar dari sisa Tagihan";
								$_SISWA[$_TAGIHI[$b][0]]=$_TAGIHS[$b][0];
								$_SISWAN[$_TAGIHI[$b][0]]+=$_XLSN[$a];
								$_SISWAD[$_TAGIHI[$b][0]].=$_XLSD[$a]." ";
							}
							
						}						
					}
				}
				
				$jsM="vc1=new Array(\"".implode("\",\"",array_keys($_SISWA))."\");vc2=new Array(\"".implode("\",\"",$_SISWA)."\");vc3=new Array(\"".implode("\",\"",$_SISWAN)."\");vc4=new Array(\"".implode("\",\"",$_SISWAD)."\");errm=new Array(\"".implode("\",\"",$_errnSiswa)."\");";
				
				$_errnSiswa=array_filter($_errnSiswa);
				
				$errtTanggal="Lengkapi data isian Anda!!!";
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
			$tSiswaCount=$_PT['tSiswaCount'];
						
			$_errnSiswa=array();
			
			for($i=0;$i<$tSiswaCount;$i++) {
				if($_PT['iSiswa'.$i]) {
					$_errnSiswa[$i]=(isset($_SISWA[$_PT['iSiswa'.$i]]))?"Data Penerimaan sudah duplikasi":((!$_PT['tNominal'.$i])?"Nominal masih kosong":"");
					
					$_SISWA[$_PT['iSiswa'.$i]]=$_PT['nSiswa'.$i];
					$_SISWAN[$_PT['iSiswa'.$i]]+=clearRupiah($_PT['tNominal'.$i]);
					$_SISWAD[$_PT['iSiswa'.$i]]=$_PT['tDesc'.$i];
				}
			}
			
			$a=queryDb("select t.id_siswa_tagih,t.id_siswa,t.nama_siswa,t.id_jns_bayar_siswa,t.nominal,(t.nominal-t.total_bayar) as sisa,t.id_gl4_db as id_gl4_cr,if(t.tgl_trans>'".balikTanggal($tTanggal)."',1,0) as err_tgl_tagih from t_siswa_tagih t
									inner join t_kelas j on j.id_kelas=t.id_kelas and concat(j.id_unit1,'.',j.id_unit2,'.',j.id_unit3)='".$_SESSION['unit']."'
								where t.id_siswa_tagih in ('".implode("','",array_keys($_SISWA))."') and t.id_kelas='".$_GT['id1']."'");
			while($b=mysql_fetch_array($a)) {
				$_DATA[$b['id_siswa_tagih']]['id_siswa']=$b['id_siswa'];
				$_DATA[$b['id_siswa_tagih']]['nama_siswa']=$b['nama_siswa'];
				$_DATA[$b['id_siswa_tagih']]['id_jns_bayar']=$b['id_jns_bayar_siswa'];
				$_DATA[$b['id_siswa_tagih']]['nominal_tagih']=$b['nominal'];
				$_DATA[$b['id_siswa_tagih']]['sisa']=$b['sisa'];
				$_DATA[$b['id_siswa_tagih']]['id_gl4_cr']=$b['id_gl4_cr'];				
				
				$_AKUNCN[$b['id_gl4_cr']]+=$_SISWAN[$b['id_siswa_tagih']];
				
				$_ERRTGL[$b['err_tgl_tagih']]=1;
			}
			if(is_array($_SISWA)) {
				$no=0;
				$jsM="vc1=new Array(\"".implode("\",\"",array_keys($_SISWA))."\");vc2=new Array(\"".implode("\",\"",$_SISWA)."\");vc3=new Array(\"".implode("\",\"",$_SISWAN)."\");vc4=new Array(\"".implode("\",\"",$_SISWAD)."\");";
				while(list($a,$b)=each($_SISWA)) {
					if($a && !$_errnSiswa[$no]) $_errnSiswa[$no]=(!is_array($_DATA[$a]) || $_DATA[$a]['sisa']<1)?"Data Penagihan tidak terdaftar atau telah terbayar":(($_DATA[$a]['sisa']<$_SISWAN[$a])?"Nilai lebih besar dari sisa Tagihan":"");
					$no++;
				}
				$jsM.="errm=new Array(\"".implode("\",\"",$_errnSiswa)."\");";
				
				$_errnSiswa=array_filter($_errnSiswa);
			}
			
			$eLR=substr($iAkunD,0,1);
			if($eLR==4 || $eLR==5) $statBatas=1;			
			
			$eLR=substr($iAkunC,0,1);
			if($eLR==4 || $eLR==5) $statBatas=1;
			
			$errtTanggal=(!balikTanggal($tTanggal))?"Format Tanggal masih salah (DD/MM/YYYY)":((dateDiff(TANGGAL2,balikTanggal($tTanggal))<0)?"Tanggal tidak boleh lebih besar dari sekarang":((dateDiff(balikTanggal($tTanggal),$lastPosting)<=0)?"Tanggal harus lebih besar dari tanggal Tutup Buku":(($_ERRTGL[1]==1)?"Tanggal tidak boleh lebih kecil dari data Penagihan":"")));
			$errtNoBukti=(!$tNoBukti)?"No Bukti masih kosong":"";
			$errnAkunD=(!$iAkunD)?$_IST['gl1.php']." Debit masih kosong":((!isset($_AKUN['DB'][$iAkunD]))?$_IST['gl1.php']." Debit tidak terdaftar":((!is_array($_SISWAN) || array_sum($_SISWAN)<=0)?"Nominal masih kosong":""));
			//$errnAkunC=(!$iAkunC)?$_IST['gl1.php']." Kredit masih kosong":((!isset($_AKUN['CR'][$iAkunC]))?$_IST['gl1.php']." Kredit tidak terdaftar":(($iAkunC==$iAkunD)?$_IST['gl1.php']." Debit dan Kredit tidak boleh sama":""));
			$errtBatas=($statBatas && !$tBatas)?"Pembatasan Belum dipilih":"";
			$errtJenis=(!$tJenis)?"Jenis Penerimaan Belum dipilih":((!getValue("1","t_jns_bayar_siswa","id_jns_bayar_siswa='".$tJenis."' limit 1"))?"Jenis Penerimaaan tidak terdaftar":"");
			
			
			if(!$errtTanggal && !$errtNoBukti && !$errnAkunD && !$errnAkunC && !$errtBatas && !count($_errnSiswa)) {				
				if($tId) {
				
				}
				else {
					$tTanggal=balikTanggal($tTanggal);
					$dym=substr(str_replace("-","",$tTanggal),0,6);
					$tIdTrans=$dym.substr(((getValue("substring(no_trans,7,4)","t_glt","no_trans like '".$dym."%' order by no_trans desc limit 0,1")*1)+10001),1,4);
					
					queryDb("insert into t_glt(id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user)
														select '".MTRANS."','".$tTanggal."','".$tIdTrans."','".$tIdTrans."','".$tNoBukti."','".$tDesc."',g.id_unit1,g.id_unit2,g.id_unit3,g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,if(g.id_gl1='4' or g.id_gl1='5','".$tBatas."',''),g.id_aruskas,g.id_aruskas_sub,'".array_sum($_SISWAN)."',0,if(g1.db_cr='D',(saldo+".array_sum($_SISWAN)."),(saldo-".array_sum($_SISWAN).")),'".TANGGAL."','".$_SESSION['user']."'
																from t_glu g
																	inner join t_gl1 g1 on g1.id_gl1=g.id_gl1 
																where concat(g.id_unit1,'.',g.id_unit2,'.',g.id_unit3)='".$_SESSION['unit']."' and g.id_gl4='".$iAkunD."'");
					
					reset($_AKUNCN);							
					while(list($a,$b)=each($_AKUNCN)) {
						queryDb("insert into t_glt(id_mtrans,tgl_trans,no_trans,no_trans_ref,no_bukti,keterangan,id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_glx,id_aruskas,id_aruskas_sub,nominal_d,nominal_c,saldo,tanggal,id_user)
															select '".MTRANS."','".$tTanggal."','".$tIdTrans."','".$tIdTrans."','".$tNoBukti."','".$tDesc."',g.id_unit1,g.id_unit2,g.id_unit3,g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,if(g.id_gl1='4' or g.id_gl1='5','".$tBatas."',''),g.id_aruskas,g.id_aruskas_sub,0,'".$_AKUNCN[$a]."',if(g1.db_cr='C',(saldo+".$_AKUNCN[$a]."),(saldo-".$_AKUNCN[$a].")),'".TANGGAL."','".$_SESSION['user']."'
																	from t_glu g
																		inner join t_gl1 g1 on g1.id_gl1=g.id_gl1
															where concat(g.id_unit1,'.',g.id_unit2,'.',g.id_unit3)='".$_SESSION['unit']."' and g.id_gl4='".$a."'");
					}
					
					reset($_DATA);
					while(list($a,$b)=each($_DATA)) {
						$tId=((getValue("id_siswa_bayar","t_siswa_bayar","1=1 order by id_siswa_bayar desc limit 0,1")*1)+1);
						queryDb("insert into t_siswa_bayar(id_siswa_bayar,id_siswa_tagih,id_jns_bayar_siswa,id_siswa,nama_siswa,id_kelas,nominal_tagih,tgl_trans,no_trans,no_bukti,keterangan,uraian,nominal,id_gl4_db,id_gl4_cr,tanggal,id_user)
										values('".$tId."','".$a."','".$_DATA[$a]['id_jns_bayar']."','".$_DATA[$a]['id_siswa']."','".$_DATA[$a]['nama_siswa']."','".$_GT['id1']."','".$_DATA[$a]['nominal_tagih']."','".$tTanggal."','".$tIdTrans."','".$tNoBukti."','".$tDesc."','".(($_SISWAD[$a])?$_SISWAD[$a]:$tDesc)."','".$_SISWAN[$a]."','".$iAkunD."','".$_DATA[$a]['id_gl4_cr']."','".TANGGAL."','".$_SESSION['user']."')");
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

div#input table tr td.x ol li:nth-child(1) { width:238px; }
div#input table tr td.x ol li:nth-child(2) { width:127px; }
div#input table tr td.x ol li:nth-child(3) { width:198px; }
div#input table tr td.x ul li:nth-child(1) input[type=text] { width:230px; }
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
                <!--<tr>
                  <td><?=strtoupper($_IST['gl1.php'])?> (KREDIT)</td>
                  <td>:</td>
                  <td>
                      <input type="hidden" name="iAkunC" id="iAkunC" value="<?=htmlentities($iAkunC)?>" />
                      <input name="nAkunC" type="text" id="nAkunC" maxlength="200" value="<?=htmlentities($nAkunC)?>" autocomplete="off" onkeyup="goFramePopup(this,'AkunC','akun','C','<?=MTRANS?>','CR');hideFade('errnAkunC');" onfocus="framePopupFocus('AkunC')" onblur="framePopupBlur('AkunC')" required="required" />
                      <div class="framePopup"><img id="imgAkunC" src="../images/loader.gif" /><iframe id="fAkunC"></iframe></div>
                    <div id="errnAkunC" class="err"><?=$errnAkunC?></div>
                  </td>
                </tr>-->
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
                        /*$a=queryDb("select id_gl4,nama from t_gl4 where id_gl1='3' order by id_gl4");
						while($b=mysql_fetch_array($a)) {
							echo "<option value=\"".$b['id_gl4']."\" ".(($tBatas==$b['id_gl4'])?"selected='selected'":"").">[".$b['id_gl4']."] ".$b['nama']."</option>";
						}*/
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
                        /*$a=queryDb("select j.id_jns_bayar_siswa,j.nama from t_jns_bayar_siswa j
											inner join t_siswa_tagih b on b.id_jns_bayar_siswa=j.id_jns_bayar_siswa and b.id_kelas='".$_GT['id1']."' and b.no_trans2=''
										group by j.id_jns_bayar_siswa,j.nama
										order by j.id_jns_bayar_siswa asc");
						while($b=mysql_fetch_array($a)) {
							echo "<option value=\"".$b['id_jns_bayar_siswa']."\" ".(($tJenis==$b['id_jns_bayar_siswa'])?"selected='selected'":"").">".$b['nama']."</option>";
						}*/
						?>
                    </select>
                    <div id="errtJenis" class="err"><?=$errtJenis?></div>
                  </td>
                </tr>-->
                <tr>
                  <td colspan="3" class="x">
                  	<ol class="title" style="margin:8px 0 0 0;"><li><?=strtoupper($_IST['siswa.php'])?></li><li>NOMINAL</li><li>URAIAN</li></ol>
                    <div id="dSiswa0"></div><input type="hidden" name="tSiswaCount" id="tSiswaCount" value="0" /><button id="btn-add" class="icon-add" onclick="return addSiswa()">Tambah Penerimaan</button>                  
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
    	<a href="terima_siswa.php?edit=<?=bs64_e($_GT['id1'])."&order=".bs64_e($_GT['order1'])?>" class="on">PILIH <?=strtoupper($_IST['kelas.php'])?></a>
        <a href="#">JURNAL PENERIMAAN <?=strtoupper($_IST['siswa.php'])?></a>
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
	/*$sql="SELECT x.tgl_trans,x.no_trans,x.no_bukti,x.keterangan,x.nominal,min(m.nama) as jenis,if(x.tgl_trans>'".$lastPosting."',1,0) as stat_delete
					from (
						SELECT t.tgl_trans,t.no_trans,t.no_bukti,t.keterangan,sum(t.nominal_d) as nominal,t.tanggal
								FROM t_glt t
								where concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3)='".$_SESSION['unit']."' and t.no_trans_ref=t.no_trans and t.id_mtrans='".MTRANS."'
								group by t.tgl_trans,t.no_trans,t.no_bukti,t.keterangan,t.tanggal
						) x
					inner join t_siswa_bayar b on b.no_trans=x.no_trans and b.id_kelas='".$_GT['id1']."'
					inner join t_jns_bayar_siswa m on m.id_jns_bayar_siswa=b.id_jns_bayar_siswa
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")."
				group by x.tgl_trans,x.no_trans,x.no_bukti,x.keterangan,x.nominal
				order by ".(($_GT['order'])?$_GT['order'].",x.tanggal desc":"x.tanggal desc");*/
	$sql="select x.tgl_trans,x.no_trans,x.no_bukti,x.keterangan,x.nominal,x.stat_delete from (
							SELECT b.tgl_trans,b.no_trans,b.no_bukti,b.keterangan,sum(b.nominal) as nominal,if(b.tgl_trans>'".$lastPosting."',1,0) as stat_delete,min(b.tanggal) as tanggal
										from t_siswa_bayar b
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
			echo "<ul id=\"".$b['no_trans']."\" onclick=\"listFocus(this,'edit=1,del=".$b['stat_delete'].",search=1')\"><li>".$paging['start'].".</li><li>".tglIndo(balikTanggal($b['tgl_trans']),2)."</li><li>".$b['no_trans']."</li><li>".$b['no_bukti']."</li>
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
    	  <form action="" method="post" id="formTerima" enctype="multipart/form-data" style="display:inline-block;" onsubmit="showFade('load');">
          	<input name="fileTerima" id="fileTerima" type="file" style="display:none;" onchange="elm('formTerima').submit();" accept="application/vnd.ms-excel" />
            <button class="icon-attach" onclick="elm('fileTerima').click();return false;">Import XLS</button>
          </form>
    	  <button id="btn-new" class="icon-new" onclick="elm('tIdTrans').value='<?=NOFAK?>';setTanggal('<?=date("d/m/Y")?>');newData('tNoBukti,tId,tDesc');">TAMBAH</button>
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
	
	function addSiswa(v1,v2,v3,v4,err) {
		var e="Siswa";
		
		for(var i=0;i>=0;i++) {
			if(!elm("d"+e+i)) break;
		
			if(!elm("d"+e+i).innerHTML) {
				el=e+i;
				iNext=i+1;
				elm("d"+el).innerHTML="<ul><li>"+
										"<input type=\"hidden\" name=\"i"+el+"\" id=\"i"+el+"\" value=\""+((v1 && typeof v1[i]!='undefined')?v1[i]:"")+"\" />"+
										"<input type=\"text\" name=\"n"+el+"\" id=\"n"+el+"\" maxlength=\"200\" value=\""+((v2 && typeof v2[i]!='undefined')?v2[i]:"")+"\" autocomplete=\"off\" onkeyup=\"goFramePopup(this,'"+el+"','siswa_bayar','"+i+"','<?=$_GT['id1']?>');hideFade('errn"+el+"');\" onfocus=\"framePopupFocus('"+el+"')\" onblur=\"framePopupBlur('"+el+"')\" />&nbsp;"+
										"<input type=\"text\" class=\"n\" name=\"tNominal"+i+"\" id=\"tNominal"+i+"\" value=\""+((v3 && typeof v3[i]!='undefined')?v3[i]:"")+"\" maxlength=\"20\" onkeyup=\"valnominal(this)\" />&nbsp;"+
										"<input type=\"text\" name=\"tDesc"+i+"\" id=\"tDesc"+i+"\" value=\""+((v4 && typeof v4[i]!='undefined')?v4[i]:"")+"\" maxlength=\"200\" class=\"k\" onkeyup=\"hideFade('errn"+el+"');\" />"+
										"<div class=\"framePopup\"><img id=\"img"+el+"\" src=\"../images/loader.gif\" /><iframe id=\"f"+el+"\"></iframe></div><div id=\"errn"+el+"\" class=\"err\">"+((err && typeof err[i]!='undefined')?err[i]:"")+"</div>"+
										"</li></ul><div id=\"d"+e+iNext+"\"></div>";
				
				if(elm("t"+e+"Count").value<=iNext) {
					elm("t"+e+"Count").value=iNext;
					break;
				}
			}			
			
		}
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
		if($jsM) echo $jsM."elm('tSiswaCount').value=vc1.length;addSiswa(vc1,vc2,vc3,vc4,errm);";
		
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
