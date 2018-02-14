<?php
	//========================= PENAMBAHAN HARI "$date = date() + day()" =======================//
	// 																							//
	// 				$date adalah data tanggal yang akan ditambah $int_day.						//
	// 		dengan hasil dalam format date sebagai data tanggal setelah ditambah jumlah hari.	//
	// 																							//
	//==========================================================================================//
	
	if(preg_match("/function.php/",$_SERVER['PHP_SELF'])) {
		header("location:../index.php");
		die;
	}
	
	function dateInterval($date,$year=0,$month=0,$day=0,$hours=0,$minute=0,$second=0,$format="Y-m-d H:i:s") {
		$c=getdate(strtotime($date));
		
		return date($format,mktime(
						($c['hours']+($hours)),
						($c['minutes']+($minute)),
						($c['seconds']+($second)),
						($c['mon']+($month)),
						($c['mday']+($day)),
						($c['year']+($year))));
	}
	
	function dateDiff($date1,$date2,$format="d") {
		$s=strtotime($date1)-strtotime($date2);
		
		switch ($format) {
			case "s":
				$out=$s;
				break;
			case "m":
				$out=floor($s/60);
				break;
			case "h":
				$out=floor($s/3600);
				break;
			default:
				$out=floor($s/86400);
		}
		return $out;
	}
	
	function balikTanggal($tgl)	{
		$tgl= str_replace('/','-',$tgl);
		if(is_numeric(substr($tgl,0,4)) && is_numeric(substr($tgl,5,2)) && is_numeric(substr($tgl,8,2))) { 
			$tahun = substr($tgl,0,4);
			$bulan = substr($tgl,5,2);
			$tanggal = substr($tgl,8,2);
			if(checkdate($bulan,$tanggal,$tahun)) return $tanggal.'-'.$bulan.'-'.$tahun;
			else return false;
		} 
		else if(is_numeric(substr($tgl,0,2)) && is_numeric(substr($tgl,3,2)) && is_numeric(substr($tgl,6,2))) {
			$tahun = substr($tgl,6,4);
			$bulan = substr($tgl,3,2);
			$tanggal = substr($tgl,0,2);
			if(checkdate($bulan,$tanggal,$tahun)) return $tahun.'-'.$bulan.'-'.$tanggal;
			else return false;
		}
		else return false;
	}
	
	function tglIndo($tgl,$template=1)	{
		global $array_bulan;
		global $array_bln;
		
		$tgl= str_replace('/','-',$tgl);
		if(is_numeric(substr($tgl,0,4)) && is_numeric(substr($tgl,5,2)) && is_numeric(substr($tgl,8,2))) { 
			$tahun = substr($tgl,0,4);
			$bulan = substr($tgl,5,2);
			$tanggal = substr($tgl,8,2);
		}
		else if(is_numeric(substr($tgl,0,2)) && is_numeric(substr($tgl,3,2)) && is_numeric(substr($tgl,6,2))) {
			$tahun = substr($tgl,6,4);
			$bulan = substr($tgl,3,2);
			$tanggal = substr($tgl,0,2);
		}
		
		if(checkdate($bulan,$tanggal,$tahun)) return $tanggal.' '.(($template==1)?$array_bulan[($bulan-1)]:$array_bln[($bulan-1)]).' '.$tahun;
		else return false;
	}
	//---------------------------------------------------------------------------------------------------------//
	
	// Untuk round().
	function fungsiRound($nilai) {
		$nilaii=$nilai."";
		$panjang=strlen($nilaii);
		$hasil="";
		for($i=0;$i<$panjang;$i++) {
			$proses=substr($nilaii,$i,1);
			if($proses=='.') break;
			$hasil.=$proses;
		}
		$sisa=substr($nilaii,$i+1,1);
		if($sisa>=5) $hasil=($hasil*1)+1;	
		return $hasil;	
	}
	//------------------------------------------------------------------------------------------------------------//
	
	// Untuk menampilkan data dalam bentuk rupiah.
	function showRupiah($nilai) {
		return "Rp ".number_format($nilai,0,'','.').",-";	
	}
	
	function showRupiah2($nilai) {
		return number_format($nilai,0,'','.');	
	}
	
	function clearRupiah($nilai) {
		$nilai=str_replace("Rp","",$nilai);
		$nilai=str_replace(".","",$nilai);
		$nilai=trim(str_replace(",-","",$nilai));
		$nilai=($nilai*1)?($nilai*1):$nilai;
		return $nilai;
	}
	//------------------------------------------------------------------------------------------------------------//
	
	// Untuk menampilkan data dalam bentuk currency.
	function showCurrency($nilai) {
		$cnilai=fungsiRound($nilai);
		$panjang=strlen($cnilai);
		$sisa=$panjang%3;
		$ulang=floor($panjang/3);
		$hasil=substr($cnilai,0,$sisa);
		for($i=0;$i<$ulang;$i++) {
			if($sisa==0 && $i==0) $hasil.=substr($cnilai,(3*$i)+$sisa,3);
			else $hasil.=".".substr($cnilai,(3*$i)+$sisa,3);		
		}
		
		return $hasil;	
	}
	//----------------------------------------------------------------------------------------------------------//
	
	//----------------------------------- Paging ---------------------------------------------------------------------------------------------
	// format penulisan 
	// paging("jumlah record dlm satu halaman","jumlah halaman yang ditampilkan","perintah query","$_GET halaman","variable tambahan contoh='act=$_GET[act]$id=$_GET[id]'");
	
	function paging($query="",$max_row=20,$max_page=10,$page=false,$param=false) {
		$_ARR=array();
		
		$start_row=($page)?(($page-1)*$max_row):0; // jumlah dimulainya max query
		$page=($page)?$page:1; // menunjukkan page yang ditampilkan
		
		if($qpaging=queryDb($query)) {
			if($rs_count=mysql_num_rows($qpaging)) {
				$total_page=ceil($rs_count/$max_row); // total page seluruhnya
			
				if($total_page<=$max_page) {
					$start_page=1;
					$finish_page=$total_page+1;
				}
				else {
					$start=$page-floor($max_page/2);
					
					if($start<2) {
						$start_page=1;
						$finish_page=$max_page+1;
					}
					else if(($start+$max_page)<=($total_page+1)) {
						$start_page=$start;
						$finish_page=$max_page+$start;
					}
					else {
						$start_page=($total_page+1)-$max_page;
						$finish_page=$total_page+1;
					}
				}
				
				$_ARR['show']="Show ".($start_row+1)."-".((($start_row+$max_row)<$rs_count)?($start_row+$max_row):$rs_count)." dari $rs_count data";
				
				if($page>1) $_ARR['page'].="<a href=\"?hal=".bs64_e(1)."&".$param."\" title=\"Awal\" target=\"_self\"><</a><a href=\"?hal=".bs64_e($page-1)."&".$param."\" title=\"Sebelumnya\" target=\"_self\"><<</a>";
				for($i=$start_page;$i<$finish_page;$i++) {
					if(($page==$i)&&(($start_page+1)<$finish_page)) $_ARR['page'].="<span>".$i."</span>"; // mematikan link pada page yang aktif
					else if($page!=$i) $_ARR['page'].="<a href=\"?hal=".bs64_e($i)."&".$param."\" target=\"_self\">".$i."</a>";
				}
				if($page<$total_page) $_ARR['page'].="<a href=\"?hal=".bs64_e($page+1)."&".$param."\" title=\"Selanjutnya\" target=\"_self\">>></a><a href=\"?hal=".bs64_e($total_page)."&".$param."\" title=\"Akhir\" target=\"_self\">></a>";	
			}
		}
		
		$_ARR['start']=$start_row;
		
		return $_ARR;
	}
	
	function getValue($field,$tabel,$where) {
		$query=queryDb("select $field from $tabel where $where");
		$fetch=mysql_fetch_array($query);
		
		return $fetch[0];
	}	
	
	function getValue2($field,$tabel,$opt) {
		$query=queryDb("select $field from $tabel $opt");
		$fetch=mysql_fetch_array($query);
		return $fetch[0];
	}
	//----------------------------------------------------------------------------------------------------------------------------------------
	
	function valEmail($email) {
		$email = strtolower($email); 
		if(preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i',$email)) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function terbilang($v) {
		$huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
		
		switch($v) {
			case $v<12:
				return " ".$huruf[$v];
				break;
			case $v<20:
				return terbilang($v-10)." Belas";
				break;
			case $v<100:
				return terbilang($v/10)." Puluh".terbilang($v%10);
				break;
			case $v<200:
				return " Seratus".terbilang($v-100);
				break;
			case $v<1000:
				return terbilang($v/100)." Ratus".terbilang($v%100);
				break;
			case $v<2000:
				return " Seribu".terbilang($v - 1000);
				break;
			case $v<1000000:
				return terbilang($v/1000)." Ribu".terbilang($v%1000);
				break;
			case $v<1000000000:
				return terbilang($v/1000000)." Juta".terbilang($v%1000000);
				break;
			case $v<1000000000000:
				$milyar=$v/1000000000;
				return terbilang($milyar)." Milyar".terbilang($v-(floor($milyar)*1000000000));
				break;
			default:
				//return "Konversi terbilang salah";
		}
	}
	
	function clearHTML($param) {
		return trim(preg_replace('/\s+/',' ',strip_tags($param)));
	}
	
	function bs64_e($v) {
		return urlencode(base64_encode($v));
	}
	
	function bs64_d($v) {
		return base64_decode(urldecode($v));
	}
?>