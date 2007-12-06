function SelectMsg(theState){
	if (theState == "ALL"){
		setTo = new Boolean(true);
	} else {
		setTo = new Boolean(false);
	}

	for (i = 0; i < document.forms['msgform'].length; i++){
		if (document.forms['msgform'].elements[i].type=="checkbox"){
			document.forms['msgform'].elements[i].checked = setTo.valueOf();
		}
	}
}

function submitformsub(actionToDo){
	document.forms['msgform'].elements['actiontodo'].value = actionToDo;
	document.forms['msgform'].submit();
}