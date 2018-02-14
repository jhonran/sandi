// JavaScript Document
function elm(obj) {
	var theObj;
	if (document.getElementById) {
		if (typeof obj == "string") return document.getElementById(obj);
		else return obj.style;
	}
	else if (document.all) {
		if (typeof obj == "string") return document.all(obj);
		else return obj.style;
	}
	return null;
}

function cssValue(e,v) {
	return e.style.v || window.getComputedStyle(e).getPropertyValue(v);
}

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
						vars[key] = bs64_d(value);
					});
    return vars;
}

function getHeightChild(e) {
	var h=0;
	for(var i=0;i<elm(e).childNodes.length;i++) {
		if(elm(e).childNodes[i].offsetHeight>0) {
			h+=elm(e).childNodes[i].offsetHeight;
		}
	}
	return h;
}

function valnominal(angka) {
	 var isi = angka.value;
	 var hasil='';
	 
	 for(var i=0;i<isi.length;i++)
	 {
		var nilai = isi.substr(i,1);
		if(parseFloat(nilai) || parseFloat(nilai)==0 || (i==0 && nilai=='-'))
		{
			if(i==0 && nilai=='-') hasil=nilai;
			else if(i==1 && hasil==0) hasil=nilai;
			else if(i==1 && nilai==0 && hasil=='-') hasil=hasil;
			else hasil+= nilai;
		}
	 }
	 
	 var jumlah=Math.floor(hasil.length/3);
	 var sisa=hasil.length%3;
	 var hasill='';
	 for(var i=-1;i<jumlah;i++) {
		 if(i==-1) hasill+=hasil.substr(0,sisa);
		 else if(hasill=='' || hasill=='-') hasill+=hasil.substr(sisa+(3*i),3);
		 else hasill+="."+hasil.substr(sisa+(3*i),3);
	 }
	 angka.value = hasill;
}

function shownominal(nilai) {
	 var value=(nilai+'').replace(/\./g,"");
	 
	 var negative="";
	 if(parseInt(value)<0) {
		negative="-";
	 	value=(value+'').replace(/-/g,"");
	 }
	 var jumlah=Math.floor(value.length/3);
	 var sisa=value.length%3;
	 var hasil="";
	 for(var i=-1;i<jumlah;i++) {
		 if(i==-1) hasil+=value.substr(0,sisa);
		 else if(hasil=='') hasil+=value.substr(sisa+(3*i),3);
		 else hasil+="."+value.substr(sisa+(3*i),3);
	 }
	 return negative+hasil;
}

function clearRupiah(nilai) {
	return parseFloat((nilai.replace(/Rp/g,"").replace(/\./g,"").replace(/,-/g,""))*1);
}

function checkAll(stat,e) {
	var x=document.getElementsByName(e);
	for(var i=0;i<x.length;i++) { x[i].checked=stat; }
}

function uncheckAll(e) {
	var stat=true;
	var x=document.getElementsByName(e);
	
	for(var i=0;i<x.length;i++) {
		if(!x[i].checked) stat=false;
	}
	
	if(elm(e+"All")) elm(e+"All").checked=stat;
}

function checkSubAll(stat,e,a,c) {
	var x=document.getElementsByName(e);
	
	for(var i=0;i<x.length;i++) {
		if(x[i].alt==a) {
			x[i].checked=stat;
			if(x[i].id) checkSubAll(stat,e,x[i].id,1);
		}
	}
	
	if(!c) {
		if(elm(a).alt) uncheckSubAll(e,elm(a).alt);
		else uncheckAll(e);
	}
}

function uncheckSubAll(e,a) {
	var stat=true;
	var x=document.getElementsByName(e);
	for(var i=0;i<x.length;i++) {
		if(x[i].alt==a && !x[i].checked) stat=false;
	}
	
	if(elm(a)) elm(a).checked=stat;
	if(elm(a).alt) uncheckSubAll(e,elm(a).alt);
	else uncheckAll(e);
}

function bs64_e(v) {
	//return encodeURIComponent(window.btoa(unescape(v)));
	return encodeURIComponent(window.btoa(v));
}

function bs64_d(v) {
	//return escape(window.atob(decodeURIComponent(v)));
	return window.atob(decodeURIComponent(v));
}

var windowPopUp=new Array();

function popUp(u,w2,h2,t2) {
	var h=(h2)?h2:(screen.availHeight-80);
	var w=(w2)?w2:(screen.availWidth-80);
	var t=(t2)?t2:"";
	
	var top=(screen.availHeight-h)/2;
	var left=(screen.availWidth-w)/2;
	
	if(windowPopUp[t]) windowPopUp[t].close();
	windowPopUp[t]=window.open(u,'','toolbar=0,scrollbars=yes,status=0,width='+w+',height='+h+',top='+top+',left='+left);
	windowPopUp[t].focus();
	return false;
}