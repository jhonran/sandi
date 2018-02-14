<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='sisa_tagihan_siswa.php'",
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
/*$sql="select id_siswa_tagih,id_siswa,nama,id_jns_bayar_siswa,jenis,keterangan,sum(ns-ms) as nominal from (
				select max(tgl_trans) as tgl_trans,max(no_trans) as no_trans,x.id_siswa_tagih,x.id_siswa,m.nama,x.id_jns_bayar_siswa,j.nama as jenis,max(keterangan) as keterangan,sum(n) as ns,sum(m) as ms from (
						select tgl_trans,no_trans,id_siswa_tagih,id_siswa,id_jns_bayar_siswa,keterangan,nominal as n,0 as m from t_siswa_tagih where id_kelas='".$_GT['kelas']."' and tgl_trans<='".balikTanggal($_GT['periode'])."'
						UNION
						(select '' as tgl_trans,'' as no_trans,id_siswa_tagih,id_siswa,id_jns_bayar_siswa,'' as keterangan,0 as n,sum(nominal) as m from t_siswa_bayar where id_kelas='".$_GT['kelas']."' group by id_siswa_tagih,id_siswa,id_jns_bayar_siswa)
					) x
					inner join t_siswa m on m.id_siswa=x.id_siswa ".(($_GT['nik'])?"and m.id_siswa='".$_GT['nik']."'":"")."
					inner join t_jns_bayar_siswa j on j.id_jns_bayar_siswa=x.id_jns_bayar_siswa and j.id_jns_bayar_siswa in ('".str_replace("#","','",$_GT['jenis'])."')
					group by x.id_siswa_tagih,x.id_siswa,m.nama,x.id_jns_bayar_siswa,j.nama
					having sum(n)<>0
			) y
			group by id_siswa_tagih,id_siswa,nama,id_jns_bayar_siswa,jenis,keterangan
			having sum(ns-ms)<>0
			order by tgl_trans,no_trans,jenis,nama";*/
			
			
$sql="select h.id_siswa_tagih,h.id_siswa,h.nama_siswa,j.nama as jenis,h.uraian,(h.nominal-h.total_bayar) as nominal from t_siswa_tagih h
				inner join t_jns_bayar_siswa j on j.id_jns_bayar_siswa=h.id_jns_bayar_siswa and j.id_jns_bayar_siswa in ('".str_replace("#","','",$_GT['jenis'])."')
			where h.nominal<>h.total_bayar and h.id_kelas='".$_GT['kelas']."' and h.tgl_trans<='".balikTanggal($_GT['periode'])."' ".(($_GT['nik'])?"and h.id_siswa='".$_GT['nik']."'":"")."
			order by h.tgl_trans,h.no_trans,j.nama,h.nama_siswa";

$a=queryDb($sql);
while($b=mysql_fetch_array($a)) {
	$_D[$b['id_siswa_tagih']]['nim']=$b['id_siswa'];
	$_D[$b['id_siswa_tagih']]['nama']=$b['nama_siswa'];
	$_D[$b['id_siswa_tagih']]['jenis']=$b['jenis'];
	$_D[$b['id_siswa_tagih']]['keterangan']=$b['uraian'];
	$_D[$b['id_siswa_tagih']]['nilai']=$b['nominal'];
	
	$total+=$b['nominal'];
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td class="c">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            <tr>
              <td class="c" style="background:url(../images/logo_laporan<?=(file_exists("../images/logo_laporan_".$_SESSION['unit'].".png"))?("_".$_SESSION['unit']):""?>.png) no-repeat left center;padding-bottom:2em;vertical-align:middle;">
                <strong class="fw">SALDO PIUTANG <?=strtoupper($_IST['siswa.php'])?> <br />
                <?=($tInstansi)?$tInstansi:INSTANSI?><?=getValue("concat('<br />',nama)","t_kelas","id_kelas='".$_GT['kelas']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' limit 1")?></strong>
                <!--<br />Jl Sariasih No. 54 Sarijadi Bandung 40151<br />Telp. (022) 2010491 Fax. (022) 2010491-->
                <br /><strong>Periode per <?=tglIndo(balikTanggal($_GT['periode']))?></strong>              </td>
            </tr>
        </table>
    </td></tr>
    <tr>
    	<td>&nbsp;<br />
        	<table width="100%" border="0" cellspacing="0" cellpadding="0">
            	<tr>
            	  <td class="c"><strong>NIM</strong></td>
            	  <td class="c"><strong>NAMA <?=strtoupper($_IST['siswa.php'])?></strong></td>
            	  <td class="c"><strong>JENIS PIUTANG</strong></td>
            	  <td class="c"><strong>URAIAN</strong></td>
            	  <td class="c"><strong>JUMLAH</strong></td>
            	</tr>
                <?php
				if(is_array($_D)) {
					reset($_D);
					while(list($a,$b)=each($_D)) {
						?>
						<tr>
						  <td class="c"><?=$_D[$a]['nim']?></td>
						  <td class="l"><?=$_D[$a]['nama']?></td>
						  <td class="c"><?=$_D[$a]['jenis']?></td>
						  <td class="l"><?=$_D[$a]['keterangan']?></td>
						  <td class="r"><?=numbAcc($_D[$a]['nilai'])?></td>
						</tr>
						<?php
					}
				}
				?>
                <tr>
                	<td colspan="4" class="r"><strong>TOTAL : </strong></td>
                  	<td class="r"><strong><?=numbAcc($total)?></strong></td>
                </tr>
          </table><br />&nbsp;
        </td>
    </tr>
    <tr>
		<td class="c">
            <?php
            $no=0;
            $a=queryDb("select keterangan,jabatan,pegawai from t_lap_tandatangan where url='x=9&unit=".bs64_e($_SESSION['unit'])."&".$_SERVER['QUERY_STRING']."' order by id");
            while($b=mysql_fetch_array($a)) {
                $no++;
                $_K[$no]=$b['keterangan'];
                $_J[$no]=$b['jabatan'];
                $_P[$no]=$b['pegawai'];
            }
            
            if($no==0) {
				$unit=getValue("concat(id_unit1,'.',id_unit2,'.',id_unit3) as unit","t_unit3","concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".$_SESSION['unit']."','01.01.01') order by unit desc limit 1");
				
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