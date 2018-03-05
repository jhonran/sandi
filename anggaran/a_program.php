<?php
	//session_start();
	error_reporting(0);
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='donatur.php'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	$_UNITS=array();
										
	$a=queryDb("select concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) as id_unit,u.nama from t_user s 
						inner join t_entitas_unit e on e.id_entitas=s.id_entitas
						inner join t_unit3 u on u.id_unit1=e.id_unit1 and u.id_unit2=e.id_unit2 and u.id_unit3=e.id_unit3
					where s.id_user='".$_SESSION['user']."'");
	
	while($b=mysql_fetch_array($a)) {
		$_UNITS[$b['id_unit']]=$b['nama'];
	}
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		if($_GT['del']) {
			if(!getValue("1","t_giat","id_program='".$_GT['del']."' and id_donatur='".$_GT['id1']."' limit 0,1")) {
				queryDb("delete from t_program where id_program='".$_GT['del']."' and id_donatur='".$_GT['id1']."'");
				header("location:?order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdProgram=$_PT['tIdProgram'];
			$tNama=$_PT['tNama'];
			$tTanggal=$_PT['tTanggal'];
			
			$errtNama=(!$tNama)?"Data Nama masih kosong":((getValue("1","t_program","nama='".$tNama."' and id_program<>'".$tId."' and id_donatur='".$_GT['id1']."' limit 1"))?"Nama Program sudah terdaftar":"");
			$errtTanggal=(!balikTanggal($tTanggal))?"Format Tanggal masih salah (DD/MM/YYYY)":((dateDiff(balikTanggal($tTanggal),TANGGAL2)<0)?"Tanggal tidak boleh lebih kecil dari sekarang":"");
				
			if(!$errtNama && !$errtTanggal) {				
				if($tId) {
					queryDb("update t_program set nama='".$tNama."',expired='".balikTanggal($tTanggal)."',tanggal='".TANGGAL."' where id_program='".$tId."' and id_donatur='".$_GT['id1']."'");
				}
				else {
					$tId=substr(((getValue("id_program","t_program","id_donatur='".$_GT['id1']."' order by id_program desc limit 0,1")*1)+101),1,2);
					queryDb("insert into t_program(id_program,id_donatur,nama,expired,tanggal) values('".$tId."','".$_GT['id1']."','".$tNama."','".balikTanggal($tTanggal)."','".TANGGAL."')");
				}
				
				header("location:?edit=".bs64_e($tId)."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1']));
				exit;
			}
		}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:120px;text-align:center; }
#titleTop ol li:nth-child(4) , #list ul li:nth-child(4) { width:140px;text-align:center; }
#titleTop ol li:nth-child(5) , #list ul li:nth-child(5) { width:120px; }
#list ul li:nth-child(5) { text-align:right; }
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
                <tr>
                  <td>ID PROGRAM</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdProgram" id="tIdProgram" value="<?=htmlentities($tIdProgram)?>" maxlength="2" class="readonly" readonly="readonly" />
                  </td>
                </tr>
                <tr>
                  <td>NAMA</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNama" id="tNama" maxlength="255" value="<?=htmlentities($tNama)?>" required="required" onkeyup="hideFade('errtNama');" />
                    <div id="errtNama" class="err"><?=$errtNama?></div>
                  </td>
                </tr>
                <tr>
                  <td>JATUH TEMPO</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tTanggal" id="tTanggal" value="<?=htmlentities($tTanggal)?>" maxlength="10" onkeydown="return DateFormat(this,event.keyCode)" autocomplete="off" required="required" onkeyup="hideFade('errtTanggal')" />
                    <div id="errtTanggal" class="err"><?=$errtTanggal?></div>
                  </td>
                </tr>
                <tr>
                  <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                  <th colspan="3">
                  	<input name="tSimpan" type="submit" class="icon-save" id="tSimpan" value="SIMPAN" />
                  	<button class="icon-close" onclick="hideFade('input'); return false;">TUTUP</button></th>
                </tr>
              </table>
          </form>
        </li>
    </ul>
</div>
<div id="titleTop">
  	<ol><li><?=strtoupper($titleHTML)?></li></ol>
    <ol class="tab"><li>
    	<a href="a_donatur.php?edit=<?=bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order1'])?>" class="on">DONATUR</a>
        <a href="#">PROGRAM</a>
        <a href="#" class="off">KEGIATAN</a>
        <a href="#" class="off">DETAIL</a>
        <a href="#" class="off">ANGGARAN</a>
    </li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("p.id_program#ID Program","p.nama#Nama","p.expired#Jatuh Tempo","nominal#Nominal");
		
		if(is_array($listTitle)) {
			reset($listTitle);
			foreach($listTitle as $value) {
				$arrayValue=explode("#",$value);
				echo "<li ".(($arrayValue[0])?("id=\"".$arrayValue[0]."\" onclick=\"return goOrder('order,id1,hal,sort,search,order1',this.id);\" ".(($_GT['order']==($arrayValue[0]." asc"))?"class=\"asc\"":(($_GT['order']==($arrayValue[0]." desc"))?"class=\"desc\"":""))." title=\"Urutkan berdasarkan ".strtoupper($arrayValue[1])."\""):"class=\"only\"")."><span>".strtoupper($arrayValue[1])."</span></li>";
			}
		}
		?>
    </ol>
</div>
<div id="list">
	<?php
	$paging['start']=0;
	$sql="select p.id_program,p.nama,p.expired,ifnull((select sum(nominal) from t_anggaran where id_donatur=p.id_donatur and id_program=p.id_program and concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".implode("','",array_keys($_UNITS))."')),0) as nominal,if(min(g.id_giat) is null,1,0) as stat_delete from t_program p
					left join t_giat g on g.id_program=p.id_program and g.id_donatur=p.id_donatur		
				where p.id_donatur='".$_GT['id1']."'
				group by p.id_program,p.nama,p.expired
				order by ".(($_GT['order'])?$_GT['order'].",p.tanggal desc":"p.tanggal desc");
					
	$a=queryDb($sql);
	while($b=mysql_fetch_array($a)) {
		$paging['start']++;
		echo "<input type=\"hidden\" id=\"tId-".$b['id_program']."\" value=\"".$b['id_program']."\" />";
		echo "<input type=\"hidden\" id=\"tIdProgram-".$b['id_program']."\" value=\"".$_GT['id1'].".".$b['id_program']."\" />";
		echo "<input type=\"hidden\" id=\"tNama-".$b['id_program']."\" value=\"".$b['nama']."\" />";
		echo "<input type=\"hidden\" id=\"tTanggal-".$b['id_program']."\" value=\"".str_replace("-","/",balikTanggal($b['expired']))."\" />";
		echo "<ul id=\"".$b['id_program']."\" onclick=\"listFocus(this,'edit=1,del=".$b['stat_delete']."')\" ondblclick=\"_URI['id2']='".$b['id_program']."';_URI['order2']='".$_GT['order']."';goAddress('id1,hal,sort,search,order1,id2,order2','a_giat.php')\"><li>".$paging['start'].".</li><li>".$_GT['id1'].".".$b['id_program']."</li><li>".$b['nama']."</li><li>".tglIndo($b['expired'],2)."</li><li>".showRupiah($b['nominal'])."</li></ul>";
	
		if($_GT['edit']==$b['id_program']) $jsEdit="listFocus(elm('".$b['id_program']."'),'edit=1,del=".$b['stat_delete']."');";
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li class="r" style="width:auto;">
    	  <button class="icon-new" onclick="elm('tIdProgram').value='<?=$_GT['id1'].".".substr(((getValue("id_program","t_program","id_donatur='".$_GT['id1']."' order by id_program desc limit 0,1")*1)+101),1,2)?>';setTanggal('<?=date("d/m/Y")?>');newData('tId,tNama');">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tNama,tTanggal,tIdProgram');setTanggal(elm('tTanggal').value)">EDIT</button>
    	  <button id="btn-del" class="icon-del disabled" onclick="delData('del,order,id1,hal,sort,search,order1');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	function setTanggal(v) {
		$(function() {
			$("#tTanggal").datepicker();
			$("#tTanggal").datepicker("option","dateFormat","dd/mm/yy");
			$("#tTanggal").datepicker("setDate",v);
		});
	}
		
	<?php
		if($tTanggal) echo "setTanggal('".$tTanggal."');";
	?>
		
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtNama || $errtTanggal)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
