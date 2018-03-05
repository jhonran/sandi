// JavaScript Document
var timerEfek=new Array();

function showFade(e,f) {		
	if(!f) f="";
	
	if(cssValue(elm(e),'display')=="none") {
		elm(e).style.opacity = 0;
		elm(e).style.filter = "alpha(opacity=0)";
		//if(elm(e).childNodes.length) elm(e).style.height=elm(e).childNodes[0].offsetHeight+"px";
		elm(e).style.display="inline-block";		
	}
	
	var sisa=0;
	sisa=Math.round(cssValue(elm(e),'opacity')*20)+4;
	
	clearTimeout(timerEfek[e]);
	timerEfek[e]=false;	
		
	if(sisa>=20) {
		elm(e).style.opacity = 1;
		//elm(e).style.filter = "alpha(opacity=100)";
		if(f!='') return eval(f);
	}
	else {
		elm(e).style.opacity = parseFloat(sisa/20);
		//elm(e).style.filter = 'alpha(opacity=' + sisa*5 + ')';
		
		timerEfek[e]=setTimeout("showFade('"+e+"',\""+f+"\")",50);
	}
}

function hideFade(e,f) {
	if(!f) f="";
	var sisa=0;
	sisa=Math.round(cssValue(elm(e),'opacity')*20)-4;
	clearTimeout(timerEfek[e]);
	timerEfek[e]=false;

	if(sisa<=0) { 
		elm(e).style.opacity = 0;
		//elm(e).style.filter = "alpha(opacity=0)";
		elm(e).style.display="none";
		if(f!='') return eval(f);
	}
	else {
		elm(e).style.opacity = parseFloat(sisa/20);
		//elm(e).style.filter = 'alpha(opacity=' + sisa*5 + ')';
		
		timerEfek[e]=setTimeout("hideFade('"+e+"',\""+f+"\")",50);
	}
	
}

function menuSub(e,h,v,s) {
	var hOut=elm(e).offsetHeight;
	
	if(v) {
		if(v==1) {
			if(hOut>h) {
				elm(e).style.height="auto";
				//elm(e).style.height=getHeightChild(e)+"px";			
			}
			else {
				elm(e).style.height=(hOut+s)+"px";
				
				setTimeout("menuSub('"+e+"',"+h+","+v+","+(s*2)+")",50);
			}
		}
		else if(v==2) {
			if((hOut-s)<0) {
				elm(e).style.height="0px";
			}
			else {
				elm(e).style.height=(hOut-s)+"px";
				
				setTimeout("menuSub('"+e+"',"+h+","+v+","+(s*2)+")",50);
			}
		}
		
	}
	else {
		if(hOut==0) setTimeout("menuSub('"+e+"',"+h+",1,4)",50);
		else if(hOut==h) setTimeout("menuSub('"+e+"',"+h+",2,4)",50);
	}
}