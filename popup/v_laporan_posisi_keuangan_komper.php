<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='laporan_posisi_keuangan.php'",
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


if(getValue("1","t_posting","tgl_finish='".balikTanggal($_GT['awal'])."' limit 1")) {
	$sql="select u.id_gl1,u.id_gl2,u.id_gl3,g1.nama as nm1,g2.nama as nm2,g3.nama as nm3,sum(saldo) as nilai from t_gluh u
							inner join t_gl1 g1 on g1.id_gl1=u.id_gl1
							inner join t_gl2 g2 on g2.id_gl2=u.id_gl2
							inner join t_gl3 g3 on g3.id_gl3=u.id_gl3
						where concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) in ('".str_replace("#","','",$tUnit)."') and g1.balance<>'LR' and u.tanggal='".balikTanggal($_GT['awal'])."'
						group by u.id_gl1,u.id_gl2,u.id_gl3,nm1,nm2,nm3
						order by u.id_gl1,u.id_gl2,u.id_gl3";
}
else {
	$sql="select u.id_gl1,u.id_gl2,u.id_gl3,g1.nama as nm1,g2.nama as nm2,g3.nama as nm3,sum((t.nominal_d-t.nominal_c)*if(g1.db_cr='D',1,-1)) as nilai from t_glu u
							inner join t_gl1 g1 on g1.id_gl1=u.id_gl1
							inner join t_gl2 g2 on g2.id_gl2=u.id_gl2
							inner join t_gl3 g3 on g3.id_gl3=u.id_gl3
							left join t_glt t on t.id_unit1=u.id_unit1 and t.id_unit2=u.id_unit2 and t.id_unit3=u.id_unit3 and (t.id_gl4=u.id_gl4 or t.id_glx=u.id_gl4) and tgl_trans<='".balikTanggal($_GT['awal'])."'
						where concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) in ('".str_replace("#","','",$tUnit)."') and g1.balance<>'LR'
						group by u.id_gl1,u.id_gl2,u.id_gl3,nm1,nm2,nm3
						order by u.id_gl1,u.id_gl2,u.id_gl3";
}

$a=queryDb($sql);

while($b=mysql_fetch_array($a)) {
	$_G1[$b['id_gl1']]=$b['nm1'];
	$_G2[$b['id_gl1']][$b['id_gl2']]=$b['nm2'];
	$_G3[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]=$b['nm3'];
	
	$_M1[$b['id_gl1']]+=$b['nilai'];
	$_M2[$b['id_gl1']][$b['id_gl2']]+=$b['nilai'];
	$_M3[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]=$b['nilai'];
}

if(getValue("1","t_posting","tgl_finish='".balikTanggal($_GT['periode'])."' limit 1")) {
	$sql="select u.id_gl1,u.id_gl2,u.id_gl3,g1.nama as nm1,g2.nama as nm2,g3.nama as nm3,sum(saldo) as nilai from t_gluh u
							inner join t_gl1 g1 on g1.id_gl1=u.id_gl1
							inner join t_gl2 g2 on g2.id_gl2=u.id_gl2
							inner join t_gl3 g3 on g3.id_gl3=u.id_gl3
						where concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) in ('".str_replace("#","','",$tUnit)."') and g1.balance<>'LR' and u.tanggal='".balikTanggal($_GT['periode'])."'
						group by u.id_gl1,u.id_gl2,u.id_gl3,nm1,nm2,nm3
						order by u.id_gl1,u.id_gl2,u.id_gl3";
	$valid="<div align='left'><i>Laporan Valid</i></div>";
}
else {
	$sql="select u.id_gl1,u.id_gl2,u.id_gl3,g1.nama as nm1,g2.nama as nm2,g3.nama as nm3,sum((t.nominal_d-t.nominal_c)*if(g1.db_cr='D',1,-1)) as nilai from t_glu u
							inner join t_gl1 g1 on g1.id_gl1=u.id_gl1
							inner join t_gl2 g2 on g2.id_gl2=u.id_gl2
							inner join t_gl3 g3 on g3.id_gl3=u.id_gl3
							left join t_glt t on t.id_unit1=u.id_unit1 and t.id_unit2=u.id_unit2 and t.id_unit3=u.id_unit3 and (t.id_gl4=u.id_gl4 or t.id_glx=u.id_gl4) and tgl_trans<='".balikTanggal($_GT['periode'])."'
						where concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) in ('".str_replace("#","','",$tUnit)."') and g1.balance<>'LR'
						group by u.id_gl1,u.id_gl2,u.id_gl3,nm1,nm2,nm3
						order by u.id_gl1,u.id_gl2,u.id_gl3";
}

