<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='laporan_aktifitas.php'",
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
	size: 17in 11in;
	margin:0.3in;
}

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
$idAset=3;

$sql="select if(g1.id_gl1=".$idAset.",t.id_gl4,t.id_glx) as id_gls,t.id_gl1,sum((t.nominal_d-t.nominal_c)*if(g1.db_cr='D',1,-1)) as nilai from t_glt t
							inner join t_gl1 g1 on g1.id_gl1=t.id_gl1 and (g1.balance='LR' or g1.id_gl1=".$idAset.")
						where concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3) in ('".str_replace("#","','",$tUnit)."') and t.tgl_trans<'".balikTanggal($_GT['periode1'])." 00:00:00'
						group by id_gls,t.id_gl1,g1.db_cr
						order by id_gls,t.id_gl1";
$a=queryDb($sql);

while($b=mysql_fetch_array($a)) {
	if($b['id_gl1']==$idAset) {
		$b['id_gl1']=($b['nilai']<0)?5:4;
		$b['nilai']=abs($b['nilai']);
	}
	
	$_A1[$b['id_gls']][$b['id_gl1']]+=$b['nilai'];
	$_ZA1[$b['id_gl1']]+=$b['nilai'];
}

//=========================================

/*$sql="select ifnull(t.id_glx,t.id_gl4) as id_glx,u.id_gl1,u.id_gl2,u.id_gl3,u.id_gl4,g1.nama as nm1,g2.nama as nm2,g3.nama as nm3,u.nama as nm4,sum((t.nominal_d-t.nominal_c)*if(g1.db_cr='D',1,-1)) as nilai from t_glu u
							inner join t_gl1 g1 on g1.id_gl1=u.id_gl1
							inner join t_gl2 g2 on g2.id_gl2=u.id_gl2
							inner join t_gl3 g3 on g3.id_gl3=u.id_gl3
							left join t_glt t on concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3)=concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) and (t.id_gl4=u.id_gl4 or t.id_glx=u.id_gl4) and t.tgl_trans between '".balikTanggal($_GT['periode1'])." 00:00:00' and '".balikTanggal($_GT['periode2'])." 23:59:59'
						where concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) in ('".str_replace("#","','",$tUnit)."') and (g1.balance='LR')
						group by t.id_glx,u.id_gl1,u.id_gl2,u.id_gl3,u.id_gl4,nm1,nm2,nm3,nm4,g1.db_cr
						order by u.id_gl1,u.id_gl2,u.id_gl3,u.id_gl4,t.id_glx";*/
						
$sql="select id_glx,if(id_gl1=3,if((jumlah*perkalian)<0,5,4),id_gl1) as id_gl1,id_gl2,id_gl3,id_gl4,nm1,nm2,nm3,nm4,if(id_gl1=3,abs(jumlah*perkalian),(jumlah*perkalian)) as nilai from (
					select id_glx,id_gl1,id_gl2,id_gl3,id_gl4,max(nm1) as nm1,max(nm2) as nm2,max(nm3) as nm3,max(nm4) as nm4,sum(jumlah) as jumlah,sum(perkalian) as perkalian from (
								(select g4.id_gl4 as id_glx,u.id_gl1,u.id_gl2,u.id_gl3,u.id_gl4,g1.nama as nm1,g2.nama as nm2,g3.nama as nm3,u.nama as nm4,0 as jumlah,if(g1.db_cr='D',1,-1) as perkalian from t_glu u
											inner join t_gl1 g1 on g1.id_gl1=u.id_gl1
											inner join t_gl2 g2 on g2.id_gl2=u.id_gl2
											inner join t_gl3 g3 on g3.id_gl3=u.id_gl3
											inner join t_gl4 g4 on g4.id_gl1='3'
										where concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) in ('".str_replace("#","','",$tUnit)."') and (g1.balance='LR' or u.id_gl1='".$idAset."')
										group by id_glx,u.id_gl1,u.id_gl2,u.id_gl3,u.id_gl4,nm1,nm2,nm3,nm4,perkalian)
								union
								(select if(t.id_glx='',t.id_gl4,t.id_glx) as id_glx,t.id_gl1,t.id_gl2,t.id_gl3,t.id_gl4,'' as nm1,'' as nm2,'' as nm3,'' as nm4,sum(t.nominal_d-t.nominal_c) as jumlah,0 as perkalian from t_glt t 
										where concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3) in ('".str_replace("#","','",$tUnit)."') and (t.id_glx<>'' or t.id_gl1='".$idAset."') and t.tgl_trans between '".balikTanggal($_GT['periode1'])." 00:00:00' and '".balikTanggal($_GT['periode2'])." 23:59:59'
										group by t.id_glx,t.id_gl1,t.id_gl2,t.id_gl3,t.id_gl4)
							) x
							group by id_glx,id_gl1,id_gl2,id_gl3,id_gl4
			) y
			where jumlah<>0
			order by id_gl1,id_gl2,id_gl3,id_gl4,id_glx";
			
