<?php
	session_start();
	
	require "../includes/masterConfig.php";
	
	$titleHTML=getValue("s.judul","t_user u
											inner join t_akses_menu_sub h on h.id_akses=u.id_akses
											inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1' and s.page='arus_kas_gl.php'",
										"u.`id_user`='".$_SESSION['user']."'");
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time() || !$titleHTML) {
		session_destroy();
		
		header("location:../index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		$_EXC=array();
		$a=queryDb("(select distinct id_aruskas_sub from t_gl4 where id_aruskas='".$_GT['id1']."') union
						(select distinct id_aruskas_sub from t_glt where id_aruskas='".$_GT['id1']."') union
						(select distinct id_aruskas_sub from t_glu where id_aruskas='".$_GT['id1']."') union
						(select distinct id_aruskas_sub from t_gluh where id_aruskas='".$_GT['id1']."')");
		while($b=mysql_fetch_array($a)) {
			$_EXC[$b['id_aruskas_sub']]=1;
		}
		
		$noUrut=1;
		$a=queryDb("select id_aruskas_sub from t_aruskas_sub where id_aruskas='".$_GT['id1']."' order by id_aruskas_sub");
		while($b=mysql_fetch_array($a)){
			if(($b['id_aruskas_sub']*1)==$noUrut) $noUrut++;
			else break;
		}
		
		if($_GT['del']) {
			if(!$_EXC[$_GT['del']]) {
				queryDb("delete from t_aruskas_sub where id_aruskas_sub='".$_GT['del']."' and id_aruskas='".$_GT['id1']."'");
				header("location:?order=".bs64_e($_GT['order'])."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1']));
				exit;
			}
		}
		
		if($_PT['tSimpan']) {
			$tId=$_PT['tId'];
			$tIdUnit2=$_PT['tIdUnit2'];
			$tNama=$_PT['tNama'];
			
			$errtNama=(!$tNama)?"Data Nama masih kosong":((getValue("1","t_aruskas_sub","nama='".$tNama."' and id_aruskas_sub<>'".$tId."' and id_aruskas='".$_GT['id1']."' limit 1"))?"Nama Unit II sudah terdaftar":"");
				
			if(!$errtNama) {				
				if($tId) {
					queryDb("update t_aruskas_sub set nama='".$tNama."',tanggal='".TANGGAL."' where id_aruskas_sub='".$tId."' and id_aruskas='".$_GT['id1']."'");
				}
				else {
					$tId=substr(($noUrut+100),1,2);
					queryDb("insert into t_aruskas_sub(id_aruskas_sub,id_aruskas,nama,tanggal) values('".$tId."','".$_GT['id1']."','".$tNama."','".TANGGAL."')");
				}
				
				header("location:?edit=".bs64_e($tId)."&id1=".bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order1=".bs64_e($_GT['order1']));
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
#titleTop ol li:nth-child(2) , #list ul li:nth-child(2) { width:120px;text-align:center; }
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
                  <td>ID AKTIFITAS</td>
                  <td>:</td>
                  <td>
                  	<input type="hidden" name="tId" id="tId" value="<?=htmlentities($tId)?>" />
                    <input type="text" name="tIdUnit2" id="tIdUnit2" value="<?=htmlentities($tIdUnit2)?>" maxlength="2" class="readonly" readonly="readonly" />
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
    <ol class="tab"><li>
    	<a href="arus_kas_gl.php?edit=<?=bs64_e($_GT['id1'])."&hal=".bs64_e($_GT['hal'])."&sort=".bs64_e($_GT['sort'])."&search=".bs64_e($_GT['search'])."&order=".bs64_e($_GT['order1'])?>" class="on">KATEGORI ARUS KAS</a>
        <a href="#">KATEGORI AKTIFITAS</a>
        <a href="#" class="off"><?=strtoupper($_IST['gl1.php'])?></a>
    </li></ol>
	<ol class="title">
    	<li>NO</li>
        <?php        
        $listTitle=array("p.id_aruskas_sub#ID Aktifitas","p.nama#Nama");
		
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
	$sql="select p.id_aruskas_sub,p.nama from t_aruskas_sub p where p.id_aruskas='".$_GT['id1']."' order by ".(($_GT['order'])?$_GT['order'].",p.tanggal desc":"p.tanggal desc");
					
	$a=queryDb($sql);
	while($b=mysql_fetch_array($a)) {
		$paging['start']++;
		echo "<input type=\"hidden\" id=\"tId-".$b['id_aruskas_sub']."\" value=\"".$b['id_aruskas_sub']."\" />";
		echo "<input type=\"hidden\" id=\"tIdUnit2-".$b['id_aruskas_sub']."\" value=\"".$_GT['id1'].".".$b['id_aruskas_sub']."\" />";
		echo "<input type=\"hidden\" id=\"tNama-".$b['id_aruskas_sub']."\" value=\"".$b['nama']."\" />";
		echo "<ul id=\"".$b['id_aruskas_sub']."\" onclick=\"listFocus(this,'edit=1,del=".(!$_EXC[$b['id_aruskas_sub']])."')\" ondblclick=\"_URI['id2']='".$b['id_aruskas_sub']."';_URI['order2']='".$_GT['order']."';goAddress('id1,hal,sort,search,order1,id2,order2','arus_kas_gl_sub_set.php')\"><li>".$paging['start'].".</li><li>".$_GT['id1'].".".$b['id_aruskas_sub']."</li><li>".$b['nama']."</li></ul>";
	
		if($_GT['edit']==$b['id_aruskas_sub']) $jsEdit="listFocus(elm('".$b['id_aruskas_sub']."'),'edit=1,del=".(!$_EXC[$b['id_aruskas_sub']])."');";
	}
	?>
</div>
<div id="titleBottom">
	<ol>
    	<li class="r" style="width:auto;">
    	  <button id="btn-new" class="icon-new" onclick="elm('tIdUnit2').value='<?=$_GT['id1'].".".substr(($noUrut+100),1,2)?>';newData('tNama,tId');">TAMBAH</button>
    	  <button id="btn-edit" class="icon-edit disabled" onclick="editData('tNama,tId,tIdUnit2');">EDIT</button>
    	  <button id="btn-del" class="icon-del disabled" onclick="delData('del,order,id1,hal,sort,search,order1');">HAPUS</button>
        </li>
    </ol>
</div>
<script language="javascript">
	if(elm('list')) {
		if(elm('titleTop')) elm('list').style.paddingTop=elm('titleTop').offsetHeight+"px";
		if(elm('titleBottom')) elm('list').style.paddingBottom=elm('titleBottom').offsetHeight+"px";
	}
	hideFade("load","<?=($errtNama)?"showFade('input')":""?>");	
	<?=$jsEdit?>
</script>
</body>
</html>
<?php } ?>