<?php	
	session_start();
	
	$_STYLE['header']['background']['image']="url(../images/header.png) left top no-repeat";
	$_STYLE['header']['background']['color']="#cad5fd"; //222222 or a44d01
	$_STYLE['menu']['background']['color']="#e77817";
	$_STYLE['menu']['button']['color']="#aaaaaa";
	$_STYLE['menu']['button']['colorHover']="#333333";
	$_STYLE['isi']['background']['image']="url(../images/body.jpg) center repeat";
	$_STYLE['isi']['background']['color']="#ffffff";
	
	header("Content-type:text/css");
	header("Cache-Control:public");
?>
@charset "utf-8";
/* CSS Document */

body { 
	padding:80px 0 0 15px;
	background:<?=$_STYLE['header']['background']['image']?> <?=$_STYLE['header']['background']['color']?>;
}
#dAkun {
	position:absolute;
    top:0;
    right:0;
    width:340px;
    height:82px;
    background:url(../images/sandi.png) center no-repeat;    
	vertical-align:bottom;
}
#dAkun div {
	font-weight:bold;
	color:#555555;
    text-decoration:underline;
    text-align:right;
    line-height:1.4;
    width:335px;
    height:77px;
	vertical-align:bottom;
	display:table-cell;
}
#dPage { 
	position:relative;
	border-left:ridge 3px #93a5b3;
	border-top:ridge 3px #93a5b3;
	border-top-left-radius:10px;
	-moz-border-top-left-radius:10px;
	-webkit-border-top-left-radius:10px;
	-o-border-top-left-radius:10px;
	-ms-border-top-left-radius:10px;
	overflow:hidden;
    background-color:<?=(($_SESSION['user'])?$_STYLE['isi']['background']['color']:"none")?>;
}
#dMenu { 
	position:absolute;
	width:220px;
	top:0;
	left:0;
	background:url(../images/listMenu.png) repeat-y right <?=$_STYLE['menu']['background']['color']?>;
}
#dMenu #imgMenuPanel { position:absolute;top:10px;left:220px;cursor:pointer; }
#dMenu div.cMenu { overflow:auto;padding:10px;text-align:left;height:100%; }
#dMenu div.cMenu div { 
	padding-left:15px;
    height:30px;
    line-height:26px;
    font-weight:bold;
	cursor:pointer;
	border:2px solid rgba(255,255,255,1);
	background:url(../images/listMenuButton.png) repeat-x top <?=$_STYLE['menu']['button']['color']?>;
	margin-top:5px;
	
	border-radius:6px;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
	-o-border-radius:6px;
	-ms-border-radius:6px;
}
#dMenu div.cMenu div:hover { background-color:<?=$_STYLE['menu']['button']['colorHover']?>; }
#dMenu div.cMenu div.sMenu { 
	padding:0;
    position:relative;
    margin:0;background:none;
    border:none;height:0;
    overflow:hidden;
	border-radius:0;
	-moz-border-radius:0;
	-webkit-border-radius:0;
	-o-border-radius:0;
	-ms-border-radius:0;
}
#dMenu div.cMenu div.sMenu:nth-child(2) { height:auto; }
#dMenu div.cMenu div span { display:block;height:26px;line-height:inherit;background:url(../images/menuArrow.png) right center no-repeat;margin-right:10px; }
#dMenu div.cMenu div a { display:block;padding-left:25px;border-bottom:1px dotted rgba(255,255,255,0.5);color:#ffffff;background:url(../images/icon.png) 0px -357px no-repeat;height:28;line-height:28px;font-weight:bold; }
#dMenu div.cMenu div a:last-child { border:none; }
#dMenu div.cMenu div a:hover { background-color:rgba(0,0,0,0.3); }
#dMenu div.cMenu div:first-child a { background:url(../images/menuHome.png) right center no-repeat;color:inherit;padding:0;margin-right:8px;height:26px;line-height:26px; }
#dMenu div.cMenu div:last-child a { background:none;color:#990000;padding:0;height:26px;line-height:26px; }
#isi {
	overflow:auto;
	padding-left:220px;
	background:<?=$_STYLE['isi']['background']['image']?> <?=$_STYLE['isi']['background']['color']?>;
}