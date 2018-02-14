// JavaScript Document
function menuPanel(v,s) {
	var w=elm('dMenu').offsetWidth;
	var l=elm('dMenu').offsetLeft;
	var l2=l+w;
	
	if(v) {
		if(v==1) {
			if(l<(w*-1)) {
				elm('dMenu').style.left=(w*-1)+"px";
				elm('isi').style.paddingLeft="0px";
				
				elm('imgMenuPanel').src="images/btnArrowR.png";					
			}
			else {
				elm('dMenu').style.left=(l-s)+"px";
				//elm('isi').style.paddingLeft=(l2-s)+"px";
				
				setTimeout("menuPanel("+v+","+(s*2)+")",50);
			}
		}
		else if(v==2) {			
			if(l>0) {
				elm('dMenu').style.left="0px";
				elm('isi').style.paddingLeft=w+"px";
				
				elm('imgMenuPanel').src="images/btnArrowL.png";
			}
			else {
				elm('dMenu').style.left=(l+s)+"px";
				//elm('isi').style.paddingLeft=(l2+s)+"px";
				
				setTimeout("menuPanel("+v+","+(s*2)+")",50);
			}
		}
		
	}
	else {
		if(l==0) setTimeout("menuPanel(1,4)",50);
		else if(l==(w*-1)) setTimeout("menuPanel(2,4)",50);
	}
}

function logOut() {
	if(!confirm("Anda yakin untuk keluar !!!")) return false;
}