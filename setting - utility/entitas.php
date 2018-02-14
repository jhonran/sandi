<?php
	session_start();
	
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
		
		if($_GT['del']) {
			if(!getValue("1","t_user","id_entitas='".$_GT['del']."' limit 0,1")) {
				queryDb("delete from t_entitas where id_entitas='".$_GT['del']."'");
			}
			
			header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
			exit;
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdEntitas=$_PT['tIdEntitas'];
			$tNama=$_PT['tNama'];
			
			for($i=0;$i<count($_PT['tUnit']);$i++) {
				if($_PT['tUnit'][$i]) $tUnit[$_PT['tUnit'][$i]]=$_PT['tUnit'][$i];
			}
			
			$errtIdEntitas=(!$tIdEntitas || !is_numeric($tIdEntitas) || ($tIdEntitas*1)<1 || ($tIdEntitas*1)>999999)?"Data ID Entitas masih kosong atau bukan angka":((getValue("1","t_entitas","id_entitas='".substr((($tIdEntitas*1)+1000000),1,6)."' and id_entitas<>'".$tId."' limit 1"))?"ID Entitas sudah terdaftar":"");
			$errtNama=(!$tNama)?"Data Nama masih kosong":((getValue("1","t_entitas","nama='".$tNama."' and id_entitas<>'".$tId."' limit 1"))?"Nama Entitas sudah terdaftar":"");
			$errtUnit=($i<=0)?"Data Unit belum dipilih":"";
			
			if(!$errtIdEntitas && !$errtNama && !$errtUnit) {
				$tIdEntitas=substr((($tIdEntitas*1)+1000000),1,6);	
				if($tId) {
					queryDb("update t_entitas set id_entitas='".$tIdEntitas."',nama='".$tNama."',nama='".$tNama."',tanggal='".TANGGAL."' where id_entitas='".$tId."'");
				}
				else {
					queryDb("insert into t_entitas(id_entitas,nama,tanggal) values('".$tIdEntitas."','".$tNama."','".TANGGAL."')");
				}
				
				$unitWhere=implode("','",$tUnit);
				
				queryDb("insert into t_entitas_unit(id_entitas,id_unit1,id_unit2,id_unit3) 
								select '".$tIdEntitas."',id_unit1,id_unit2,id_unit3 from t_unit3 where concat(id_unit1,'.',id_unit2,'.',id_unit3) in ('".$unitWhere."')");
				
				header("location:?edit=".bs64_e($tIdEntitas));
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
<script language="javascript">if(self.document.location==top.document.location) self.document.location="../index.php?logout=1";</script>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#input table ul li { text-align:left;width:auto; }
#input table ul li:first-child { width:30px; }
#titleTop ol li:nth-child(1) , #list ul li:nth-child(1) { width:30px;text-align:center; }
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:110px;text-align:center; }
#titleTop ol li { text-align:center; }

table input[type=text] { width:400px; }
table div.cMenu div.sMenu { height:auto; }
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
                  <td>ID ENTITAS</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdEntitas" id="tIdEntitas" required="required" value="<?=htmlentities($tIdEntitas)?>" maxlength="6" onkeyup="hideFade('errtIdEntitas');" />
                    <div id="errtIdEntitas" class="err"><?=$errtIdEntitas?></div>
                  </td>
                </tr>
                <tr>
                  <td>NAMA</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNama" id="tNama" maxlength="200" value="<?=htmlentities($tNama)?>" required="required" onkeyup="hideFade('errtNama')" />
                    <div id="errtNama" class="err"><?=$errtNama?></div>
                  </td>
                </tr>
                <tr>
                  <td colspan="3">
                    <div style="height:250px;overflow:auto;" class="cMenu" onclick="hideFade('errtUnit')" >
                    <?php
                        $j=$k=0;
						$statCheckAll=1;
						$a=queryDb("select u3.id_unit1,u1.nama as nama1,u3.id_unit2,u2.nama as nama2,u3.id_unit3,u3.nama as nama3 
											from t_unit3 u3
												inner join t_unit2 u2 on u2.id_unit1=u3.id_unit1 and u2.id_unit2=u3.id_unit2
												inner join t_unit1 u1 on u1.id_unit1=u3.id_unit1
											order by u3.id_unit1,u3.id_unit2,u3.id_unit3");
						while($b=mysql_fetch_array($a)) {							
							if(!$checked) $statCheckAll=0;
							
							if($unit1!=$b['id_unit1']) {
								$j++;
								if($_PT['tSimpan']) $checked=($tUnit[$b['id_unit1']])?"checked='checked'":"";
								
								if($unit1) echo "</div>";
								echo "<div><ul>
										<li><input type=\"checkbox\" name=\"tUnit[]\" id=\"tUnit-".$j."\" value=\"".$b['id_unit1']."\" ".$checked." onclick=\"checkSubAll(this.checked,'tUnit[]','tUnit-".$j."')\" /></li>
										<li onclick=\"menuSub('divSub".$j."',getHeightChild('divSub".$j."'))\">".$b['id_unit1']." ".$b['nama1']."</li>
									  </ul></div>
									  <div class=\"sMenu\" id=\"divSub".$j."\">";
							}
							
							if(($unit1.$unit2)!=($b['id_unit1'].$b['id_unit2'])) {
								$k++;
								if($_PT['tSimpan']) $checked=($tUnit[$b['id_unit1'].".".$b['id_unit2']])?"checked='checked'":"";
								
								echo "<div style='padding-left:45px;'><ul>
										<li><input type=\"checkbox\" name=\"tUnit[]\" id=\"tUnit-".$j."-".$k."\" alt=\"tUnit-".$j."\" value=\"".$b['id_unit2']."\" ".$checked." onclick=\"checkSubAll(this.checked,'tUnit[]','tUnit-".$j."-".$k."')\" /></li>
										<li>".$b['id_unit1'].".".$b['id_unit2']." ".$b['nama2']."</li>
									  </ul></div>";
							}
							
							if(($unit1.$unit2.$unit3)!=($b['id_unit1'].$b['id_unit2'].$b['id_unit3'])) {
								if($_PT['tSimpan']) $checked=($tUnit[$b['id_unit1'].".".$b['id_unit2'].".".$b['id_unit3']])?"checked='checked'":"";
								
								echo "<div style='padding-left:65px;'><ul>
										<li><input type=\"checkbox\" name=\"tUnit[]\" alt=\"tUnit-".$j."-".$k."\" value=\"".$b['id_unit1'].".".$b['id_unit2'].".".$b['id_unit3']."\" ".$checked."  onclick=\"uncheckSubAll('tUnit[]','tUnit-".$j."-".$k."')\"/></li>
										<li>".$b['id_unit1'].".".$b['id_unit2'].".".$b['id_unit3']." ".$b['nama3']."</li>
									  </ul></div>";
							}
							
							$unit1=$b['id_unit1'];
							$unit2=$b['id_unit2'];
							$unit3=$b['id_unit3'];				
						}
						
						if($unit1) echo "</div>";
                    ?>
                    </div>
                    </td>
                </tr>
                <tr>
                  <td colspan="3">
                    <input type="checkbox" id="tUnit[]All" onclick="checkAll(this.checked,'tUnit[]')" <?=($statCheckAll==1)?"checked='checked'":""?> /> Check semua
                    <div id="errtUnit" class="err"><?=$errtUnit?></div></td>
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
        $listTitle=array("a.id_entitas#ID Entitas","a.nama#Nama");
		
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
	$sql="select a.id_entitas,a.nama,group_concat(DISTINCT concat(e.id_unit1,'.',e.id_unit2,'.',e.id_unit3) ORDER BY e.id_unit1,e.id_unit2,e.id_unit3 SEPARATOR ',') as unit,if(min(u.id_entitas) is null,1,0) as stat_delete from t_entitas a
						left join t_entitas_unit e on e.id_entitas=a.id_entitas
						left join t_user u on u.id_entitas=a.id_entitas
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				 group by a.id_entitas,a.nama order by ".(($_GT['order'])?$_GT['order'].",a.tanggal desc":"a.tanggal desc");
	
	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['id_entitas']."\" value=\"".$b['id_entitas']."\" />";
			echo "<input type=\"hidden\" id=\"tIdEntitas-".$b['id_entitas']."\" value=\"".$b['id_entitas']."\" />";
			echo "<input type=\"hidden\" id=\"tNama-".$b['id_entitas']."\" value=\"".$b['nama']."\" />";
			echo "<input type=\"hidden\" id=\"tUnit[]-".$b['id_entitas']."\" value=\"".$b['unit']."\" />";
			echo "<ul id=\"".$b['id_entitas']."\" onclick=\"listFocus(this,'edit=1,del=".$b['stat_delete']."')\"><li>".$paging['start'].".</li><li>".$b['id_entitas']."</li><li>".$b['nama']."</li></ul>";
			
			if($_GT['edit']==$b['id_entitas']) $jsEdit="listFocus(elm('".$b['id_entitas']."'),'edit=1,del=".$b['stat_delete']."');";
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
    	  <button id="btn-new" class="icon-new" onclick="newData('tId,tIdEntitas,tNama,tUnit[]');">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tIdEntitas,tNama,tUnit[]');">EDIT</button>
    	  <button id="btn-del" class="icon-del disabled" onclick="delData('del,hal,sort,search,order');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtIdEntitas || $errtNama || $errtUnit)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