$a=queryDb($sql);

while($b=mysql_fetch_array($a)) {
	$_TMP[$b['id_glx']]=$b['id_glx'];
	
	$_G1[$b['id_gl1']]=$b['nm1'];
	$_G2[$b['id_gl1']][$b['id_gl2']]=$b['nm2'];
	$_G3[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]=$b['nm3'];
	$_G4[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']][$b['id_gl4']]=$b['nm4'];
	
	$_N1[$b['id_glx']][$b['id_gl1']]+=$b['nilai'];
	$_N2[$b['id_glx']][$b['id_gl1']][$b['id_gl2']]+=$b['nilai'];
	$_N3[$b['id_glx']][$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]+=$b['nilai'];
	$_N4[$b['id_glx']][$b['id_gl1']][$b['id_gl2']][$b['id_gl3']][$b['id_gl4']]+=$b['nilai'];
	
	$_ZN1[$b['id_gl1']]+=$b['nilai'];
	$_ZN2[$b['id_gl1']][$b['id_gl2']]+=$b['nilai'];
	$_ZN3[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']]+=$b['nilai'];
	$_ZN4[$b['id_gl1']][$b['id_gl2']][$b['id_gl3']][$b['id_gl4']]+=$b['nilai'];
}

unset($_TMP['']);	

$a=queryDb("select u.id_gl4,u.nama from t_glu u where u.id_gl1='3' group by u.id_gl4 order by u.id_gl4");
while($b=mysql_fetch_array($a)) {
	$_TMP[$b['id_gl4']]=$b['nama'];
}
ksort($_TMP);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td class="c">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            <tr>
              <td class="c" style="background:url(../images/logo_laporan<?=(file_exists("../images/logo_laporan_".$tUnit.".png"))?("_".$tUnit):""?>.png) no-repeat left center;padding-bottom:2em;vertical-align:middle;">
                <strong class="fw">LAPORAN AKTIVITAS<br /><?=($tInstansi)?$tInstansi:INSTANSI?></strong>
                <!--<br />Jl Sariasih No. 54 Sarijadi Bandung 40151<br />Telp. (022) 2010491 Fax. (022) 2010491-->
                <br /><strong>Periode <?=tglIndo(balikTanggal($_GT['periode1']),2)?> - <?=tglIndo(balikTanggal($_GT['periode2']),2)?></strong>
              </td>
            </tr>
        </table>
    </td></tr>
    <tr>
    	<td>
        	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">            	
           	<?php
				if(is_array($_TMP)) {
					echo "<tr>
								<td>&nbsp;</td>
								<td class='bg c' colspan='".(count($_TMP)+1)."'><strong>".tglIndo(balikTanggal($_GT['periode1']),2)." s/d ".tglIndo(balikTanggal($_GT['periode2']),2)."</strong></td>
								<td>&nbsp;</td>
							</tr>";
					
					
					echo "<tr><td>&nbsp;</td>";
					reset($_TMP);
					while(list($a,$b)=each($_TMP)) {
						echo "<td class='c'><strong>".$b."</strong></td>";
					}
					echo "<td class='c'><strong>TOTAL</strong></td><td>&nbsp;</td>";					
					
					if(is_array($_G1)) {
						reset($_G1);
						//reset($_N1);
						//reset($_M1);
						while(list($a,$b)=each($_G1)) {
							echo "<tr><td>&nbsp;<br /><strong>".$b."</strong></td>";
							reset($_TMP);							
							while(list($x,$y)=each($_TMP)) {
								echo "<td class='r'>&nbsp;<br /><strong>".numbAcc($_N1[$x][$a])."</strong></td>";
							}
							echo "<td class='r'>&nbsp;<br /><strong>".numbAcc($_ZN1[$a])."</strong></td><td>&nbsp;</td>";
							
							reset($_G2);
							//reset($_N2);
							//reset($_M2);
							while(list($aa,$bb)=each($_G2[$a])) {
								/*echo "<tr><td> &nbsp; ".$bb."</td>";
								reset($_TMP);							
								while(list($x,$y)=each($_TMP)) {
									echo "<td class='r'>".numbAcc($_N2[$x][$a][$aa])."</td>";
								}
								echo "<td class='r'>".numbAcc($_ZN2[$a][$aa])."</td><td>&nbsp;</td>";*/
								
								reset($_G3);
								//reset($_N3);
								//reset($_M3);
								while(list($aaa,$bbb)=each($_G3[$a][$aa])) {
									echo "<tr><td> - &nbsp; ".$bbb."</td>";
									reset($_TMP);							
									while(list($x,$y)=each($_TMP)) {
										echo "<td class='r'>".numbAcc($_N3[$x][$a][$aa][$aaa])."</td>";
									}
									echo "<td class='r'>".numbAcc($_ZN3[$a][$aa][$aaa])."</td><td>&nbsp;</td>";
									
									reset($_G4);
									//reset($_N4);
									//reset($_M4);
									while(list($aaaa,$bbbb)=each($_G4[$a][$aa][$aaa])) {
										/*echo "<tr><td> &nbsp; &nbsp; - &nbsp; ".$bbbb."</td>";
										reset($_TMP);							
										while(list($x,$y)=each($_TMP)) {
											echo "<td class='r'>".numbAcc($_N4[$x][$a][$aa][$aaa][$aaaa])."</td>";
										}
										echo "<td class='r'>".numbAcc($_ZN4[$a][$aa][$aaa][$aaaa])."</td><td>&nbsp;</td>";*/
										
									}
									
								}
							}
							
						}
					}  
					
					echo "<tr><td colspan='".((count($_TMP)*1)+3)."'>&nbsp;</td></tr>";
					echo "<tr><td class='bg'><strong>TOTAL ASET BERSIH :</strong></td>";
					reset($_TMP);							
					while(list($a,$b)=each($_TMP)) {
						echo "<td class='bg r'><strong>".numbAcc($_N1[$a][4]-$_N1[$a][5])."</strong></td>";
					}
					echo "<td class='bg r'><strong>".numbAcc($_ZN1[4]-$_ZN1[5])."</strong></td><td>&nbsp;</td>";
					
					//=====================================================================
					
					echo "<tr><td colspan='".((count($_TMP)*1)+3)."'>&nbsp;</td></tr>";
					echo "<tr><td>Perubahan Aset Bersih :</td>";
					reset($_TMP);							
					while(list($a,$b)=each($_TMP)) {
						echo "<td class='r'>".numbAcc($_N1[$a][4]-$_N1[$a][5])."</td>";
					}
					echo "<td class='r'>".numbAcc($_ZN1[4]-$_ZN1[5])."</td><td>&nbsp;</td>";
					//----------------------------------------------------------------------------
					echo "<tr><td>Aset Bersih Awal Periode :</td>";
					reset($_TMP);							
					while(list($a,$b)=each($_TMP)) {
						echo "<td class='r'>".numbAcc($_A1[$a][4]-$_A1[$a][5])."</td>";
					}
					echo "<td class='r'>".numbAcc($_ZA1[4]-$_ZA1[5])."</td><td>&nbsp;</td>";				
					//----------------------------------------------------------------------------
					echo "<tr><td class='bg'><strong>Aset Bersih Akhir Periode :</strong></td>";
					reset($_TMP);							
					while(list($a,$b)=each($_TMP)) {
						echo "<td class='bg r'><strong>".numbAcc(($_N1[$a][4]-$_N1[$a][5])+($_A1[$a][4]-$_A1[$a][5]))."</strong></td>";
					}
					echo "<td class='bg r'><strong>".numbAcc(($_ZN1[4]-$_ZN1[5])+($_ZA1[4]-$_ZA1[5]))."</strong></td><td>&nbsp;</td>";
				}	     
			?>
             </table><br />&nbsp;
        </td>
    </tr>
    <tr>
		<td class="c">
            <?php
            $no=0;
            $a=queryDb("select keterangan,jabatan,pegawai from t_lap_tandatangan where url='x=5&".$_SERVER['QUERY_STRING']."' order by id");
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