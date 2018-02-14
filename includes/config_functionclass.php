<?php
	// ***************************************************************
	// File template.php
	// File untuk keperluan pemisahan desain tampilan, script dan data
	// ***************************************************************
	
	class template {
		var $CONTENT="{MAIN}";
		var $THEME=array();
		var $TAGS=array();
			
		function define_theme($filename,$themename="{MAIN}") { $this->THEME[$themename]=$filename; }
		
		function define_tag($varname,$tagname) { $this->TAGS[$tagname]=$varname; }
	
		function parse() {			
			while(list($key,$val)=each($this->THEME)) {
				$this->CONTENT=preg_replace("/".$key."/",implode("",file($val)),$this->CONTENT);
			}
			
			if(is_array($this->TAGS)) {
				while(list($key,$val)=each($this->TAGS)) {
					$this->CONTENT=preg_replace("/".$key."/",$val,$this->CONTENT);
				}
			}
		}
		
		function printproses() { echo $this->CONTENT; }
	}
?>