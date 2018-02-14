<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='daftar_aset.php'",
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
		size: 8.5in 11in;
		margin:0.3in;
	}
	
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
tr.title td { vertical-align:middle; }
td.non { border:none; }
td.bg { background-color:#eeeeee; }
p { margin:0;}
.c { text-align:center; }
.r { text-align:right; }
.dn {  display:none; }

table.nb { border:none; }
table.nb td { border:none; }

ol, li { margin:0;padding:0; }
ol { padding-left:15px; }
</style>
</head>

<body>
<?php
$where_hibah=($_GT['hibah']!="-")?" and a.hibah='".$_GT['hibah']."'":"";

$_JENIS[1]="ASET TETAP";
$_JENIS[2]="ASET SEWA";
$_JENIS[9]="ASET LAIN-LAIN";
$_JENIS[0]="ASET TAK BERWUJUD";

$sql="select 
			if(a.hibah='1','ASET HIBAH','ASET NON HIBAH') as hibah,a.id_aset,a.id_aset_kategori,k.nama as kategori,r.nama as ruangan,a.nama,a.no_inventaris,a.tgl_perolehan,a.jumlah,a.nilai
		from t_aset a 
			inner join t_ruangan r on r.id_unit1=a.id_unit1 and r.id_unit2=a.id_unit2 and r.id_unit3=a.id_unit3 and r.id_ruangan=a.id_ruangan
			inner join t_aset_kategori k on k.id_aset_kategori=a.id_aset_kategori
		where a.tgl_perolehan<='".balikTanggal($_GT['periode2'])." 23:59:59' and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".str_replace("#","','",$tUnit)."') and a.jenis='".$_GT['jenis']."' ".$where_hibah." and a.status='1'
		order by hibah desc,a.id_aset_kategori,a.tgl_perolehan,a.id_aset
		";
		
		echo $sql;

$a=queryDb($sql);
while($b=mysql_fetch_array($a)) {
	$_D[$b['id_aset']]['hibah']=$b['hibah'];
	$_D[$b['id_aset']]['id_aset_kategori']=$b['id_aset_kategori'];
	$_D[$b['id_aset']]['kategori']=$b['kategori'];
	$_D[$b['id_aset']]['ruangan']=$b['ruangan'];
	$_D[$b['id_aset']]['nama']=$b['nama'];
	$_D[$b['id_aset']]['no_inventaris']=$b['no_inventaris'];
	$_D[$b['id_aset']]['tgl_perolehan']=tglIndo($b['tgl_perolehan'],2);
	$_D[$b['id_aset']]['jumlah']=$b['jumlah'];
	$_D[$b['id_aset']]['nilai']=$b['nilai'];
	$_D[$b['id_aset']]['nilai_susut1']=0;
	$_D[$b['id_aset']]['nilai_susut21']=0;
	$_D[$b['id_aset']]['nilai_susut22']=0;
	$_D[$b['id_aset']]['akum_susut1']=0;
	$_D[$b['id_aset']]['akum_susut21']=0;
	$_D[$b['id_aset']]['akum_susut22']=0;
	$_D[$b['id_aset']]['akum_susut2']=0;
	$_D[$b['id_aset']]['akum_susut3']=0;
	$_D[$b['id_aset']]['nilai_buku1']=0;
	$_D[$b['id_aset']]['nilai_buku3']=0;
}

$sql="select y.id_aset,sum(y.nilai_susut1) as nilai_susut1,sum(y.nilai_susut21) as nilai_susut21,sum(y.nilai_susut22) as nilai_susut22
		from (
			select id_aset,
					if(tgl_rev<'".balikTanggal($_GT['periode1'])." 00:00:00',nilaisusut,0) as nilai_susut1,
					if(tgl_rev>='".balikTanggal($_GT['periode1'])." 00:00:00' and nilaisusut>=0,nilaisusut,0) as nilai_susut21,
					if(tgl_rev>='".balikTanggal($_GT['periode1'])." 00:00:00' and nilaisusut<0,(nilaisusut*-1),0) as nilai_susut22
				from ( select id_aset,tgl_rev,(nilai_susut-residu) as nilaisusut from t_aset_rev where tgl_rev<='".balikTanggal($_GT['periode2'])." 23:59:59' ) x
			) y
			inner join t_aset a on a.id_aset=y.id_aset and a.tgl_perolehan<='".balikTanggal($_GT['periode2'])." 23:59:59' and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".str_replace("#","','",$tUnit)."') and a.jenis='".$_GT['jenis']."' ".$where_hibah." and a.status='1'
			group by y.id_aset";

$a=queryDb($sql);
while($b=mysql_fetch_array($a)) {
	$_D[$b['id_aset']]['nilai_susut1']+=$b['nilai_susut1'];
	$_D[$b['id_aset']]['nilai_susut21']+=$b['nilai_susut21'];
	$_D[$b['id_aset']]['nilai_susut22']+=$b['nilai_susut22'];
	$_D[$b['id_aset']]['nilai_buku1']+=$b['nilai_susut1'];
	$_D[$b['id_aset']]['nilai_buku3']+=($b['nilai_susut1']+($b['nilai_susut21']-$b['nilai_susut22']));
}

$sql="select y.id_aset,sum(y.akum_susut1) as akum_susut1,sum(y.akum_susut21) as akum_susut21,sum(y.akum_susut22) as akum_susut22
		from (
			select id_aset,
					if(tgl_trans<'".balikTanggal($_GT['periode1'])." 00:00:00',nominal,0) as akum_susut1,
					if(tgl_trans>='".balikTanggal($_GT['periode1'])." 00:00:00' and nominal>=0,nominal,0) as akum_susut21,
					if(tgl_trans>='".balikTanggal($_GT['periode1'])." 00:00:00' and nominal<0,(nominal*-1),0) as akum_susut22
				from ( select id_aset,tgl_trans,nominal from t_susut where tgl_trans<='".balikTanggal($_GT['periode2'])." 23:59:59' ) x
			) y
			inner join t_aset a on a.id_aset=y.id_aset and a.tgl_perolehan<='".balikTanggal($_GT['periode2'])." 23:59:59' and concat(a.id_unit1,'.',a.id_unit2,'.',a.id_unit3) in ('".str_replace("#","','",$tUnit)."') and a.jenis='".$_GT['jenis']."' ".$where_hibah." and a.status='1'
			group by y.id_aset";

$a=queryDb($sql);
while($b=mysql_fetch_array($a)) {
	$_D[$b['id_aset']]['akum_susut1']+=$b['akum_susut1'];
	$_D[$b['id_aset']]['akum_susut21']+=$b['akum_susut21'];
	$_D[$b['id_aset']]['akum_susut22']+=$b['akum_susut22'];
	$_D[$b['id_aset']]['akum_susut2']+=($b['akum_susut21']-$b['akum_susut22']);
	$_D[$b['id_aset']]['akum_susut3']+=($b['akum_susut1']+($b['akum_susut21']-$b['akum_susut22']));
	$_D[$b['id_aset']]['nilai_buku1']-=$b['akum_susut1'];
	$_D[$b['id_aset']]['nilai_buku3']-=($b['akum_susut1']+($b['akum_susut21']-$b['akum_susut22']));
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td class="c">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="nb">
            <tr>
              <td class="c" style="background:url(../images/logo_laporan<?=(file_exists("../images/logo_laporan_".$tUnit.".png"))?("_".$_SESSION['unit']):""?>.png) no-repeat left center;padding-bottom:2em;vertical-align:middle;">
                <strong class="fw">DAFTAR <?=$_JENIS[$_GT['jenis']]?><br />
                <?=($tInstansi)?$tInstansi:INSTANSI?><?=getValue("concat('<br />',nama)","t_kelas","id_kelas='".$_GT['kelas']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' limit 1")?></strong>
                <!--<br />Jl Sariasih No. 54 Sarijadi Bandung 40151<br />Telp. (022) 2010491 Fax. (022) 2010491-->
              <br />
              <strong>Periode <?=tglIndo(balikTanggal($_GT['periode1']))?> s/d <?=tglIndo(balikTanggal($_GT['periode2']))?></strong></td>
            </tr>
        </table>
    </td></tr>
    <tr>
    	<td>&nbsp;<br />
        <?php
        if(is_array($_D)) {
		?>
        	<table width="100%" border="0" cellspacing="0" cellpadding="0">
            	<tr class="title">
            	  <td rowspan="3" class="c"><strong>NO</strong></td>
            	  <td rowspan="3" class="c"><strong>POSISI</strong></td>
            	  <td rowspan="3" class="c"><strong>NAMA ASET</strong></td>
            	  <td rowspan="3" class="c"><strong>NO<br /> INVENTARIS</strong></td>
            	  <td rowspan="3" class="c"><strong>TAHUN<br />PEROLEHAN</strong></td>
            	  <td rowspan="3" class="c"><strong>JUMLAH</strong></td>
            	  <td rowspan="3" class="c dn"><strong>HARGA<br />PEROLEHAN</strong></td>
            	  <td rowspan="3" class="c"><strong>NILAI<br />PEROLEHAN</strong> </td>
            	  <td rowspan="3" class="c"><strong>AKUMULASI<br />PENYUSUTAN<br />s/d <?=tglIndo(dateInterval(balikTanggal($_GT['periode1']),0,0,-1,0,0,0,"Y-m-d"),2);?></strong> </td>
            	  <td colspan="4" class="c"><strong><?=tglIndo(balikTanggal($_GT['periode1']))?> s.d <?=tglIndo(balikTanggal($_GT['periode2']),2)?></strong></td>
            	  <td rowspan="3" class="c"><strong>BIAYA<br />PENYUSUTAN</strong></td>
            	  <td rowspan="3" class="c"><strong>AKUMULASI<br />PENYUSUTAN<br />s.d <?=tglIndo(balikTanggal($_GT['periode2']),2)?></strong></td>
            	  <td rowspan="3" class="c"><strong>NILAI BUKU<br />s/d <?=tglIndo(balikTanggal($_GT['periode2']),2);?></strong></td>
            	  <td rowspan="3" class="c"><strong>NILAI BUKU<br />s/d <?=tglIndo(dateInterval(balikTanggal($_GT['periode1']),0,0,-1,0,0,0,"Y-m-d"),2);?></strong></td>
            	</tr>
            	<tr>
            	  <td colspan="2" class="c"><strong>TAMBAH</strong><strong></strong></td>
            	  <td colspan="2" class="c"><strong>KURANG</strong></td>
           	    </tr>
            	<tr>
            	  <td class="c"><strong>NILAI</strong></td>
            	  <td class="c"><strong>PENYUSUTAN</strong></td>
            	  <td class="c"><strong>NILAI</strong></td>
            	  <td class="c"><strong>PENYUSUTAN</strong></td>
            	</tr>
                <?php
					reset($_D);
					while(list($a,$b)=each($_D)) {
						if($idAsetKategori!=$_D[$a]['id_aset_kategori']) {
							if($idAsetKategori) {
							?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td colspan="3"><strong>Jumlah <?=$nmAsetKategori?> : </strong></td>
                                <td class="r"><strong><?=numbAcc($ttl1)?></strong></td>
                                <td class="r dn"><strong><?=numbAcc($ttl2)?></strong></td>
                                <td class="r"><strong><?=numbAcc($ttl3)?></strong></td>
                                <td class="r"><strong><?=numbAcc($ttl4)?></strong></td>
                                <td class="r"><strong><?=numbAcc($ttl5)?></strong></td>
                                <td class="r"><strong><?=numbAcc($ttl6)?></strong></td>
                                <td class="r"><strong><?=numbAcc($ttl7)?></strong></td>
                                <td class="r"><strong><?=numbAcc($ttl8)?></strong></td>
                                <td class="r"><strong><?=numbAcc($ttl9)?></strong></td>
                                <td class="r"><strong><?=numbAcc($ttl10)?></strong></td>
                                <td class="r"><strong><?=numbAcc($ttl11)?></strong></td>
                                <td class="r"><strong><?=numbAcc($ttl12)?></strong></td>
                            </tr>
							<?php
                            }
							
							$no=0;
	
							$ttl1=0;
							$ttl2=0;
							$ttl3=0;
							$ttl4=0;
							$ttl5=0;
							$ttl6=0;
							$ttl7=0;
							$ttl8=0;
							$ttl9=0;
							$ttl10=0;
							$ttl11=0;
							$ttl12=0;
							
							
							if($hibah!=$_D[$a]['hibah']) {
								echo "<tr><td colspan='17'>&nbsp;</td></tr>";
								echo "<tr><td>&nbsp;</td><td class='c' colspan='16'><strong>".$_D[$a]['hibah']."</strong></td></tr>";
							}
							
							echo "<tr><td colspan='17'>&nbsp;</td></tr>";
							echo "<tr><td>&nbsp;</td><td class='c'><strong>".$_D[$a]['id_aset_kategori']."</strong></td><td colspan='15'><strong>".$_D[$a]['kategori']."</strong></td></tr>";
						}
						
						$no++;

						if($b['nilai_susut1']==0) {
							$ttl3+=0;
							$ttl5+=$b['nilai'];
							$total3+=0;
							$total5+=$b['nilai'];
						}
						else {
							$ttl3+=$b['nilai'];
							$ttl5+=0;
							$total3+=$b['nilai'];
							$total5+=0;
						}
	
						$ttl1+=$b['jumlah'];
						$ttl2+=$b['nilai'];
						//$ttl3+=$b['nilai_susut1'];
						$ttl4+=$b['akum_susut1'];
						//$ttl5+=$b['nilai_susut21'];
						$ttl6+=$b['akum_susut21'];
						$ttl7+=$b['nilai_susut22'];
						$ttl8+=$b['akum_susut22'];
						$ttl9+=$b['akum_susut2'];
						$ttl10+=$b['akum_susut3'];
						$ttl11+=$b['nilai_buku3'];
						$ttl12+=$b['nilai_buku1'];
	
						$total1+=$b['jumlah'];
						$total2+=$b['nilai'];
						//$total3+=$b['nilai_susut1'];
						$total4+=$b['akum_susut1'];
						//$total5+=$b['nilai_susut21'];
						$total6+=$b['akum_susut21'];
						$total7+=$b['nilai_susut22'];
						$total8+=$b['akum_susut22'];
						$total9+=$b['akum_susut2'];
						$total10+=$b['akum_susut3'];
						$total11+=$b['nilai_buku3'];
						$total12+=$b['nilai_buku1'];
						?>
						<tr>
						  <td class="r"><?=$no?>.</td>
						  <td class="l"><?=$_D[$a]['ruangan']?></td>
						  <td class="l"><?=$_D[$a]['nilai']."====".$_D[$a]['nilai_susut1']."====".$_D[$a]['nilai_susut21']?></td>
						  <td class="c"><?=$_D[$a]['no_inventaris']?></td>
						  <td class="c"><?=$_D[$a]['tgl_perolehan']?></td>
						  <td class="r"><?=numbAcc($_D[$a]['jumlah'])?></td>
						  <td class="r dn"><?=numbAcc($_D[$a]['nilai'])?></td>
						  <td class="r"><?=numbAcc((($_D[$a]['nilai_susut1']==0)?0:$_D[$a]['nilai']))?></td>
						  <td class="r"><?=numbAcc($_D[$a]['akum_susut1'])?></td>
						  <td class="r"><?=numbAcc((($_D[$a]['nilai_susut21']==0)?0:$_D[$a]['nilai']))?></td>
						  <td class="r"><?=numbAcc($_D[$a]['akum_susut21'])?></td>
						  <td class="r"><?=numbAcc($_D[$a]['nilai_susut22'])?></td>
						  <td class="r"><?=numbAcc($_D[$a]['akum_susut22'])?></td>
						  <td class="r"><?=numbAcc($_D[$a]['akum_susut2'])?></td>
						  <td class="r"><?=numbAcc($_D[$a]['akum_susut3'])?></td>
						  <td class="r"><?=numbAcc($_D[$a]['nilai_buku3'])?></td>
						  <td class="r"><?=numbAcc($_D[$a]['nilai_buku1'])?></td>
						</tr>
						<?php
						
						$hibah=$_D[$a]['hibah'];
						$idAsetKategori=$_D[$a]['id_aset_kategori'];
						$nmAsetKategori=$_D[$a]['kategori'];
					}
				?>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                    <td colspan="3"><strong>Jumlah <?=$nmAsetKategori?> : </strong></td>
                    <td class="r"><strong><?=numbAcc($ttl1)?></strong></td>
                    <td class="r dn"><strong><?=numbAcc($ttl2)?></strong></td>
                    <td class="r"><strong><?=numbAcc($ttl3)?></strong></td>
                    <td class="r"><strong><?=numbAcc($ttl4)?></strong></td>
                    <td class="r"><strong><?=numbAcc($ttl5)?></strong></td>
                    <td class="r"><strong><?=numbAcc($ttl6)?></strong></td>
                    <td class="r"><strong><?=numbAcc($ttl7)?></strong></td>
                    <td class="r"><strong><?=numbAcc($ttl8)?></strong></td>
                    <td class="r"><strong><?=numbAcc($ttl9)?></strong></td>
                    <td class="r"><strong><?=numbAcc($ttl10)?></strong></td>
                    <td class="r"><strong><?=numbAcc($ttl11)?></strong></td>
                    <td class="r"><strong><?=numbAcc($ttl12)?></strong></td>
                </tr>
                <tr><td colspan="17">&nbsp;</td></tr>
                <tr>
                	<td class="bg">&nbsp;</td>
                	<td class="bg">&nbsp;</td>
                	<td colspan="3" class="bg"><strong>TOTAL : </strong></td>
                  	<td class="r bg"><strong><?=numbAcc($total1)?></strong></td>
                  	<td class="r bg dn"><strong><?=numbAcc($total2)?></strong></td>
                  	<td class="r bg"><strong><?=numbAcc($total3)?></strong></td>
                  	<td class="r bg"><strong><?=numbAcc($total4)?></strong></td>
                  	<td class="r bg"><strong><?=numbAcc($total5)?></strong></td>
                  	<td class="r bg"><strong><?=numbAcc($total6)?></strong></td>
                  	<td class="r bg"><strong><?=numbAcc($total7)?></strong></td>
                  	<td class="r bg"><strong><?=numbAcc($total8)?></strong></td>
                  	<td class="r bg"><strong><?=numbAcc($total9)?></strong></td>
                  	<td class="r bg"><strong><?=numbAcc($total10)?></strong></td>
                  	<td class="r bg"><strong><?=numbAcc($total11)?></strong></td>
                  	<td class="r bg"><strong><?=numbAcc($total12)?></strong></td>
                </tr>
          </table>
        	<br />&nbsp;
           <?php
			}
           ?>
        </td>
    </tr>
    <tr>
		<td class="c">
            <?php
            $no=0;
            $a=queryDb("select keterangan,jabatan,pegawai from t_lap_tandatangan where url='x=8&".$_SERVER['QUERY_STRING']."' order by id");
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