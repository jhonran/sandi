<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='gl1.php'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		if($_GT['del']) {
			if(!getValue("1","t_gl4","id_gl3='".$_GT['del']."' limit 0,1") && $_GT['id1']!="3") {
				queryDb("delete from t_gl3 where id_gl3='".$_GT['del']."'");
				header("location:?order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdGl=$_PT['tIdGl'];
			$tNama=$_PT['tNama'];
			
			$errtIdGl=(!$tIdGl || !is_numeric($tIdGl) || $tIdGl<1 || $tIdGl>999)?"Data ID ".$_IST['gl1.php']." masih kosong atau bukan angka":((getValue("1","t_gl3","id_gl3='".$_GT['id2'].substr($tIdGl+1000,1,3)."' and id_gl3<>'".$tId."' limit 1"))?"ID ".$_IST['gl1.php']." sudah terdaftar":"");
			$errtNama=(!$tNama)?"Data Nama masih kosong":((getValue("1","t_gl3","nama='".$tNama."' and id_gl3<>'".$tId."' limit 1"))?"Nama ".$_IST['gl1.php']." sudah terdaftar":"");
			
			if(!$errtIdGl && !$errtNama && $_GT['id1']!="3") {				
				$tIdGl=substr($tIdGl+1000,1,3);
						
				if($tId) {
					queryDb("update t_gl3 set id_gl3='".$_GT['id2'].$tIdGl."',nama='".$tNama."',tanggal='".TANGGAL."' where id_gl3='".$tId."' and id_gl2='".$_GT['id2']."'");
				}
				else {
					queryDb("insert into t_gl3(id_gl1,id_gl2,id_gl3,nama,tanggal) values('".$_GT['id1']."','".$_GT['id2']."','".$_GT['id2'].$tIdGl."','".$tNama."','".TANGGAL."')");
				}
				
				header("location:?edit=".bs64_e($_GT['id2'].$tIdGl)."&id1=".bs64_e($_GT['id1'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2']));
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
#titleTop ol li:nth-child(1) , #list ul li:nth-child(1) { width:30px;text-align:center; }
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:110px;text-align:center; }
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
                  <td>ID LEVEL III</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" class="readonly" readonly="readonly" style="width:50px;" value="<?=$_GT['id2']?>" /><input type="text" name="tIdGl" id="tIdGl" required="required" value="<?=htmlentities($tIdGl)?>" maxlength="3" style="width:350px;" onkeyup="hideFade('errtIdGl');" />
                    <div id="errtIdGl" class="err"><?=$errtIdGl?></div>
                  </td>
                </tr>
                <tr>
                  <td>NAMA</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="tNama" id="tNama" maxlength="100" value="<?=htmlentities($tNama)?>" required="required" onkeyup="hideFade('errtNama');" />
                    <div id="errtNama" class="err"><?=$errtNama?></div>
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
    <ol class="tab">
		<li>
		<a href="gl1.php?edit=<?=bs64_e($_GT['id1'])?>&order=<?=bs64_e($_GT['order1'])?>" class="on">LEVEL I</a>
		<a href="gl2.php?edit=<?=bs64_e($_GT['id2'])?>&order=<?=bs64_e($_GT['order2'])?>&id1=<?=bs64_e($_GT['id1'])?>&order1=<?=bs64_e($_GT['order1'])?>" class="on">LEVEL II</a>
		<a href="#">LEVEL III</a>
		<a href="#" class="off">LEVEL IV</a>
		</li>
	</ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("g.id_gl3#ID LEVEL III","g.nama#Nama");
		
		if(is_array($listTitle)) {
			reset($listTitle);
			foreach($listTitle as $value) {
				$arrayValue=explode("#",$value);
				echo "<li ".(($arrayValue[0])?("id=\"".$arrayValue[0]."\" onclick=\"return goOrder('order,id1,order1,id2,order2',this.id);\" ".(($_GT['order']==($arrayValue[0]." asc"))?"class=\"asc\"":(($_GT['order']==($arrayValue[0]." desc"))?"class=\"desc\"":""))." title=\"Urutkan berdasarkan ".strtoupper($arrayValue[1])."\""):"class=\"only\"")."><span>".strtoupper($arrayValue[1])."</span></li>";
			}
		}
		?>
    </ol>
</div>
<div id="list">
	<?php
	$row=50;
	$page=8;
	$sql="select g.id_gl3,g.nama,if(min(l.id_gl4) is null,1,0) as stat_delete from t_gl3 g
					left join t_gl4 l on l.id_gl3=g.id_gl3
				where g.id_gl2='".$_GT['id2']."'
				group by g.id_gl3,g.nama
				order by ".(($_GT['order'])?$_GT['order'].",g.tanggal desc":"g.tanggal desc");
				
	$param="order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&order1=".bs64_e($_GT['order1'])."&id2=".bs64_e($_GT['id2'])."&order2=".bs64_e($_GT['order2']);
	
	$paging=paging($sql,$row,$page,$_GT['hal'],$param);
	
	if(is_array($paging)) {		
		$a=queryDb($sql." limit ".$paging['start'].",".$row);
		while($b=mysql_fetch_array($a)) {
			$paging['start']++;
			echo "<input type=\"hidden\" id=\"tId-".$b['id_gl3']."\" value=\"".$b['id_gl3']."\" />";
			echo "<input type=\"hidden\" id=\"tIdGl-".$b['id_gl3']."\" value=\"".substr($b['id_gl3'],strlen($_GT['id2']),3)."\" />";
			echo "<input type=\"hidden\" id=\"tNama-".$b['id_gl3']."\" value=\"".$b['nama']."\" />";
			echo "<ul id=\"".$b['id_gl3']."\" onclick=\"listFocus(this,'edit=1,del=".$b['stat_delete']."')\" ondblclick=\"_URI['id3']='".$b['id_gl3']."';_URI['order3']=_URI['order'];goAddress('id1,order1,id2,order2,id3,order3','gl4.php')\"><li>".$paging['start'].".</li><li>".$b['id_gl3']."</li><li>".$b['nama']."</li></ul>";
		
			if($_GT['edit']==$b['id_gl3']) $jsEdit="listFocus(elm('".$b['id_gl3']."'),'edit=1,del=".$b['stat_delete']."');";
		}
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li class="r" style="width:auto;<?=($_GT['id1']=="3")?"display:none;":""?>">
    	  <button id="btn-new" class="icon-new" onclick="newData('tIdGl,tId,tNama');">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tIdGl,tId,tNama');">EDIT</button>
    	  <button id="btn-del" class="icon-del disabled" onclick="delData('del,order,id1,order1,id2,order2');">HAPUS</button>
    	</li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtIdGl || $errtNama)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>