$a=queryDb($sql);

while($b=mysql_fetch_array($a)) {
	$_G1[$b['id_gl1']]=$b['nm1'];
	$_G2[$b['id_gl1']][$b['id_gl2']]=$b['nm2'];
	$_G3[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]=$b['nm3'];
	
	$_N1[$b['id_gl1']]+=$b['nilai'];
	$_N2[$b['id_gl1']][$b['id_gl2']]+=$b['nilai'];
	$_N3[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]=$b['nilai'];
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td colspan="2" class="c">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            <tr>
              <td class="c" style="background:url(../images/logo_laporan<?=(file_exists("../images/logo_laporan_".$tUnit.".png"))?("_".$tUnit):""?>.png) no-repeat left center;padding-bottom:2em;vertical-align:middle;">
                <strong class="fw">LAPORAN POSISI KEUANGAN<br /><?=($tInstansi)?$tInstansi:INSTANSI?></strong>
                <!--<br />Jl Sariasih No. 54 Sarijadi Bandung 40151<br />Telp. (022) 2010491 Fax. (022) 2010491-->
                <br /><strong>per <?=tglIndo(balikTanggal($_GT['periode']))?> dan <?=tglIndo(balikTanggal($_GT['awal']))?></strong></td>
            </tr>
        </table>
    </td></tr>
    <tr>
    	<td style="width:50%">
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            	<tr>
                	<td>&nbsp;</td><td>&nbsp;</td>
                    <td class='c'><strong><?=tglIndo(balikTanggal($_GT['periode']),2)?></strong><br />&nbsp;</td>
                	<td class='c'><strong><?=tglIndo(balikTanggal($_GT['awal']),2)?></strong><br />&nbsp;</td>
                </tr>
            <?php
			if(is_array($_G2[1])) {
				reset($_G2);
				//reset($_N2);
				//reset($_M2);
				while(list($a,$b)=each($_G2[1])) {
					echo "<tr><td>&nbsp;<br /><strong>$b</strong></td><td>&nbsp;</td><td class='r'>&nbsp;<br /><strong>".numbAcc($_N2[1][$a])."</strong></td><td class='r'>&nbsp;<br /><strong>".numbAcc($_M2[1][$a])."</strong></td></tr>";
					reset($_G3);
					//reset($_N3);
					//reset($_M3);
					while(list($aa,$bb)=each($_G3[1][$a])) {
						echo "<tr><td>- &nbsp; $bb</td><td>&nbsp;</td><td class='r'>".numbAcc($_N3[1][$a][$aa])."</td><td class='r'>".numbAcc($_M3[1][$a][$aa])."</td></tr>";
					}
				}
				echo "<tr><td class='bg'><strong>Jumlah ".$_G1[1]."</strong></td><td class='bg'>&nbsp;</td><td class='r bg'><strong>".numbAcc($_N1[1])."</strong></td><td class='r bg'><strong>".numbAcc($_M1[1])."</strong></td></tr>";
			}	            
			?>
             </table><br />&nbsp;
        </td>
    	<td style="width:50%">
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            	<tr>
                	<td>&nbsp;</td><td>&nbsp;</td>
                    <td class='c'><strong><?=tglIndo(balikTanggal($_GT['periode']),2)?></strong><br />&nbsp;</td>
                	<td class='c'><strong><?=tglIndo(balikTanggal($_GT['awal']),2)?></strong><br />&nbsp;</td>
                </tr>
           	<?php
			if(is_array($_G2[2])) {
				reset($_G2);
				//reset($_N2);
				//reset($_M2);
				while(list($a,$b)=each($_G2[2])) {
					echo "<tr><td>&nbsp;<br /><strong>$b</strong></td><td>&nbsp;</td><td class='r'>&nbsp;<br /><strong>".numbAcc($_N2[2][$a])."</strong></td><td class='r'>&nbsp;<br /><strong>".numbAcc($_M2[2][$a])."</strong></td></tr>";
					reset($_G3);
					//reset($_N3);
					//reset($_M3);
					while(list($aa,$bb)=each($_G3[2][$a])) {
						echo "<tr><td>- &nbsp; $bb</td><td>&nbsp;</td><td class='r'>".numbAcc($_N3[2][$a][$aa])."</td><td class='r'>".numbAcc($_M3[2][$a][$aa])."</td></tr>";
					}
				}
				echo "<tr><td class='bg'><strong>Jumlah ".$_G1[2]."</strong></td><td class='bg'>&nbsp;</td><td class='r bg'><strong>".numbAcc($_N1[2])."</strong></td><td class='r bg'><strong>".numbAcc($_M1[2])."</strong></td></tr>";
			}	            
			?>
            	<tr><td colspan="3">&nbsp;</td></tr>
           	<?php
			if(is_array($_G2[3])) {
				reset($_G2);
				//reset($_N2);
				//reset($_M2);
				while(list($a,$b)=each($_G2[3])) {
					echo "<tr><td>&nbsp;<br /><strong>$b</strong></td><td>&nbsp;</td><td class='r'>&nbsp;<br /><strong>".numbAcc($_N2[3][$a])."</strong></td><td class='r'>&nbsp;<br /><strong>".numbAcc($_M2[3][$a])."</strong></td></tr>";
					reset($_G3);
					//reset($_N3);
					//reset($_M3);
					while(list($aa,$bb)=each($_G3[3][$a])) {
						echo "<tr><td>- &nbsp; $bb</td><td>&nbsp;</td><td class='r'>".numbAcc($_N3[3][$a][$aa])."</td><td class='r'>".numbAcc($_M3[3][$a][$aa])."</td></tr>";
					}
				}
				echo "<tr><td class='bg'><strong>Jumlah ".$_G1[3]."</strong></td><td class='bg'>&nbsp;</td><td class='r bg'><strong>".numbAcc($_N1[3])."</strong></td><td class='r bg'><strong>".numbAcc($_M1[3])."</strong></td></tr>";
			}	            
			?>
             </table><br />&nbsp;
        </td>
    </tr>
    <tr>
    	<td>
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            	<tr><td class='bg'><strong>Total <?=$_G1[1]?></strong></td><td class='bg'>&nbsp;</td><td class='r bg'><strong><?=numbAcc($_N1[1])?></strong></td><td class='r bg'><strong><?=numbAcc($_M1[1])?></strong></td></tr>
            </table>
        </td>
    	<td>
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            	<tr><td class='bg'><strong>Total <?=$_G1[2]?> & <?=$_G1[3]?></strong></td><td class='bg'>&nbsp;</td><td class='r bg'><strong><?=numbAcc($_N1[2]+$_N1[3])?></strong></td><td class='r bg'><strong><?=numbAcc($_M1[2]+$_M1[3])?></strong></td></tr>
            </table>
        </td>
    </tr>
	<tr>
		<td class="c" colspan="2">
            <?php
            $no=0;
            $a=queryDb("select keterangan,jabatan,pegawai from t_lap_tandatangan where url='x=4&".$_SERVER['QUERY_STRING']."' order by id");
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