<?php
	error_reporting(0);
	//session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='".basename($_SERVER["SCRIPT_FILENAME"])."'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		
		$_SESSION['waktu']=time()+1800;
		
		$statTutupBuku=getValue("stat_hapus_trans","t_user","id_user='".$_SESSION['user']."'");
		$startTrans=getValue("max(t)","(
												(select DATE_ADD(tgl_finish,INTERVAL 1 DAY) AS t from t_posting where 1=1 order by tgl_finish desc limit 1)
												UNION
												(select tgl_trans AS t from t_glt where 1=1 order by tgl_trans asc limit 1)
											) x","1=1 limit 1");
		if($_PT['tSimpan']) {
			$tIdPosting=$_PT['tIdPosting'];
			$tTanggal=$_PT['tTanggal'];
			$tTanggalAwal=$_PT['tTanggalAwal'];
			
			//$errtTanggal=(!balikTanggal($tTanggal))?"Format Tanggal masih salah (DD/MM/YYYY)":((dateDiff(TANGGAL2,balikTanggal($tTanggal))<0)?"Tanggal tidak boleh lebih besar dari sekarang":((dateDiff(balikTanggal($tTanggal),$startTrans)<0)?"Minimal Tgl Posting ".tglIndo($startTrans):""));
			
			if(!$errtTanggal && $startTrans) {
				$tId=substr(((getValue("id_posting","t_posting","1=1 order by id_posting desc limit 0,1")*1)+101),1,2);
				$sqlPosting = "SELECT * FROM t_posting WHERE id_posting = '".$tIdPosting."'";
				$dataPost = queryDb($sqlPosting);
				$tTanggal=balikTanggal($tTanggal);
				$tTanggalAwal=balikTanggal($tTanggalAwal);
				if(mysql_num_rows($dataPost) == 0) {
					queryDb("insert into t_posting(id_posting,tgl_start,tgl_finish,tanggal,id_user) values('".$tId."','".$tTanggalAwal."','".$tTanggal."','".TANGGAL."','".$_SESSION['user']."')");
				
					queryDb("insert into t_glu(id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_aruskas,id_aruskas_sub,nama,saldo_awal,mutasi_d,mutasi_c,saldo,tanggal)
							select t.id_unit1,t.id_unit2,t.id_unit3,g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,g.id_aruskas,g.id_aruskas_sub,g.nama,'0','0','0','0','".TANGGAL2."'
									from t_gl4 g
										inner join t_glt t on t.id_gl4=g.id_gl4 and t.tgl_trans between '".$tTanggalAwal." 00:00:00' and '".$tTanggal." 23:59:59'
										left join t_glu u on u.id_unit1=t.id_unit1 and u.id_unit2=t.id_unit2 and u.id_unit3=t.id_unit3 and u.id_gl4=g.id_gl4
									where u.id_gl4 is null
									group by t.id_unit1,t.id_unit2,t.id_unit3,g.id_gl1,g.id_gl2,g.id_gl3,g.id_gl4,g.id_aruskas,g.id_aruskas_sub,g.nama");
								
					queryDb("update t_glu u 
										inner join t_gl1 g1 on g1.id_gl1=u.id_gl1 
										inner join (
														select id_unit1,id_unit2,id_unit3,id_gl4,sum(nominal_d) as nominal_d,sum(nominal_c) as nominal_c from (
																(select id_unit1,id_unit2,id_unit3,id_gl4,sum(nominal_d) as nominal_d,sum(nominal_c) as nominal_c from t_glt
																		where tgl_trans between '".$tTanggalAwal." 00:00:00' and '".$tTanggal." 23:59:59'
																		group by id_unit1,id_unit2,id_unit3,id_gl4)
																union
																(select id_unit1,id_unit2,id_unit3,id_glx as id_gl4,sum(nominal_d) as nominal_d,sum(nominal_c) as nominal_c from t_glt
																		where id_glx<>'' and tgl_trans between '".$tTanggalAwal." 00:00:00' and '".$tTanggal." 23:59:59'
																		group by id_unit1,id_unit2,id_unit3,id_glx)
															) x
															group by id_unit1,id_unit2,id_unit3,id_gl4																
													) t on t.id_unit1=u.id_unit1 and t.id_unit2=u.id_unit2 and t.id_unit3=u.id_unit3 and t.id_gl4=u.id_gl4
									set u.mutasi_d=(u.mutasi_d+t.nominal_d),u.mutasi_c=(u.mutasi_c+t.nominal_c),u.saldo=(u.saldo+((t.nominal_d-t.nominal_c)*if(g1.db_cr='D',1,-1)))");
				
					queryDb("insert into t_gluh(id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_aruskas,id_aruskas_sub,nama,saldo_awal,mutasi_d,mutasi_c,saldo,tanggal)
								select id_unit1,id_unit2,id_unit3,id_gl1,id_gl2,id_gl3,id_gl4,id_aruskas,id_aruskas_sub,nama,saldo_awal,mutasi_d,mutasi_c,saldo,'".$tTanggal."' from t_glu");
								
					queryDb("update t_glu set saldo_awal=saldo,mutasi_d=0,mutasi_c=0,tanggal='".$tTanggal."'");
				} else {
					queryDb("update t_posting set tgl_start='".$tTanggalAwal."',tgl_finish='".$tTanggal."',tanggal='".TANGGAL."',id_user='".$_SESSION['user']."' where id_posting='".$tIdPosting."'");
				}
				
				
				
				
				header("location:?edit=".bs64_e($tId));
				exit;
			}
		}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$titleHTML?></title>
<script src="../js/function.js" type="text/javascript"></script>
<script src="../js/effect.js" type="text/javascript"></script>
<script src="../js/submain.js" type="text/javascript"></script>
<script src="../js/dateVal.js" type="text/javascript"></script>
<script src="../js/jquery-1.5.js" type="text/javascript"></script>
<script src="../js/ui/jquery.ui.core.js" type="text/javascript"></script>
<script src="../js/ui/jquery.ui.widget.js" type="text/javascript"></script>
<script src="../js/ui/jquery.ui.datepicker.js" type="text/javascript"></script>
<script language="javascript">if(self.document.location==top.document.location) self.document.location="../index.php?logout=1";</script>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<link href="../css/ui/base/jquery.ui.all.css" rel="stylesheet" />
<style type="text/css">
#titleTop ol li:nth-child(1) , #list ul li:nth-child(1) { width:30px;text-align:center; }
/*#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:110px;text-align:center; }*/
#titleTop ol li:nth-child(3) , #list ul li:nth-child(3) { width:170px;text-align:center; }
#titleTop ol li:nth-child(4) , #list ul li:nth-child(4) { width:170px;text-align:center; }
#titleTop ol li { text-align:center; }

table input[type=text], table input[type=password],  table select { width:400px; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<div id="input">
	<ul class="full">
        <li style="text-align:center;">
          <form action="" target="_self" method="post" onsubmit="showFade('load');">
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <th colspan="3" class="title">FORM INPUT <?=strtoupper($titleHTML)?></th>
                </tr>
                <tr>
                  <td colspan="3">&nbsp;</td>
                </tr>
                <input type="hidden" id="x" />
                     <input type="hidden" name="tIdPosting" id="tIdPosting" value="<?=substr(((getValue("id_posting","t_posting","1=1 order by id_posting desc limit 0,1")*1)+101),1,2)?>" maxlength="2" class="readonly" readonly="readonly" />
                <tr>
                  <td>TGL TRANSAKSI</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tTanggalAwal" id="tTanggalAwal" style="width:140px" value="" /> s/d
                    <input type="text" name="tTanggal" id="tTanggal" value="" />
                    <div id="errtTanggal" class="err"><?=$errtTanggal?></div>
                  </td>
                </tr>
                <tr>
                  <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                  <th colspan="3">
                  	<input name="tSimpan" type="submit" class="icon-save" id="tSimpan" value="SIMPAN" />
                  	<input type="reset" id="tTutup" class="icon-no" onclick="hideFade('input'); return false;" value="TUTUP" /></th>
                </tr>
              </table>
          </form>
        </li>
    </ul>
</div>

<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)?></li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("d.id_posting#ID Posting","d.tgl_start#Tgl Mulai Transaksi","d.tgl_finish#Tgl Selesai Transaksi");
		
		if(is_array($listTitle)) {
			reset($listTitle);
			foreach($listTitle as $value) {
				$arrayValue=explode("#",$value);
				echo "<li ".(($arrayValue[0])?("id=\"".$arrayValue[0]."\" onclick=\"return goOrder('order,sort,search',this.id);\" ".(($_GT['order']==($arrayValue[0]." asc"))?"class=\"asc\"":(($_GT['order']==($arrayValue[0]." desc"))?"class=\"desc\"":""))." title=\"Urutkan berdasarkan ".strtoupper($arrayValue[1])."\""):"class=\"only\"")."><span>".strtoupper($arrayValue[1])."</span></li>";
			}
		}
		?>
    </ol>
</div>
<div id="list">
	<?php
	$row=50;
	$page=8;
	$sql="select d.id_posting,d.tgl_start,d.tgl_finish from t_posting d
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				order by ".(($_GT['order'])?$_GT['order'].",d.tanggal desc":"d.tanggal desc");
				
	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tIdPosting-".$b['id_posting']."\" value=\"".$b['id_posting']."\" />";
			echo "<input type=\"hidden\" id=\"tTanggalAwal-".$b['id_posting']."\" value=\"".date('d/m/Y', strtotime($b['tgl_start']))."\" />";
			echo "<input type=\"hidden\" id=\"tTanggal-".$b['id_posting']."\" value=\"".date('d/m/Y', strtotime($b['tgl_finish']))."\" />";
			echo "<ul id=\"".$b['id_posting']."\" onclick=\"listFocus(this,'edit=1,del=".(!$_EXC[$b['id_posting']])."')\"><li>".$paging['start'].".</li><li>".$b['id_posting']."</li><li>".tglIndo($b['tgl_start'])."</li><li>".tglIndo($b['tgl_finish'])."</li></ul>";
			if($_GT['edit']==$b['id_posting']) $jsEdit="listFocus(elm('".$b['id_posting']."'),'edit=1,del=".$b['id_posting']."');";
		
		}
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li style="width:auto;" class="l">
        	<select style="width:120px;" onchange="_URI['sort']=this.value;">
				<?php
				if(is_array($listTitle)) {
					reset($listTitle);
					
					if(count($listTitle)>1) echo "<option value=\"\">- Pilih -</option>";
					
					foreach($listTitle as $value) {
						$arrayValue=explode("#",$value);
						if($arrayValue[0]) echo "<option value=\"".$arrayValue[0]."\" ".(($_GT['sort']==$arrayValue[0])?"selected=\"selected\"":"").">".ucfirst($arrayValue[1])."</option>";
					}
				}
                ?>
            </select>
            <input type="text" style="width:120px;" onblur="_URI['search']=this.value;" onmouseout="_URI['search']=this.value;" value="<?=$_GT['search']?>" />
            <input type="reset" class="icon-search" onclick="goAddress('sort,search,order,tab');" value="SEARCH" />
        </li>
    	<li style="width:auto;" class="paging">
			<span style="margin-right:40px;"><?=$paging['show']?></span><?=(($paging['page'])?"<span>Page :</span>".$paging['page']:"")?>
        </li>
    	<li class="r" style="width:auto;">
		  <?=($statTutupBuku==1)?"<button id=\"btn-new\" class=\"icon-new\" onclick=\"newData('tIdPosting');\">TAMBAH</button>":""?>
		  <?=($statTutupBuku==1)?"<button id=\"btn-edit\" class=\"icon-edit disabled\" onclick=\"editData('tIdPosting,tTanggal,tTanggalAwal');\">EDIT</button>":""?>
		  
        </li>
    </ol>
</div>
<script language="javascript">
	function setTanggal(v) {
		v=(v)?v:"<?=date("d/m/Y")?>";
		
		$(function() {
			$("#tTanggal").datepicker();
			$("#tTanggal").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggalAwal").datepicker();
			$("#tTanggalAwal").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal").datepicker("setDate",v);
			$("#tTanggalAwal").datepicker("setDate",v);
		});
	}
	
	setTanggal("<?=$tTanggal?>");
	setTanggal("<?=$tTanggalAwal?>");
	
	
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtTanggal)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
