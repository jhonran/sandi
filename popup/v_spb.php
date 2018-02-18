<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='kas_keluar.php'",
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
		
		function numbAcc($v) {
			return ($v<0)?"(".showRupiah2($v*-1).")":showRupiah2($v);
		}
		
		define("MTRANS","02");
		
		$tInstansi=getValue("nama","t_unit3","concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' limit 1");
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$titleHTML?></title>
<style type="text/css">
<?php if($_GET['x']) { ?>
@page {
	size: 11in 8.5in;
	margin:0.3in;
}

body>div { page-break-after:always; }
body>div:last-child { page-break-after:inherit; }

body,p,table { font-family:'Times New Roman', Times, serif;font-size:10pt;line-height:normal; }
table { border-right:1pt solid #000000;border-bottom:1pt solid #000000; }
td { border-left:1pt solid #000000;border-top:1pt solid #000000;padding:3pt 6pt; }
.fw { font-size:12pt; } 
.x1 { width:180pt; } 
.x2 { width:80pt; }
<?php } else { ?>
@media all {
	body,p,table { font-family:Tahoma;font-size:11px;line-height:normal; }
	table { border-right:1px solid #000000;border-bottom:1px solid #000000; }
	td { border-left:1px solid #000000;border-top:1px solid #000000;padding:3px 6px; }
	.fw { font-size:13px; }
	.x1 { width:500px; } 
	.x2 { width:200px; }
}

@media print {
	@page {
		size: 11in 8.5in;
		margin:0.3in;
	}
	
	body>div { page-break-after:always; }
	body>div:last-child { page-break-after:inherit; }
	
	body,p,table { font-family:'Times New Roman', Times, serif;font-size:9pt;line-height:normal; }
	table { border-right:1pt solid #000000;border-bottom:1pt solid #000000; }
	td { border-left:1pt solid #000000;border-top:1pt solid #000000;padding:3pt 6pt; }
	.fw { font-size:12pt; } 
	.x1 { width:180pt; } 
	.x2 { width:80pt; }
}
<?php } ?>
</style>
<?php
	$a=queryDb("SELECT t.tgl_trans,t.no_trans,t.no_bukti,t.keterangan,sum(t.nominal_d) as nominal
			FROM t_glt t
			where t.no_trans='".$_GT['id']."' and concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3)='".$_SESSION['unit']."' and t.no_trans_ref=t.no_trans and t.id_mtrans='".MTRANS."'
			group by t.tgl_trans,t.no_trans,t.no_bukti,t.keterangan");
			
	$b=mysql_fetch_array($a);
	
	$nominalText=terbilang($b['nominal'])." Rupiah";
		
	$text=implode("",file("v_spb_form.php"));
	
	$text=str_replace("[INSTANSI]",strtoupper(INSTANSI),$text);
	$text=str_replace("[LOGO]","background:url(../images/logo_laporan".((file_exists("../images/logo_laporan_".$_SESSION['unit'].".png"))?("_".$_SESSION['unit']):"").".png)",$text);
	$text=str_replace("[NPWP]",NPWP,$text);
	$text=str_replace("[PENERIMA]",(($tInstansi)?$tInstansi:INSTANSI),$text);
	$text=str_replace("[KOTA]",KOTA,$text);
	$text=str_replace("[NO]",$b['no_trans'],$text);
	$text=str_replace("[NILAI]",showRupiah2($b['nominal']),$text);
	$text=str_replace("[NILAI_TEXT1]",substr($nominalText,0,80),$text);
	$text=str_replace("[NILAI_TEXT2]",substr($nominalText,80,100),$text);
	$text=str_replace("[KETERANGAN]",$b['keterangan'],$text);
	$text=str_replace("[TANGGAL]",tglIndo($b['tgl_trans']),$text);
	
	$no=0;
	$a=queryDb("select keterangan,jabatan,pegawai from t_trans_tandatangan where no_trans='".$_GT['id']."' order by id");
	while($b=mysql_fetch_array($a)) {
		$no++;
		$_K[$no]=$b['keterangan'];
		$_J[$no]=$b['jabatan'];
		$_P[$no]=$b['pegawai'];
	}
	
	if($no==0) {
		$a=queryDb("select keterangan,jabatan,pegawai from t_tandatangan where concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' order by id_tandatangan");
		while($b=mysql_fetch_array($a)) {
			$no++;
			$_K[$no]=$b['keterangan'];
			$_J[$no]=$b['jabatan'];
			$_P[$no]=$b['pegawai'];
		}
	}
	
	if(is_array($_P)) {	
		$jmlKolom=floor(100/count($_P));
	
		for($i=1;$i<=$no;$i++) {
			$isi1.="<td width=\"".$jmlKolom."%\" class=\"c\">".$_K[$i]."</td>";
			$isi2.="<td style=\"height:60px;\" class=\"c\">".$_J[$i]."</td>";
			$isi3.="<td class=\"c\">".$_P[$i]."</td>";
		}
		
		$text=str_replace("[SIGNATURE]","<table width=\"100%\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\" class=\"nb\"><tr>".$isi1."</tr><tr>".$isi2."</tr><tr>".$isi3."</tr></table>",$text);
	}
	else $text=str_replace("[SIGNATURE]","",$text);
	
	echo $text;
?>
<script language="javascript">
<?php
	if(!$_GET['x']) echo "setTimeout(\"if(confirm('Apakah anda ingin mencetak Laporan ini')) window.print();\",300);";
?>
</script>
</body>
</html>
<?php } ?>