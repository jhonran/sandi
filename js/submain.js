// JavaScript Document
var _URI=getUrlVars();

function listFocusLoop(e) {
	var c=e.childNodes;
	
	for(var i=0;i<c.length;i++) {
		if(c[i].tagName=="UL" && c[i].className!="disabled") c[i].className="";
		else if(c[i].tagName=="DIV") listFocusLoop(c[i]);
	}
}

function listFocus(e,v) {
	if(e.className!="disabled") {
		
		listFocusLoop(elm('list'));
		
		e.className="selected";
		
		var param=v.split(",");
		var param2=false;
		
		for(var key in param) {
			param2=param[key].split("=");
			
			if(elm('btn-'+param2[0])) {
			if(param2[1]==1) {
					elm('btn-'+param2[0]).className=(elm('btn-'+param2[0]))?"icon-"+param2[0]:"icon-"+param2[0]+" disabled";
					_URI[param2[0]]=e.id;
				}
				else {
					elm('btn-'+param2[0]).className="icon-"+param2[0]+" disabled";
					_URI[param2[0]]=false;
				}
			}
			
		}
	}
}

function goAddress(v,u) {
	var url=(u)?u:"";
	var param=v.split(",");
	var output="";
	
	for(var key in param) {
		if(_URI[param[key]]) output+=param[key]+"="+bs64_e(_URI[param[key]])+"&";
	}
	self.document.location.href=url+"?"+output;
}

function goFrame(v,u) {
	var url=(u)?u:"";
	var param=v.split(",");
	var output="";
	
	for(var key in param) {
		if(_URI[param[key]]) output+=param[key]+"="+bs64_e(_URI[param[key]])+"&";
	}
	elm('fInput').src=url+"?"+output;
}

function delData(v,b) {
	console.log(b);
	var b=(b)?b:"del";
	
	if(_URI[b]) {
		if(confirm("Anda yakin untuk menghapus data ini!!!")) {
			if(elm('load')) showFade('load');
			goAddress(v);
		}
	}
	else alert("Data belum dipilih");
}

function editData(v,f,b) {
	var b=(b)?b:"edit";
	
	if(_URI[b]) {
		var param=v.split(",");
		
		for(var key in param) {
			if(elm(param[key])) {
				if(elm(param[key]).tagName=="INPUT" || elm(param[key]).tagName=="TEXTAREA" || elm(param[key]).tagName=="DIV" || elm(param[key]).tagName=="IMG") {
					if(elm(param[key]).type=="radio" || elm(param[key]).type=="checkbox") elm(param[key]).checked=elm(param[key]+"-"+_URI[b]).value*1;
					else if(elm(param[key]).tagName=="TEXTAREA") tinymce.get(param[key]).setContent(elm(param[key]+"-"+_URI[b]).value);
					else if(elm(param[key]).tagName=="DIV") elm(param[key]).innerHTML=elm(param[key]+"-"+_URI[b]).value;
					else if(elm(param[key]).tagName=="IMG") elm(param[key]).src=elm(param[key]+"-"+_URI[b]).value;
					else elm(param[key]).value=elm(param[key]+"-"+_URI[b]).value;
				}
				else if(elm(param[key]).tagName=="SELECT") {
					for(var i=0;i<elm(param[key]).length;i++) { elm(param[key]).options[i].selected=(elm(param[key]).options[i].value==elm(param[key]+"-"+_URI[b]).value)?true:false; }
				}
			}
			else if(document.getElementsByName(param[key])) {
				var e=document.getElementsByName(param[key]);
				var sparam=elm(param[key]+"-"+_URI[b]).value.split(",");
				
				if(e[0].type=="radio" || e[0].type=="checkbox") {
					var check_sum=0;
					
					for(var i=0;i<e.length;i++) {
						e[i].checked=false;
						for(var skey in sparam) {
							if(e[i].value==sparam[skey]) {
								e[i].checked=true;
								check_sum+=1;
								break;
							}						
						}
					}
					
					if(elm(param[key]+"All")) elm(param[key]+"All").checked=(i===check_sum); 
											   
				}
				else if(e[0].tagName=="IMG") {
					for(var i=0;i<e.length;i++) {
						e[i].src=sparam[i];
					}
				}
				else {
					for(var i=0;i<e.length;i++) {
						e[i].value=sparam[i];
					}
				}
			}
		
			if(elm("err"+param[key])) elm("err"+param[key]).innerHTML="";
		}
		
		showFade('input');
		if(elm(param[0])) elm(param[0]).focus();
		
		if(f!='') return eval(f);
	}
	else alert("Data belum dipilih");
}

