function change_country(nform) {
//	document.forms[nform].elements["IdRegion"].value="0" ;
	document.forms[nform].elements["IdCity"].value="0" ;
	document.forms[nform].elements["action"].value="change_country" ;
	document.forms[nform].submit() ;
//	document.write("c'est ici") ; ;
}	


function change_region(nameform) {
	document.forms[nameform].elements["IdCity"].value="0" ;
	document.forms[nameform].elements["action"].value="change_region" ;
	document.forms[nameform].submit() ;
}

function change_city(nameform) {
	document.forms[nameform].elements["action"].value="change_city" ;
	document.forms[nameform].submit() ;
}		
