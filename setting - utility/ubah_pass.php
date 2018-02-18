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
		
		if($_PT['tSimpan']) {
			$tPassword=$_PT['tPassword'];
			$tPasswordBaru=$_PT['tPasswordBaru'];
			$tCPasswordBaru=$_PT['tCPasswordBaru'];
			
			$errtPassword=(!getValue("1","t_user","id_user='".$_SESSION['user']."' and pass=PASSWORD('".$tPassword."') limit 1"))?"Password anda salah":"";
			$errtPasswordBaru=(!preg_match(VALCHAR2,$tPasswordBaru))?"Password Baru masih kosong atau format salah":"";
			$errtCPasswordBaru=($tPasswordBaru!=$tCPasswordBaru)?"Konfirmasi Password Baru tidak sesuai":"";
			
			if(!$errtPassword && !$errtPasswordBaru && !$errtCPasswordBaru) {
				if(queryDb("update t_user set pass=PASSWORD('".$tPasswordBaru."') where id_user='".$_SESSION['user']."' and pass=PASSWORD('".$tPassword."')")) {				
					session_destroy();
					
					header("location:../index.php?ed=1");
					exit();
				}
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
table input[type=text], table input[type=password],  table select { width:400px; }
</style>
</head>

<body>
<div id="load">
	<ol class="full"><li><img src="../images/load.gif" border="0" alt="Please wait..." /></li></ol>
</div>
<div id="input" style="display:block;background:none;">
	<ul class="full">
        <li style="text-align:center;">
          <form action="" target="_self" method="post" onsubmit="showFade('load');">
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <th colspan="3" class="title"><?=strtoupper($titleHTML)?></th>
                </tr>
                <tr>
                  <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                  <td>PASSWORD LAMA</td>
                  <td>:</td>
                  <td><input type="password" name="tPassword" id="tPassword" maxlength="12" onkeyup="hideFade('errtPassword')" />
                    <div id="errtPassword" class="err"><?=$errtPassword?></div>
                   </td>
                </tr>
                <tr>
                  <td>PASSWORD BARU</td>
                  <td>:</td>
                  <td><input type="password" name="tPasswordBaru" id="tPasswordBaru" maxlength="12" onkeyup="hideFade('errtPasswordBaru')" />
                    <div id="errtPasswordBaru" class="err"><?=$errtPasswordBaru?></div>
                   </td>
                </tr>
                <tr>
                  <td>ULANGI PASSWORD BARU</td>
                  <td>:</td>
                  <td><input type="password" name="tCPasswordBaru" id="tCPasswordBaru" maxlength="12" onkeyup="hideFade('errtCPasswordBaru')" />
                    <div id="errtCPasswordBaru" class="err"><?=$errtCPasswordBaru?></div>
                   </td>
                </tr>
                <tr>
                  <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                  <th colspan="3"><input name="tSimpan" type="submit" class="icon-save" id="tSimpan" value="SIMPAN" /></th>
                </tr>
              </table>
          </form>
        </li>
    </ul>
</div>
<script language="javascript">
	hideFade("load");
</script>
</body>
</html>
<?php } ?>