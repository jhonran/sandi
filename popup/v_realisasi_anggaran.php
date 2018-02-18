<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='realisasi_anggaran.php'",
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
		
		function showPersen($a,$b) {
			if($a==0) { return "?"; }
			else if($b==0) { return "0 %"; }
			else { return numbAcc(($b*100)/$a)." %"; }
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
if($_GT['jenis']=="bulan") {
	$wsql1="and month(u.tgl_trans)='".$_GT['bulan']."' and year(u.tgl_trans)='".$_GT['tahun']."'";
	$wsql2="and ((month(u.tgl_start)='".$_GT['bulan']."' and year(u.tgl_start)='".$_GT['tahun']."') or (month(u.tgl_finish)='".$_GT['bulan']."' and year(u.tgl_finish)='".$_GT['tahun']."'))";
	
	$titleHeader="Bulan ".$array_bulan[$_GT['bulan']-1]." ".$_GT['tahun'];
}
else if($_GT['jenis']=="tahun") {
	$wsql1="and year(u.tgl_trans)='".$_GT['tahun']."'";
	$wsql2="and (year(u.tgl_start)='".$_GT['tahun']."' or year(u.tgl_finish)='".$_GT['tahun']."')";
	
	$titleHeader="Tahun ".$_GT['tahun'];
}
else {
	$wsql1="and u.tgl_trans between '".balikTanggal($_GT['periode1'])." 00:00:00' and '".balikTanggal($_GT['periode2'])." 23:59:59'";
	$wsql2="and (u.tgl_start between '".balikTanggal($_GT['periode1'])." 00:00:00' and '".balikTanggal($_GT['periode2'])." 23:59:59' or u.tgl_finish between '".balikTanggal($_GT['periode1'])." 00:00:00' and '".balikTanggal($_GT['periode2'])." 23:59:59')";
	
	$titleHeader="Periode ".tglIndo(balikTanggal($_GT['periode1']))." s/d ".tglIndo(balikTanggal($_GT['periode2']));
}

$sql="select x.id_gl1,x.id_gl2,x.id_gl3,x.id_gl4,max(nm1) as nm1,g2.nama as nm2,g3.nama as nm3,g4.nama as nm4,sum(x.nilai_r) as nilai_r,sum(x.nilai) as nilai
		from (
			(select u.id_gl1,u.id_gl2,u.id_gl3,u.id_gl4,g1.nama as nm1,0 as nilai_r,sum((u.nominal_d-u.nominal_c)*if(g1.db_cr='D',1,-1)) as nilai from t_glt u
										inner join t_gl1 g1 on g1.id_gl1=u.id_gl1
									where concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) in ('".str_replace("#","','",$tUnit)."') ".$wsql1." and (g1.balance='LR' or (g1.balance='NR' and (u.id_gl3='102002' or u.id_gl2='105')))
									group by u.id_gl1,u.id_gl2,u.id_gl3,u.id_gl4)
			UNION
			(select g4.id_gl1,g4.id_gl2,g4.id_gl3,u.id_gl4,g1.nama as nm1,sum(nominal) as nilai_r,0 as nilai from t_danaanggaran u
										inner join t_gl4 g4 on g4.id_gl4=u.id_gl4
										inner join t_gl1 g1 on g1.id_gl1=g4.id_gl1
									where concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) in ('".str_replace("#","','",$tUnit)."') ".$wsql2." and (g1.balance='LR' or (g1.balance='NR' and (g4.id_gl3='102002' or g4.id_gl2='105')))
									group by g4.id_gl1,g4.id_gl2,g4.id_gl3,u.id_gl4)
		) x
			inner join t_gl2 g2 on g2.id_gl2=x.id_gl2
			inner join t_gl3 g3 on g3.id_gl3=x.id_gl3
			inner join t_gl4 g4 on g4.id_gl4=x.id_gl4
		group by x.id_gl1,x.id_gl2,x.id_gl3,x.id_gl4
		order by x.id_gl1,x.id_gl2,x.id_gl3,x.id_gl4";
		
$a=queryDb($sql);

