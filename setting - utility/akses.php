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
			if(!getValue("1","t_user","id_akses='".$_GT['del']."' limit 0,1")) {
				queryDb("delete from t_akses where id_akses='".$_GT['del']."'");
			}
			
			header("location:?hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']));
			exit;
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdAkses=$_PT['tIdAkses'];
			$tNama=$_PT['tNama'];
			
			for($i=0;$i<count($_PT['tMenu']);$i++) {
				if($_PT['tMenu'][$i]) $tMenu[$_PT['tMenu'][$i]]=$_PT['tMenu'][$i];
			}
			
			$errtIdAkses=(!$tIdAkses || !is_numeric($tIdAkses) || ($tIdAkses*1)<1 || ($tIdAkses*1)>99)?"Data ID Akses masih kosong atau bukan angka":((getValue("1","t_akses","id_akses='".substr((($tIdAkses*1)+100),1,2)."' and id_akses<>'".$tId."' limit 1"))?"ID Akses sudah terdaftar":"");
			$errtNama=(!$tNama)?"Data Nama masih kosong":((getValue("1","t_akses","nama='".$tNama."' and id_akses<>'".$tId."' limit 1"))?"Nama Akses sudah terdaftar":"");
			$errtMenu=($i<=0)?"Data Menu belum dipilih":"";
			
			if(!$errtIdAkses && !$errtNama && !$errtMenu) {
				$tIdAkses=substr((($tIdAkses*1)+100),1,2);	
				if($tId) {
					queryDb("update t_akses set id_akses='".$tIdAkses."',nama='".$tNama."',nama='".$tNama."',tanggal='".TANGGAL."' where id_akses='".$tId."'");
				}
				else {
					queryDb("insert into t_akses(id_akses,nama,tanggal) values('".$tIdAkses."','".$tNama."','".TANGGAL."')");
				}
				
				$menuWhere=implode("','",$tMenu);
				queryDb("insert into t_akses_menu_sub(id_akses,id_menu_sub) 
								select '".$tIdAkses."',id_menu_sub from t_menu_sub where id_menu_sub in ('".$menuWhere."')");
								
				header("location:?edit=".bs64_e($tIdAkses));
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
                  <td>ID AKSES</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdAkses" id="tIdAkses" required="required" value="<?=htmlentities($tIdAkses)?>" maxlength="2" onkeyup="hideFade('errtIdAkses');" />
                    <div id="errtIdAkses" class="err"><?=$errtIdAkses?></div>
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
                    <div style="height:250px;overflow:auto;" class="cMenu" onclick="hideFade('errtMenu')" >
                    <?php
                        $i=0;
                        $statCheckAll=1;
                        $a=queryDb("select id_menu,nama from t_menu where status='1' order by id_menu");
                        while($b=mysql_fetch_array($a)) {
                            $i++;
                            echo "<div onclick=\"menuSub('divSub".$i."',getHeightChild('divSub".$i."'))\">".$b['nama']."</div><div class=\"sMenu\" id=\"divSub".$i."\">";
                            $a2=queryDb("select s.id_menu_sub,s.nama
														from t_menu_sub s
													where s.id_menu='".$b['id_menu']."' and s.status='1' order by s.id_menu,s.id_menu_sub");
                            while($b2=mysql_fetch_array($a2)) {
                                if($_PT['tSimpan']) $checked=($tMenu[$b2['id_menu_sub']])?"checked='checked'":"";
                                if(!$checked) $statCheckAll=0;
								
                                echo "<div><ul>
										<li><input type=\"checkbox\" name=\"tMenu[]\" value=\"".$b2['id_menu_sub']."\" onclick=\"uncheckAll('tMenu[]');\" ".$checked." /></li>
										<li>".$b2['nama']."</li>
									  </ul></div>";
                            }
                            echo "</div>";
                        }
                    ?>
                    </div>
                    </td>
                </tr>
                <tr>
                  <td colspan="3">
                    <input type="checkbox" id="tMenu[]All" onclick="checkAll(this.checked,'tMenu[]')" <?=($statCheckAll==1)?"checked='checked'":""?> /> Check semua
                    <div id="errtMenu" class="err"><?=$errtMenu?></div></td>
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
        $listTitle=array("a.id_akses#ID Akses","a.nama#Nama");
		
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
	$sql="select a.id_akses,a.nama,group_concat(DISTINCT s.id_menu_sub ORDER BY s.id_menu_sub SEPARATOR ',') as menu,if(min(u.id_akses) is null,1,0) as stat_delete from t_akses a
						left join t_akses_menu_sub h on h.id_akses=a.id_akses
						left join t_menu_sub s on s.id_menu_sub=h.id_menu_sub
						left join t_user u on u.id_akses=a.id_akses
				".(($_GT['sort'] && $_GT['search'])?"where lower(".$_GT['sort'].") like '%".strtolower($_GT['search'])."%'":"")." 
				 group by a.id_akses,a.nama order by ".(($_GT['order'])?$_GT['order'].",a.tanggal desc":"a.tanggal desc");
	
	$param="sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['id_akses']."\" value=\"".$b['id_akses']."\" />";
			echo "<input type=\"hidden\" id=\"tIdAkses-".$b['id_akses']."\" value=\"".$b['id_akses']."\" />";
			echo "<input type=\"hidden\" id=\"tNama-".$b['id_akses']."\" value=\"".$b['nama']."\" />";
			echo "<input type=\"hidden\" id=\"tMenu[]-".$b['id_akses']."\" value=\"".$b['menu']."\" />";
			echo "<ul id=\"".$b['id_akses']."\" onclick=\"listFocus(this,'edit=1,del=".$b['stat_delete']."')\"><li>".$paging['start'].".</li><li>".$b['id_akses']."</li><li>".$b['nama']."</li></ul>";
			
			if($_GT['edit']==$b['id_akses']) $jsEdit="listFocus(elm('".$b['id_akses']."'),'edit=1,del=".$b['stat_delete']."');";
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
    	  <button id="btn-new" class="icon-new" onclick="newData('tId,tIdAkses,tNama,tMenu[]');">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tId,tIdAkses,tNama,tMenu[]');">EDIT</button>
    	  <button id="btn-del" class="icon-del disabled" onclick="delData('del,hal,sort,search,order');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtIdAkses || $errtNama || $errtMenu)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>
