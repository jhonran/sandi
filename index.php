<?php
	// *********************************************
	// File index.php
	// *********************************************
	error_reporting(0);
	//session_start();
	
	require "includes/masterConfig.php";
	
	if($_GET['logout'] || ($_SESSION['user'] && $_SESSION['waktu']<time())) {
		session_destroy();
		
		header("location:?".(($_SESSION['waktu']<time())?"timeout=1":""));
		exit();
	}
	else if($_POST['tLogin']) {
		if(!$_SESSION['login']) $_SESSION['login']=0;
		
		if($_SESSION['login']>=3 && $_SESSION['waktu']>time()) {
			$error="Anda tidak dapat melakukan Login saat ini !!!<br />Ulangi Login anda kembali beberapa saat lagi";
			$_SESSION['waktu']=time()+1800;
		}
		else {
			$_SESSION['login']=($_SESSION['login']<3)?($_SESSION['login']+1):1;
			$_SESSION['waktu']=time()+1800;
			
			$tUsername=stripslashes(strtolower($_POST['tUsername']));
			$tPassword=stripslashes($_POST['tPassword']);
			
			if(preg_match(VALCHAR2,$tUsername) && preg_match(VALCHAR2,$tPassword)) {	
				$nama=getValue("nama","t_user","id_user='".$tUsername."' and pass=PASSWORD('".$tPassword."')");
			}
			
			if($nama) {
				$_SESSION['user']=$tUsername;
				$_SESSION['nama']=$b['nama'];
				
				if(!getValue("1","t_index","tgl='".TANGGAL2."'")) {
					queryDb("CALL run_index('".TANGGAL2."')");
				}
			}
			else {
				$error="Username atau password anda salah !!!<br />".(($_SESSION['login']>=3)?"Ulangi Login anda kembali beberapa saat lagi":"Anda melakukan kegagalan Login ".$_SESSION['login']." kali");
			}
		}
	}
	else if($_GET['timeout']) $error="Session anda telah habis !!!";
	else if($_GET['ed']) $error="Password anda berhasil diubah !!!";
	
	if($_SESSION['user']) {
		$i=0;
		$query=queryDb("select h.id_menu_sub,replace(s.nama,'_',' ') namaSub,o.folder,s.page,o.nama nama from t_user u
							inner join t_akses_menu_sub h on h.id_akses=u.id_akses
							inner join t_menu_sub s on s.id_menu_sub=h.id_menu_sub and s.status='1'
							inner join t_menu o on o.id_menu=s.id_menu and o.status='1'
						where u.id_user='".$_SESSION['user']."' order by o.id_menu,s.id_menu_sub");
		while($fetch=mysql_fetch_array($query)) {
			if($fetch['nama']!=$tempOtoritas) {
				$i++;
				$menu.=(($i>1)?"</div>":"")."<div onclick=\"menuSub('divSub".$i."',getHeightChild('divSub".$i."'))\"><span>$fetch[nama]</span></div><div id=\"divSub".$i."\" class=\"sMenu\">";
			}
			
			$menu.="<a href=\"".$fetch['folder']."/".$fetch['page']."\" title=\"$fetch[namaSub]\" target=\"isi\">$fetch[namaSub]</a>";
			$tempOtoritas=$fetch['nama'];
		}
		$menu.=($i>0)?"</div>":"";
		
		$goFrame="pilih_unit.php";
		
	}
	else {
		$error=($error)?$error:"Demi keamanan bersama 3 kali Login gagal,<br />Anda dapat login kembali 30 menit setelahnya.";		
	}

	//----------------------------------------------------------Penjabaran Class Templete.
	
	require "includes/config_functionclass.php";			//Pemanggilan fungsi class templete.
	
	$tpl = new template;
	$tpl->define_theme("index.html");
	$tpl->define_theme((($_SESSION['user'])?"main.html":"login.html"),"{PANEL}");
	$tpl->define_tag("PANEL >> ADMINISTRATOR","{TITLE}");
	$tpl->define_tag($menu,"{MENU}");
	$tpl->define_tag($goFrame,"{GOFRAME}");
	$tpl->define_tag($error,"{ERROR}");
	$tpl->parse();
	$tpl->printproses();
	
	//------------------------------------------------------------------------------------------------------------------------------------------------------
?>