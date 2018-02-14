var isShift=false;
var seperator = "/";

function DateFormat(txt , keyCode) {
	if(keyCode==16) isShift = true;
	//Validate that its Numeric
	if(((keyCode >= 48 && keyCode <= 57) || keyCode == 8 || keyCode <= 37 || keyCode <= 39 || (keyCode >= 96 && keyCode <= 105)) && isShift == false) {
		if ((txt.value.length == 2 || txt.value.length==5) && keyCode != 8) {
			txt.value += seperator;
		}
		return true;
	}
	else {
		return false;
	}
}

/*function ValidateDate(txt, keyCode) {
	if(keyCode==16) isShift = false;
	
	var val=txt.value;
	var lblmesg = document.getElementById("<%=lblMesg.ClientID%>") ;
	
	if(val.length == 10) {
		var splits = val.split("/");
		var dt = new Date(splits[1] + "/" + splits[0] + "/" + splits[2]);
		
		//Validation for Dates
		if(dt.getDate()==splits[0] && dt.getMonth()+1==splits[1] && dt.getFullYear()==splits[2]) {
			lblmesg.style.color="green";
			lblmesg.innerHTML = "Valid Date";
		}
		else {
			lblmesg.style.color="red";
			lblmesg.innerHTML = "InValid Date";
			return;
		} 
		
		//Range Validation
		if(txt.id.indexOf("txtRange") != -1) RangeValidation(dt);
		
		//BirthDate Validation
		if(txt.id.indexOf("txtBirthDate") != -1) BirthDateValidation(dt)
	}
	else if(val.length<10) {
		lblmesg.style.color="blue";
		lblmesg.innerHTML = "Required dd/mm/yy format. Slashes will come up automatically.";
	}
}

function RangeValidation(dt) {
	var startrange = new Date(Date.parse("01/01/1900"));
	var endrange = new Date(Date.parse("12/31/2099"));
	var lblmesg = document.getElementById("<%=lblMesg.ClientID%>");
	
	if (dt<startrange || dt>endrange) {
		lblmesg.style.color="red";
		lblmesg.innerHTML = "Date should be between 01/01/1900 and 31/12/2099";
	}
}

function BirthDateValidation(dt) {
	var dtToday = new Date();
	var pastDate = new Date(Date.parse(dtToday.getMonth()+"/"+dtToday.getDate()+"/"+parseInt(dtToday.getFullYear()-100)));
	var lblmesg = document.getElementById("<%=lblMesg.ClientID%>");
	
	if (dt<pastDate || dt>=dtToday) {
		lblmesg.style.color="red";
		lblmesg.innerHTML = "Invalid BirthDate";
	}
	else {
		lblmesg.style.color="green";
		lblmesg.innerHTML = "Valid BirthDate";
	}
}*/