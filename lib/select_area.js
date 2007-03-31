function change_country(nameform) {
	document.forms[nameform].elements["IdRegion"].value="0" ;
	document.forms[nameform].elements["IdCity"].value="0" ;
	document.forms[nameform].elements["action"].value="change_country" ;
	document.forms[nameform].submit() ;
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
