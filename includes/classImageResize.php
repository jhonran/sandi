<?php
class img_opt {
	var $max_width;
	var $max_height;
	var $path;
	var $img;
	var $new_width;
	var $new_height;
	var $mime;
	var $image;
	var $width;
	var $height;

	function max_width($width) {
		$this->max_width = $width;
	}
	
	function max_height($height) {
		$this->max_height = $height;
	}
	
	function image_path($path) {
		$this->path = $path;
	}
	
	function get_mime() {
		$img_data = getimagesize($this->path);
		$this->mime = $img_data['mime'];
	}
	
	function create_image()	{
		switch($this->mime)	{
			case 'image/jpeg':
				$this->image = imagecreatefromjpeg($this->path);
			break;
			case 'image/gif':
				$this->image = imagecreatefromgif($this->path);
			break;
			case 'image/png':
				$this->image = imagecreatefrompng($this->path);
			break;
		}
	}
	
	function image_resize($quality=75,$mask=false)	{
		set_time_limit(120);
		$this->get_mime();
		$this->create_image();
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
		$this->set_dimension();
		$image_resized = imagecreatetruecolor($this->new_width,$this->new_height);
		imagecopyresampled($image_resized, $this->image, 0, 0, 0, 0, $this->new_width, $this->new_height,$this->width, $this->height);
		
		if($mask) {
			$maskPath = "../files/mask.png";
			if(file_exists($maskPath)) {
				$maskImg = imagecreatefrompng($maskPath);
				$maskSize = getimagesize($maskPath);
				imagecopyresampled($image_resized, $maskImg,round(($this->new_width-$maskSize[0])/2),round(($this->new_height-$maskSize[1])/2), 0, 0,$maskSize[0],$maskSize[1],$maskSize[0],$maskSize[1]);
				imagedestroy($maskImg);
			}
		}
		
		imagejpeg($image_resized,$this->path,$quality);
		imagedestroy($this->image);
		imagedestroy($image_resized);
	}
		
	//######### FUNCTION FOR RESETTING DEMENSIONS OF IMAGE ###########
	function set_dimension() {
		$this->new_width =$this->max_width;
		$this->new_height = round(($this->height*$this->max_width)/$this->width);
		/*if($this->width/$this->max_width > $this->height/$this->max_height) {
			$this->new_width = $this->max_width;
			$this->new_height = ($this->height*$this->max_width)/$this->width;
		}
		else {
			$this->new_width = ($this->width*$this->max_height)/$this->height;
			$this->new_height = $this->max_height;
		}*/
		
		if($this->new_width*$this->new_height > $this->width*$this->height) {
			$this->new_width = $this->width;
			$this->new_height = $this->height;
		}
		
		
			
		/*if($this->width==$this->height)	$case = 'first'; // sama sisi
		else if($this->width > $this->height) $case = 'second'; // landscape
		else $case = 'third'; // portrait
		
		if($this->width>$this->max_width && $this->height>$this->max_height) $cond = 'first';
		else if($this->width>$this->max_width && $this->height<=$this->max_height) $cond = 'first';
		else $cond = 'third';
						
		switch($case) {
			case 'first':
				$this->new_width = $this->max_width;
				$this->new_height = $this->max_height;
			break;
			case 'second':
				$ratio = $this->width/$this->height;
				$amount = $this->width - $this->max_width;
				$this->new_width = $this->width - $amount;
				$this->new_height = $this->height - ($amount/$ratio);
			break;
			case 'third':
				$ratio = $this->height/$this->width;
				$amount = $this->height - $this->max_height;
				$this->new_height = $this->height - $amount;
				$this->new_width = $this->width - ($amount/$ratio);
			break;
		}*/
	}
}
?>