while($b=mysql_fetch_array($a)) {
	$_G1[$b['id_gl1']]=$b['nm1'];
	$_G2[$b['id_gl1']][$b['id_gl2']]=$b['nm2'];
	$_G3[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]=$b['nm3'];
	$_G4[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']][$b['id_gl4']]=$b['nm4'];
	
	$_N1['a'][$b['id_gl1']]+=$b['nilai_r'];
	$_N1['d'][$b['id_gl1']]+=$b['nilai'];
	//$_N1['c'][$b['id_gl1']]=($b['nilai_r']>0 && $b['nilai']>0)?$b['nilai']*100/$b['nilai_r'];
	$_N2['a'][$b['id_gl1']][$b['id_gl2']]+=$b['nilai_r'];
	$_N2['d'][$b['id_gl1']][$b['id_gl2']]+=$b['nilai'];
	$_N3['a'][$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]+=$b['nilai_r'];
	$_N3['d'][$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]+=$b['nilai'];
	$_N4['a'][$b['id_gl1']][$b['id_gl2']][$b['id_gl3']][$b['id_gl4']]=$b['nilai_r'];
	$_N4['d'][$b['id_gl1']][$b['id_gl2']][$b['id_gl3']][$b['id_gl4']]=$b['nilai'];
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td class="c">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            <tr>
              <td class="c" style="background:url(../images/logo_laporan<?=(file_exists("../images/logo_laporan_".$tUnit.".png"))?("_".$tUnit):""?>.png) no-repeat left center;padding-bottom:2em;vertical-align:middle;">
                <strong class="fw">REALISASI ANGGARAN<br />
                <?=($tInstansi)?$tInstansi:INSTANSI?></strong>
                <!--<br />Jl Sariasih No. 54 Sarijadi Bandung 40151<br />Telp. (022) 2010491 Fax. (022) 2010491-->
                <br /><strong><?=$titleHeader?></strong>
              </td>
            </tr>
        </table>
    </td></tr>
    <tr>
    	<td>
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            	<tr><td class="c"><strong><?=strtoupper($_IST['gl1.php'])?></strong></td>
            	<td class="c"><strong>RKA</strong></td>
            	<td class="c"><strong>REALISASI</strong></td>
            	<td class="c"><strong>%</strong></td>
            	<td class="c"><strong>SISA</strong></td>
            	</tr>
			<?php
			if(is_array($_G1)) {
				reset($_G1);
				reset($_N1);
				while(list($a,$b)=each($_G1)) {
					echo "<tr><td class='bg'>".$a." &nbsp; ".$b."</td><td class='r bg'>".numbAcc($_N1['a'][$a])."</td><td class='r bg'>".numbAcc($_N1['d'][$a])."</td><td class='r bg'>".showPersen($_N1['a'][$a],$_N1['d'][$a])."</td><td class='r bg'>".numbAcc($_N1['a'][$a]-$_N1['d'][$a])."</td></tr>";
					reset($_G2);
					reset($_N2);
					while(list($aa,$bb)=each($_G2[$a])) {
						echo "<tr><td>".$aa." &nbsp; ".$bb."</td><td class='r'>".numbAcc($_N2['a'][$a][$aa])."</td><td class='r'>".numbAcc($_N2['d'][$a][$aa])."</td><td class='r'>".showPersen($_N2['a'][$a][$aa],$_N2['d'][$a][$aa])."</td><td class='r'>".numbAcc($_N2['a'][$a][$aa]-$_N2['d'][$a][$aa])."</td></tr>";
						if($_GT['level']>=3) {
							reset($_G3);
							reset($_N3);
							while(list($aaa,$bbb)=each($_G3[$a][$aa])) {
								echo "<tr><td class='bg'>".$aaa." &nbsp; ".$bbb."</td><td class='r bg'>".numbAcc($_N3['a'][$a][$aa][$aaa])."</td><td class='r bg'>".numbAcc($_N3['d'][$a][$aa][$aaa])."</td><td class='r bg'>".showPersen($_N3['a'][$a][$aa][$aaa],$_N3['d'][$a][$aa][$aaa])."</td><td class='r bg'>".numbAcc($_N3['a'][$a][$aa][$aaa]-$_N3['d'][$a][$aa][$aaa])."</td></tr>";
								if($_GT['level']>=4) {
									reset($_G4);
									reset($_N4);
									while(list($aaaa,$bbbb)=each($_G4[$a][$aa][$aaa])) {
										echo "<tr><td>".$aaaa." &nbsp; ".$bbbb."</td><td class='r'>".numbAcc($_N4['a'][$a][$aa][$aaa][$aaaa])."</td><td class='r'>".numbAcc($_N4['d'][$a][$aa][$aaa][$aaaa])."</td><td class='r'>".showPersen($_N4['a'][$a][$aa][$aaa][$aaaa],$_N4['d'][$a][$aa][$aaa][$aaaa])."</td><td class='r'>".numbAcc($_N4['a'][$a][$aa][$aaa][$aaaa]-$_N4['d'][$a][$aa][$aaa][$aaaa])."</td></tr>";
									}
								}
							}
						}				
					}
				}
				
				echo "<tr>
						<td class='r bg'><strong>TOTAL</td>
						<td class='r bg'><strong>".((is_array($_N1['a']))?numbAcc(array_sum($_N1['a'])):"0")."</strong></td>
						<td class='r bg'><strong>".((is_array($_N1['d']))?numbAcc(array_sum($_N1['d'])):"0")."</strong></td>
						<td class='r bg'>
							<strong>
								".showPersen(((is_array($_N1['a']))?array_sum($_N1['a']):"0")  ,  ((is_array($_N1['d']))?array_sum($_N1['d']):"0"))."
							</strong>
						</td>
						<td class='r bg'>
							<strong>
								".numbAcc(((is_array($_N1['a']))?array_sum($_N1['a']):"0")  -  ((is_array($_N1['d']))?array_sum($_N1['d']):"0"))."
							</strong>
						</td>
					</tr>";
			}	            
			?>
             </table>
       	  <br />&nbsp;
        </td>
    </tr>
    <tr>
		<td class="c">
            <?php
            $no=0;
            $a=queryDb("select keterangan,jabatan,pegawai from t_lap_tandatangan where url='x=7&".$_SERVER['QUERY_STRING']."' order by id");
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