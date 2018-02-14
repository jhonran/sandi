<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='jurnal_transaksi.php'",
										"u.`id_user`='".$_SESSION['user']."'");

	$titleHTML="...";
	
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
		
		
		$_UNITS=array();
		
		if(!$_SESSION['unit']) {										
			$a=queryDb("select concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) as id_unit,u.nama from t_user s 
								inner join t_entitas_unit e on e.id_entitas=s.id_entitas
								inner join t_unit3 u on u.id_unit1=e.id_unit1 and u.id_unit2=e.id_unit2 and u.id_unit3=e.id_unit3
							where s.id_user='".$_SESSION['user']."'");
			
			while($b=mysql_fetch_array($a)) {
				$_UNITS[$b['id_unit']]=$b['nama'];
			}
		}
		else {
			$tInstansi=getValue("nama","t_unit3","concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' limit 1");	
			$_UNITS[$_SESSION['unit']]=$tInstansi;
		}
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
$_GLX=array();
$a=queryDb("select id_gl4,nama from t_glu where concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."') and id_gl1='3'");
while($b=mysql_fetch_assoc($a)) {
	$_GLX[$b['id_gl4']]=$b['nama'];
}

$sql="SELECT t.tgl_trans,t.no_trans,t.keterangan,t.id_gl4,t.id_glx,ifnull(u.nama,'') as nama_gl,t.nominal_d,t.nominal_c,if(nominal_d>nominal_c,1,0) as sort_nominal
			FROM t_glt t
				left join t_glu u on u.id_unit1=t.id_unit1 and u.id_unit2=t.id_unit2 and u.id_unit3=t.id_unit3 and u.id_gl4=t.id_gl4
			where concat(t.id_unit1,'.',t.id_unit2,'.',t.id_unit3) in ('".implode("','",array_keys($_UNITS))."') and t.no_trans='".$_GT['id']."' and t.no_trans_ref=t.no_trans
			order by t.tgl_trans,t.no_trans,sort_nominal desc,t.id_gl4";

$a=queryDb($sql);
while($b=mysql_fetch_array($a)) {
	$_D[$b['no_trans']]['tgl_trans']=$b['tgl_trans'];
	$_D[$b['no_trans']]['keterangan']=$b['keterangan'];
	$_D[$b['no_trans']]['id_glx']=$_D[$b['no_trans']]['id_glx']?$_D[$b['no_trans']]['id_glx']:$b['id_glx'];
	$_D[$b['no_trans']]['id_gl4'][$b['id_gl4']]="[".$b['id_gl4']."] ".$b['nama_gl'];
	$_D[$b['no_trans']]['nominal_d'][$b['id_gl4']]=$b['nominal_d'];
	$_D[$b['no_trans']]['nominal_c'][$b['id_gl4']]=$b['nominal_c'];
	$ds+=$b['nominal_d'];
	$cs+=$b['nominal_c'];
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td class="c">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            <tr>
              <td class="c" style="background:url(../images/logo_laporan<?=(file_exists("../images/logo_laporan_".$tUnit.".png"))?("_".$tUnit):""?>.png) no-repeat left center;padding-bottom:2em;vertical-align:middle;">
                <strong class="fw">JURNAL TRANSAKSI<br /><?=($tInstansi)?$tInstansi:INSTANSI?></strong>
                <!--<br />Jl Sariasih No. 54 Sarijadi Bandung 40151<br />Telp. (022) 2010491 Fax. (022) 2010491-->
              </td>
            </tr>
        </table>
    </td></tr>
    <tr>
    	<td>&nbsp;<br />
        	<table width="100%" border="0" cellspacing="0" cellpadding="0">
            	<tr>
            	  <td class="c"><strong>TANGGAL</strong></td>
            	  <td class="c"><strong>NO TRANS</strong></td>
            	  <td class="c"><strong>KETERANGAN</strong></td>
            	  <td class="c"><strong><?=strtoupper($_IST['gl1.php'])?></strong></td>
            	  <td class="c"><strong>DEBIT</strong></td>
            	  <td class="c"><strong>KREDIT</strong></td>
            	</tr>
                <?php
				if(is_array($_D)) {
					reset($_D);
					while(list($a,$b)=each($_D)) {
						$rowSpan=count($_D[$a]['id_gl4']);
						?>
						<tr>
						  <td rowspan="<?=$rowSpan?>" style="vertical-align:middle" class="c"><?=tglIndo($_D[$a]['tgl_trans'],2)?></td>
						  <td rowspan="<?=$rowSpan?>" style="vertical-align:middle" class="c"><?=$a?></td>
						  <td rowspan="<?=$rowSpan?>" style="vertical-align:middle" class="l"><?=$_D[$a]['keterangan']?><?=($_D[$a]['id_glx'] && $_GLX[$_D[$a]['id_glx']]?(" (".$_GLX[$_D[$a]['id_glx']].")"):"")?></td>
                          <?php
						  reset($_D[$a]['id_gl4']);
                          while(list($aa,$bb)=each($_D[$a]['id_gl4'])) {
						  	  if(!$rowSpan) {
							  	unset($rowSpan);
							  	echo "<tr>";
							  }						  
							  ?>
						  <td class="l"><?=$bb?></td>
							  <td class="r"><?=numbAcc($_D[$a]['nominal_d'][$aa])?></td>
							  <td class="r"><?=numbAcc($_D[$a]['nominal_c'][$aa])?></td>
							</tr>
						<?php	
						  }		
					}
				}
				?>
                <tr>
                	<td colspan="4" class="r"><strong>TOTAL : </strong></td>
                  	<td class="r"><strong><?=numbAcc($ds)?></strong></td>
                  	<td class="r"><strong><?=numbAcc($cs)?></strong></td>
                </tr>
          </table><br />&nbsp;
        </td>
    </tr>
	<tr>
        <td class="c">
            <?php
            $no=0;
            $a=queryDb("select keterangan,jabatan,pegawai from t_lap_tandatangan where url='x=1&".$_SERVER['QUERY_STRING']."' order by id");
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