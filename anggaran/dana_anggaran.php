<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='".basename($_SERVER["SCRIPT_FILENAME"])."'",
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
		
		if($_GT['del']) {
			$tglStart=getValue("tgl_start","t_danaanggaran","id_danaanggaran='".$_GT['del']."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' limit 0,1");
			
			queryDb("delete from t_danaanggaran where concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' and tgl_start='".$tglStart."'");
			
			header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
			exit;
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tTanggal=$_PT['tTanggal'];
			$tTanggal2=$_PT['tTanggal2'];
			
			$errtTanggal=(!balikTanggal($tTanggal))?"Format Tanggal masih salah (DD/MM/YYYY)":((!balikTanggal($tTanggal2))?"Format Tanggal masih salah (DD/MM/YYYY)":((dateDiff(balikTanggal($tTanggal2),balikTanggal($tTanggal))<0)?"Periksa isian Tanggal":
								((getValue("id_danaanggaran","t_danaanggaran","((tgl_start between '".balikTanggal($tTanggal)."' and '".balikTanggal($tTanggal2)."') or (tgl_finish between '".balikTanggal($tTanggal)."' and '".balikTanggal($tTanggal2)."')) and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' and tgl_start not in (select tgl_start from t_danaanggaran where id_danaanggaran='".$tId."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."') order by id_danaanggaran asc limit 0,1"))?"Periode Tanggal telah digunakan data yang lain":"")));
			
			if(!$errtTanggal) {				
				if($tId) {
					queryDb("update t_danaanggaran 
									set tgl_start='".balikTanggal($tTanggal)."',tgl_finish='".balikTanggal($tTanggal2)."' 
								where concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."' 
										 and tgl_start in (select tgl_start from (select tgl_start from t_danaanggaran where id_danaanggaran='".$tId."' and concat(id_unit1,'.',id_unit2,'.',id_unit3)='".$_SESSION['unit']."')x)");
				}
				else {
					$tId=(getValue("id_danaanggaran","t_danaanggaran","1=1 order by id_danaanggaran desc limit 0,1")*1)+1;
					queryDb("insert into t_danaanggaran(id_danaanggaran,id_unit1,id_unit2,id_unit3,id_gl4,tgl_start,tgl_finish,nominal,tanggal,id_user) 
								values('".$tId."','".$_UNIT['id_unit1']."','".$_UNIT['id_unit2']."','".$_UNIT['id_unit3']."','0','".balikTanggal($tTanggal)."','".balikTanggal($tTanggal2)."','0','".TANGGAL."','".$_SESSION['user']."')");
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:200px;text-align:center; }
#titleTop ol li:nth-child(3) , #list ul li:nth-child(3) { width:200px;text-align:center; }
#list ul li:nth-child(4) { text-align:right; }
#titleTop ol li { text-align:center; }

table input[type=text], table input[type=password],  table select { width:150px; }
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
                <tr>
                  <td>PERIODE</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tTanggal" id="tTanggal" value="<?=htmlentities($tTanggal)?>" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" onkeyup="hideFade('errtTanggal')" /> s/d
                    <input type="text" name="tTanggal2" id="tTanggal2" value="<?=htmlentities($tTanggal)?>" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" onkeyup="hideFade('errtTanggal')" />
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
    <ol class="tab"><li><a href="#">DANA ANGGARAN</a><a href="#" class="off">SETTING NILAI BIAYA & PENDAPATAN</a></li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php
        $listTitle=array("tgl_start#Tgl Mulai","tgl_finish#Tgl Selesai","nilai#Nilai Anggaran");
		
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
	$sql="select id,tgl_start,tgl_finish,nilai from (
					select min(id_danaanggaran) as id,tgl_start,tgl_finish,sum(nominal) as nilai,max(tanggal) as tgl from t_danaanggaran d
						where concat(d.id_unit1,'.',d.id_unit2,'.',d.id_unit3)='".$_SESSION['unit']."' group by tgl_start,tgl_finish
				) x
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				order by ".(($_GT['order'])?$_GT['order'].",tgl desc":"tgl desc");
				
	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['id']."\" value=\"".$b['id']."\" />";
			echo "<input type=\"hidden\" id=\"tTanggal-".$b['id']."\" value=\"".str_replace("-","/",balikTanggal($b['tgl_start']))."\" />";
			echo "<input type=\"hidden\" id=\"tTanggal2-".$b['id']."\" value=\"".str_replace("-","/",balikTanggal($b['tgl_finish']))."\" />";
			echo "<ul id=\"".$b['id']."\" onclick=\"listFocus(this,'edit=1,del=1');\" ondblclick=\"_URI['edit']='".$b['id']."';_URI['order']='".$_GT['order']."';goAddress('edit,order,hal,sort,search','dana_anggaran_set.php')\"><li>".$paging['start'].".</li><li>".tglIndo($b['tgl_start'])."</li><li>".tglIndo($b['tgl_finish'])."</li><li>".showRupiah2($b['nilai'])."</li></ul>";
		
			if($_GT['edit']==$b['id']) $jsEdit="listFocus(elm('".$b['id']."'),'edit=1,del=1');";
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
    	  <button id="btn-new" class="icon-new" onclick="newData('tId');setTanggal();">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tTanggal,tTanggal2');setTanggal(elm('tTanggal').value,elm('tTanggal2').value);">EDIT</button>
    	  <button id="btn-del" class="icon-del disabled" onclick="delData('del,hal,sort,search,order');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	function setTanggal(v,v2) {
		v=(v)?v:"<?=date("1/m/Y")?>";
		v2=(v2)?v2:"<?=date("d/m/Y")?>";
		
		$(function() {
			$("#tTanggal").datepicker();
			$("#tTanggal").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal").datepicker("setDate",v);
			$("#tTanggal2").datepicker();
			$("#tTanggal2").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal2").datepicker("setDate",v2);
		});
	}
	
	setTanggal("<?=$tTanggal?>","<?=$tTanggal2?>");
	
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
