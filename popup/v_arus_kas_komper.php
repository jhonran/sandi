<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='arus_kas.php'",
										"u.`id_user`='".$_SESSION['user']."'");

	$tUnit=getValue("group_concat(distinct concat(e.id_unit1,'.',e.id_unit2,'.',e.id_unit3) order by e.id_unit1,e.id_unit2,e.id_unit3 separator '#')","t_user s 
						inner join t_entitas_unit e on e.id_entitas=s.id_entitas and concat(e.id_unit1,'.',e.id_unit2,'.',e.id_unit3) in ('".str_replace("#","','",$_GT['unit'])."')","
							s.id_user='".$_SESSION['user']."'");
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;	
		
		function numbAcc($v) {
			return ($v<0)?"(".showRupiah2($v*-1).")":showRupiah2($v);
		}
		
		$tInstansi=getValue("nama","t_unit3","concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$tUnit."' limit 1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$titleHTML?></title>
<style type="text/css">
<?php if($_GET['x']) { ?>
@page {
	size: 8.5in 11in;
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
	.fw { font-size:16px; }
	.x1 { width:500px; } 
	.x2 { width:200px; }
}

@media print {
	@page {
		size: 8.5in 11in;
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

table.non { border:none; }
td { text-align:left;vertical-align:top; }
td.non { border:none; }
td.bg { background-color:#eeeeee; }
p { margin:0;}
.c { text-align:center; }
.r { text-align:right; }

table.nb { border:none; }
table.nb td { border:none; }

ol, li { margin:0;padding:0; }
ol { padding-left:15px; }
</style>
</head>

<body>
<?php
$sql="select s.id_aruskas,s.id_aruskas_sub,a.nama,s.nama as snama from t_aruskas_sub s
				inner join t_aruskas a on a.id_aruskas=s.id_aruskas
			where s.id_aruskas<>'00'
			order by s.id_aruskas,s.id_aruskas_sub";

$a=queryDb($sql);

while($b=mysql_fetch_array($a)) {
	$_A[$b['id_aruskas']]=$b['nama'];		
	$_A2[$b['id_aruskas']][$b['id_aruskas_sub']]=$b['snama'];	
}

$_A['100']=$_IST['gl1.php']." yang tidak terklasifikasi";		
$_A2['100']['100']=$_IST['gl1.php']." yang tidak terklasifikasi";
$_N=array();
$_M=array();

$_G=array();
$a=queryDb("select id_gl4,nama from t_gl4");
while($b=mysql_fetch_array($a)) {
	$_G[$b['id_gl4']]=$b['nama'];	
}

$saldoAwal=getValue("sum(nominal_d-nominal_c)","t_glt","id_aruskas='00' and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".str_replace("#","','",$tUnit)."') and tgl_trans<'".balikTanggal($_GT['awal'])." 00:00:00'");

$awal2=dateInterval(balikTanggal($_GT['periode1']),0,0,-1,0,0,0,"Y-m-d");

$noTemp="";
$statTemp=0;
$nilaiTemp=0;
$sql="select y.no_trans,if(y.id_aruskas='','100',y.id_aruskas) as id_aruskas,if(y.id_aruskas_sub='','100',y.id_aruskas_sub) as id_aruskas_sub,y.id_gl4,y.nilai,if(y.nilai>0,'1','-1') as stat from (
					select no_trans,id_aruskas,id_aruskas_sub,id_gl4,sum(n) as nilai from (
								select t.no_trans,if(t.id_aruskas='00','',t.id_aruskas) as id_aruskas,if(t.id_aruskas='00','',t.id_aruskas_sub) as id_aruskas_sub,if(t.id_aruskas='00','',t.id_gl4) as id_gl4,(t.nominal_c-t.nominal_d) as n from t_glt t
										where t.no_trans in (select no_trans from t_glt where id_aruskas='00' and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".str_replace("#","','",$tUnit)."') 
											and tgl_trans between '".balikTanggal($_GT['awal'])." 00:00:00' and '".$awal2." 23:59:59')
										order by t.tgl_trans,t.no_trans,t.id_gl4 
							) x
						group by no_trans,id_aruskas,id_aruskas_sub,id_gl4
				) y
			where y.nilai<>'0' order by y.no_trans,y.id_gl4";
		
$a=queryDb($sql);

while($b=mysql_fetch_array($a)) {
	if($noTemp!=$b['no_trans']) {
		if($b['id_gl4']=="") {
			$noTemp=$b['no_trans'];
			$statTemp=$b['stat'];
			$nilaiTemp=$b['nilai'];
		}
	}
	else if($statTemp!=$b['stat'] && $nilaiTemp!=0) {
		$nilaiX=(($nilaiTemp*$statTemp)>($b['nilai']*$b['stat']))?$b['nilai']:($nilaiTemp*-1);
		$nilaiTemp+=$nilaiX;
		
		$_M[$b['id_aruskas']]+=$nilaiX;	
		$_M2[$b['id_aruskas']][$b['id_aruskas_sub']]+=$nilaiX;
		$_M3[$b['id_aruskas']][$b['id_aruskas_sub']][$b['id_gl4']]+=$nilaiX;
	}
}
//=======================================================================================================================
$noTemp="";
$statTemp=0;
$nilaiTemp=0;
$sql="select y.no_trans,if(y.id_aruskas='','100',y.id_aruskas) as id_aruskas,if(y.id_aruskas_sub='','100',y.id_aruskas_sub) as id_aruskas_sub,y.id_gl4,y.nilai,if(y.nilai>0,'1','-1') as stat from (
					select no_trans,id_aruskas,id_aruskas_sub,id_gl4,sum(n) as nilai from (
								select t.no_trans,if(t.id_aruskas='00','',t.id_aruskas) as id_aruskas,if(t.id_aruskas='00','',t.id_aruskas_sub) as id_aruskas_sub,if(t.id_aruskas='00','',t.id_gl4) as id_gl4,(t.nominal_c-t.nominal_d) as n from t_glt t
										where t.no_trans in (select no_trans from t_glt where id_aruskas='00' and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".str_replace("#","','",$tUnit)."') 
											and tgl_trans between '".balikTanggal($_GT['periode1'])." 00:00:00' and '".balikTanggal($_GT['periode2'])." 23:59:59')
										order by t.tgl_trans,t.no_trans,t.id_gl4 
							) x
						group by no_trans,id_aruskas,id_aruskas_sub,id_gl4
				) y
			where y.nilai<>'0' order by y.no_trans,y.id_gl4";
		
$a=queryDb($sql);

while($b=mysql_fetch_array($a)) {
	if($noTemp!=$b['no_trans']) {
		if($b['id_gl4']=="") {
			$noTemp=$b['no_trans'];
			$statTemp=$b['stat'];
			$nilaiTemp=$b['nilai'];
		}
	}
	else if($statTemp!=$b['stat'] && $nilaiTemp!=0) {
		$nilaiX=(($nilaiTemp*$statTemp)>($b['nilai']*$b['stat']))?$b['nilai']:($nilaiTemp*-1);
		$nilaiTemp+=$nilaiX;
		
		$_N[$b['id_aruskas']]+=$nilaiX;	
		$_N2[$b['id_aruskas']][$b['id_aruskas_sub']]+=$nilaiX;
		$_N3[$b['id_aruskas']][$b['id_aruskas_sub']][$b['id_gl4']]+=$nilaiX;
	}
}

ksort($_N3);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr><td class="c">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            <tr>
              <td class="c" style="background:url(../images/logo_laporan<?=(file_exists("../images/logo_laporan_".$tUnit.".png"))?("_".$tUnit):""?>.png) no-repeat left center;padding-bottom:2em;vertical-align:middle;">
                <strong class="fw">LAPORAN ARUS KAS<br /><?=($tInstansi)?$tInstansi:INSTANSI?></strong>
                <!--<br />Jl Sariasih No. 54 Sarijadi Bandung 40151<br />Telp. (022) 2010491 Fax. (022) 2010491-->
                <br /><strong>Periode <?=tglIndo(balikTanggal($_GT['periode1']),2)?> - <?=tglIndo(balikTanggal($_GT['periode2']),2)?> dan <?=tglIndo(balikTanggal($_GT['awal']),2)?> - <?=tglIndo($awal2,2)?></strong>
              </td>
            </tr>
        </table>
    </td></tr>
    <tr>
    	<td>&nbsp;<br />
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            	<tr>
                	<td>&nbsp;</td><td>&nbsp;</td>
                	<td class='c'><strong><?=tglIndo(balikTanggal($_GT['periode1']),2)?> s/d<br /><?=tglIndo(balikTanggal($_GT['periode2']),2)?></strong><br />&nbsp;</td>
                	<td class='c'><strong><?=tglIndo(balikTanggal($_GT['awal']),2)?> s/d<br /><?=tglIndo($awal2,2)?></strong><br />&nbsp;</td>
                </tr>
           	<?php
			if(is_array($_N3)) {
				reset($_N3);
				while(list($a,$b)=each($_N3)) {
					echo "<tr><td>&nbsp;<br /><strong>".$_A[$a]."</strong></td><td>&nbsp;</td><td class='r'>&nbsp;<br /><strong>".numbAcc($_N[$a])."</strong></td><td class='r'>&nbsp;<br /><strong>".numbAcc($_M[$a])."</strong></td></tr>";
					
					ksort($b);
					while(list($aa,$bb)=each($b)) {
						echo "<tr><td>- &nbsp; ".$_A2[$a][$aa]."</td><td>&nbsp;</td><td class='r'>".numbAcc($_N2[$a][$aa])."</td><td class='r'>".numbAcc($_M2[$a][$aa])."</td></tr>";
					}
				}
			}	 
            ?>
            	<tr><td colspan="4">&nbsp;</td></tr>
            	<tr>
                	<td class='bg'><strong>Total Arus Kas</strong></td><td class='bg'>&nbsp;</td>
                	<td class='r bg'><strong><?=numbAcc(array_sum($_N))?></strong></td>
                    <td class='r bg'><strong><?=numbAcc(array_sum($_M))?></strong></td>
            	</tr>
            	<tr><td colspan="4">&nbsp;</td></tr>
            	<tr>
                	<td>Kenaikan (Penurun) Kas</td><td>&nbsp;</td>
                	<td class='r'><?=numbAcc(array_sum($_N))?></td>
                    <td class='r'><?=numbAcc(array_sum($_M))?></td>
            	</tr>
            	<tr>
                	<td>Kas pada Awal Periode</td><td>&nbsp;</td>
                	<td class='r'><?=numbAcc(array_sum($_M)+$saldoAwal)?></td>
                    <td class='r'><?=numbAcc($saldoAwal)?></td>
            	</tr>
            	<tr>
                	<td class='bg'><strong>Kas pada Akhir Periode</strong></td><td class='bg'>&nbsp;</td>
                	<td class='r bg'><strong><?=numbAcc(array_sum($_N)+array_sum($_M)+$saldoAwal)?></strong></td>
                    <td class='r bg'><strong><?=numbAcc(array_sum($_M)+$saldoAwal)?></strong></td>
            	</tr>
             </table><br />&nbsp;
        </td>
    </tr>
    <tr>
		<td class="c">
            <?php
            $no=0;
            $a=queryDb("select keterangan,jabatan,pegawai from t_lap_tandatangan where url='x=6&".$_SERVER['QUERY_STRING']."' order by id");
            while($b=mysql_fetch_array($a)) {
                $no++;
                $_K[$no]=$b['keterangan'];
                $_J[$no]=$b['jabatan'];
                $_P[$no]=$b['pegawai'];
            }
            
            if($no==0) {
				$unit=getValue("concat(id_unit1,'.',id_unit2,'.',id_unit3) as unit","t_unit3","concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".$_GT['unit']."','01.01.01') order by unit desc limit 1");
				
                $a=queryDb("select keterangan,jabatan,pegawai from t_tandatangan where concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$unit."' order by id_tandatangan");
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
                
                echo "<table width=\"100%\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\" class=\"nb\"><tr>".$isi1."</tr><tr>".$isi2."</tr><tr>".$isi3."</tr></table>";
            }
            ?>
        	</td>
        </tr>
</table>
<script language="javascript">
<?php
	if(!$_GET['x']) echo "setTimeout(\"if(confirm('Apakah anda ingin mencetak Laporan ini')) window.print();\",300);";
?>
</script>
</body>
</html>
<?php } ?>