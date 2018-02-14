<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='tagihan_siswa.php'",
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
		
		if(isset($_GT['xls'])) {
			chdir('../includes/excel_writer');
			require_once 'Writer.php';
			chdir('..');
			
			$sheet1=array(array('No','Braviano','CustCode','CustName','Type','OverwriteAddRemove','Amount','LastPeriode','Keterangan'));
			
			$no=1;
			$a=queryDb("select concat(if(k.id_unit3='02','1',if(k.id_unit3='03','2','0')),t.id_jns_bayar_siswa,t.id_siswa) as id,t.nama_siswa,t.nominal,t.uraian from t_siswa_tagih t
								inner join t_kelas k on k.id_kelas=t.id_kelas
							where t.no_trans='".$_GT['xls']."'");
			while($b=mysql_fetch_array($a)) {
				$sheet1[$no]=array($no,'77976',$b['id'],$b['nama_siswa'],'K','A',showRupiah2($b['nominal']),'',$b['uraian']);
				
				$no++;
			}
			
			/*$sheet2 =  array(
			  array('eventid','funny_name'   ),
			  array('32'      ,'Adam Baum'    ),
			  array('33'      ,'Anne Teak'    ),
			  array('34'      ,'Ali Katt'     ),
			  array('35'      ,'Anita Bath'   ),  
			  array('36'      ,'April Schauer'),
			  array('37'      ,'Bill Board'   ),
			);*/
			
			$workbook = new Spreadsheet_Excel_Writer();
			
			$format_und =& $workbook->addFormat();
			//$format_und->setBottom(2);//thick
			$format_und->setAlign("center");
			$format_und->setBorder(1);
			$format_und->setBold();
			$format_und->setColor('red');
			$format_und->setFontFamily('Calibri');
			$format_und->setSize(12);
			
			$format_reg =& $workbook->addFormat();
			$format_reg->setColor('black');
			$format_reg->setFontFamily('Calibri');
			$format_reg->setSize(12);
			
			$arr = array(
				  'Sheet 1'=>$sheet1,
				  /*'Names'   =>$sheet2,*/
				  );
			
			foreach($arr as $wbname=>$rows) {
				$rowcount = count($rows);
				$colcount = count($rows[0]);
			
				$worksheet =& $workbook->addWorksheet($wbname);
			
				$worksheet->setColumn(0,0, 6.14);//setColumn(startcol,endcol,float)
				$worksheet->setColumn(1,3,15.00);
				$worksheet->setColumn(4,4, 8.00);
				
				for( $j=0; $j<$rowcount; $j++ )
				{
					for($i=0; $i<$colcount;$i++)
					{
						$fmt  =& $format_reg;
						if ($j==0)
							$fmt =& $format_und;
			
						if (isset($rows[$j][$i]))
						{
							$data=$rows[$j][$i];
							$worksheet->write($j, $i, $data, $fmt);
						}
					}
				}
			}
			
			$workbook->send('file upload bank.xls');
			$workbook->close();		
		}
	}
?>
