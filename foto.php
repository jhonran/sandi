<?php
	header("Content-type:image/jpeg");
	header("Cache-Control:no-cache");
	header("Pragma: no-cache");
	if(file_exists("../files/".$_GET['sid'].".jpg")) readfile("../files/".$_GET['sid'].".jpg");
?>