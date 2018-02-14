<?php
	session_start();
	
	require "includes/masterConfig.php";
	
	$titleHTML="Pilih Unit Kerja";
	
	if(!$_SESSION['user'] || $_SESSION['waktu']<time()) {
		session_destroy();
		
		header("location:index.php".(($_SESSION['waktu']<time())?"?timeout=1":""));
		exit;
	}
	else {
		$_SESSION['waktu']=time()+1800;
		
		$_UNITS=array();
		$a=queryDb("select concat(u.id_unit1,'.',u.id_unit2,'.',u.id_unit3) as id_unit,u.nama from t_user s
							inner join t_entitas_unit e on e.id_entitas=s.id_entitas
							inner join t_unit3 u on u.id_unit1=e.id_unit1 and u.id_unit2=e.id_unit2 and u.id_unit3=e.id_unit3
						where s.id_user='".$_SESSION['user']."'
						order by u.id_unit1,u.id_unit2,u.id_unit3");
						
		while($b=mysql_fetch_array($a)) {
			$_UNITS[$b['id_unit']]=$b['nama'];
		}
		
		if(count($_UNITS)==1) {
			reset($_UNITS);
			$_SESSION['unit']=key($_UNITS);
		}
		
		if($_PT['tSimpan']) {
			$tUnit=$_PT['tUnit'];
			
			$errtUnit=(!$tUnit)?"Data Unit belum dipilih":((!$_UNITS[$tUnit])?"Data Unit tidak terdaftar":"");
			
			if(!$errtUnit) {				
				if($_GT['direct']) {
					$_SESSION['unit']=$tUnit;
					//header("location:".$_GT['direct']);
					//exit;
				}
				else {
					$_SESSION['unit']=$tUnit;
					$errtUnit="Data Unit sudah berhasil disimpan.";
				}
			}
		}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$titleHTML?></title>
<script src="js/function.js" type="text/javascript"></script>
<script src="js/effect.js" type="text/javascript"></script>
<script src="js/submain.js" type="text/javascript"></script>
<script language="javascript">if(self.document.location==top.document.location) self.document.location="index.php?logout=1";</script>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#input table ul li { text-align:left;width:auto; }
#input table ul li:first-child { width:30px; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<div id="input" style="display:block;background:none;">
	<ul class="full">
        <li style="text-align:center;">
          <form action="" target="_self" method="post" onsubmit="showFade('load');">
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <th colspan="3" class="title"><?=($unitCount==1)?"UNIT KERJA YANG SEDANG AKTIF":strtoupper($titleHTML)?></th>
                </tr>
                <tr>
                  <td colspan="3"><div id="errtUnit" class="err" align="center"><?=$errtUnit?>&nbsp;</div></td>
                </tr>
                <tr>
                  <td colspan="3">
                    <div style="height:200px;overflow:auto;width:500px;" class="cMenu"><div class="sMenu" style="height:auto;">
                    <?php
						reset($_UNITS);
						while(list($a,$b)=each($_UNITS)) {
							$checked=($a==$_SESSION['unit'])?"checked='checked'":"";
                            echo "<div><ul><li><input type=\"radio\" name=\"tUnit\" value=\"".$a."\" ".$checked." /></li><li>[".$a."] ".$b."</li></ul></div>";
                        }
                    ?>
                    	</div></div>
                    </td>
                </tr>
                <tr>
                  <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                  <th colspan="3"><?=(count($_UNITS)>1)?"<input name=\"tSimpan\" type=\"submit\" class=\"icon-save\" id=\"tSimpan\" value=\"SIMPAN\" />":""?></th>
                </tr>
              </table>
          </form>
        </li>
    </ul>
</div>
<script language="javascript">
	parent.elm('dAkun').innerHTML="<div>Hi, <?=$_SESSION['user']?><?=(($_SESSION['unit'])?"<br />[".$_SESSION['unit']."] ".$_UNITS[$_SESSION['unit']]:"")?></div>";
	parent.elm('bodyId').style.backgroundImage="url(images/header<?=($_SESSION['unit'] && file_exists("images/header_".$_SESSION['unit'].".png"))?("_".$_SESSION['unit']):""?>.png)";
	<?php
		if($_PT['tSimpan'] && !$errtUnit && $_GT['direct']) {
			echo "document.location.href='".$_GT['direct']."';";
		}
	?>
	hideFade("load");
</script>
</body>
</html>
<?php } ?>