function newData(v,f) {
	var param=v.split(",");
	
	for(var key in param) {
		if(elm(param[key])) {
			if(elm(param[key]).tagName=="INPUT" || elm(param[key]).tagName=="TEXTAREA" || elm(param[key]).tagName=="DIV" || elm(param[key]).tagName=="IMG") {
				if(elm(param[key]).type=="radio" || elm(param[key]).type=="checkbox") elm(param[key]).checked="";
				else if(elm(param[key]).tagName=="TEXTAREA") tinymce.get(param[key]).setContent("");
				else if(elm(param[key]).tagName=="DIV") elm(param[key]).innerHTML="";
				else if(elm(param[key]).tagName=="IMG") elm(param[key]).src="";
				else elm(param[key]).value="";
			}
			else if(elm(param[key]).tagName=="SELECT") {
				elm(param[key]).options[0].selected=true;
			}
		}
		else if(document.getElementsByName(param[key])) {
			var e=document.getElementsByName(param[key]);
			
			if(e[0].type=="radio" || e[0].type=="checkbox") {
				for(var i=0;i<e.length;i++) {
					e[i].checked=false;
				}
				
				if(elm(param[key]+"All")) elm(param[key]+"All").checked=false;
			}
			else if(e[0].tagName=="IMG") {
				for(var i=0;i<e.length;i++) {
					e[i].src="";
				}
			}
			else {
				for(var i=0;i<e.length;i++) {
					e[i].value="";
				}
			}
		}
		
		if(elm("err"+param[key])) elm("err"+param[key]).innerHTML="";
	}
	
	showFade('input');
	if(elm(param[0])) elm(param[0]).focus();
	
	if(f!='') return eval(f);
}

function goOrder(v,e) {
	_URI['order']=(_URI['order']==(e+" asc"))?(e+" desc"):(e+" asc"); 
	goAddress(v);
	return false;	
}

var framePopupTimer=new Array();

function framePopupFocus(f){
	if(elm('f'+f).src) showFade('f'+f);
}

function framePopupBlur(f){
	if(!elm('i'+f).value && elm('n'+f).value) setTimeout("elm('n"+f+"').focus()",100);
	else setTimeout("hideFade('f"+f+"')",300);
}

function goFramePopup(e,f,u,v,q,r){
	clearTimeout(framePopupTimer[f]);
	hideFade('img'+f);
	hideFade('f'+f);
	
	if(e.value.length>1){		
		framePopupTimer[f]=setTimeout("showFade('img"+f+"');elm('f"+f+"').src='../popup/"+u+".php?s="+bs64_e(e.value)+"&v="+((v)?bs64_e(v):'')+"&q="+((q)?bs64_e(q):'')+"&r="+((r)?bs64_e(r):'')+"';",400)
	}
	elm('i'+f).value="";
}

window.addEventListener("keydown",function(e) {
	if(e.ctrlKey || e.metaKey) {
		var nm=String.fromCharCode(e.which).toLowerCase();
		
		if(nm=='n' || nm=='e' || nm=='d') {
			e.preventDefault();
			if (navigator.userAgent.indexOf('MSIE') !== -1 || navigator.appVersion.indexOf('Trident/') > 0) { //hack for ie
				alert("Please, use the print button located on the top bar");
				return;
			}
		
			switch (nm) {
				case 'n':
					var el=elm('btn-new');
					if(el && typeof el.onclick == "function") setTimeout("elm('btn-new').onclick.apply(elm('btn-new'))",10);
					break;
				case 'e':
					var el=elm('btn-edit');
					if(el && typeof el.onclick == "function") setTimeout("elm('btn-edit').onclick.apply(elm('btn-edit'))",10);
					break;
				case 'd':
					var el=elm('btn-del');
					if(el && typeof el.onclick == "function") setTimeout("elm('btn-del').onclick.apply(elm('btn-del'))",10);
					break;	
			}
		}
    }
});

window.addEventListener("keyup",function(e) {		
	if(e.keyCode === 13) {  } //enter	
	if(e.keyCode === 27) { setTimeout("tutup_form_input()",10); } //esc
});

function tutup_form_input() {
	if(elm('tTutup') && typeof elm('tTutup').onclick == "function") elm('tTutup').onclick.apply(elm('tTutup'));
	if(elm('tTutup1') && typeof elm('tTutup1').onclick == "function") elm('tTutup1').onclick.apply(elm('tTutup1'));
	if(elm('tTutup2') && typeof elm('tTutup2').onclick == "function") elm('tTutup2').onclick.apply(elm('tTutup2'));
	if(elm('tTutup3') && typeof elm('tTutup3').onclick == "function") elm('tTutup3').onclick.apply(elm('tTutup3'));
	if(elm('tTutup4') && typeof elm('tTutup4').onclick == "function") elm('tTutup4').onclick.apply(elm('tTutup4'));
	if(elm('tTutup5') && typeof elm('tTutup5').onclick == "function") elm('tTutup5').onclick.apply(elm('tTutup5'));
}





